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
 * List of all elang instances in a course.
 *
 * Moodle 5.0 and later replace per-module index pages with the central activities
 * overview, which this plugin serves through \mod_elang\courseformat\overview.
 * This page is retained for Moodle 4.5, where the overview does not exist yet.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
require_course_login($course);

$context = context_course::instance($course->id);

$event = \mod_elang\event\course_module_instance_list_viewed::create(['context' => $context]);
$event->add_record_snapshot('course', $course);
$event->trigger();

$PAGE->set_url('/mod/elang/index.php', ['id' => $course->id]);
$PAGE->set_title(format_string($course->shortname) . ': ' . get_string('modulenameplural', 'mod_elang'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mod_elang'));

$instances = get_all_instances_in_course('elang', $course);

if (empty($instances)) {
    echo $OUTPUT->notification(get_string('noinstances', 'mod_elang'), \core\output\notification::NOTIFY_INFO);
    echo $OUTPUT->footer();
    exit;
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($course->format === 'weeks') {
    $table->head = [get_string('week'), get_string('name')];
} else if ($course->format === 'topics') {
    $table->head = [get_string('topic'), get_string('name')];
} else {
    $table->head = [get_string('name')];
}

foreach ($instances as $instance) {
    $link = html_writer::link(
        new moodle_url('/mod/elang/view.php', ['id' => $instance->coursemodule]),
        format_string($instance->name, true),
        ['class' => $instance->visible ? '' : 'dimmed']
    );

    if (count($table->head) === 2) {
        $table->data[] = [$instance->section, $link];
    } else {
        $table->data[] = [$link];
    }
}

echo html_writer::table($table);
echo $OUTPUT->footer();
