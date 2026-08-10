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

namespace mod_elang\local\import;

/**
 * Recognises mod_elang 1.x inline gap syntax in a subtitle transcript.
 *
 * V1 exercises were authored by marking gaps directly in the subtitle text:
 * `[word]` created a gap whose learners may request help, `{word}` created a
 * gap without help (verified against a real V1 instance, see
 * Migration_V1_V2.md). This parser strips those markers from a single cue
 * transcript and reports each gap's solution and its character range in the
 * CLEANED transcript, so an importing editor can materialise real V2 gaps
 * instead of leaving the brackets behind as literal text.
 *
 * Character offsets are Unicode codepoint offsets computed via
 * core_text::strlen() — the same convention transcript_masker and
 * v1_cue_parser use, so a parsed gap can be handed straight to the rest of
 * the authoring pipeline.
 *
 * Deliberately tolerant: markers never nest in V1, so the first closing
 * bracket ends a gap; an unmatched opening bracket, an empty pair (`[]`) or a
 * pair spanning a line break is left in the text untouched rather than
 * guessed about.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class gap_syntax_parser {
    /**
     * Extract V1-style gap markers from one cue transcript.
     *
     * @param string $transcript The raw cue transcript, possibly containing
     *        `[gap]` and `{gap}` markers
     * @return object An object with ->transcript (the text with all
     *         recognised markers stripped) and ->gaps, an array of objects
     *         with ->charstart, ->charlength (codepoint offsets into the
     *         cleaned transcript), ->solution and ->hintsallowed (true for
     *         `[...]`, false for `{...}`)
     */
    public static function parse(string $transcript): object {
        $pairs = ['[' => ']', '{' => '}'];
        $cleaned = '';
        $gaps = [];
        $length = \core_text::strlen($transcript);
        $position = 0;

        while ($position < $length) {
            $char = \core_text::substr($transcript, $position, 1);

            if (!isset($pairs[$char])) {
                $cleaned .= $char;
                $position++;
                continue;
            }

            $solution = self::find_marker($transcript, $position, $pairs[$char]);
            if ($solution === null) {
                // Unmatched, empty or line-spanning marker: keep it literal.
                $cleaned .= $char;
                $position++;
                continue;
            }

            $gap = new \stdClass();
            $gap->charstart = \core_text::strlen($cleaned);
            $gap->charlength = \core_text::strlen($solution);
            $gap->solution = $solution;
            $gap->hintsallowed = ($char === '[');
            $gaps[] = $gap;

            // The solution text stays in the transcript at its range — the
            // same convention transcript_masker expects — only the brackets
            // are stripped.
            $cleaned .= $solution;
            $position += \core_text::strlen($solution) + 2;
        }

        $result = new \stdClass();
        $result->transcript = $cleaned;
        $result->gaps = $gaps;

        return $result;
    }

    /**
     * Return the marker content starting after an opening bracket, or null
     * when the marker is unusable (no closing bracket before the end or a
     * line break, or empty content).
     *
     * @param string $transcript The full transcript being scanned
     * @param int $openposition Codepoint position of the opening bracket
     * @param string $close The matching closing bracket character
     * @return string|null The inner solution text, or null to treat the
     *         opening bracket as literal text
     */
    private static function find_marker(string $transcript, int $openposition, string $close): ?string {
        $length = \core_text::strlen($transcript);
        $inner = '';

        for ($cursor = $openposition + 1; $cursor < $length; $cursor++) {
            $char = \core_text::substr($transcript, $cursor, 1);
            if ($char === $close) {
                return $inner === '' ? null : $inner;
            }
            if ($char === "\n") {
                return null;
            }
            $inner .= $char;
        }

        return null;
    }
}
