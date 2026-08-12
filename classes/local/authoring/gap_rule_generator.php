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

namespace mod_elang\local\authoring;

/**
 * Generates gap definitions from a rule instead of hand-placed markers.
 *
 * The 2.1 "rule-based gaps" feature lets an author say, for example, "blank out
 * this vocabulary list wherever it appears" or "blank out every third word"
 * rather than selecting each word by hand. This class is the pure foundation:
 * given a cue transcript and a rule it returns the resulting gap spans as
 * codepoint offsets (the same shape gap_syntax_parser produces), which the
 * authoring layer then turns into elang_gap rows. It does not touch the database
 * or the editor UI.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class gap_rule_generator {
    /** Rule: blank out each whole-word occurrence of a given word list. */
    public const RULE_WORDS = 'words';

    /** Rule: blank out every nth word. */
    public const RULE_EVERY_NTH = 'everynth';

    /**
     * Generate gap spans for a transcript from a rule.
     *
     * @param string $transcript The cue transcript.
     * @param array $rule The rule: a 'type' plus its parameters (see the RULE_* constants).
     * @return \stdClass[] Gap spans with ->charstart, ->charlength (codepoint offsets) and ->solution,
     *                     ordered by position and never overlapping.
     */
    public function generate(string $transcript, array $rule): array {
        $words = $this->tokenize($transcript);

        switch ($rule['type'] ?? '') {
            case self::RULE_WORDS:
                $selected = $this->select_by_word_list($words, $rule);
                break;
            case self::RULE_EVERY_NTH:
                $selected = $this->select_every_nth($words, $rule);
                break;
            default:
                throw new \coding_exception('Unknown gap rule type: ' . ($rule['type'] ?? '(none)'));
        }

        $gaps = [];
        foreach ($selected as $word) {
            $gap = new \stdClass();
            $gap->charstart = $word->charstart;
            $gap->charlength = $word->charlength;
            $gap->solution = $word->text;
            $gaps[] = $gap;
        }

        return $gaps;
    }

    /**
     * Split a transcript into word tokens with codepoint offsets.
     *
     * A word is a run of letters, optionally continued by marks, digits,
     * apostrophes or hyphens (so "l'enfant" and "well-known" stay whole). The
     * regex reports byte offsets, which are converted to codepoint offsets so
     * the spans line up with the server's mb_substr view.
     *
     * @param string $transcript The transcript to tokenize.
     * @return \stdClass[] Tokens with ->charstart, ->charlength and ->text.
     */
    private function tokenize(string $transcript): array {
        $tokens = [];
        $matches = [];
        preg_match_all(
            "/[\\p{L}\\p{M}][\\p{L}\\p{M}\\p{Nd}'\\x{2019}\\-]*/u",
            $transcript,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        foreach ($matches[0] as $match) {
            [$text, $byteoffset] = $match;
            $token = new \stdClass();
            $token->charstart = \core_text::strlen(substr($transcript, 0, $byteoffset));
            $token->charlength = \core_text::strlen($text);
            $token->text = $text;
            $tokens[] = $token;
        }

        return $tokens;
    }

    /**
     * Select the tokens whose text matches an entry in the rule's word list.
     *
     * @param \stdClass[] $words The transcript tokens.
     * @param array $rule The rule; 'words' is the target list, 'casesensitive' defaults to false.
     * @return \stdClass[] The matching tokens.
     */
    private function select_by_word_list(array $words, array $rule): array {
        $casesensitive = !empty($rule['casesensitive']);
        $targets = [];
        foreach ($rule['words'] ?? [] as $target) {
            $target = trim((string) $target);
            if ($target !== '') {
                $targets[$this->normalise($target, $casesensitive)] = true;
            }
        }
        if (empty($targets)) {
            return [];
        }

        return array_values(array_filter($words, function (\stdClass $word) use ($targets, $casesensitive): bool {
            return isset($targets[$this->normalise($word->text, $casesensitive)]);
        }));
    }

    /**
     * Select every nth token, starting from an optional offset.
     *
     * @param \stdClass[] $words The transcript tokens.
     * @param array $rule The rule; 'n' is the interval (at least 1), 'offset' the zero-based start.
     * @return \stdClass[] The selected tokens.
     */
    private function select_every_nth(array $words, array $rule): array {
        $n = max(1, (int) ($rule['n'] ?? 1));
        $offset = max(0, (int) ($rule['offset'] ?? 0));

        $selected = [];
        for ($i = $offset; $i < count($words); $i += $n) {
            $selected[] = $words[$i];
        }

        return $selected;
    }

    /**
     * Normalise a word for comparison, lowercasing unless the rule is case
     * sensitive.
     *
     * @param string $text The word.
     * @param bool $casesensitive Whether case matters.
     * @return string The normalised word.
     */
    private function normalise(string $text, bool $casesensitive): string {
        return $casesensitive ? $text : \core_text::strtolower($text);
    }
}
