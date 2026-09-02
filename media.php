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
 * The first stop of the authoring workflow: subtitles are timed against a
 * medium, so the editor refuses to open before one exists. A manager either
 * uploads a video/audio file (and an optional poster) through Moodle's file
 * picker, or gives a source URL — which the provider registry resolves to a
 * YouTube or Vimeo embed, or keeps as a direct media URL. What is currently
 * set is shown next to the form, because replacing the medium of an activity
 * that already has cues is a decision that should not be made blind.
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

$mediaurl = new moodle_url('/mod/elang/media.php', ['id' => $cm->id]);

$mediaoptions = ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['video', 'audio']];
$posteroptions = ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['image']];

$mediadraftid = file_get_submitted_draft_itemid('mediafiles');
file_prepare_draft_area($mediadraftid, $context->id, 'mod_elang', 'media', $draft->id, $mediaoptions);
$posterdraftid = file_get_submitted_draft_itemid('posterfiles');
file_prepare_draft_area($posterdraftid, $context->id, 'mod_elang', 'poster', $draft->id, $posteroptions);

$providers = implode(', ', array_map(
    fn(string $key): string => get_string('provider_' . $key, 'mod_elang'),
    \mod_elang\local\media\provider_registry::providers()
));

$form = new \mod_elang\form\media_form(null, [
    'mediaoptions' => $mediaoptions,
    'posteroptions' => $posteroptions,
    'providers' => $providers,
]);
$form->set_data([
    'id' => $cm->id,
    'versionid' => $draft->id,
    'mediafiles' => $mediadraftid,
    'posterfiles' => $posterdraftid,
    // Guarded with ??: get_or_create_draft() returns a freshly inserted record
    // whose nullable media columns are not populated on the object it hands
    // back, so reading them directly warns on a brand new draft.
    'mediaurl' => ($draft->mediakind ?? '') === 'file' ? '' : (string) ($draft->mediaurl ?? ''),
]);

if ($form->is_cancelled()) {
    redirect($mediaurl);
} else if ($data = $form->get_data()) {
    $source = trim((string) ($data->mediaurl ?? ''));

    if ($source !== '') {
        // An explicit source wins over an upload: a teacher who fills this in
        // has said which medium they mean, and silently keeping a leftover file
        // would leave the version in a state neither of them chose.
        $detected = \mod_elang\local\media\provider_registry::detect($source);
        $versionmanager->set_draft_media((int) $draft->id, $detected !== null
            ? [
                'kind' => 'provider',
                'provider' => $detected['provider'],
                'providerref' => $detected['reference'],
                'posterdraftitemid' => (int) $data->posterfiles,
            ]
            : [
                'kind' => 'url',
                'url' => $source,
                'posterdraftitemid' => (int) $data->posterfiles,
            ]);
    } else {
        $versionmanager->set_draft_media((int) $draft->id, [
            'kind' => 'file',
            'mediadraftitemid' => (int) $data->mediafiles,
            'posterdraftitemid' => (int) $data->posterfiles,
        ]);
    }

    // Back to this page, not on to the editor: the medium is now visible next
    // to the form, so the author can confirm what was stored before moving on.
    redirect($mediaurl, get_string('editor_mediasaved', 'mod_elang'));
}

$PAGE->set_url('/mod/elang/media.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($elang->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_activity_record($elang);
$PAGE->set_secondary_active_tab('mod_elang_media');

echo $OUTPUT->header();

$hascues = $DB->record_exists('elang_cue', ['versionid' => $draft->id]);

echo $OUTPUT->render_from_template('mod_elang/media_page', (new \mod_elang\output\media_page(
    $draft,
    $context,
    $form->render(),
    $hascues
))->export_for_template($OUTPUT));

echo $OUTPUT->footer();
