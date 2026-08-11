<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_elang\local\grading;

/**
 * Evaluates a learner's response against a gap's solution and answer variants.
 *
 * Two grading algorithms are offered, chosen per gap via elang_gap.gradingalgorithm:
 *
 * - ALGORITHM_EXACT ("komplett-richtig"): only a character-perfect match counts
 *   as correct, diacritics, case and apostrophes included. Unicode encoding
 *   form is still canonicalised (NFC), because two visually identical strings
 *   must compare equal regardless of how their accents happen to be encoded —
 *   that is a technical necessity, not leniency.
 * - ALGORITHM_WORDRECOGNIZED ("Wort erkannt"): a match counts as correct once
 *   the response reduces to the same base form as the solution — case-folded,
 *   with diacritics stripped or transliterated to Latin base letters, and with
 *   equivalent punctuation such as apostrophe variants unified — OR, failing
 *   that exact-after-reduction comparison, once the Jaro similarity between
 *   the two reduced forms reaches the activity's configured $jarothreshold
 *   (elang.jarothreshold, 0..1, migrated from version 1's jaroDistance
 *   setting; 1.0 — the default — requires the reduced forms to be identical,
 *   which reproduces the pre-Jaro behaviour exactly). The reduction itself is
 *   script-specific (see script_handler, script_handler_manager); for scripts
 *   that do not reduce to Latin letters this way, an elangscript subplugin
 *   supplies the transliteration.
 *
 * Regardless of which algorithm a gap is configured with, the evaluator always
 * determines the finest classification it can (see grading_result): an exact
 * match against a gap configured as word-recognised is still reported as
 * RESULTSTATE_EXACT, so reports can distinguish precision from mere
 * correctness even when the gap itself is lenient. A Jaro-only match (reduced
 * forms not identical, but similar enough to clear the threshold) is reported
 * as RESULTSTATE_WORDRECOGNIZED, the same as an identical-after-reduction
 * match — the two are not distinguished in resultstate, only in whether a
 * literal reduced-form match was found first.
 *
 * Regular-expression answer variants (elang_gapanswer.isregex) are matched
 * with preg_match and, on a match, are always treated as an exact result:
 * regular expressions are an alternative matching mode for authors with
 * mod/elang:useregex, not a third leniency tier.
 *
 * This class assumes the caller has already enforced a hard length limit on
 * $responsetext (see elang_gap.maxlength and the activity-wide default); it
 * additionally refuses to run regular expressions against implausibly long
 * input as a defence-in-depth measure against catastrophic backtracking, not
 * as the primary control.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class answer_evaluator {
    /** @var string Gap configured to require a character-perfect match. */
    public const ALGORITHM_EXACT = 'exact';

    /** @var string Gap configured to accept a word-recognised match as correct. */
    public const ALGORITHM_WORDRECOGNIZED = 'wordrecognized';

    /** @var float Default Jaro-similarity threshold: 1.0 requires the reduced forms to be identical (no fuzziness). */
    public const DEFAULT_JARO_THRESHOLD = 1.0;

    /** @var int Defence-in-depth cap on input handed to preg_match; the primary length limit is enforced by the caller. */
    private const MAX_REGEX_INPUT_LENGTH = 1000;

    /**
     * A control character (0x01) used as the PCRE delimiter for stored regex
     * answer variants. Author-supplied patterns are free text and may
     * legitimately contain '#', '/' or any other printable delimiter, so a
     * printable delimiter risks being swallowed by the pattern itself; a
     * control character that can never appear in an intentional pattern
     * cannot collide.
     *
     * @var string
     */
    private const REGEX_DELIMITER = "\x01";

    /**
     * Check whether an author-supplied regex answer variant compiles under the
     * same delimiter and flags used at grading time. The authoring layer calls
     * this so an uncompilable pattern is rejected when the draft is saved,
     * rather than being stored and then silently never matching at grade time.
     *
     * @param string $pattern The stored pattern, without PCRE delimiters
     * @return bool True when the pattern compiles, false otherwise
     */
    public static function is_valid_regex(string $pattern): bool {
        return @preg_match(self::REGEX_DELIMITER . $pattern . self::REGEX_DELIMITER . 'u', '') !== false;
    }

    /** @var script_handler_manager */
    private $handlermanager;

    /**
     * Construct the evaluator.
     *
     * @param script_handler_manager $handlermanager Locates the script_handler for the activity's language
     */
    public function __construct(script_handler_manager $handlermanager) {
        $this->handlermanager = $handlermanager;
    }

    /**
     * Evaluate a response against a gap's solution and accepted variants.
     *
     * @param string $solution The primary model answer (elang_gap.solution)
     * @param string $gradingalgorithm One of the ALGORITHM_* constants (elang_gap.gradingalgorithm)
     * @param \stdClass[] $gapanswers Records with ->id, ->answer, ->isregex (elang_gapanswer rows for this gap)
     * @param string $language Activity language/script code (elang.language)
     * @param string $responsetext The learner's raw response
     * @param float $jarothreshold Minimum Jaro similarity (0..1) between reduced forms for a
     *        wordrecognised-algorithm gap to accept a non-identical reduction as a match
     *        (elang.jarothreshold); 1.0 (the default) requires an identical reduction
     * @return grading_result The evaluation outcome
     */
    public function evaluate(
        string $solution,
        string $gradingalgorithm,
        array $gapanswers,
        string $language,
        string $responsetext,
        float $jarothreshold = self::DEFAULT_JARO_THRESHOLD
    ): grading_result {
        if (trim($responsetext) === '') {
            return new grading_result(grading_result::RESULTSTATE_EMPTY, false, null);
        }

        $handler = $this->handlermanager->get_handler_for_language($language);
        $candidates = $this->build_candidates($solution, $gapanswers);

        $best = grading_result::RESULTSTATE_INCORRECT;
        $bestcandidateid = null;

        foreach ($candidates as $candidate) {
            if ($candidate->isregex) {
                if ($this->matches_regex($candidate->text, $responsetext)) {
                    // A regex match is conclusive: it cannot be improved on by
                    // another candidate, so stop looking.
                    $best = grading_result::RESULTSTATE_EXACT;
                    $bestcandidateid = $candidate->id;
                    break;
                }
                continue;
            }

            if (
                $best !== grading_result::RESULTSTATE_EXACT
                    && $handler->normalise_for_exact($responsetext) === $handler->normalise_for_exact($candidate->text)
            ) {
                $best = grading_result::RESULTSTATE_EXACT;
                $bestcandidateid = $candidate->id;
                continue;
            }

            if (
                $best === grading_result::RESULTSTATE_INCORRECT
                    && $this->is_word_recognised_match($handler, $responsetext, $candidate->text, $jarothreshold)
            ) {
                $best = grading_result::RESULTSTATE_WORDRECOGNIZED;
                $bestcandidateid = $candidate->id;
            }
        }

        $accepted = $this->is_accepted($best, $gradingalgorithm);

        return new grading_result($best, $accepted, $bestcandidateid);
    }

    /**
     * Decide whether two texts count as a word-recognised match.
     *
     * A match is either an identical reduced form (the pre-Jaro behaviour,
     * always reached first since it is the strictest possible passing case),
     * or, failing that, a Jaro similarity between the two reduced forms that
     * reaches $jarothreshold. A threshold of 1.0 makes the Jaro branch
     * unreachable (similarity 1.0 already implies the strings are identical),
     * so callers that never configure a threshold see exactly the previous
     * behaviour.
     *
     * @param script_handler $handler The script handler for the activity's language
     * @param string $responsetext The learner's raw response
     * @param string $candidatetext The candidate solution or answer variant
     * @param float $jarothreshold Minimum Jaro similarity (0..1) to accept a non-identical reduction
     * @return bool Whether this counts as a word-recognised match
     */
    private function is_word_recognised_match(
        script_handler $handler,
        string $responsetext,
        string $candidatetext,
        float $jarothreshold
    ): bool {
        $reducedresponse = $handler->normalise_for_word_recognised($responsetext);
        $reducedcandidate = $handler->normalise_for_word_recognised($candidatetext);

        if ($reducedresponse === $reducedcandidate) {
            return true;
        }

        if ($jarothreshold >= 1.0) {
            // A threshold of 1.0 can only ever be satisfied by an identical
            // reduction, already handled above — skip the (pointless)
            // similarity computation entirely.
            return false;
        }

        return self::jaro_similarity($reducedresponse, $reducedcandidate) >= $jarothreshold;
    }

    /**
     * Compute the Jaro similarity between two strings.
     *
     * Jaro similarity (not Jaro-Winkler: no extra weight for a shared
     * prefix) is a value between 0.0 (no similarity) and 1.0 (identical),
     * based on the number of matching characters within a bounded window and
     * the number of transpositions among them. Operates on Unicode
     * codepoints, not bytes, so multi-byte characters are compared as single
     * units rather than being split across byte boundaries.
     *
     * @param string $a First string, already reduced/normalised by the caller
     * @param string $b Second string, already reduced/normalised by the caller
     * @return float Jaro similarity, 0.0 to 1.0
     */
    public static function jaro_similarity(string $a, string $b): float {
        if ($a === $b) {
            return 1.0;
        }

        $achars = preg_split('//u', $a, -1, PREG_SPLIT_NO_EMPTY);
        $bchars = preg_split('//u', $b, -1, PREG_SPLIT_NO_EMPTY);
        $alen = count($achars);
        $blen = count($bchars);

        if ($alen === 0 || $blen === 0) {
            return 0.0;
        }

        $matchdistance = intdiv(max($alen, $blen), 2) - 1;
        if ($matchdistance < 0) {
            $matchdistance = 0;
        }

        $amatched = array_fill(0, $alen, false);
        $bmatched = array_fill(0, $blen, false);
        $matches = 0;

        for ($i = 0; $i < $alen; $i++) {
            $start = max(0, $i - $matchdistance);
            $end = min($i + $matchdistance + 1, $blen);

            for ($j = $start; $j < $end; $j++) {
                if ($bmatched[$j] || $achars[$i] !== $bchars[$j]) {
                    continue;
                }
                $amatched[$i] = true;
                $bmatched[$j] = true;
                $matches++;
                break;
            }
        }

        if ($matches === 0) {
            return 0.0;
        }

        $transpositions = 0;
        $bpointer = 0;
        for ($i = 0; $i < $alen; $i++) {
            if (!$amatched[$i]) {
                continue;
            }
            while (!$bmatched[$bpointer]) {
                $bpointer++;
            }
            if ($achars[$i] !== $bchars[$bpointer]) {
                $transpositions++;
            }
            $bpointer++;
        }
        $transpositions = intdiv($transpositions, 2);

        return (
            ($matches / $alen)
            + ($matches / $blen)
            + (($matches - $transpositions) / $matches)
        ) / 3;
    }

    /**
     * Decide whether a resultstate counts as correct for a gap's configured algorithm.
     *
     * @param string $resultstate One of the RESULTSTATE_* constants found by evaluate()
     * @param string $gradingalgorithm One of the ALGORITHM_* constants
     * @return bool Whether the response is accepted as correct
     */
    private function is_accepted(string $resultstate, string $gradingalgorithm): bool {
        if ($resultstate === grading_result::RESULTSTATE_EXACT) {
            return true;
        }

        if ($resultstate === grading_result::RESULTSTATE_WORDRECOGNIZED) {
            return $gradingalgorithm === self::ALGORITHM_WORDRECOGNIZED;
        }

        return false;
    }

    /**
     * Build the ordered list of candidates a response is compared against.
     *
     * @param string $solution The primary model answer
     * @param \stdClass[] $gapanswers Additional accepted variants
     * @return \stdClass[] Objects with ->id, ->text, ->isregex, in comparison order
     */
    private function build_candidates(string $solution, array $gapanswers): array {
        $primary = new \stdClass();
        $primary->id = null;
        $primary->text = $solution;
        $primary->isregex = false;

        $candidates = [$primary];

        foreach ($gapanswers as $gapanswer) {
            $candidate = new \stdClass();
            $candidate->id = (int) $gapanswer->id;
            $candidate->text = (string) $gapanswer->answer;
            $candidate->isregex = !empty($gapanswer->isregex);
            $candidates[] = $candidate;
        }

        return $candidates;
    }

    /**
     * Match a response against a regular-expression candidate, failing safe.
     *
     * Uses a control-character delimiter (see REGEX_DELIMITER) so that a
     * stored pattern containing '#', '/' or any other printable character
     * can never be misread as the end of the pattern. A pattern that fails
     * to compile is treated as "no match" for this candidate rather than
     * aborting the whole evaluation — an author error in one answer variant
     * must not make every other variant (or the plain solution) unusable —
     * but is reported through debugging() rather than silently swallowed, so
     * it is visible in logs and during development instead of only
     * surfacing as a confusing "always wrong" gap.
     *
     * @param string $pattern The stored pattern (without PCRE delimiters)
     * @param string $responsetext The learner's raw response
     * @return bool Whether the pattern matched
     */
    private function matches_regex(string $pattern, string $responsetext): bool {
        if (\core_text::strlen($responsetext) > self::MAX_REGEX_INPUT_LENGTH) {
            return false;
        }

        $result = @preg_match(self::REGEX_DELIMITER . $pattern . self::REGEX_DELIMITER . 'u', $responsetext);

        if ($result === false) {
            debugging(
                'mod_elang: invalid regex answer variant could not be compiled: ' . preg_last_error_msg(),
                DEBUG_DEVELOPER
            );
            return false;
        }

        return $result === 1;
    }
}
