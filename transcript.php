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
 * Transcript export for mod_elang.
 *
 * Streams the published version's transcript as a PDF, Word, ODF or text file,
 * or shows a small chooser when no format is given. The learner worksheet
 * (every gap blanked out) is gated on mod/elang:exporttranscript, which
 * learners hold too; the solution copy (full text) additionally requires
 * mod/elang:exportsolution, which learners do not hold.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$format = optional_param('format', '', PARAM_ALPHA);
$solution = optional_param('solution', 0, PARAM_BOOL);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'elang');
$elang = $DB->get_record('elang', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/elang:exporttranscript', $context);

// The capability alone is not the whole answer: learners hold it by default,
// so the activity's own settings decide whether they get a worksheet at all,
// and whether (and when) they may also see the solutions. Staff hold
// mod/elang:exportsolution and are unaffected by either setting.
$canworksheet = elang_can_export_worksheet($elang, $context);
$cansolution = elang_can_export_solution($elang, $context);
if (!$canworksheet && !$cansolution) {
    throw new moodle_exception('error:transcriptnotavailable', 'mod_elang');
}
if ($solution && !$cansolution) {
    throw new moodle_exception('error:solutionnotavailable', 'mod_elang');
}
if (!$solution && !$canworksheet) {
    throw new moodle_exception('error:transcriptnotavailable', 'mod_elang');
}

$masked = !$solution;
$suffix = $masked ? '' : '-solution';

$version = (new \mod_elang\local\domain\version_manager())->get_published((int) $elang->id);
$name = clean_filename(format_string($elang->name));

if ($format === 'pdf' || $format === 'txt' || $format === 'docx' || $format === 'odt') {
    if ($version === null) {
        throw new moodle_exception('error:nopublishedversion', 'mod_elang');
    }

    $exporter = new \mod_elang\local\export\transcript_exporter();

    if ($format === 'txt') {
        send_file(
            $exporter->plain_text((int) $version->id, $masked),
            $name . $suffix . '.txt',
            0,
            0,
            true,
            true,
            'text/plain; charset=utf-8'
        );
    }

    if ($format === 'docx') {
        $bytes = (new \mod_elang\local\export\docx_writer())->build(
            format_string($elang->name),
            $exporter->paragraphs((int) $version->id, $masked)
        );
        send_file(
            $bytes,
            $name . $suffix . '.docx',
            0,
            0,
            true,
            true,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );
    }

    if ($format === 'odt') {
        $bytes = (new \mod_elang\local\export\odt_writer())->build(
            format_string($elang->name),
            $exporter->paragraphs((int) $version->id, $masked)
        );
        send_file($bytes, $name . $suffix . '.odt', 0, 0, true, true, 'application/vnd.oasis.opendocument.text');
    }

    $text = $exporter->plain_text((int) $version->id, $masked);

    require_once($CFG->libdir . '/pdflib.php');
    $pdf = new pdf();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    $pdf->SetFont('freeserif', '', 12);

    $html = html_writer::tag('h1', s(format_string($elang->name)));
    foreach (explode("\n\n", $text) as $paragraph) {
        $html .= html_writer::tag('p', nl2br(s($paragraph)));
    }
    $pdf->writeHTML($html);
    $pdf->Output($name . $suffix . '.pdf', 'D');
    exit;
}

$PAGE->set_url('/mod/elang/transcript.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($elang->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_activity_record($elang);
$PAGE->set_secondary_active_tab('mod_elang_exporttranscript');

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('mod_elang/transcript_page', (new \mod_elang\output\transcript_page(
    (int) $cm->id,
    $canworksheet,
    $cansolution,
    (string) ($elang->solutionavailability ?? 'never'),
    $version !== null
))->export_for_template($OUTPUT));

echo $OUTPUT->footer();
