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
 * Return the current state of an attempt: aggregate counters plus, per gap
 * answered so far, how it was evaluated and what the learner typed.
 *
 * responsetext is the learner's own previously submitted text, returned so a
 * player resuming an in-progress attempt can restore what was typed before —
 * this is not solution data, it never came from elang_gap.solution or
 * elang_gapanswer. No gap's stored solution or accepted answer variants are
 * ever included here or anywhere else in this response.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_attempt_state extends external_api {
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
     * Return the attempt's aggregates and per-gap response state.
     *
     * @param int $attemptid Attempt id
     * @return array See execute_returns()
     */
    public static function execute(int $attemptid): array {
        global $DB;

        ['attemptid' => $attemptid] = self::validate_parameters(self::execute_parameters(), [
            'attemptid' => $attemptid,
        ]);

        [$attempt] = self::require_owned_attempt($attemptid);

        $responses = $DB->get_records('elang_response', ['attemptid' => $attemptid], 'gapid ASC');

        $responsedata = [];
        foreach ($responses as $response) {
            $responsedata[] = [
                'gapid' => (int) $response->gapid,
                'responsetext' => $response->responsetext,
                'resultstate' => $response->resultstate,
                'accepted' => (bool) $response->accepted,
                'tries' => (int) $response->tries,
                'hintlevel' => (int) $response->hintlevel,
            ];
        }

        return [
            'attemptid' => (int) $attempt->id,
            'state' => $attempt->state,
            'totalgaps' => (int) $attempt->totalgaps,
            'answeredgaps' => (int) $attempt->answeredgaps,
            'exactgaps' => (int) $attempt->exactgaps,
            'correctgaps' => (int) $attempt->correctgaps,
            'hintedgaps' => (int) $attempt->hintedgaps,
            'score' => (float) $attempt->score,
            'responses' => $responsedata,
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
            'state' => new external_value(PARAM_ALPHA, 'Attempt state: inprogress, finished or abandoned'),
            'totalgaps' => new external_value(PARAM_INT, 'Total number of gaps in the attempted version'),
            'answeredgaps' => new external_value(PARAM_INT, 'Number of gaps answered so far'),
            'exactgaps' => new external_value(PARAM_INT, 'Number of gaps answered exactly'),
            'correctgaps' => new external_value(PARAM_INT, 'Number of gaps accepted as correct'),
            'hintedgaps' => new external_value(PARAM_INT, 'Number of gaps for which a hint was used'),
            'score' => new external_value(PARAM_FLOAT, 'Current attempt score as a fraction of total gaps'),
            'responses' => new external_multiple_structure(
                new external_single_structure([
                    'gapid' => new external_value(PARAM_INT, 'Gap id'),
                    'responsetext' => new external_value(PARAM_RAW, "The learner's own previously submitted text for this gap"),
                    'resultstate' => new external_value(PARAM_ALPHA, 'exact, wordrecognized, incorrect or empty'),
                    'accepted' => new external_value(PARAM_BOOL, 'Whether the response was accepted as correct'),
                    'tries' => new external_value(PARAM_INT, 'How many times this gap has been answered'),
                    'hintlevel' => new external_value(PARAM_INT, 'Current hint level used for this gap'),
                ])
            ),
        ]);
    }
}
