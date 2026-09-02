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
use renderable;
use renderer_base;
use templatable;

/**
 * One attempt, read as a piece of work rather than as a row dump.
 *
 * The gaps are grouped by the cue they belong to, because that is the unit a
 * teacher thinks in: "which sentences did this person struggle with" is a
 * question the flat table could not answer without reading every row and
 * mentally regrouping them. The counts a teacher checks first — answered, of
 * those correct, how many needed a hint — lead, so the table only has to
 * confirm them.
 *
 * This class runs no queries. Everything comes from attempt_report::detail().
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attempt_detail implements renderable, templatable {
    /** How a graded gap is shown: an icon, and the wording as its accessible name. */
    private const RESULT_ICONS = [
        'exact' => ['icon' => 'fa-check', 'class' => 'text-success', 'key' => 'report_result_exact'],
        'wordrecognized' => [
            'icon' => 'fa-exclamation-triangle',
            'class' => 'text-warning',
            'key' => 'report_result_wordrecognized',
        ],
        'incorrect' => ['icon' => 'fa-times', 'class' => 'text-danger', 'key' => 'report_result_incorrect'],
        '' => ['icon' => 'fa-minus', 'class' => 'text-muted', 'key' => 'report_result_empty'],
    ];

    /** @var array The attempt summary from attempt_report::detail() */
    private array $attempt;

    /** @var array The gap rows from attempt_report::detail() */
    private array $gaps;

    /** @var string The learner's name */
    private string $fullname;

    /** @var int The course module id */
    private int $cmid;

    /**
     * Build the detail description.
     *
     * @param array $attempt The attempt summary
     * @param array $gaps The gap rows, in cue order
     * @param string $fullname The learner's name
     * @param int $cmid The course module id
     */
    public function __construct(array $attempt, array $gaps, string $fullname, int $cmid) {
        $this->attempt = $attempt;
        $this->gaps = $gaps;
        $this->fullname = $fullname;
        $this->cmid = $cmid;
    }

    /**
     * The counts a teacher checks before reading anything else.
     *
     * @return array Each with a label and a value
     */
    private function figures(): array {
        $total = (int) $this->attempt['totalgaps'];
        $answered = (int) $this->attempt['answeredgaps'];

        return [
            [
                'label' => get_string('report_kpianswered', 'mod_elang'),
                'value' => $answered . ' / ' . $total,
            ],
            [
                'label' => get_string('report_kpicorrect', 'mod_elang'),
                'value' => (string) (int) $this->attempt['correctgaps'],
            ],
            [
                'label' => get_string('report_kpiexact', 'mod_elang'),
                'value' => (string) (int) $this->attempt['exactgaps'],
            ],
            [
                'label' => get_string('report_kpihintedgaps', 'mod_elang'),
                'value' => (string) (int) $this->attempt['hintedgaps'],
            ],
        ];
    }

    /**
     * Group the gaps under the cue they belong to.
     *
     * detail() returns them in cue order already, so consecutive rows sharing a
     * transcript belong together. Grouping on the transcript rather than on a
     * cue id keeps this class free of a second query, and two adjacent cues
     * with identical text would read the same either way.
     *
     * @param renderer_base $output The renderer
     * @return array The cues, each with its gaps
     */
    private function cues(renderer_base $output): array {
        $cues = [];
        $current = null;

        foreach ($this->gaps as $gap) {
            $transcript = (string) $gap['transcript'];
            if ($current === null || $current['transcript'] !== $transcript) {
                if ($current !== null) {
                    $cues[] = $current;
                }
                $current = ['transcript' => $transcript, 'gaps' => [], 'hasopen' => false];
            }

            $state = (string) $gap['resultstate'];
            $info = self::RESULT_ICONS[$state] ?? self::RESULT_ICONS[''];
            $label = get_string($info['key'], 'mod_elang');

            $current['gaps'][] = [
                'solution' => (string) $gap['solution'],
                'responsetext' => (string) $gap['responsetext'],
                'hasresponse' => trim((string) $gap['responsetext']) !== '',
                'noresponse' => get_string('report_noresponse', 'mod_elang'),
                'icon' => $info['icon'],
                'iconclass' => $info['class'],
                'resultlabel' => $label,
                'tries' => (int) $gap['tries'],
                'hintlevel' => (int) $gap['hintlevel'],
                'usedhint' => (int) $gap['hintlevel'] > 0,
                'score' => format_float((float) $gap['score'], 2),
            ];

            if ($state === '' || $state === 'incorrect') {
                $current['hasopen'] = true;
            }
        }

        if ($current !== null) {
            $cues[] = $current;
        }

        return $cues;
    }

    /**
     * Export the detail for the template.
     *
     * @param renderer_base $output The renderer
     * @return array The template context
     */
    public function export_for_template(renderer_base $output): array {
        $states = [
            'inprogress' => 'badge-warning bg-warning text-dark',
            'finished' => 'badge-success bg-success',
            'abandoned' => 'badge-secondary bg-secondary',
        ];
        $state = (string) $this->attempt['state'];

        return [
            'fullname' => $this->fullname,
            'statelabel' => get_string('report_state_' . $state, 'mod_elang'),
            'stateclass' => $states[$state] ?? 'badge-secondary bg-secondary',
            'scorelabel' => get_string('report_score', 'mod_elang'),
            'score' => format_float((float) $this->attempt['score'], 2),
            'startedlabel' => get_string('report_started', 'mod_elang'),
            'started' => userdate((int) $this->attempt['timestart']),
            'finishedlabel' => get_string('report_finished', 'mod_elang'),
            'finished' => $this->attempt['timefinish']
                ? userdate((int) $this->attempt['timefinish'])
                : '—',
            'figures' => $this->figures(),
            'cues' => $this->cues($output),
            'hascues' => !empty($this->gaps),
            'nogaps' => get_string('report_nogaps', 'mod_elang'),
            'headsolution' => get_string('report_solution', 'mod_elang'),
            'headresponse' => get_string('report_response', 'mod_elang'),
            'headtries' => get_string('report_tries', 'mod_elang'),
            'headscore' => get_string('report_score', 'mod_elang'),
            'hintlabel' => get_string('report_hints', 'mod_elang'),
            'backurl' => (new moodle_url('/mod/elang/report.php', ['id' => $this->cmid]))->out(false),
            'backlabel' => get_string('report_back', 'mod_elang'),
        ];
    }
}
