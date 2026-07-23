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

namespace mod_elang\local\domain;

/**
 * Redacts gap ranges out of a cue's transcript.
 *
 * elang_cue.transcript stores the full original text, and
 * elang_gap.charstart/charlength are character offsets *into that text* —
 * the gap word or phrase is literally still there. Sending the raw
 * transcript to any external function response would hand the player the
 * solution outright, which Lastenheft P12 ("Player-Nutzlast enthält niemals
 * Lösungen") explicitly forbids. Every external function that returns a
 * transcript MUST run it through mask() first; nothing in this class talks
 * to the database, so it has no way to accidentally do the right thing only
 * some of the time.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class transcript_masker {
    /**
     * Replace each gap's character range in the transcript with a
     * placeholder token carrying its gapkey, so the player can find and
     * replace it with an input element without ever seeing the solution.
     *
     * Character offsets are interpreted as Unicode codepoint offsets (via
     * mb_* functions), matching how they are validated and stored when a
     * gap is authored — never raw byte offsets, which would misalign on any
     * non-ASCII transcript.
     *
     * @param string $transcript The cue's full, unredacted transcript
     * @param iterable $gaps Each element an object/array with charstart, charlength and gapkey
     * @return string The transcript with every gap range replaced by a {{gap:<gapkey>}} token
     */
    public static function mask(string $transcript, iterable $gaps): string {
        $gaps = is_array($gaps) ? $gaps : iterator_to_array($gaps);

        usort($gaps, static function ($a, $b) {
            return self::charstart_of($a) <=> self::charstart_of($b);
        });

        $masked = '';
        $cursor = 0;

        foreach ($gaps as $gap) {
            $charstart = self::charstart_of($gap);
            $charlength = self::charlength_of($gap);
            $gapkey = self::gapkey_of($gap);

            if ($charstart < $cursor) {
                // Overlapping gaps should never be authorable, but this is
                // the boundary of untrusted-ish stored data feeding a
                // player-facing response: fail loudly rather than emit a
                // transcript with solution text still exposed by a
                // miscalculated splice.
                throw new \coding_exception(
                    "Gap '{$gapkey}' starts at {$charstart}, before the end of a preceding gap ({$cursor})"
                );
            }

            $masked .= mb_substr($transcript, $cursor, $charstart - $cursor, 'UTF-8');
            $masked .= '{{gap:' . $gapkey . '}}';
            $cursor = $charstart + $charlength;
        }

        $masked .= mb_substr($transcript, $cursor, null, 'UTF-8');

        return $masked;
    }

    /**
     * Read charstart from either an object or an array gap representation.
     *
     * @param object|array $gap
     * @return int
     */
    private static function charstart_of($gap): int {
        return (int) (is_array($gap) ? $gap['charstart'] : $gap->charstart);
    }

    /**
     * Read charlength from either an object or an array gap representation.
     *
     * @param object|array $gap
     * @return int
     */
    private static function charlength_of($gap): int {
        return (int) (is_array($gap) ? $gap['charlength'] : $gap->charlength);
    }

    /**
     * Read gapkey from either an object or an array gap representation.
     *
     * @param object|array $gap
     * @return string
     */
    private static function gapkey_of($gap): string {
        return (string) (is_array($gap) ? $gap['gapkey'] : $gap->gapkey);
    }
}
