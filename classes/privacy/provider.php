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

namespace mod_elang\privacy;

/**
 * Privacy provider for mod_elang.
 *
 * The 2.0 skeleton stores no personal data, so a null provider is correct for
 * this version. It is replaced by a metadata provider, a plugin provider and a
 * userlist provider in phase 2, when attempts and responses are introduced.
 * Release of any version that stores learner data without that replacement is a
 * blocking defect; see docs/materials/Lastenheft_Pflichtenheft_Blueprint.md.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Return the reason why this plugin stores no personal data.
     *
     * @return string The identifier of the language string explaining the reason
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
