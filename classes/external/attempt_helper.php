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

    /**
     * Load an attempt the current user owns, verifying both ownership and the
     * mod/elang:attempt capability. Every attempt-scoped external function
     * begins with this exact load-and-authorise sequence, so it lives here
     * once rather than being repeated in each.
     *
     * @param int $attemptid The elang_attempt id
     * @return array A two-element list: the elang_attempt record and its \context_module
     */
    private static function require_owned_attempt(int $attemptid): array {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);
        $context = self::require_attempt_ownership($attempt);
        require_capability('mod/elang:attempt', $context);

        return [$attempt, $context];
    }

    /**
     * Load an in-progress attempt the current user owns, rejecting one that is
     * no longer in progress with the stable error a player can act on (for
     * example after the attempt was finished in another tab).
     *
     * @param int $attemptid The elang_attempt id
     * @return array A two-element list: the elang_attempt record and its \context_module
     */
    private static function require_inprogress_attempt(int $attemptid): array {
        [$attempt, $context] = self::require_owned_attempt($attemptid);

        if ($attempt->state !== \mod_elang\local\domain\attempt_manager::STATE_INPROGRESS) {
            throw new \moodle_exception('error:attemptnotinprogress', 'mod_elang');
        }

        return [$attempt, $context];
    }

    /**
     * Load a gap and confirm it belongs to the version the attempt is pinned
     * to. A caller must never be able to answer or request a hint against a
     * gap outside the attempted exercise version.
     *
     * @param int $gapid The elang_gap id
     * @param \stdClass $attempt The attempt whose version the gap must belong to
     * @return \stdClass The elang_gap record
     */
    private static function require_gap_in_attempt_version(int $gapid, \stdClass $attempt): \stdClass {
        global $DB;

        $gap = $DB->get_record('elang_gap', ['id' => $gapid], '*', MUST_EXIST);
        $cue = $DB->get_record('elang_cue', ['id' => $gap->cueid], '*', MUST_EXIST);
        if ((int) $cue->versionid !== (int) $attempt->versionid) {
            throw new \moodle_exception('error:gapnotinattemptversion', 'mod_elang');
        }

        return $gap;
    }
}
