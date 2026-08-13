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
 * classes/external/, not in this file.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * How many learners a whole-activity grade rebuild processes per batch, so the
 * work stays bounded in memory on activities with very many learners.
 */
define('ELANG_GRADE_REBUILD_CHUNK', 500);

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
        case FEATURE_BACKUP_MOODLE2:
            // Course backup/restore is provided by backup/moodle2/ (structure,
            // media/poster files and, with user info, learner attempts).
            return true;
        // The following features belong to the 2.0 target scope but stay switched
        // off until their implementation lands. Declaring a feature without its
        // callbacks is not a harmless promise.
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
 * Stores the base activity record. Exercise content (versions, cues, gaps) is
 * created separately through the authoring API, not from this form.
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
    // explicitly here; callers that do not set it (such as a programmatic
    // creation without the form) get an empty value rather than a DB error.
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
 * @return cached_cm_info|false The result of this call.
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
 * @return void No return value.
 */
function elang_update_grades(stdClass $elang, int $userid = 0, bool $nullifnone = true): void {
    global $DB;

    if ((int) $elang->grade == 0) {
        // Ungraded activity: only (re)configure the grade item, nothing to push.
        elang_grade_item_update($elang);
        return;
    }

    if ($userid) {
        $userids = [(int) $userid];
    } else {
        $userids = array_map('intval', $DB->get_fieldset_select(
            'elang_attempt',
            'DISTINCT userid',
            'elangid = ?',
            [$elang->id]
        ));
    }

    if (empty($userids)) {
        elang_grade_item_update($elang);
        return;
    }

    $scaleitemcount = (int) $elang->grade < 0 ? elang_get_scale_item_count(-(int) $elang->grade) : 0;

    // Rebuild in chunks so neither the best-score IN(...) query nor the grades
    // array grows with the total number of learners on the activity. Each user's
    // best (highest) score among their finished attempts is read with one grouped
    // query per chunk, not one query per learner.
    $pushedgrades = false;
    foreach (array_chunk($userids, ELANG_GRADE_REBUILD_CHUNK) as $chunk) {
        [$insql, $inparams] = $DB->get_in_or_equal($chunk);
        $bestscores = $DB->get_records_sql(
            "SELECT userid, MAX(score) AS bestscore
               FROM {elang_attempt}
              WHERE elangid = ? AND state = ? AND userid $insql
           GROUP BY userid",
            array_merge([$elang->id, \mod_elang\local\domain\attempt_manager::STATE_FINISHED], $inparams)
        );

        $grades = [];
        foreach ($chunk as $uid) {
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
            $pushedgrades = true;
        }
    }

    // No chunk produced any grade to push (for example every learner is unfinished
    // and $nullifnone is false): still make sure the grade item itself exists.
    if (!$pushedgrades) {
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
    // A normalised attempt score is defined on [0, 1]; clamp defensively so a
    // stray out-of-range value can never produce a rawgrade above the numeric
    // maximum or off the end of a scale.
    $bestscore = min(1.0, max(0.0, $bestscore));

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
 * The first path segment is the elang_version id (the area itemid). Viewing the
 * activity (mod/elang:view) is necessary but not sufficient: which version a
 * user may receive is decided by version_manager::user_can_access_version_file()
 * — the published version and a user's own pinned archived versions for
 * learners, any version for managers, never a draft for a non-manager. This is
 * the same version protection the attempt-bound read API (get_attempt_exercise)
 * enforces, so an unpublished authoring upload cannot leak through a guessed
 * URL. The version is also confined to this activity so a crafted URL cannot
 * borrow this context to serve another activity's files.
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
    global $USER;

    if ($context->contextlevel !== CONTEXT_MODULE) {
        return false;
    }
    if (!in_array($filearea, ['media', 'poster'], true)) {
        return false;
    }

    require_login($course, true, $cm);
    require_capability('mod/elang:view', $context);

    $versionid = (int) array_shift($args);

    // Serving a file needs more than :view. A learner may only receive the
    // published version's media, or an archived version their own attempt is
    // pinned to; draft media stays invisible to non-managers so an unpublished
    // upload cannot leak through a guessed URL. Managers may fetch any of this
    // activity's versions. The helper also confines the version to this activity.
    if (
        !\mod_elang\local\domain\version_manager::user_can_access_version_file(
            $versionid,
            (int) $cm->instance,
            $context,
            (int) $USER->id
        )
    ) {
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

/**
 * Add an "Edit content" link to the activity's settings navigation for users
 * who may author it.
 *
 * @param settings_navigation $settingsnav The settings navigation tree
 * @param navigation_node $elangnode The activity's node within it
 * @return void No return value.
 */
function elang_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $elangnode): void {
    global $PAGE;

    if (empty($PAGE->cm) || $PAGE->cm->modname !== 'elang') {
        return;
    }

    $context = context_module::instance($PAGE->cm->id);

    if (has_capability('mod/elang:manage', $context)) {
        $elangnode->add_node(navigation_node::create(
            get_string('editcontent', 'mod_elang'),
            new moodle_url('/mod/elang/edit.php', ['id' => $PAGE->cm->id]),
            navigation_node::TYPE_SETTING,
            null,
            'mod_elang_editcontent',
            new pix_icon('t/edit', '')
        ));
    }

    if (has_capability('mod/elang:viewreports', $context)) {
        $elangnode->add_node(navigation_node::create(
            get_string('reports', 'mod_elang'),
            new moodle_url('/mod/elang/report.php', ['id' => $PAGE->cm->id]),
            navigation_node::TYPE_SETTING,
            null,
            'mod_elang_reports',
            new pix_icon('i/report', '')
        ));
    }

    if (has_capability('mod/elang:exporttranscript', $context)) {
        $elangnode->add_node(navigation_node::create(
            get_string('exporttranscript', 'mod_elang'),
            new moodle_url('/mod/elang/transcript.php', ['id' => $PAGE->cm->id]),
            navigation_node::TYPE_SETTING,
            null,
            'mod_elang_exporttranscript',
            new pix_icon('i/export', '')
        ));
    }
}

/**
 * Serve files from the mod_elang file areas (exercise media and poster images).
 *
 * The player references these through pluginfile.php; without this callback
 * Moodle refuses every such request and the medium never loads. Media and poster
 * files are stored per content version (the item id is the elang_version id), so
 * access is granted by resolving that version back to its activity and checking
 * the viewer may see the activity.
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module.
 * @param context $context The context.
 * @param string $filearea The file area ('media' or 'poster').
 * @param array $args The remaining file path arguments, starting with the item id.
 * @param bool $forcedownload Whether to force download.
 * @param array $options Additional options affecting file serving.
 * @return bool False if the file was not found; otherwise the file is served and the script exits.
 */
function mod_elang_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []): bool {
    global $DB;

    if ($context->contextlevel !== CONTEXT_MODULE) {
        return false;
    }

    if (!in_array($filearea, ['media', 'poster'], true)) {
        return false;
    }

    require_login($course, true, $cm);
    if (!has_capability('mod/elang:view', $context)) {
        return false;
    }

    $versionid = (int) array_shift($args);

    // The requested version must belong to this activity, so a valid token for
    // one activity cannot be used to read another activity's media.
    $elang = $DB->get_record('elang', ['id' => $cm->instance], '*', MUST_EXIST);
    if (!$DB->record_exists('elang_version', ['id' => $versionid, 'elangid' => $elang->id])) {
        return false;
    }

    $filename = array_pop($args);
    $filepath = empty($args) ? '/' : '/' . implode('/', $args) . '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_elang', $filearea, $versionid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 86400, 0, $forcedownload, $options);

    return true;
}
