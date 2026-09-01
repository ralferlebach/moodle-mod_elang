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
 * Content authoring page for mod_elang.
 *
 * Ensures the activity has a draft version to edit (branching a copy from the
 * published version when there is one), refuses to open without a medium (that
 * belongs on the media page) and hands off to the mod_elang/editor
 * AMD module, which loads the draft through the external API and drives editing,
 * subtitle import, saving and publishing, including the timeline with draggable
 * cue edges.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'elang');
$elang = $DB->get_record('elang', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/elang:manage', $context);

$PAGE->set_url('/mod/elang/edit.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($elang->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_activity_record($elang);
$PAGE->set_secondary_active_tab('mod_elang_editcontent');

$draft = (new \mod_elang\local\domain\version_manager())->get_or_create_draft((int) $elang->id);

// Subtitles are timed against a medium, so editing them before one exists
// produces a timeline with nothing to scrub and cue times that cannot be
// captured from playback. The check is server side rather than a disabled
// button, so calling this URL directly lands on the same notice.
$mediakind = (string) ($draft->mediakind ?? '');
if ($mediakind === '' || $mediakind === 'none') {
    $mediaurl = new moodle_url('/mod/elang/media.php', ['id' => $cm->id]);
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('editor:nomedianotice', 'mod_elang'), 'info');
    echo $OUTPUT->single_button($mediaurl, get_string('editor:gotomedia', 'mod_elang'), 'get');
    echo $OUTPUT->footer();
    exit;
}

// Load the prebuilt React editor bundle as a regular page script. It lives in
// js/vendor/react/ (not amd/build/, which moodle-plugin-ci wipes and rebuilds)
// and exposes window.mod_elang_editor; the mod_elang/editor AMD module then
// mounts it. Loading via $PAGE->requires->js keeps it in Moodle's asset
// handling.
//
// The plugin ships its own React bundle because it still supports Moodle 4.5
// through 5.1, which do not provide React in core. Moodle 5.2 ships React (with
// import maps) in core; once the minimum supported version rises to 5.2 — the
// next release after 5.1 reaches end of life — this should switch to core's
// React instead of a second bundled copy, at which point this file and build.mjs
// change together.
$PAGE->requires->js(new moodle_url('/mod/elang/js/vendor/react/editor.bundle.js'));
$PAGE->requires->js_call_amd('mod_elang/editor', 'init', [(int) $draft->id]);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_elang/editor', [
    'mediauploadurl' => (new moodle_url('/mod/elang/media.php', ['id' => $cm->id]))->out(false),
]);
echo $OUTPUT->footer();
