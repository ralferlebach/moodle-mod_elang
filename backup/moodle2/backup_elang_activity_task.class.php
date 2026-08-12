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
 * Backup task for mod_elang.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/elang/backup/moodle2/backup_elang_stepslib.php');

/**
 * Backup task that provides the steps to back up an elang activity.
 */
class backup_elang_activity_task extends backup_activity_task {
    /**
     * Define particular settings this activity can have. None are needed.
     *
     * @return void No return value; this activity defines no extra settings.
     */
    protected function define_my_settings() {
    }

    /**
     * Define the particular backup steps this activity runs.
     *
     * @return void No return value; the steps are added to the plan.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_elang_activity_structure_step('elang_structure', 'elang.xml'));
    }

    /**
     * Encode absolute links to this activity so they survive backup and restore.
     *
     * @param string $content The content to encode links within
     * @return string The content with elang links encoded
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // Link to the activity index of a course.
        $search = "/(" . $base . "\/mod\/elang\/index.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@ELANGINDEX*$2@$', $content);

        // Link to one activity view by course module id.
        $search = "/(" . $base . "\/mod\/elang\/view.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@ELANGVIEWBYID*$2@$', $content);

        return $content;
    }
}
