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

$id = required_param('id', PARAM_INT);
$attemptid = optional_param('attemptid', 0, PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'elang');
$elang = $DB->get_record('elang', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/elang:viewreports', $context);

$PAGE->set_url('/mod/elang/report.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($elang->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$groupmode = groups_get_activity_groupmode($cm, $course);

$statelabel = function (string $state): string {
    $labels = [
        'inprogress' => get_string('report:state_inprogress', 'mod_elang'),
        'finished' => get_string('report:state_finished', 'mod_elang'),
        'abandoned' => get_string('report:state_abandoned', 'mod_elang'),
    ];
    return $labels[$state] ?? $state;
};

$resultlabel = function (string $state): string {
    if ($state === '') {
        return get_string('report:result_none', 'mod_elang');
    }
    $labels = [
        'exact' => get_string('report:result_exact', 'mod_elang'),
        'wordrecognized' => get_string('report:result_wordrecognized', 'mod_elang'),
        'incorrect' => get_string('report:result_incorrect', 'mod_elang'),
        'empty' => get_string('report:result_empty', 'mod_elang'),
    ];
    return $labels[$state] ?? $state;
};

$report = new \mod_elang\local\report\attempt_report();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report:heading', 'mod_elang'));

if ($attemptid) {
    $detail = $report->detail($attemptid);
    if ((int) $detail['attempt']['elangid'] !== (int) $elang->id) {
        throw new moodle_exception('invalidrecord', 'error');
    }

    if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
        $allowedgroups = groups_get_activity_allowed_groups($cm);
        $shared = false;
        foreach (array_keys($allowedgroups) as $groupid) {
            if (groups_is_member($groupid, (int) $detail['attempt']['userid'])) {
                $shared = true;
                break;
            }
        }
        if (!$shared) {
            throw new moodle_exception('nopermissions', 'error', '', get_string('report:heading', 'mod_elang'));
        }
    }

    $attempt = $detail['attempt'];
    $user = $DB->get_record('user', ['id' => $attempt['userid']]);
    echo html_writer::tag('p', ($user ? fullname($user) : (string) $attempt['userid'])
        . ' — ' . $statelabel($attempt['state'])
        . ' — ' . get_string('report:score', 'mod_elang') . ': ' . format_float($attempt['score'], 2));

    $table = new html_table();
    $table->head = [
        get_string('report:transcript', 'mod_elang'),
        get_string('report:solution', 'mod_elang'),
        get_string('report:response', 'mod_elang'),
        get_string('report:result', 'mod_elang'),
        get_string('report:tries', 'mod_elang'),
        get_string('report:hints', 'mod_elang'),
        get_string('report:score', 'mod_elang'),
    ];
    foreach ($detail['gaps'] as $gap) {
        $table->data[] = [
            s(shorten_text($gap['transcript'], 60)),
            s($gap['solution']),
            s($gap['responsetext']),
            $resultlabel($gap['resultstate']),
            $gap['tries'],
            $gap['hintlevel'],
            format_float($gap['score'], 2),
        ];
    }
    echo html_writer::table($table);

    echo html_writer::div(html_writer::link(
        new moodle_url('/mod/elang/report.php', ['id' => $cm->id]),
        get_string('report:back', 'mod_elang')
    ));
} else {
    $currentgroup = 0;
    if ($groupmode != NOGROUPS) {
        $currentgroup = groups_get_activity_group($cm, true);
        echo groups_print_activity_menu($cm, $PAGE->url, true);
    }
    $attempts = $report->list_for_activity((int) $elang->id, (int) $currentgroup);
    if (empty($attempts)) {
        echo html_writer::div(get_string('report:noattempts', 'mod_elang'));
    } else {
        $users = $DB->get_records_list('user', 'id', array_unique(array_column($attempts, 'userid')));

        $table = new html_table();
        $table->head = [
            get_string('report:user', 'mod_elang'),
            get_string('report:attemptnumber', 'mod_elang'),
            get_string('report:state', 'mod_elang'),
            get_string('report:score', 'mod_elang'),
            get_string('report:answered', 'mod_elang'),
            get_string('report:correct', 'mod_elang'),
            get_string('report:exact', 'mod_elang'),
            get_string('report:hinted', 'mod_elang'),
            get_string('report:finished', 'mod_elang'),
            '',
        ];
        foreach ($attempts as $attempt) {
            $user = $users[$attempt['userid']] ?? null;
            $viewurl = new moodle_url('/mod/elang/report.php', ['id' => $cm->id, 'attemptid' => $attempt['attemptid']]);
            $table->data[] = [
                $user ? fullname($user) : (string) $attempt['userid'],
                $attempt['attemptnumber'],
                $statelabel($attempt['state']),
                format_float($attempt['score'], 2),
                $attempt['answeredgaps'] . ' / ' . $attempt['totalgaps'],
                $attempt['correctgaps'],
                $attempt['exactgaps'],
                $attempt['hintedgaps'],
                $attempt['timefinish'] ? userdate($attempt['timefinish']) : '-',
                html_writer::link($viewurl, get_string('report:view', 'mod_elang')),
            ];
        }
        echo html_writer::table($table);
    }
}

echo $OUTPUT->footer();
