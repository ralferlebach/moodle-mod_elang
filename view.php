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
 * Main activity page for mod_elang.
 *
 * Renders the Moodle-native player shell inside the standard page frame and
 * hands off to the mod_elang/player AMD module, which drives the attempt
 * lifecycle (start/resume the attempt, load the pinned version's media and
 * cues) entirely through the external API. Answering, media/cue
 * synchronisation and resume are layered on in later phase 3 slices.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/completionlib.php');

$id = required_param('id', PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'elang');
$elang = $DB->get_record('elang', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/elang:view', $context);

$PAGE->set_url('/mod/elang/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($elang->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_activity_record($elang);

$event = \mod_elang\event\course_module_viewed::create([
    'objectid' => $elang->id,
    'context' => $context,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('elang', $elang);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->requires->js_call_amd('mod_elang/player', 'init', [(int) $cm->id]);

echo $OUTPUT->header();

$actions = '';
if (has_capability('mod/elang:manage', $context)) {
    $actions .= $OUTPUT->single_button(
        new moodle_url('/mod/elang/edit.php', ['id' => $cm->id]),
        get_string('editcontent', 'mod_elang'),
        'get'
    );
}
if (has_capability('mod/elang:viewreports', $context)) {
    $actions .= $OUTPUT->single_button(
        new moodle_url('/mod/elang/report.php', ['id' => $cm->id]),
        get_string('reports', 'mod_elang'),
        'get'
    );
}
if (has_capability('mod/elang:exporttranscript', $context)) {
    $actions .= $OUTPUT->single_button(
        new moodle_url('/mod/elang/transcript.php', ['id' => $cm->id]),
        get_string('exporttranscript', 'mod_elang'),
        'get'
    );
}
if ($actions !== '') {
    echo html_writer::div($actions, 'mod_elang-actions mb-3');
}

echo $OUTPUT->render_from_template('mod_elang/player', []);
echo $OUTPUT->footer();
