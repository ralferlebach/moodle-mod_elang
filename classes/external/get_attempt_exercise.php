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
 * Return the static shape of the version an attempt is pinned to: counts and
 * identifiers only, no cue/gap content and no solutions. A player fetches the
 * actual content in pages via get_attempt_cues.
 *
 * This is deliberately attempt-scoped rather than activity-scoped. An
 * in-progress attempt is immutably bound to the version it was started on
 * (elang_attempt.versionid), so a learner who resumes an attempt must keep
 * seeing that exact version even after a teacher has published a newer one.
 * Reading the currently *published* version here instead would let the
 * player render content from a version the learner's saved responses do not
 * belong to — the write side (submit_response) already rejects gaps that are
 * not part of the attempt's version, so the two sides would disagree. Binding
 * every read to elang_attempt.versionid makes that mismatch structurally
 * impossible. A separate teacher-facing preview API can expose arbitrary
 * versions later; this learner API never does.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_attempt_exercise extends external_api {
    use attempt_helper;

    /**
     * Describe the parameters this function accepts.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'attemptid' => new external_value(PARAM_INT, 'Id of the in-progress attempt to read'),
        ]);
    }

    /**
     * Return the attempt version's identifiers and counts.
     *
     * @param int $attemptid Attempt id
     * @return array See execute_returns()
     */
    public static function execute(int $attemptid): array {
        global $DB;

        ['attemptid' => $attemptid] = self::validate_parameters(self::execute_parameters(), [
            'attemptid' => $attemptid,
        ]);

        $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);
        $context = self::require_attempt_ownership($attempt);
        require_capability('mod/elang:attempt', $context);

        $elang = $DB->get_record('elang', ['id' => $attempt->elangid], '*', MUST_EXIST);
        $version = $DB->get_record('elang_version', ['id' => $attempt->versionid], '*', MUST_EXIST);

        $totalcues = (int) $DB->count_records('elang_cue', ['versionid' => $attempt->versionid]);
        $totalgaps = (int) $DB->count_records_sql(
            'SELECT COUNT(g.id)
               FROM {elang_gap} g
               JOIN {elang_cue} c ON c.id = g.cueid
              WHERE c.versionid = ?',
            [$attempt->versionid]
        );

        return [
            'attemptid' => (int) $attempt->id,
            'elangid' => (int) $elang->id,
            'versionid' => (int) $attempt->versionid,
            'language' => (string) $elang->language,
            'totalcues' => $totalcues,
            'totalgaps' => $totalgaps,
            'contenthash' => (string) $version->contenthash,
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
            'elangid' => new external_value(PARAM_INT, 'Activity instance id'),
            'versionid' => new external_value(PARAM_INT, 'Id of the version this attempt is pinned to'),
            'language' => new external_value(PARAM_RAW, "The activity's language/script code"),
            'totalcues' => new external_value(PARAM_INT, 'Total number of cues in the attempted version'),
            'totalgaps' => new external_value(PARAM_INT, 'Total number of gaps in the attempted version'),
            'contenthash' => new external_value(
                PARAM_RAW,
                'Content hash of the attempted version, usable as a client-side cache key'
            ),
        ]);
    }
}
