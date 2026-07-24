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

namespace mod_elang\local\migration;

/**
 * Reconstructs a V1 (mod_elang 1.x) cue's transcript and gaps from its
 * elang_cues.json column.
 *
 * Deliberately scoped to only the parts of a V1 cue that are fully
 * determined by its own json content — this is one mechanical piece of the
 * eventual migration (Migration_V1_V2.md), not the migration itself.
 * Everything this class does NOT decide is exactly the set of things that
 * genuinely need either a source-code check against V1's locallib.php or a
 * product decision before a real migrator can be written; see the "offene
 * Punkte" in Migration_V1_V2.md chapter 3 for the current list — in
 * particular this class has no opinion on elang_gap.gradingalgorithm
 * (derived from the ACTIVITY-wide usecasesensitive/usetransliteration/
 * jaroDistance options, not from anything per-gap) or on how a gap's `help`
 * flag becomes elang_gaphint rows.
 *
 * elang_cues.title is NOT the source for the reconstructed transcript: it is
 * already a "…"-masked preview (the same purpose transcript_masker.php
 * serves in V2), not the original text. The real transcript only exists as
 * the ordered concatenation of every segment's `content` in `json`.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class v1_cue_parser {
    /**
     * Parse one elang_cues.json value into a reconstructed transcript and
     * the gaps within it.
     *
     * Character offsets (charstart/charlength) are Unicode codepoint offsets
     * into the reconstructed transcript, computed via core_text::strlen() as
     * segments accumulate — the same convention transcript_masker.php uses
     * to interpret elang_gap.charstart/charlength, so a parsed gap can be
     * handed straight to it without any further conversion.
     *
     * @param string $json The raw elang_cues.json value (a JSON array of
     *        {"type":"text","content":...} and {"type":"input","content":...,
     *        "order":int,"help":bool,"link":?string} segments)
     * @return object{transcript: string, gaps: object[]} gaps entries have
     *         ->charstart, ->charlength, ->solution, ->linkurl (string, empty
     *         when absent), ->hintsallowed (bool, from the segment's `help`)
     * @throws \moodle_exception if $json is not valid JSON or not an array
     */
    public static function parse(string $json): object {
        $segments = json_decode($json, false);
        if (!is_array($segments)) {
            throw new \moodle_exception(
                'error:invalidv1cuejson',
                'mod_elang',
                '',
                null,
                'elang_cues.json did not decode to a JSON array'
            );
        }

        $transcript = '';
        $gaps = [];

        foreach ($segments as $segment) {
            $type = $segment->type ?? '';
            $content = (string) ($segment->content ?? '');

            if ($type === 'input') {
                $gap = new \stdClass();
                $gap->charstart = \core_text::strlen($transcript);
                $gap->charlength = \core_text::strlen($content);
                $gap->solution = $content;
                $gap->linkurl = (string) ($segment->link ?? '');
                $gap->hintsallowed = !empty($segment->help);
                $gaps[] = $gap;
            }

            // A text segment's content becomes part of the transcript as-is;
            // an input segment's content is ALSO part of the transcript (the
            // gap word is literally still there at its charstart/charlength
            // range) — this mirrors how transcript_masker.php expects to
            // find the solution text still present at that range in V2's own
            // elang_cue.transcript, so the same convention is reproduced here
            // rather than inventing a different one for migrated content.
            $transcript .= $content;
        }

        $result = new \stdClass();
        $result->transcript = $transcript;
        $result->gaps = $gaps;

        return $result;
    }
}
