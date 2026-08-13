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
 * All functions are exposed to core/ajax and to the official mobile service, so
 * the same functions serve both the web player and the Moodle App without
 * duplication.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_elang_get_attempt_exercise' => [
        'classname' => 'mod_elang\external\get_attempt_exercise',
        'methodname' => 'execute',
        'description' => 'Return the attempt version shape: counts and identifiers, no content or solutions.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/elang:attempt',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_elang_get_attempt_cues' => [
        'classname' => 'mod_elang\external\get_attempt_cues',
        'methodname' => 'execute',
        'description' => 'Return a page of cues and gaps for the attempt\'s pinned version, transcript solution-masked.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/elang:attempt',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_elang_get_attempt_state' => [
        'classname' => 'mod_elang\external\get_attempt_state',
        'methodname' => 'execute',
        'description' => "Return an attempt's aggregate counters and per-gap response state.",
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/elang:attempt',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
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
    'mod_elang_request_hint' => [
        'classname' => 'mod_elang\external\request_hint',
        'methodname' => 'execute',
        'description' => 'Reveal the next hint level for a gap within an in-progress attempt.',
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
    'mod_elang_save_draft_version' => [
        'classname' => 'mod_elang\external\save_draft_version',
        'methodname' => 'execute',
        'description' => 'Overwrite a draft version\'s content with the editor\'s current state.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/elang:manage',
    ],
    'mod_elang_publish_version' => [
        'classname' => 'mod_elang\external\publish_version',
        'methodname' => 'execute',
        'description' => 'Validate and publish a draft version.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/elang:manage',
    ],
    'mod_elang_get_version_content' => [
        'classname' => 'mod_elang\external\get_version_content',
        'methodname' => 'execute',
        'description' => 'Read a version\'s full authoring content, including solutions.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/elang:manage',
    ],
    'mod_elang_preview_import' => [
        'classname' => 'mod_elang\external\preview_import',
        'methodname' => 'execute',
        'description' => 'Parse a WebVTT or SubRip subtitle file into cue segments for the editor.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/elang:manage',
    ],
    'mod_elang_generate_rule_gaps' => [
        'classname' => 'mod_elang\\external\\generate_rule_gaps',
        'methodname' => 'execute',
        'description' => 'Generate gap definitions from a rule (a word list or every nth word) for the editor.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/elang:manage',
    ],
    'mod_elang_set_draft_media' => [
        'classname' => 'mod_elang\external\set_draft_media',
        'methodname' => 'execute',
        'description' => 'Set a draft version\'s medium: an uploaded file, a url, a provider, or none.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/elang:manage',
    ],
];
