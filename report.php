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
 * Teacher attempt report for mod_elang.
 *
 * Lists every learner attempt at the activity and, for one attempt, its full
 * gap-by-gap detail. It is read only — grade overrides live in the Moodle
 * gradebook.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
// The filter form extends moodleform, which is not autoloaded.
require_once($CFG->libdir . '/formslib.php');

$id = required_param('id', PARAM_INT);
$attemptid = optional_param('attemptid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

// Cap the attempt list per page so the report never loads an activity's whole
// attempt history into memory at once.
/** @var int The number of attempts shown per page in the report overview. */
const ELANG_REPORT_PERPAGE = 50;

[$course, $cm] = get_course_and_cm_from_cmid($id, 'elang');
$elang = $DB->get_record('elang', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/elang:viewreports', $context);

$action = optional_param('action', '', PARAM_ALPHA);
$dataformat = optional_param('dataformat', '', PARAM_ALPHA);

// Filters and sorting travel in the URL, so a filtered view can be bookmarked
// and the paging bar and column headings only have to carry the same
// parameters. Every value is validated again in
// attempt_report::clean_filters() and against the sort whitelist before it
// reaches a query — nothing here is trusted.
//
// These names differ from the filter form's own field names on purpose. A
// date_selector submits filterfrom as an array of day/month/year, and reading
// that back as a scalar is a coding error; the form posts its own names, this
// page reads only these canonical scalars, and the two never meet.
$rawfilters = [
    'userid' => optional_param('fuser', 0, PARAM_INT),
    'state' => optional_param('fstate', '', PARAM_ALPHA),
    'from' => optional_param('ffrom', 0, PARAM_INT),
    'to' => optional_param('fto', 0, PARAM_INT),
    'attemptnumber' => optional_param('fattempt', 0, PARAM_INT),
];
$filters = \mod_elang\local\report\attempt_report::clean_filters($rawfilters);
$sort = optional_param('tsort', '', PARAM_ALPHA);
$direction = optional_param('tdir', 'DESC', PARAM_ALPHA);

/** @var array<string, string> Canonical URL parameter name for each filter. */
const ELANG_FILTER_PARAMS = [
    'userid' => 'fuser',
    'state' => 'fstate',
    'from' => 'ffrom',
    'to' => 'fto',
    'attemptnumber' => 'fattempt',
];

$urlparams = ['id' => $cm->id];
foreach ($filters as $key => $value) {
    $urlparams[ELANG_FILTER_PARAMS[$key]] = $value;
}
if ($sort !== '') {
    $urlparams['tsort'] = $sort;
    $urlparams['tdir'] = $direction;
}
$candelete = has_capability('mod/elang:deleteattempts', $context);
$canexport = has_capability('mod/elang:exportreports', $context);

$PAGE->set_url('/mod/elang/report.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($elang->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_activity_record($elang);
$PAGE->set_secondary_active_tab('mod_elang_reports');

$groupmode = groups_get_activity_groupmode($cm, $course);
$currentgroup = $groupmode != NOGROUPS ? groups_get_activity_group($cm, true) : 0;

// Streaming a data export must happen before any page output is sent. The
// group filter in force on the overview is honoured so a teacher restricted to
// separate groups only ever exports the attempts they may already see.
if ($dataformat !== '' && $attemptid === 0) {
    require_capability('mod/elang:exportreports', $context);
    $report = new \mod_elang\local\report\attempt_report();
    \core\dataformat::download_data(
        clean_filename(format_string($elang->name) . '-attempts'),
        $dataformat,
        $report->export_columns(),
        $report->export_rows((int) $elang->id, (int) $currentgroup, $filters, $sort, $direction)
    );
    exit;
}

// Deleting an attempt is destructive, so it runs only through a confirmed POST
// carrying a valid session key, and the attempt is re-checked against this
// activity before anything is removed.
if ($action === 'delete') {
    require_capability('mod/elang:deleteattempts', $context);
    $deleteid = required_param('attemptid', PARAM_INT);
    // Object-level authorisation: the attempt must belong to this activity and,
    // in separate-groups mode, to a learner this teacher shares a group with.
    $todelete = (new \mod_elang\local\report\attempt_report())
        ->require_attempt_access($deleteid, (int) $elang->id, $cm, $context);

    if (optional_param('confirm', 0, PARAM_BOOL) && confirm_sesskey()) {
        $manager = new \mod_elang\local\domain\attempt_manager(
            new \mod_elang\local\grading\answer_evaluator(
                new \mod_elang\local\grading\script_handler_manager()
            )
        );
        $deleted = $manager->delete_attempt($deleteid);
        elang_update_grades($elang, (int) $deleted->userid);
        redirect(
            new moodle_url('/mod/elang/report.php', ['id' => $cm->id]),
            get_string('report_deleted', 'mod_elang'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    $PAGE->set_url('/mod/elang/report.php', ['id' => $cm->id, 'action' => 'delete', 'attemptid' => $deleteid]);
    echo $OUTPUT->header();
    echo $OUTPUT->confirm(
        get_string('report_deleteconfirm', 'mod_elang'),
        new moodle_url('/mod/elang/report.php', [
            'id' => $cm->id,
            'action' => 'delete',
            'attemptid' => $deleteid,
            'confirm' => 1,
            'sesskey' => sesskey(),
        ]),
        new moodle_url('/mod/elang/report.php', ['id' => $cm->id])
    );
    echo $OUTPUT->footer();
    exit;
}

$report = new \mod_elang\local\report\attempt_report();

// Built before any output: submitting the filters redirects to their
// canonical URL, and a redirect after the header has been sent is too late.
$filterform = null;
if (!$attemptid) {
    // The person filter offers exactly the people this caller may already see:
    // filter_users() reuses the report's own group-scoped query. Its own
    // "everyone with an attempt here" query ignored the group scope, which in
    // separate-groups mode showed a teacher the names of learners whose
    // attempts the report itself was hiding.
    $useroptions = [0 => get_string('report_filterany', 'mod_elang')]
        + $report->filter_users((int) $elang->id, (int) $currentgroup);

    $filterform = new \mod_elang\form\report_filter_form(
        new moodle_url('/mod/elang/report.php'),
        ['users' => $useroptions],
        'get'
    );
    // Submitting the form lands on the canonical URL for the chosen filters,
    // so what the teacher then sees is a link they can bookmark or pass on —
    // and every later request reads scalars only.
    if ($formdata = $filterform->get_data()) {
        $chosen = \mod_elang\local\report\attempt_report::clean_filters([
            'userid' => (int) ($formdata->filteruserid ?? 0),
            'state' => (string) ($formdata->filterstate ?? ''),
            'from' => (int) ($formdata->filterfrom ?? 0),
            'to' => (int) ($formdata->filterto ?? 0),
            'attemptnumber' => (int) ($formdata->filterattemptnumber ?? 0),
        ]);
        $target = ['id' => $cm->id];
        foreach ($chosen as $key => $value) {
            $target[ELANG_FILTER_PARAMS[$key]] = $value;
        }
        redirect(new moodle_url('/mod/elang/report.php', $target));
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report_heading', 'mod_elang'));

if ($attemptid) {
    $report->require_attempt_access($attemptid, (int) $elang->id, $cm, $context);
    $detail = $report->detail($attemptid);

    $owner = $DB->get_record('user', ['id' => $detail['attempt']['userid']]);

    echo $OUTPUT->render_from_template('mod_elang/attempt_detail', (new \mod_elang\output\attempt_detail(
        $detail['attempt'],
        $detail['gaps'],
        $owner ? fullname($owner) : (string) $detail['attempt']['userid'],
        (int) $cm->id
    ))->export_for_template($OUTPUT));
} else {
    if ($groupmode != NOGROUPS) {
        echo groups_print_activity_menu($cm, $PAGE->url, true);
    }

    $filterform->set_data([
        'id' => $cm->id,
        'filteruserid' => $filters['userid'] ?? 0,
        'filterstate' => $filters['state'] ?? '',
        'filterfrom' => $filters['from'] ?? 0,
        'filterto' => $filters['to'] ?? 0,
        'filterattemptnumber' => $filters['attemptnumber'] ?? 0,
    ]);
    $filterform->display();

    $baseurl = new moodle_url('/mod/elang/report.php', $urlparams);

    $overview = new \mod_elang\output\report_overview(
        $baseurl,
        $report->list_for_activity(
            (int) $elang->id,
            (int) $currentgroup,
            $page,
            ELANG_REPORT_PERPAGE,
            $filters,
            $sort,
            $direction
        ),
        $report->aggregate_for_activity((int) $elang->id, (int) $currentgroup, $filters),
        $sort,
        $direction,
        $candelete,
        $canexport,
        !empty($filters)
    );

    echo $OUTPUT->render_from_template('mod_elang/report_overview', $overview->export_for_template($OUTPUT));

    echo $OUTPUT->paging_bar(
        $report->count_for_activity((int) $elang->id, (int) $currentgroup, $filters),
        $page,
        ELANG_REPORT_PERPAGE,
        $baseurl
    );
}

echo $OUTPUT->footer();
