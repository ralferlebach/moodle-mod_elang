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
            'cues' => new external_multiple_structure(self::cue_structure(), 'The draft\'s full cue list'),
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
