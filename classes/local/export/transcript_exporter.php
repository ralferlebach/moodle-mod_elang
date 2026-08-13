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

namespace mod_elang\local\export;

use mod_elang\local\domain\transcript_masker;

/**
 * Builds the transcript of an exercise version for download.
 *
 * The transcript is the full text of the exercise (every cue's transcript, in
 * order). It comes in two shapes: a learner worksheet with every gap blanked
 * out, and a teacher solution copy with the full text. The worksheet is the
 * default because elang_cue.transcript stores the gap word literally inside the
 * text (elang_gap.charstart/charlength index into it), so exporting it raw
 * would hand a learner the solutions the player hides — the same reason the
 * player never receives an unmasked transcript. Only a caller that has checked
 * mod/elang:exportsolution passes masked = false.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class transcript_exporter {
    /**
     * The visible placeholder a gap is replaced with in a learner worksheet. A
     * fixed width is used deliberately so the blank never leaks the length of
     * the solution it hides.
     *
     * @var string
     */
    private const GAP_PLACEHOLDER = '_____';

    /**
     * Build the transcript of a version as an ordered list of paragraphs: every
     * non-empty cue transcript, in order. This is the shared basis for every
     * output format.
     *
     * @param int $versionid The version to export
     * @param bool $masked When true (the default) every gap is blanked out for a
     *      learner worksheet; only pass false after checking mod/elang:exportsolution
     * @return string[] The transcript paragraphs, in order
     */
    public function paragraphs(int $versionid, bool $masked = true): array {
        global $DB;

        $cues = $DB->get_records('elang_cue', ['versionid' => $versionid], 'sortorder ASC, id ASC', 'id, transcript');
        $gapsbycue = $masked ? $this->gaps_by_cue(array_keys($cues)) : [];

        $paragraphs = [];
        foreach ($cues as $cue) {
            $text = (string) $cue->transcript;
            if ($masked) {
                $text = $this->blank_out_gaps($text, $gapsbycue[$cue->id] ?? []);
            }
            $text = trim($text);
            if ($text !== '') {
                $paragraphs[] = $text;
            }
        }

        return $paragraphs;
    }

    /**
     * Build the plain-text transcript of a version: every non-empty cue
     * transcript in order, separated by blank lines.
     *
     * @param int $versionid The version to export
     * @param bool $masked When true (the default) every gap is blanked out for a
     *      learner worksheet; only pass false after checking mod/elang:exportsolution
     * @return string The transcript as plain text
     */
    public function plain_text(int $versionid, bool $masked = true): string {
        return implode("\n\n", $this->paragraphs($versionid, $masked));
    }

    /**
     * Load every gap of the given cues in a single query and group them by cue
     * id, so a masked export needs one gap query rather than one per cue.
     *
     * @param int[] $cueids The cue ids to load gaps for
     * @return array<int, \stdClass[]> Gap records keyed by cue id
     */
    private function gaps_by_cue(array $cueids): array {
        global $DB;

        if (empty($cueids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($cueids);
        $gaps = $DB->get_records_select(
            'elang_gap',
            "cueid $insql",
            $params,
            'charstart ASC, id ASC',
            'id, cueid, charstart, charlength, gapkey'
        );

        $grouped = [];
        foreach ($gaps as $gap) {
            $grouped[(int) $gap->cueid][] = $gap;
        }

        return $grouped;
    }

    /**
     * Replace every gap range in a cue transcript with the fixed blank
     * placeholder. Reuses transcript_masker so the codepoint-safe splicing and
     * overlap guard are defined in exactly one place, then swaps its
     * player-facing token for the printable blank.
     *
     * @param string $transcript The cue's full, unredacted transcript
     * @param \stdClass[] $gaps The cue's gap records (charstart, charlength, gapkey)
     * @return string The transcript with every gap blanked out
     */
    private function blank_out_gaps(string $transcript, array $gaps): string {
        if (empty($gaps)) {
            return $transcript;
        }

        $masked = transcript_masker::mask($transcript, $gaps);

        return (string) preg_replace('/\{\{gap:[A-Za-z0-9_.\-]+\}\}/', self::GAP_PLACEHOLDER, $masked);
    }
}
