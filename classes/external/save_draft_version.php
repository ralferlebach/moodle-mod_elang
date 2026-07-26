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

/**
 * Overwrite a draft version's content with the editor's current state.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_draft_version extends external_api {
    use authoring_helper;

    /**
     * Describe the structure of one accepted-answer variant.
     *
     * @return external_single_structure
     */
    private static function answer_structure(): external_single_structure {
        return new external_single_structure([
            'sortorder' => new external_value(PARAM_INT, 'Sort order within the gap'),
            'answer' => new external_value(PARAM_RAW, 'Accepted answer variant'),
            'isregex' => new external_value(PARAM_INT, '1 if the variant is a regular expression, otherwise 0'),
        ]);
    }

    /**
     * Describe the structure of one graded hint.
     *
     * @return external_single_structure
     */
    private static function hint_structure(): external_single_structure {
        return new external_single_structure([
            'level' => new external_value(PARAM_INT, 'Hint level, counting up from 1'),
            'hinttype' => new external_value(PARAM_ALPHA, 'Hint type, e.g. text, firstletter, wordlength'),
            'hinttext' => new external_value(PARAM_RAW, 'Hint text shown to the learner', VALUE_DEFAULT, ''),
            'penalty' => new external_value(PARAM_FLOAT, 'Score penalty applied when this hint is revealed'),
        ]);
    }

    /**
     * Describe the structure of one gap.
     *
     * @return external_single_structure
     */
    private static function gap_structure(): external_single_structure {
        return new external_single_structure([
            'gapkey' => new external_value(PARAM_ALPHANUMEXT, 'Version-stable gap key'),
            'sortorder' => new external_value(PARAM_INT, 'Sort order within the cue'),
            'charstart' => new external_value(PARAM_INT, 'Character offset of the gap within the transcript'),
            'charlength' => new external_value(PARAM_INT, 'Character length of the gap'),
            'solution' => new external_value(PARAM_RAW, 'Primary model answer'),
            'gradingalgorithm' => new external_value(PARAM_ALPHA, 'exact or wordrecognized'),
            'maxlength' => new external_value(PARAM_INT, 'Per-gap response length override, 0 for none', VALUE_DEFAULT, 0),
            'linkurl' => new external_value(PARAM_URL, 'Optional supplementary link, empty for none', VALUE_DEFAULT, ''),
            'answers' => new external_multiple_structure(self::answer_structure(), 'Accepted answer variants', VALUE_DEFAULT, []),
            'hints' => new external_multiple_structure(self::hint_structure(), 'Graded hints', VALUE_DEFAULT, []),
        ]);
    }

    /**
     * Describe the parameters this function accepts.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'versionid' => new external_value(PARAM_INT, 'Id of the draft version to overwrite'),
            'expectedrevision' => new external_value(
                PARAM_INT,
                'The revision the caller last saw for this draft, for optimistic concurrency; '
                    . '-1 saves unconditionally'
            ),
            'cues' => new external_multiple_structure(new external_single_structure([
                'cuekey' => new external_value(PARAM_ALPHANUMEXT, 'Version-stable cue key'),
                'sortorder' => new external_value(PARAM_INT, 'Sort order within the version'),
                'starttime' => new external_value(PARAM_INT, 'Start time in milliseconds'),
                'endtime' => new external_value(PARAM_INT, 'End time in milliseconds'),
                'transcript' => new external_value(PARAM_RAW, 'Transcript text for the cue'),
                'transcriptformat' => new external_value(PARAM_INT, 'Moodle text format constant for the transcript'),
                'gaps' => new external_multiple_structure(self::gap_structure(), 'Gaps within the cue', VALUE_DEFAULT, []),
            ]), 'The draft\'s full cue list'),
        ]);
    }

    /**
     * Overwrite the draft's content and return its new revision.
     *
     * @param int $versionid Id of the draft version to overwrite
     * @param int $expectedrevision The revision the caller last saw, or -1 to save unconditionally
     * @param array $cues The draft's full cue list, each with nested gaps/answers/hints
     * @return array The saved draft's new revision, see execute_returns()
     */
    public static function execute(int $versionid, int $expectedrevision, array $cues): array {
        [
            'versionid' => $versionid,
            'expectedrevision' => $expectedrevision,
            'cues' => $cues,
        ] = self::validate_parameters(self::execute_parameters(), [
            'versionid' => $versionid,
            'expectedrevision' => $expectedrevision,
            'cues' => $cues,
        ]);

        self::require_manage_version($versionid);

        $version = self::get_version_manager()->save_draft_content($versionid, $cues, $expectedrevision);

        return [
            'versionid' => (int) $version->id,
            'revision' => (int) $version->revision,
        ];
    }

    /**
     * Describe the structure this function returns.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'versionid' => new external_value(PARAM_INT, 'Id of the saved draft version'),
            'revision' => new external_value(PARAM_INT, 'The draft revision after this save'),
        ]);
    }
}
