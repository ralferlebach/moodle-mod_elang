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
 * Finish an in-progress attempt.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class finish_attempt extends external_api {
    use attempt_helper;

    /**
     * Describe the parameters this function accepts.
     *
     * @return external_function_parameters The description of this function's parameters.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'attemptid' => new external_value(PARAM_INT, 'Attempt id'),
        ]);
    }

    /**
     * Finish an in-progress attempt.
     *
     * @param int $attemptid Attempt id
     * @return array The finished attempt's final state, see execute_returns()
     */
    public static function execute(int $attemptid): array {
        global $DB, $CFG;

        ['attemptid' => $attemptid] = self::validate_parameters(self::execute_parameters(), [
            'attemptid' => $attemptid,
        ]);

        [$attempt] = self::require_owned_attempt($attemptid);

        // Only a genuinely wrong state (abandoned) is rejected here — an
        // attempt that is already finished is deliberately let through to
        // attempt_manager::finish_attempt(), which is idempotent for that
        // exact case (see its docblock) so that a caller retrying a request
        // it never learned the outcome of gets the same successful result
        // back instead of an error.
        if (
            $attempt->state !== \mod_elang\local\domain\attempt_manager::STATE_INPROGRESS
                && $attempt->state !== \mod_elang\local\domain\attempt_manager::STATE_FINISHED
        ) {
            throw new \moodle_exception('error_attemptnotinprogress', 'mod_elang');
        }

        $finished = self::get_attempt_manager()->finish_attempt($attemptid);

        // Gradebook callbacks in lib.php are plain global functions, not
        // guaranteed to be autoloaded in an external-function context —
        // require it explicitly rather than relying on it having been
        // pulled in by something else already.
        require_once($CFG->dirroot . '/mod/elang/lib.php');
        $elang = $DB->get_record('elang', ['id' => $finished->elangid], '*', MUST_EXIST);
        elang_update_grades($elang, (int) $finished->userid);

        // Tell Moodle to re-evaluate this activity's completion for the learner
        // now, so the course page and any downstream availability reflect the
        // finished attempt immediately rather than on the next page load or
        // cron run. update_state() calls custom_completion::get_state()
        // (classes/completion/custom_completion.php) to decide the actual
        // state; the COMPLETION_COMPLETE argument is only the hint Moodle's API
        // expects. Guarded by is_enabled() so it is a no-op when completion is
        // off for this activity.
        require_once($CFG->libdir . '/completionlib.php');
        $cm = get_coursemodule_from_instance('elang', $finished->elangid, 0, false, MUST_EXIST);
        $completion = new \completion_info(get_course($cm->course));
        if ($completion->is_enabled($cm)) {
            $completion->update_state($cm, COMPLETION_COMPLETE, (int) $finished->userid);
        }

        return [
            'attemptid' => (int) $finished->id,
            'state' => $finished->state,
            'correctgaps' => (int) $finished->correctgaps,
            'totalgaps' => (int) $finished->totalgaps,
            'score' => (float) $finished->score,
            'timefinish' => (int) $finished->timefinish,
        ];
    }

    /**
     * Describe the structure this function returns.
     *
     * @return external_single_structure The description of this function's return value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'attemptid' => new external_value(PARAM_INT, 'Attempt id'),
            'state' => new external_value(PARAM_ALPHA, 'Attempt state after finishing'),
            'correctgaps' => new external_value(PARAM_INT, 'Number of gaps accepted as correct'),
            'totalgaps' => new external_value(PARAM_INT, 'Total number of gaps in the attempted version'),
            'score' => new external_value(PARAM_FLOAT, 'Final attempt score as a fraction of total gaps'),
            'timefinish' => new external_value(PARAM_INT, 'Unix timestamp the attempt was finished'),
        ]);
    }
}
