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

namespace mod_elang\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use mod_elang\local\import\gap_syntax_parser;
use mod_elang\local\import\subtitle_parser;

/**
 * Parse a WebVTT or SubRip subtitle file into cue segments for the editor.
 *
 * This only previews the parse result — it never writes to the database. The
 * editor shows the cues, the teacher marks gaps, and the whole draft is then
 * persisted through save_draft_version. Access is gated on mod/elang:manage.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class preview_import extends external_api {
    use authoring_helper;

    /**
     * Describe the parameters this function accepts.
     *
     * @return external_function_parameters The description of this function's parameters.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'versionid' => new external_value(PARAM_INT, 'Id of the draft version being edited, for authorisation'),
            'subtitles' => new external_value(PARAM_RAW, 'Raw WebVTT or SubRip file content'),
            'parsegaps' => new external_value(
                PARAM_BOOL,
                'When true, mod_elang 1.x inline gap markers ([word] with help, {word} without) are stripped from each '
                    . 'transcript and returned as gaps instead of being kept as literal text',
                VALUE_DEFAULT,
                false
            ),
        ]);
    }

    /**
     * Parse the subtitle content and return the resulting cues and warnings.
     *
     * @param int $versionid Id of the draft version being edited
     * @param string $subtitles Raw WebVTT or SubRip file content
     * @param bool $parsegaps Whether to recognise V1 inline gap markers
     * @return array The parsed cues, a count and any warnings, see execute_returns()
     */
    public static function execute(int $versionid, string $subtitles, bool $parsegaps = false): array {
        [
            'versionid' => $versionid,
            'subtitles' => $subtitles,
            'parsegaps' => $parsegaps,
        ] = self::validate_parameters(self::execute_parameters(), [
            'versionid' => $versionid,
            'subtitles' => $subtitles,
            'parsegaps' => $parsegaps,
        ]);

        self::require_manage_version($versionid);

        $parsed = (new subtitle_parser())->parse($subtitles);

        $cues = array_map(static function (\stdClass $cue) use ($parsegaps): array {
            $transcript = (string) $cue->transcript;
            $gaps = [];

            if ($parsegaps) {
                $marked = gap_syntax_parser::parse($transcript);
                $transcript = $marked->transcript;
                $gaps = array_map(static function (\stdClass $gap): array {
                    return [
                        'charstart' => (int) $gap->charstart,
                        'charlength' => (int) $gap->charlength,
                        'solution' => (string) $gap->solution,
                        'hintsallowed' => (bool) $gap->hintsallowed,
                    ];
                }, $marked->gaps);
            }

            return [
                'sortorder' => (int) $cue->sortorder,
                'starttime' => (int) $cue->starttime,
                'endtime' => (int) $cue->endtime,
                'transcript' => $transcript,
                'transcriptformat' => FORMAT_PLAIN,
                'gaps' => $gaps,
            ];
        }, $parsed->cues);

        return [
            'cues' => $cues,
            'cuecount' => count($cues),
            'warnings' => array_values($parsed->warnings),
        ];
    }

    /**
     * Describe the structure this function returns.
     *
     * @return external_single_structure The description of this function's return value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cues' => new external_multiple_structure(new external_single_structure([
                'sortorder' => new external_value(PARAM_INT, 'Sort order within the version'),
                'starttime' => new external_value(PARAM_INT, 'Start time in milliseconds'),
                'endtime' => new external_value(PARAM_INT, 'End time in milliseconds'),
                'transcript' => new external_value(PARAM_RAW, 'Transcript text parsed from the cue'),
                'transcriptformat' => new external_value(PARAM_INT, 'Moodle text format constant, always plain text'),
                'gaps' => new external_multiple_structure(new external_single_structure([
                    'charstart' => new external_value(PARAM_INT, 'Codepoint offset of the gap in the cleaned transcript'),
                    'charlength' => new external_value(PARAM_INT, 'Codepoint length of the gap'),
                    'solution' => new external_value(PARAM_RAW, 'Solution text found inside the marker'),
                    'hintsallowed' => new external_value(PARAM_BOOL, 'True for a [help-allowed] marker, false for {no-help}'),
                ]), 'Gaps recognised from V1 inline markers; empty unless parsegaps was set', VALUE_DEFAULT, []),
            ]), 'The parsed cues, in file order'),
            'cuecount' => new external_value(PARAM_INT, 'Number of cues parsed'),
            'warnings' => new external_multiple_structure(
                new external_value(PARAM_RAW, 'A non-fatal parse warning'),
                'Non-fatal problems that caused a block to be skipped',
                VALUE_DEFAULT,
                []
            ),
        ]);
    }
}
