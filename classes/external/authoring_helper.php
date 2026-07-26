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

namespace mod_elang\external;

/**
 * Helpers shared by the authoring-related external functions.
 *
 * Kept as a trait rather than a base class so each external function class
 * can still directly extend \core_external\external_api, which the Moodle
 * external API framework expects.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait authoring_helper {
    /**
     * Build a version_manager for the authoring operations.
     *
     * @return \mod_elang\local\domain\version_manager
     */
    private static function get_version_manager(): \mod_elang\local\domain\version_manager {
        return new \mod_elang\local\domain\version_manager();
    }

    /**
     * Load a version and authorise the current user to author its activity,
     * confirming the mod/elang:manage capability in the activity context.
     * Every authoring external function begins with this exact
     * load-and-authorise sequence, so it lives here once.
     *
     * @param int $versionid The elang_version id being authored
     * @return array A two-element list: the elang_version record and its \context_module
     */
    private static function require_manage_version(int $versionid): array {
        global $DB;

        $version = $DB->get_record('elang_version', ['id' => $versionid], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('elang', $version->elangid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/elang:manage', $context);

        return [$version, $context];
    }
}
