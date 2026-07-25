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
 * Covers the standard Moodle module callbacks: supported features, instance
 * lifecycle, cached course-module info for the completionfinishattempt
 * custom completion rule (see classes/completion/custom_completion.php), and
 * gradebook integration. The exercise domain itself (versions, cues, gaps,
 * attempts, responses, grading) lives under classes/local/ and
 * classes/external/, not in this file; see docs/materials/ for the current
 * scope of each phase. The learner-facing player, authoring tool, reporting
 * and exports remain unimplemented (phase 3/4).
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
        // backup look for backup_elang_activity_task.
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            // See classes/completion/custom_completion.php.
            return true;
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
    if (!isset($elang->jarothreshold)) {
        // Same rationale as elang.language above: elang.jarothreshold is
        // NOTNULL without a schema-level default (see db/upgrade.php), so
        // every insert must supply it explicitly. 1.0 means "reduced forms
        // must be identical", i.e. no Jaro-based fuzziness — the behaviour
        // every wordrecognised gap had before this field existed.
        $elang->jarothreshold = \mod_elang\local\grading\answer_evaluator::DEFAULT_JARO_THRESHOLD;
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
 * Build the cached course-module info Moodle stores for this instance.
 *
 * The only reason this exists, currently, is to populate
 * customdata['customcompletionrules'] — without it,
 * \core_completion\activity_custom_completion::validate_rule() always
 * rejects completionfinishattempt as "not used by this activity", no matter
 * what custom_completion::get_state() itself returns, since that check
 * reads customdata rather than querying elang.completionfinishattempt
 * directly (confirmed against a real PHPUnit failure on Moodle 4.5.12, and
 * against core's own documented pattern for this callback, e.g.
 * forum_get_coursemodule_info()).
 *
 * @param stdClass $coursemodule The course_modules record, as passed by get_fast_modinfo()
 * @return cached_cm_info|false
 */
function elang_get_coursemodule_info($coursemodule) {
    global $DB;

    $elang = $DB->get_record(
        'elang',
        ['id' => $coursemodule->instance],
        'id, name, intro, introformat, completionfinishattempt'
    );
    if (!$elang) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $elang->name;

    if ($coursemodule->showdescription) {
        // Convert intro to HTML; do not filter the cached version, filters run at display time.
        $result->content = format_module_intro('elang', $elang, $coursemodule->id, false);
    }

    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionfinishattempt'] = (int) $elang->completionfinishattempt;
    }

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

    // One grouped query for every user's best (highest) score among their
    // finished attempts, instead of attempt_manager::get_best_score() called
    // once per user — the previous per-user loop meant one query per learner
    // just to recompute grades for a whole activity.
    [$insql, $inparams] = $DB->get_in_or_equal(array_map('intval', $userids));
    $bestscores = $DB->get_records_sql(
        "SELECT userid, MAX(score) AS bestscore
           FROM {elang_attempt}
          WHERE elangid = ? AND state = ? AND userid $insql
       GROUP BY userid",
        array_merge([$elang->id, \mod_elang\local\domain\attempt_manager::STATE_FINISHED], $inparams)
    );

    $scaleitemcount = (int) $elang->grade < 0 ? elang_get_scale_item_count(-(int) $elang->grade) : 0;

    $grades = [];
    foreach ($userids as $uid) {
        $uid = (int) $uid;
        $bestscore = isset($bestscores[$uid]) ? (float) $bestscores[$uid]->bestscore : null;

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
        $grade->rawgrade = elang_score_to_rawgrade($bestscore, (int) $elang->grade, $scaleitemcount);
        $grades[$uid] = $grade;
    }

    if (!empty($grades)) {
        elang_grade_item_update($elang, $grades);
    } else {
        elang_grade_item_update($elang);
    }
}

/**
 * Convert a 0..1 attempt score into a rawgrade for either a numeric grade or
 * a Moodle scale.
 *
 * For a numeric grade (elang.grade > 0), the score is a straightforward
 * fraction of the maximum. For a scale (elang.grade < 0, with -elang.grade
 * the scale id), the previous implementation multiplied the fraction by the
 * same negative grade value used for GRADE_TYPE_SCALE configuration, which
 * always produced a negative rawgrade — scale items are 1-indexed positions,
 * never negative. This maps the fraction proportionally onto the scale's
 * actual item positions (1..N) instead.
 *
 * @param float $bestscore The best attempt score, 0..1
 * @param int $elanggrade The raw elang.grade value (positive numeric max, or -scaleid)
 * @param int $scaleitemcount Number of items in the scale, or 0 for a numeric grade
 * @return float The rawgrade to push through grade_update()
 */
function elang_score_to_rawgrade(float $bestscore, int $elanggrade, int $scaleitemcount): float {
    if ($elanggrade >= 0 || $scaleitemcount <= 0) {
        return $bestscore * $elanggrade;
    }

    $position = round($bestscore * ($scaleitemcount - 1)) + 1;

    return (float) min(max($position, 1), $scaleitemcount);
}

/**
 * Return the number of items defined in a Moodle scale.
 *
 * @param int $scaleid The scale id (a positive number, already stripped of the sign elang.grade encodes it with)
 * @return int Number of items in the scale, or 0 if the scale cannot be found
 */
function elang_get_scale_item_count(int $scaleid): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $scale = grade_scale::fetch(['id' => $scaleid]);
    if (!$scale) {
        return 0;
    }

    $scale->load_items();

    return count($scale->scale_items);
}

/**
 * Return the file areas mod_elang exposes through the file browser.
 *
 * Both areas are versioned: a file's itemid is the elang_version id it
 * belongs to, so each immutable version keeps its own media and poster.
 *
 * @param stdClass $course The course object
 * @param cm_info|stdClass $cm The course module
 * @param context $context The module context
 * @return array [(string) filearea] => (string) human-readable name
 */
function elang_get_file_areas($course, $cm, $context): array {
    return [
        'media' => get_string('filearea_media', 'mod_elang'),
        'poster' => get_string('filearea_poster', 'mod_elang'),
    ];
}

/**
 * Serve files from mod_elang's versioned media and poster file areas.
 *
 * The first path segment is the elang_version id (the area itemid). Access is
 * granted to any user who may view the activity; the medium itself is not a
 * solution, and which version a learner is entitled to is enforced by the
 * attempt-bound read API (get_attempt_exercise), not here. The version is
 * checked to belong to this activity so a crafted URL cannot borrow this
 * context to serve another activity's files.
 *
 * @param stdClass $course The course object
 * @param cm_info|stdClass $cm The course module
 * @param context $context The module context
 * @param string $filearea The file area (media or poster)
 * @param array $args The remaining path segments: version id, then filepath and filename
 * @param bool $forcedownload Whether to force download
 * @param array $options Additional options affecting file serving
 * @return bool False if the file could not be served; otherwise sends the file and exits
 */
function elang_pluginfile($course, $cm, $context, string $filearea, array $args, bool $forcedownload, array $options = []): bool {
    global $DB;

    if ($context->contextlevel !== CONTEXT_MODULE) {
        return false;
    }
    if (!in_array($filearea, ['media', 'poster'], true)) {
        return false;
    }

    require_login($course, true, $cm);
    require_capability('mod/elang:view', $context);

    $versionid = (int) array_shift($args);

    // The version must belong to this activity, so the URL cannot be rewritten
    // to serve another activity's media through this module context.
    if (!$DB->record_exists('elang_version', ['id' => $versionid, 'elangid' => $cm->instance])) {
        return false;
    }

    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_elang', $filearea, $versionid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }

    // Media is immutable per version, so it is safe to let clients cache it.
    send_stored_file($file, DAYSECS, 0, $forcedownload, $options);

    return true;
}
