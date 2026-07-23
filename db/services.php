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
 * External function declarations for mod_elang.
 *
 * All three functions are exposed to core/ajax and to the official mobile
 * service, so the same functions serve both a future web player and the
 * Moodle App without duplication (see Lastenheft P11, P18).
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_elang_start_attempt' => [
        'classname' => 'mod_elang\external\start_attempt',
        'methodname' => 'execute',
        'description' => "Start or resume the current user's in-progress attempt at a language exercise.",
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/elang:attempt',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_elang_submit_response' => [
        'classname' => 'mod_elang\external\submit_response',
        'methodname' => 'execute',
        'description' => 'Submit a response to one gap within an in-progress attempt.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/elang:attempt',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_elang_finish_attempt' => [
        'classname' => 'mod_elang\external\finish_attempt',
        'methodname' => 'execute',
        'description' => 'Finish an in-progress attempt.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/elang:attempt',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];
