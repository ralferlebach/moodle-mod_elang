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
 * Start or resume the current user's attempt at a language exercise.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class start_attempt extends external_api {
    use attempt_helper;

    /**
     * Describe the parameters this function accepts.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module id of the elang activity'),
        ]);
    }

    /**
     * Start a new attempt, or return the learner's existing in-progress one.
     *
     * @param int $cmid Course module id
     * @return array The started or resumed attempt, see execute_returns()
     */
    public static function execute(int $cmid): array {
        global $DB, $USER;

        ['cmid' => $cmid] = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
        ]);

        [, $cm] = get_course_and_cm_from_cmid($cmid, 'elang');
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/elang:attempt', $context);

        $elang = $DB->get_record('elang', ['id' => $cm->instance], '*', MUST_EXIST);

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $published = $versionmanager->get_published((int) $elang->id);
        if ($published === null) {
            throw new \moodle_exception('error:nopublishedversion', 'mod_elang');
        }

        $attempt = self::get_attempt_manager()->start_attempt(
            (int) $elang->id,
            (int) $USER->id,
            (int) $published->id
        );

        return [
            'attemptid' => (int) $attempt->id,
            'versionid' => (int) $attempt->versionid,
            'state' => $attempt->state,
            'attemptnumber' => (int) $attempt->attemptnumber,
            'totalgaps' => (int) $attempt->totalgaps,
            'answeredgaps' => (int) $attempt->answeredgaps,
        ];
    }

    /**
     * Describe the structure this function returns.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'attemptid' => new external_value(PARAM_INT, 'Attempt id'),
            'versionid' => new external_value(PARAM_INT, 'Id of the exercise version being attempted'),
            'state' => new external_value(PARAM_ALPHA, 'Attempt state: inprogress, finished or abandoned'),
            'attemptnumber' => new external_value(PARAM_INT, 'Sequential attempt number for this user and activity'),
            'totalgaps' => new external_value(PARAM_INT, 'Total number of gaps in the attempted version'),
            'answeredgaps' => new external_value(PARAM_INT, 'Number of gaps answered so far'),
        ]);
    }
}
