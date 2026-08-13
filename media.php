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
 * Media upload page for mod_elang.
 *
 * Lets a manager upload the video/audio (and optional poster) for the activity's
 * draft version through Moodle's file picker, then stores them on the version
 * via version_manager::set_draft_media(). Uploading a medium here switches the
 * version to file-kind media.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

$id = required_param('id', PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'elang');
$elang = $DB->get_record('elang', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/elang:manage', $context);

$versionmanager = new \mod_elang\local\domain\version_manager();
$draft = $versionmanager->get_or_create_draft((int) $elang->id);

$editurl = new moodle_url('/mod/elang/edit.php', ['id' => $cm->id]);

$mediaoptions = ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['video', 'audio']];
$posteroptions = ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['image']];

$mediadraftid = file_get_submitted_draft_itemid('mediafiles');
file_prepare_draft_area($mediadraftid, $context->id, 'mod_elang', 'media', $draft->id, $mediaoptions);
$posterdraftid = file_get_submitted_draft_itemid('posterfiles');
file_prepare_draft_area($posterdraftid, $context->id, 'mod_elang', 'poster', $draft->id, $posteroptions);

$form = new \mod_elang\form\media_form(null, [
    'mediaoptions' => $mediaoptions,
    'posteroptions' => $posteroptions,
]);
$form->set_data([
    'id' => $cm->id,
    'versionid' => $draft->id,
    'mediafiles' => $mediadraftid,
    'posterfiles' => $posterdraftid,
]);

if ($form->is_cancelled()) {
    redirect($editurl);
} else if ($data = $form->get_data()) {
    $versionmanager->set_draft_media((int) $draft->id, [
        'kind' => 'file',
        'mediadraftitemid' => (int) $data->mediafiles,
        'posterdraftitemid' => (int) $data->posterfiles,
    ]);
    redirect($editurl, get_string('editor:mediasaved', 'mod_elang'));
}

$PAGE->set_url('/mod/elang/media.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($elang->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editor:uploadmedia', 'mod_elang'));
$form->display();
echo $OUTPUT->footer();
