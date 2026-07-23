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
 * Submit a response to one gap within an in-progress attempt.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submit_response extends external_api {
    use attempt_helper;

    /**
     * Defence-in-depth hard cap on response length, checked here regardless
     * of any future per-gap or activity-wide limit (elang_gap.maxlength /
     * a site-wide default) that the not-yet-built authoring UI will add.
     *
     * @var int
     */
    private const MAX_RESPONSE_LENGTH = 500;

    /**
     * Describe the parameters this function accepts.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'attemptid' => new external_value(PARAM_INT, 'Attempt id'),
            'gapid' => new external_value(PARAM_INT, 'Gap id being answered'),
            'responsetext' => new external_value(PARAM_RAW, "The learner's raw response"),
        ]);
    }

    /**
     * Evaluate and store a response, then return the updated attempt aggregates.
     *
     * @param int $attemptid Attempt id
     * @param int $gapid Gap id being answered
     * @param string $responsetext The learner's raw response
     * @return array The evaluation outcome and updated aggregates, see execute_returns()
     */
    public static function execute(int $attemptid, int $gapid, string $responsetext): array {
        global $DB;

        [
            'attemptid' => $attemptid,
            'gapid' => $gapid,
            'responsetext' => $responsetext,
        ] = self::validate_parameters(self::execute_parameters(), [
            'attemptid' => $attemptid,
            'gapid' => $gapid,
            'responsetext' => $responsetext,
        ]);

        if (\core_text::strlen($responsetext) > self::MAX_RESPONSE_LENGTH) {
            throw new \invalid_parameter_exception('responsetext exceeds the maximum accepted length');
        }

        $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);

        $context = self::require_attempt_ownership($attempt);
        require_capability('mod/elang:attempt', $context);

        if ($attempt->state !== \mod_elang\local\domain\attempt_manager::STATE_INPROGRESS) {
            throw new \moodle_exception('error:attemptnotinprogress', 'mod_elang');
        }

        $gap = $DB->get_record('elang_gap', ['id' => $gapid], '*', MUST_EXIST);
        $cue = $DB->get_record('elang_cue', ['id' => $gap->cueid], '*', MUST_EXIST);
        if ((int) $cue->versionid !== (int) $attempt->versionid) {
            // The gap belongs to a different version than the one this
            // attempt is on — never let a caller answer against a gap
            // outside the attempted exercise.
            throw new \moodle_exception('error:gapnotinattemptversion', 'mod_elang');
        }

        $result = self::get_attempt_manager()->submit_response($attemptid, $gapid, $responsetext);
        $updated = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);

        return [
            'resultstate' => $result->resultstate,
            'accepted' => $result->accepted,
            'answeredgaps' => (int) $updated->answeredgaps,
            'correctgaps' => (int) $updated->correctgaps,
            'exactgaps' => (int) $updated->exactgaps,
            'score' => (float) $updated->score,
        ];
    }

    /**
     * Describe the structure this function returns.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'resultstate' => new external_value(PARAM_ALPHA, 'exact, wordrecognized, incorrect or empty'),
            'accepted' => new external_value(PARAM_BOOL, 'Whether the response counts as correct for this gap'),
            'answeredgaps' => new external_value(PARAM_INT, 'Updated count of answered gaps in this attempt'),
            'correctgaps' => new external_value(PARAM_INT, 'Updated count of accepted-correct gaps in this attempt'),
            'exactgaps' => new external_value(PARAM_INT, 'Updated count of exactly-solved gaps in this attempt'),
            'score' => new external_value(PARAM_FLOAT, 'Updated attempt score as a fraction of total gaps'),
        ]);
    }
}
