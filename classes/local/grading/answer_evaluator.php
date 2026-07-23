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
 *   equivalent punctuation such as apostrophe variants unified. The reduction
 *   itself is script-specific (see script_handler, script_handler_manager);
 *   for scripts that do not reduce to Latin letters this way, an elangscript
 *   subplugin supplies the transliteration.
 *
 * Regardless of which algorithm a gap is configured with, the evaluator always
 * determines the finest classification it can (see grading_result): an exact
 * match against a gap configured as word-recognised is still reported as
 * RESULTSTATE_EXACT, so reports can distinguish precision from mere
 * correctness even when the gap itself is lenient.
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

    /** @var int Defence-in-depth cap on input handed to preg_match; the primary length limit is enforced by the caller. */
    private const MAX_REGEX_INPUT_LENGTH = 1000;

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
     * @return grading_result The evaluation outcome
     */
    public function evaluate(
        string $solution,
        string $gradingalgorithm,
        array $gapanswers,
        string $language,
        string $responsetext
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
                    && $handler->normalise_for_word_recognised($responsetext)
                        === $handler->normalise_for_word_recognised($candidate->text)
            ) {
                $best = grading_result::RESULTSTATE_WORDRECOGNIZED;
                $bestcandidateid = $candidate->id;
            }
        }

        $accepted = $this->is_accepted($best, $gradingalgorithm);

        return new grading_result($best, $accepted, $bestcandidateid);
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
     * @param string $pattern The stored pattern (without PCRE delimiters)
     * @param string $responsetext The learner's raw response
     * @return bool Whether the pattern matched
     */
    private function matches_regex(string $pattern, string $responsetext): bool {
        if (mb_strlen($responsetext, 'UTF-8') > self::MAX_REGEX_INPUT_LENGTH) {
            return false;
        }

        $result = @preg_match('#' . $pattern . '#u', $responsetext);

        return $result === 1;
    }
}
