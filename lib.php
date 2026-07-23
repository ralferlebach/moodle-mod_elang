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
        // backup look for backup_elang_activity_task, and
        // FEATURE_COMPLETION_HAS_RULES makes completion look for
        // \mod_elang\completion\custom_completion.
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_COMPLETION_HAS_RULES:
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            // See elang_grade_item_update()/elang_update_grades() below; the
            // grade itself is always computed from elang_attempt.score
            // (highest finished attempt), never entered manually.
            return true;
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

    // The elang.language column has no schema-level default (a NOTNULL CHAR
    // column with an empty-string DEFAULT is rejected by Moodle's XMLDB
    // validator with a debugging() call), so it must always be supplied
    // explicitly here until mod_form.php gains a language field of its own
    // (phase 3/4).
    if (!isset($elang->language)) {
        $elang->language = '';
    }
    if (!isset($elang->grade)) {
        // The form always supplies this via standard_grading_coursemodule_
        // elements(); this default only matters for callers that bypass the
        // form (generators, external functions that create instances directly).
        $elang->grade = 100;
    }

    $elang->timecreated = time();
    $elang->timemodified = $elang->timecreated;

    $elang->id = (int) $DB->insert_record('elang', $elang);

    elang_grade_item_update($elang);

    return $elang->id;
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

    $result = $DB->update_record('elang', $elang);

    elang_grade_item_update($elang);

    return $result;
}

/**
 * Delete an elang instance and all data that belongs to it.
 *
 * @param int $id Instance id
 * @return bool Success
 */
function elang_delete_instance(int $id): bool {
    global $DB, $CFG;

    $elang = $DB->get_record('elang', ['id' => $id]);
    if (!$elang) {
        return false;
    }

    $transaction = $DB->start_delegated_transaction();

    // Dependent records are deleted in child-to-parent order, using subqueries
    // rather than loading id lists into PHP (see Lastenheft, "manuell
    // zusammengesetzte IN-Klausel" as a V1 finding to avoid repeating).
    $DB->delete_records_select(
        'elang_response',
        'attemptid IN (SELECT id FROM {elang_attempt} WHERE elangid = ?)',
        [$id]
    );
    $DB->delete_records('elang_attempt', ['elangid' => $id]);

    $DB->delete_records_select(
        'elang_gaphint',
        'gapid IN (SELECT g.id
                     FROM {elang_gap} g
                     JOIN {elang_cue} c ON c.id = g.cueid
                     JOIN {elang_version} v ON v.id = c.versionid
                    WHERE v.elangid = ?)',
        [$id]
    );
    $DB->delete_records_select(
        'elang_gapanswer',
        'gapid IN (SELECT g.id
                     FROM {elang_gap} g
                     JOIN {elang_cue} c ON c.id = g.cueid
                     JOIN {elang_version} v ON v.id = c.versionid
                    WHERE v.elangid = ?)',
        [$id]
    );
    $DB->delete_records_select(
        'elang_gap',
        'cueid IN (SELECT c.id
                     FROM {elang_cue} c
                     JOIN {elang_version} v ON v.id = c.versionid
                    WHERE v.elangid = ?)',
        [$id]
    );
    $DB->delete_records_select(
        'elang_cue',
        'versionid IN (SELECT id FROM {elang_version} WHERE elangid = ?)',
        [$id]
    );
    $DB->delete_records('elang_version', ['elangid' => $id]);

    $DB->delete_records('elang', ['id' => $id]);

    $transaction->allow_commit();

    require_once($CFG->libdir . '/gradelib.php');
    grade_update('mod/elang', $elang->course, 'mod', 'elang', $elang->id, 0, null, ['deleted' => true]);

    return true;
}

/**
 * Create or update the grade item for an elang instance.
 *
 * Standard Moodle gradebook callback (see elang_supports(
 * FEATURE_GRADE_HAS_GRADE)), called from elang_add_instance(),
 * elang_update_instance() and elang_update_grades(). The grade itself is
 * never entered manually — elang_update_grades() always computes it from
 * elang_attempt.score — so this function's own responsibility is limited to
 * (re)configuring the grade item's type/bounds and, when $grades is given,
 * pushing already-computed grades through it.
 *
 * @param stdClass $elang Row from the elang table, at least id, course, grade
 * @param mixed $grades Null to only (re)configure the grade item, an array of
 *        grade-like objects keyed by userid to push, or the string 'reset'
 *        to remove all grades for this item
 * @return int GRADE_UPDATE_OK, or one of gradelib's GRADE_UPDATE_* failure constants
 */
function elang_grade_item_update(stdClass $elang, $grades = null): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $params = [
        'itemname' => $elang->name ?? get_string('pluginname', 'mod_elang'),
    ];

    if ((int) $elang->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = (int) $elang->grade;
        $params['grademin'] = 0;
    } else if ((int) $elang->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid'] = -(int) $elang->grade;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/elang', $elang->course, 'mod', 'elang', $elang->id, 0, $grades, $params);
}

/**
 * Recompute and push gradebook grades for one or all users of an elang instance.
 *
 * The grade is always the highest score among a user's FINISHED attempts
 * (see attempt_manager::get_best_score()) — no configurable grading method
 * (best/average/first/last across attempts) yet; that is a documented known
 * gap, not an oversight (see CHANGELOG). A user with no finished attempts
 * gets no grade pushed by default, or an explicit null grade (clearing any
 * previous one) when $nullifnone is true and they already have some grade.
 *
 * @param stdClass $elang Row from the elang table
 * @param int $userid 0 to recompute every user who has ever attempted this activity, or a specific user id
 * @param bool $nullifnone Whether a user with no finished attempts should have their grade explicitly cleared
 * @return void
 */
function elang_update_grades(stdClass $elang, int $userid = 0, bool $nullifnone = true): void {
    global $DB;

    if ((int) $elang->grade == 0) {
        // Ungraded activity: only (re)configure the grade item, nothing to push.
        elang_grade_item_update($elang);
        return;
    }

    if ($userid) {
        $userids = [$userid];
    } else {
        $userids = $DB->get_fieldset_select(
            'elang_attempt',
            'DISTINCT userid',
            'elangid = ?',
            [$elang->id]
        );
    }

    if (empty($userids)) {
        elang_grade_item_update($elang);
        return;
    }

    $attemptmanager = new \mod_elang\local\domain\attempt_manager(
        new \mod_elang\local\grading\answer_evaluator(new \mod_elang\local\grading\script_handler_manager())
    );

    $grades = [];
    foreach ($userids as $uid) {
        $uid = (int) $uid;
        $bestscore = $attemptmanager->get_best_score((int) $elang->id, $uid);

        if ($bestscore === null) {
            if ($nullifnone) {
                $grade = new stdClass();
                $grade->userid = $uid;
                $grade->rawgrade = null;
                $grades[$uid] = $grade;
            }
            continue;
        }

        $grade = new stdClass();
        $grade->userid = $uid;
        $grade->rawgrade = $bestscore * (int) $elang->grade;
        $grades[$uid] = $grade;
    }

    if (!empty($grades)) {
        elang_grade_item_update($elang, $grades);
    } else {
        elang_grade_item_update($elang);
    }
}
