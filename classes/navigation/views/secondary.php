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

namespace mod_elang\navigation\views;

use core\navigation\views\secondary as core_secondary;
use navigation_node;

/**
 * Secondary navigation for mod_elang.
 *
 * Moodle picks this class up automatically for pages in this activity (see
 * moodle_page::magic_get_secondarynav, which prefers
 * mod_{modname}\navigation\views\secondary over the core class). It exists
 * only to order the activity's own modes ahead of the generic administrative
 * entries; the nodes themselves are created in
 * elang_extend_settings_navigation().
 *
 * @package    mod_elang
 * @category   navigation
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class secondary extends core_secondary {
    /**
     * How many nodes are shown as tabs before the rest move into "More".
     *
     * One more than core's own limit, so that the six modes of this activity
     * (the exercise itself, media, subtitles and gaps, reports, settings and
     * the transcript export) are all reachable as tabs. Everything beyond
     * them — roles, filters, logs, backup, restore — still moves into "More"
     * exactly as core arranges it.
     */
    const MAX_DISPLAYED_ELANG_NAV_NODES = 6;

    /**
     * Define the order of this activity's secondary navigation nodes.
     *
     * Settings follows the activity, then the working areas in the order an
     * author moves through them, so the tabs read as a workflow rather than as
     * a list of administrative links. Whole-number positions are top-level tabs;
     * fractional ones nest under the node at the integer part, which is why
     * the inherited role entries keep their 7.x positions.
     *
     * @return array The node position map, keyed by node type
     */
    protected function get_default_module_mapping(): array {
        $mapping = parent::get_default_module_mapping();

        // Settings sits directly after the activity itself, then the working
        // areas in the order an author moves through them: choose a medium,
        // write subtitles and gaps, look at the attempts, take the transcript
        // away.
        $mapping[self::TYPE_SETTING] = array_merge($mapping[self::TYPE_SETTING], [
            'modedit' => 1,
            'mod_elang_media' => 2,
            'mod_elang_editcontent' => 3,
            'mod_elang_reports' => 4,
            'mod_elang_exporttranscript' => 5,
        ]);

        // Advanced grading is not used by this activity, but position 2 is now
        // taken; move it out of the way so the two never collide.
        $mapping[self::TYPE_CUSTOM]['advgrading'] = 13;

        return $mapping;
    }

    /**
     * Push the nodes beyond this activity's own modes into the "More" menu.
     *
     * Core caps the visible tabs at five, which would push either the settings
     * tab or the transcript export out of sight. Only that default is raised
     * here; when the caller passes an explicit limit of its own, it is left
     * alone.
     *
     * @param array $defaultmoremenunodes Keys of nodes that always belong in "More"
     * @param int|null $maxdisplayednodes The maximum number of nodes shown as tabs
     * @return void No return value.
     */
    protected function force_nodes_into_more_menu(
        array $defaultmoremenunodes = [],
        ?int $maxdisplayednodes = null
    ) {
        if ($maxdisplayednodes === self::MAX_DISPLAYED_NAV_NODES) {
            $maxdisplayednodes = self::MAX_DISPLAYED_ELANG_NAV_NODES;
        }

        parent::force_nodes_into_more_menu($defaultmoremenunodes, $maxdisplayednodes);
    }
}
