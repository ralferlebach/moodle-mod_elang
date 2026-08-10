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

/**
 * Builds the transcript of an exercise version for download.
 *
 * The transcript is the full text of the exercise (every cue's transcript, in
 * order) — the same text learners read, with nothing masked. It is assembled
 * here as plain text; the transcript page wraps it as a PDF or a text file.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class transcript_exporter {
    /**
     * Build the transcript of a version as an ordered list of paragraphs: every
     * non-empty cue transcript, in order. This is the shared basis for every
     * output format.
     *
     * @param int $versionid The version to export
     * @return string[] The transcript paragraphs, in order
     */
    public function paragraphs(int $versionid): array {
        global $DB;

        $cues = $DB->get_records('elang_cue', ['versionid' => $versionid], 'sortorder ASC, id ASC', 'id, transcript');

        $paragraphs = [];
        foreach ($cues as $cue) {
            $text = trim((string) $cue->transcript);
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
     * @return string The transcript as plain text
     */
    public function plain_text(int $versionid): string {
        return implode("\n\n", $this->paragraphs($versionid));
    }
}
