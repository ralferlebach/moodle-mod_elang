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
 * Streams the published version's transcript as a PDF or a text file, or shows
 * a small chooser when no format is given. Gated on mod/elang:exporttranscript,
 * which learners hold too.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$format = optional_param('format', '', PARAM_ALPHA);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'elang');
$elang = $DB->get_record('elang', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/elang:exporttranscript', $context);

$version = (new \mod_elang\local\domain\version_manager())->get_published((int) $elang->id);
$name = clean_filename(format_string($elang->name));

if ($format === 'pdf' || $format === 'txt' || $format === 'docx' || $format === 'odt') {
    if ($version === null) {
        throw new moodle_exception('error:nopublishedversion', 'mod_elang');
    }

    $exporter = new \mod_elang\local\export\transcript_exporter();

    if ($format === 'txt') {
        send_file(
            $exporter->plain_text((int) $version->id),
            $name . '.txt',
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
            $exporter->paragraphs((int) $version->id)
        );
        send_file(
            $bytes,
            $name . '.docx',
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
            $exporter->paragraphs((int) $version->id)
        );
        send_file($bytes, $name . '.odt', 0, 0, true, true, 'application/vnd.oasis.opendocument.text');
    }

    $text = $exporter->plain_text((int) $version->id);

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
    $pdf->Output($name . '.pdf', 'D');
    exit;
}

$PAGE->set_url('/mod/elang/transcript.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($elang->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('exporttranscript', 'mod_elang'));

if ($version === null) {
    echo html_writer::div(get_string('export:nocontent', 'mod_elang'));
} else {
    $pdfurl = new moodle_url('/mod/elang/transcript.php', ['id' => $cm->id, 'format' => 'pdf']);
    $txturl = new moodle_url('/mod/elang/transcript.php', ['id' => $cm->id, 'format' => 'txt']);
    $docxurl = new moodle_url('/mod/elang/transcript.php', ['id' => $cm->id, 'format' => 'docx']);
    $odturl = new moodle_url('/mod/elang/transcript.php', ['id' => $cm->id, 'format' => 'odt']);
    echo html_writer::div(
        html_writer::link($pdfurl, get_string('export:pdf', 'mod_elang'))
        . ' · '
        . html_writer::link($docxurl, get_string('export:docx', 'mod_elang'))
        . ' · '
        . html_writer::link($odturl, get_string('export:odt', 'mod_elang'))
        . ' · '
        . html_writer::link($txturl, get_string('export:text', 'mod_elang'))
    );
}

echo $OUTPUT->footer();
