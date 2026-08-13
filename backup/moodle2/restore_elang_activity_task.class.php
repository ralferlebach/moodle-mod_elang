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
 * Restore task for mod_elang.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/elang/backup/moodle2/restore_elang_stepslib.php');

/**
 * Restore task that provides the steps to restore an elang activity.
 */
class restore_elang_activity_task extends restore_activity_task {
    /**
     * Define particular settings this activity can have. None are needed.
     *
     * @return void No return value; this activity defines no extra settings.
     */
    protected function define_my_settings() {
    }

    /**
     * Define the particular restore steps this activity runs.
     *
     * @return void No return value; the steps are added to the plan.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_elang_activity_structure_step('elang_structure', 'elang.xml'));
    }

    /**
     * Define the activity contents whose embedded links the decoder must process.
     *
     * @return restore_decode_content[] The contents to decode
     */
    public static function define_decode_contents() {
        $contents = [];

        $contents[] = new restore_decode_content('elang', ['intro'], 'elang');

        return $contents;
    }

    /**
     * Define the decoding rules for links to this activity.
     *
     * @return restore_decode_rule[] The decode rules
     */
    public static function define_decode_rules() {
        $rules = [];

        $rules[] = new restore_decode_rule('ELANGINDEX', '/mod/elang/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('ELANGVIEWBYID', '/mod/elang/view.php?id=$1', 'course_module');

        return $rules;
    }

    /**
     * Define the restore log rules for this activity.
     *
     * @return restore_log_rule[] The log rules
     */
    public static function define_restore_log_rules() {
        $rules = [];

        $rules[] = new restore_log_rule('elang', 'add', 'view.php?id={course_module}', '{elang}');
        $rules[] = new restore_log_rule('elang', 'view', 'view.php?id={course_module}', '{elang}');

        return $rules;
    }
}
