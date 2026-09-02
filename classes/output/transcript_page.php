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

namespace mod_elang\output;

use moodle_url;
use renderer_base;
use templatable;
use renderable;

/**
 * The transcript export page: one card per export product.
 *
 * Each product leads with PDF, the format teachers actually print, and keeps
 * the remaining formats in a menu rather than as a row of equal links — a
 * choice of four made the page read as a format list rather than as two
 * things one can take away.
 *
 * Which cards exist is decided before this class is built, by
 * elang_can_export_worksheet() and elang_can_export_solution(). Nothing here
 * grants access; it only describes what was already granted.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class transcript_page implements renderable, templatable {
    /** @var int The course module id */
    private int $cmid;

    /** @var bool Whether the masked worksheet may be downloaded */
    private bool $canworksheet;

    /** @var bool Whether the solution transcript may be downloaded */
    private bool $cansolution;

    /** @var string How the activity offers the solution to learners */
    private string $solutionavailability;

    /** @var bool Whether the activity has a published version to export at all */
    private bool $haspublished;

    /**
     * Build the page description.
     *
     * @param int $cmid The course module id
     * @param bool $canworksheet Whether the masked worksheet may be downloaded
     * @param bool $cansolution Whether the solution transcript may be downloaded
     * @param string $solutionavailability never | aftersubmission | always
     * @param bool $haspublished Whether a published version exists
     */
    public function __construct(
        int $cmid,
        bool $canworksheet,
        bool $cansolution,
        string $solutionavailability,
        bool $haspublished
    ) {
        $this->cmid = $cmid;
        $this->canworksheet = $canworksheet;
        $this->cansolution = $cansolution;
        $this->solutionavailability = $solutionavailability;
        $this->haspublished = $haspublished;
    }

    /**
     * Build the download URL for one product in one format.
     *
     * @param string $format pdf | docx | odt | txt
     * @param bool $solution Whether this is the solution product
     * @return string The absolute URL
     */
    private function download_url(string $format, bool $solution): string {
        $params = ['id' => $this->cmid, 'format' => $format];
        if ($solution) {
            $params['solution'] = 1;
        }

        return (new moodle_url('/mod/elang/transcript.php', $params))->out(false);
    }

    /**
     * The formats offered behind the "more formats" menu of one product.
     *
     * @param bool $solution Whether this is the solution product
     * @return array The secondary formats, each with a label and a URL
     */
    private function secondary_formats(bool $solution): array {
        $formats = [
            'docx' => 'export_docx',
            'odt' => 'export_odt',
            'txt' => 'export_text',
        ];

        $out = [];
        foreach ($formats as $format => $stringkey) {
            $out[] = [
                'label' => get_string($stringkey, 'mod_elang'),
                'url' => $this->download_url($format, $solution),
                'format' => $format,
            ];
        }

        return $out;
    }

    /**
     * Describe who may take the solution transcript.
     *
     * The audience is not fixed any more: an activity may hand the solution to
     * learners immediately, only once they have finished, or never. Saying
     * "teachers only" regardless would be wrong for two of the three.
     *
     * @return string The badge text
     */
    private function solution_audience(): string {
        switch ($this->solutionavailability) {
            case 'always':
                return get_string('export_audiencealways', 'mod_elang');
            case 'aftersubmission':
                return get_string('export_audienceaftersubmission', 'mod_elang');
            default:
                return get_string('export_audiencestaff', 'mod_elang');
        }
    }

    /**
     * Export the page for the template.
     *
     * @param renderer_base $output The renderer
     * @return array The template context
     */
    public function export_for_template(renderer_base $output): array {
        $cards = [];

        if ($this->canworksheet) {
            $cards[] = [
                'key' => 'worksheet',
                'title' => get_string('export_worksheet', 'mod_elang'),
                'description' => get_string('export_worksheethint', 'mod_elang'),
                'audience' => '',
                'hasaudience' => false,
                'primaryurl' => $this->download_url('pdf', false),
                'primarylabel' => get_string('export_downloadpdf', 'mod_elang'),
                'formats' => $this->secondary_formats(false),
            ];
        }

        if ($this->cansolution) {
            $cards[] = [
                'key' => 'solution',
                'title' => get_string('export_solution', 'mod_elang'),
                'description' => get_string('export_solutionhint', 'mod_elang'),
                'audience' => $this->solution_audience(),
                'hasaudience' => true,
                'primaryurl' => $this->download_url('pdf', true),
                'primarylabel' => get_string('export_downloadpdf', 'mod_elang'),
                'formats' => $this->secondary_formats(true),
            ];
        }

        return [
            'heading' => get_string('export_heading', 'mod_elang'),
            'intro' => get_string('export_intro', 'mod_elang'),
            'haspublished' => $this->haspublished,
            'nocontent' => get_string('export_nocontent', 'mod_elang'),
            'hascards' => !empty($cards),
            'cards' => $cards,
            'moreformats' => get_string('export_moreformats', 'mod_elang'),
            'versionnote' => get_string('export_versionnote', 'mod_elang'),
        ];
    }
}
