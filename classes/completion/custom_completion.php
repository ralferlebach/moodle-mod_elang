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

namespace mod_elang\completion;

use core_completion\activity_custom_completion;

/**
 * mod_elang's custom completion rule.
 *
 * Defines exactly one rule beyond what core already provides for free once
 * FEATURE_COMPLETION_TRACKS_VIEWS and FEATURE_GRADE_HAS_GRADE are declared
 * (core already offers "completionview" and, via the standard grade
 * section, "completionusegrade"/a pass-grade condition on its own — neither
 * needs to be defined here). What core has no way to know on its own is
 * whether the learner actually finished an attempt at the exercise, as
 * opposed to merely opening the page or achieving some grade another way —
 * that gap is what "completionfinishattempt" fills.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion {
    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array The resulting list.
     */
    public static function get_defined_custom_rules(): array {
        return [
            'completionfinishattempt',
        ];
    }

    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule
     * @return int The completion state
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $hasfinishedattempt = $DB->record_exists('elang_attempt', [
            'elangid' => $this->cm->instance,
            'userid' => $this->userid,
            'state' => \mod_elang\local\domain\attempt_manager::STATE_FINISHED,
        ]);

        return $hasfinishedattempt ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array The resulting list.
     */
    public function get_custom_rule_descriptions(): array {
        return [
            'completionfinishattempt' => get_string('completiondetail_completionfinishattempt', 'mod_elang'),
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array The resulting list.
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionfinishattempt',
            'completionusegrade',
        ];
    }
}
