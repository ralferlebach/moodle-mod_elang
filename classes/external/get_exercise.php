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
 * Return the static shape of the currently published exercise version:
 * counts and identifiers only, no cue/gap content and no solutions. A
 * player fetches the actual content in pages via get_cues.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_exercise extends external_api {
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
     * Return the published version's identifiers and counts.
     *
     * @param int $cmid Course module id
     * @return array See execute_returns()
     */
    public static function execute(int $cmid): array {
        global $DB;

        ['cmid' => $cmid] = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
        ]);

        [, $cm] = get_course_and_cm_from_cmid($cmid, 'elang');
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/elang:view', $context);

        $elang = $DB->get_record('elang', ['id' => $cm->instance], '*', MUST_EXIST);

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $published = $versionmanager->get_published((int) $elang->id);
        if ($published === null) {
            throw new \moodle_exception('error:nopublishedversion', 'mod_elang');
        }

        $totalcues = (int) $DB->count_records('elang_cue', ['versionid' => $published->id]);
        $totalgaps = (int) $DB->count_records_sql(
            'SELECT COUNT(g.id)
               FROM {elang_gap} g
               JOIN {elang_cue} c ON c.id = g.cueid
              WHERE c.versionid = ?',
            [$published->id]
        );

        return [
            'elangid' => (int) $elang->id,
            'versionid' => (int) $published->id,
            'language' => (string) $elang->language,
            'totalcues' => $totalcues,
            'totalgaps' => $totalgaps,
            'contenthash' => (string) $published->contenthash,
        ];
    }

    /**
     * Describe the structure this function returns.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'elangid' => new external_value(PARAM_INT, 'Activity instance id'),
            'versionid' => new external_value(PARAM_INT, 'Id of the currently published exercise version'),
            'language' => new external_value(PARAM_RAW, "The activity's language/script code"),
            'totalcues' => new external_value(PARAM_INT, 'Total number of cues in the published version'),
            'totalgaps' => new external_value(PARAM_INT, 'Total number of gaps in the published version'),
            'contenthash' => new external_value(
                PARAM_RAW,
                'Content hash of the published version, usable as a client-side cache key'
            ),
        ]);
    }
}
