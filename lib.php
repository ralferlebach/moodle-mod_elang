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

/**
 * Library of interface functions and constants for mod_elang.
 *
 * Version 2.0 skeleton: infrastructure only. The exercise domain (versions, cues,
 * gaps, attempts, responses, grading, reporting) is not implemented here yet and
 * is introduced from phase 2 onwards, see docs/materials/.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return the list of features this module supports.
 *
 * @param string $feature FEATURE_xx constant
 * @return mixed True/false for supported features, a constant for FEATURE_MOD_PURPOSE, null when unknown
 */
function elang_supports(string $feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_GROUPS:
        case FEATURE_GROUPINGS:
            return true;
        // The following features belong to the 2.0 target scope but stay switched
        // off until their implementation lands. Declaring a feature without its
        // callbacks is not a harmless promise: FEATURE_BACKUP_MOODLE2 makes course
        // backup look for backup_elang_activity_task, FEATURE_COMPLETION_HAS_RULES
        // makes completion look for \mod_elang\completion\custom_completion, and
        // FEATURE_GRADE_HAS_GRADE makes instance creation call
        // elang_grade_item_update() and elang_update_grades().
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_COMPLETION_HAS_RULES:
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_MOD_PURPOSE:
            // Assessment purpose: renders the monochrome monologo on the pink
            // activity-icon background, consistent with quiz and assign.
            return MOD_PURPOSE_ASSESSMENT;
        default:
            return null;
    }
}

/**
 * Declare that the activity icon is not branded, so that Moodle may recolour it.
 *
 * @return bool Always false — pix/monologo.svg is a plain monochrome icon
 */
function elang_is_branded(): bool {
    return false;
}

/**
 * Add a new elang instance.
 *
 * Skeleton implementation: stores the base record only. Exercise content handling
 * is added in phase 2 together with the versioned data model.
 *
 * @param stdClass $elang Data from the module form
 * @param mod_elang_mod_form|null $mform The form instance
 * @return int The id of the new instance
 */
function elang_add_instance(stdClass $elang, ?mod_elang_mod_form $mform = null): int {
    global $DB;

    $elang->timecreated = time();
    $elang->timemodified = $elang->timecreated;

    return (int) $DB->insert_record('elang', $elang);
}

/**
 * Update an existing elang instance.
 *
 * @param stdClass $elang Data from the module form
 * @param mod_elang_mod_form|null $mform The form instance
 * @return bool Success
 */
function elang_update_instance(stdClass $elang, ?mod_elang_mod_form $mform = null): bool {
    global $DB;

    $elang->id = $elang->instance;
    $elang->timemodified = time();

    return $DB->update_record('elang', $elang);
}

/**
 * Delete an elang instance and all data that belongs to it.
 *
 * @param int $id Instance id
 * @return bool Success
 */
function elang_delete_instance(int $id): bool {
    global $DB;

    if (!$DB->record_exists('elang', ['id' => $id])) {
        return false;
    }

    $transaction = $DB->start_delegated_transaction();
    // Dependent records are deleted here before the parent record once the
    // versioned data model exists (phase 2).
    $DB->delete_records('elang', ['id' => $id]);
    $transaction->allow_commit();

    return true;
}
