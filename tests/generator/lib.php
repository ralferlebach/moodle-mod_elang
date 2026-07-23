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
 * Test data generator for mod_elang.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Generator class for mod_elang activity instances.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_elang_generator extends testing_module_generator {
    /**
     * Create a new elang instance.
     *
     * @param array|stdClass|null $record Instance data
     * @param array|null $options Generator options
     * @return stdClass The created instance record
     */
    public function create_instance($record = null, ?array $options = null) {
        $record = (object) (array) $record;

        if (!isset($record->name)) {
            $record->name = 'Language exercise ' . ($this->instancecount + 1);
        }
        if (!isset($record->intro)) {
            $record->intro = 'Language exercise description';
        }
        if (!isset($record->introformat)) {
            $record->introformat = FORMAT_HTML;
        }

        return parent::create_instance($record, (array) $options);
    }
}
