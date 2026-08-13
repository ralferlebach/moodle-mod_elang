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

namespace mod_elang\courseformat;

use core\output\action_link;
use core\output\local\properties\button;
use core\output\local\properties\text_align;
use core\url;
use core_courseformat\activityoverviewbase;
use core_courseformat\local\overview\overviewitem;

/**
 * Activity overview for the course overview page (Moodle 5.0+).
 *
 * Supplies the extra columns an elang activity contributes to the course
 * "Activities overview": a teacher action linking to the attempt report and a
 * count of attempts. This class is only ever loaded by the 5.0 overview
 * factory; on 4.5, where the feature and its base class do not exist, it stays
 * dormant.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class overview extends activityoverviewbase {
    /**
     * The teacher action shown for the activity: open the attempt report.
     *
     * @return overviewitem|null The action item, or null for users without the report capability
     */
    public function get_actions_overview(): ?overviewitem {
        if (!has_capability('mod/elang:viewreports', $this->context)) {
            return null;
        }

        $label = get_string('reports', 'mod_elang');
        $content = new action_link(
            url: new url('/mod/elang/report.php', ['id' => $this->cm->id]),
            text: $label,
            attributes: ['class' => button::SECONDARY_OUTLINE->classes()],
        );

        return new overviewitem(
            name: get_string('actions'),
            value: $label,
            content: $content,
            textalign: text_align::CENTER,
        );
    }

    /**
     * Extra overview items: the number of attempts, for teachers.
     *
     * @return overviewitem[] Extra items keyed by short name
     */
    public function get_extra_overview_items(): array {
        $attempts = $this->get_attempts_overview();

        return $attempts === null ? [] : ['attempts' => $attempts];
    }

    /**
     * A count of attempts made against this activity.
     *
     * @return overviewitem|null The attempts item, or null for users without the report capability
     */
    private function get_attempts_overview(): ?overviewitem {
        global $DB;

        if (!has_capability('mod/elang:viewreports', $this->context)) {
            return null;
        }

        $count = $DB->count_records('elang_attempt', ['elangid' => $this->cm->instance]);

        return new overviewitem(
            name: get_string('overview:attempts', 'mod_elang'),
            value: $count,
            content: (string) $count,
            textalign: text_align::CENTER,
        );
    }
}
