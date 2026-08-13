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
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Publish a draft version, making it the activity's live content.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class publish_version extends external_api {
    use authoring_helper;

    /**
     * Describe the parameters this function accepts.
     *
     * @return external_function_parameters The description of this function's parameters.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'versionid' => new external_value(PARAM_INT, 'Id of the draft version to publish'),
        ]);
    }

    /**
     * Validate and publish a draft version.
     *
     * @param int $versionid Id of the draft version to publish
     * @return array The published version, see execute_returns()
     */
    public static function execute(int $versionid): array {
        global $USER;

        ['versionid' => $versionid] = self::validate_parameters(self::execute_parameters(), [
            'versionid' => $versionid,
        ]);

        self::require_manage_version($versionid);

        // Publish with validation on: an incoherent draft is refused with the
        // collected problems rather than shipped to learners.
        $published = self::get_version_manager()->publish($versionid, (int) $USER->id, true);

        return [
            'versionid' => (int) $published->id,
            'versionnumber' => (int) $published->versionnumber,
            'status' => $published->status,
        ];
    }

    /**
     * Describe the structure this function returns.
     *
     * @return external_single_structure The description of this function's return value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'versionid' => new external_value(PARAM_INT, 'Id of the now-published version'),
            'versionnumber' => new external_value(PARAM_INT, 'Sequential version number within the activity'),
            'status' => new external_value(PARAM_ALPHA, 'Version status, published on success'),
        ]);
    }
}
