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

/**
 * Helpers shared by the attempt-related external functions.
 *
 * Kept as a trait rather than a base class so each external function class
 * can still directly extend \core_external\external_api, which the Moodle
 * external API framework expects.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait attempt_helper {
    /**
     * Build an attempt_manager wired to the answer_evaluator and its
     * script_handler_manager, which auto-discovers installed elangscript
     * subplugins from disk.
     *
     * @return \mod_elang\local\domain\attempt_manager
     */
    private static function get_attempt_manager(): \mod_elang\local\domain\attempt_manager {
        return new \mod_elang\local\domain\attempt_manager(
            new \mod_elang\local\grading\answer_evaluator(
                new \mod_elang\local\grading\script_handler_manager()
            )
        );
    }

    /**
     * Verify the current user owns the given attempt and validate its
     * activity context.
     *
     * Capability checks (require_capability()) are the caller's
     * responsibility: this only confirms that the attempt actually belongs
     * to $USER, which a capability check alone cannot do — a learner with
     * mod/elang:attempt in the context could otherwise operate on another
     * learner's attempt purely by guessing its id.
     *
     * @param \stdClass $attempt An elang_attempt record
     * @return \context_module The activity context
     */
    private static function require_attempt_ownership(\stdClass $attempt): \context_module {
        global $USER;

        $cm = get_coursemodule_from_instance('elang', $attempt->elangid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);

        if ((int) $attempt->userid !== (int) $USER->id) {
            throw new \moodle_exception('error:noaccesstoattempt', 'mod_elang');
        }

        return $context;
    }
}
