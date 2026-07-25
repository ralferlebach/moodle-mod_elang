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
 * Reveal the next hint level for a gap within an in-progress attempt.
 *
 * Levels are revealed strictly in order (see attempt_manager::request_hint()
 * for why), and using one applies a penalty to that gap's score — this
 * function returns the updated attempt-level score alongside the hint
 * itself, so a caller never has to make a separate round trip to find out
 * what a hint cost. If the revealed hint's hinttype happens to be
 * 'solution', its hinttext IS the solution: that is by design (a learner
 * who deliberately exhausts every hint level up to and including a
 * solution-type one has explicitly asked for it, penalty and all) and is
 * not the same thing as leaking a solution through
 * get_attempt_exercise/get_attempt_cues, which never happens regardless of
 * hint state.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class request_hint extends external_api {
    use attempt_helper;

    /**
     * Describe the parameters this function accepts.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'attemptid' => new external_value(PARAM_INT, 'Attempt id'),
            'gapid' => new external_value(PARAM_INT, 'Gap id to request a hint for'),
        ]);
    }

    /**
     * Reveal the next hint level and return the updated attempt score.
     *
     * @param int $attemptid Attempt id
     * @param int $gapid Gap id to request a hint for
     * @return array See execute_returns()
     */
    public static function execute(int $attemptid, int $gapid): array {
        global $DB;

        [
            'attemptid' => $attemptid,
            'gapid' => $gapid,
        ] = self::validate_parameters(self::execute_parameters(), [
            'attemptid' => $attemptid,
            'gapid' => $gapid,
        ]);

        $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);

        $context = self::require_attempt_ownership($attempt);
        require_capability('mod/elang:attempt', $context);

        if ($attempt->state !== \mod_elang\local\domain\attempt_manager::STATE_INPROGRESS) {
            throw new \moodle_exception('error:attemptnotinprogress', 'mod_elang');
        }

        $gap = $DB->get_record('elang_gap', ['id' => $gapid], '*', MUST_EXIST);
        $cue = $DB->get_record('elang_cue', ['id' => $gap->cueid], '*', MUST_EXIST);
        if ((int) $cue->versionid !== (int) $attempt->versionid) {
            throw new \moodle_exception('error:gapnotinattemptversion', 'mod_elang');
        }

        $existing = $DB->get_record('elang_response', ['attemptid' => $attemptid, 'gapid' => $gapid]);
        $nextlevel = ($existing ? (int) $existing->hintlevel : 0) + 1;
        if (!$DB->record_exists('elang_gaphint', ['gapid' => $gapid, 'level' => $nextlevel])) {
            throw new \moodle_exception('error:nomorehints', 'mod_elang');
        }

        $hint = self::get_attempt_manager()->request_hint($attemptid, $gapid);
        $updated = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);

        return [
            'level' => (int) $hint->level,
            'hinttype' => $hint->hinttype,
            'hinttext' => (string) $hint->hinttext,
            'penalty' => (float) $hint->penalty,
            'hintedgaps' => (int) $updated->hintedgaps,
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
            'level' => new external_value(PARAM_INT, 'The hint level just revealed'),
            'hinttype' => new external_value(PARAM_ALPHA, 'text, firstletter, wordlength, partial, solution or translation'),
            'hinttext' => new external_value(PARAM_RAW, 'The hint content for this level'),
            'penalty' => new external_value(
                PARAM_FLOAT,
                "Fraction of this gap's point value given up by having revealed hints up to this level"
            ),
            'hintedgaps' => new external_value(PARAM_INT, 'Updated count of gaps for which a hint has been used in this attempt'),
            'score' => new external_value(PARAM_FLOAT, 'Updated attempt score, reflecting this and any other hint penalty'),
        ]);
    }
}
