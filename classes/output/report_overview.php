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

use action_menu;
use action_menu_link;
use moodle_url;
use pix_icon;
use renderable;
use renderer_base;
use templatable;

/**
 * The attempt overview: figures, then the table those figures describe.
 *
 * This class only arranges what the report class returned. It runs no queries
 * of its own, so the numbers in the header and the rows in the table can never
 * describe different sets of attempts.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_overview implements renderable, templatable {
    /** The columns whose headings sort, in display order. */
    private const SORTABLE = ['user', 'attemptnumber', 'state', 'score', 'answered', 'correct', 'finished'];

    /** @var moodle_url The page URL, carrying the filters currently in force */
    private moodle_url $baseurl;

    /** @var array The attempt summaries for this page */
    private array $attempts;

    /** @var array The aggregate figures for the whole filtered set */
    private array $aggregate;

    /** @var string The active sort key */
    private string $sort;

    /** @var string The active sort direction */
    private string $direction;

    /** @var bool Whether the user may delete attempts */
    private bool $candelete;

    /** @var bool Whether the user may export the report */
    private bool $canexport;

    /** @var bool Whether any filter is currently in force */
    private bool $filtered;

    /**
     * Build the overview description.
     *
     * @param moodle_url $baseurl The page URL carrying the active filters
     * @param array $attempts The attempt summaries for this page
     * @param array $aggregate The aggregate figures for the filtered set
     * @param string $sort The active sort key
     * @param string $direction The active sort direction
     * @param bool $candelete Whether the user holds mod/elang:deleteattempts
     * @param bool $canexport Whether the user holds mod/elang:exportreports
     * @param bool $filtered Whether any filter is in force
     */
    public function __construct(
        moodle_url $baseurl,
        array $attempts,
        array $aggregate,
        string $sort,
        string $direction,
        bool $candelete,
        bool $canexport,
        bool $filtered
    ) {
        $this->baseurl = $baseurl;
        $this->attempts = $attempts;
        $this->aggregate = $aggregate;
        $this->sort = $sort;
        $this->direction = $direction;
        $this->candelete = $candelete;
        $this->canexport = $canexport;
        $this->filtered = $filtered;
    }

    /**
     * The four headline figures.
     *
     * @return array Each with a label and a value
     */
    private function figures(): array {
        $total = (int) $this->aggregate['total'];
        $hinted = (int) $this->aggregate['hinted'];

        return [
            [
                'label' => get_string('report:kpiattempts', 'mod_elang'),
                'value' => (string) $total,
            ],
            [
                'label' => get_string('report:kpifinished', 'mod_elang'),
                'value' => (string) (int) $this->aggregate['finished'],
            ],
            [
                'label' => get_string('report:kpiaverage', 'mod_elang'),
                // Of finished attempts only, which the label says: an average
                // that silently included attempts still being worked on would
                // move as people start, not as they perform.
                'value' => $this->aggregate['finished'] > 0
                    ? format_float((float) $this->aggregate['averagescore'], 2)
                    : '—',
            ],
            [
                'label' => get_string('report:kpihinted', 'mod_elang'),
                'value' => $total > 0
                    ? $hinted . ' (' . round(($hinted / $total) * 100) . '%)'
                    : '0',
            ],
        ];
    }

    /**
     * The table headings, each carrying the URL that sorts by it.
     *
     * @return array The headings
     */
    private function headings(): array {
        $headings = [];

        foreach (self::SORTABLE as $column) {
            $active = $this->sort === $column;
            // Clicking the active column reverses it; clicking another starts
            // that one ascending, which is what a first click on a name or a
            // number is normally taken to mean.
            $next = $active && $this->direction === 'ASC' ? 'DESC' : 'ASC';

            $url = clone $this->baseurl;
            $url->params(['tsort' => $column, 'tdir' => $next]);

            $headings[] = [
                'key' => $column,
                'label' => get_string('report:' . $column, 'mod_elang'),
                'url' => $url->out(false),
                'active' => $active,
                'ariasort' => $active ? ($this->direction === 'ASC' ? 'ascending' : 'descending') : 'none',
            ];
        }

        return $headings;
    }

    /**
     * The export menu, or an empty array when the user may not export.
     *
     * @return array The formats, spreadsheet ones first
     */
    private function exportformats(): array {
        if (!$this->canexport) {
            return [];
        }

        $formats = [];
        // XLSX and CSV first: a report is usually taken away to be looked at in
        // a spreadsheet, and JSON is the rarest of the four by a distance.
        foreach (['xlsx', 'csv', 'ods', 'json'] as $format) {
            $url = clone $this->baseurl;
            $url->param('dataformat', $format);
            $formats[] = [
                'label' => strtoupper($format),
                'url' => $url->out(false),
            ];
        }

        return $formats;
    }

    /**
     * Export the overview for the template.
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

        $rows = [];
        foreach ($this->attempts as $attempt) {
            $viewurl = new moodle_url('/mod/elang/report.php', [
                'id' => $this->baseurl->param('id'),
                'attemptid' => $attempt['attemptid'],
            ]);

            // The destructive action lives in a menu, not beside the primary
            // one: "View · Delete" as equal neighbours invites the wrong click.
            $menu = new action_menu();
            $menu->set_kebab_trigger(get_string('report:actions', 'mod_elang'));
            $menu->add(new action_menu_link(
                $viewurl,
                new pix_icon('i/preview', ''),
                get_string('report:view', 'mod_elang'),
                false
            ));
            if ($this->candelete) {
                $menu->add(new action_menu_link(
                    new moodle_url('/mod/elang/report.php', [
                        'id' => $this->baseurl->param('id'),
                        'action' => 'delete',
                        'attemptid' => $attempt['attemptid'],
                    ]),
                    new pix_icon('t/delete', ''),
                    get_string('report:delete', 'mod_elang'),
                    false
                ));
            }

            $total = (int) $attempt['totalgaps'];
            $answered = (int) $attempt['answeredgaps'];

            $rows[] = [
                'fullname' => $attempt['fullname'] !== '' ? $attempt['fullname'] : (string) $attempt['userid'],
                'attemptnumber' => $attempt['attemptnumber'],
                'state' => $attempt['state'],
                'statelabel' => get_string('report:state_' . $attempt['state'], 'mod_elang'),
                'stateclass' => $states[$attempt['state']] ?? 'badge-secondary bg-secondary',
                'score' => format_float((float) $attempt['score'], 2),
                'answered' => $answered . ' / ' . $total,
                'answeredpercent' => $total > 0 ? round(($answered / $total) * 100) : 0,
                'correct' => $attempt['correctgaps'],
                'exact' => $attempt['exactgaps'],
                'hinted' => $attempt['hintedgaps'],
                'finished' => $attempt['timefinish']
                    ? userdate($attempt['timefinish'], get_string('strftimedatetimeshort', 'core_langconfig'))
                    : '—',
                'viewurl' => $viewurl->out(false),
                'actions' => $output->render($menu),
            ];
        }

        $reseturl = new moodle_url('/mod/elang/report.php', ['id' => $this->baseurl->param('id')]);

        return [
            'figures' => $this->figures(),
            'headings' => $this->headings(),
            'rows' => $rows,
            'hasrows' => !empty($rows),
            'noattempts' => get_string(
                $this->filtered ? 'report:nomatchingattempts' : 'report:noattempts',
                'mod_elang'
            ),
            'filtered' => $this->filtered,
            'reseturl' => $reseturl->out(false),
            'resetlabel' => get_string('report:filterreset', 'mod_elang'),
            'exportlabel' => get_string('report:export', 'mod_elang'),
            'exportformats' => $this->exportformats(),
            'hasexport' => $this->canexport,
            'headexact' => get_string('report:exact', 'mod_elang'),
            'headhinted' => get_string('report:hinted', 'mod_elang'),
            'headactions' => get_string('report:actions', 'mod_elang'),
        ];
    }
}
