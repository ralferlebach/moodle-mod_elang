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

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Save some of a draft's cues and leave the rest as they are.
 *
 * The wholesale endpoint beside this one reads an absent cue as a deletion,
 * which is right when the editor is sending everything it has. It is wrong when
 * the editor is deliberately holding one cue back — an end time typed before
 * its start time, say — because then dropping the cue from the payload destroys
 * the last good version of it instead of protecting it.
 *
 * Here absence means nothing at all. A cue key that is not mentioned keeps
 * whatever the server already holds, and removal has to be said: only the keys
 * in removedcuekeys are deleted. That is what lets an editor save the sentence
 * an author just fixed while the contradictory one beside it stays untouched on
 * the server and marked in the browser.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_draft_cues extends external_api {
    use authoring_helper;

    /**
     * Describe the parameters this function accepts.
     *
     * @return external_function_parameters The description of this function's parameters.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'versionid' => new external_value(PARAM_INT, 'Id of the draft version to write to'),
            'expectedrevision' => new external_value(
                PARAM_INT,
                'The revision the caller last saw for this draft, for optimistic concurrency; '
                    . '-1 saves unconditionally'
            ),
            'cues' => new external_multiple_structure(
                self::cue_structure(),
                'The cues to insert or replace. Cues not listed here are left untouched'
            ),
            'removedcuekeys' => new external_multiple_structure(
                new external_value(PARAM_RAW_TRIMMED, 'A cue key to delete from the draft'),
                'Cue keys to delete. Deletion is explicit here: leaving a cue out of "cues" '
                    . 'does not remove it',
                VALUE_DEFAULT,
                []
            ),
        ]);
    }

    /**
     * Write the given cues, remove the named ones, and return the new revision.
     *
     * @param int $versionid Id of the draft version to write to
     * @param int $expectedrevision The revision the caller last saw, or -1 to save unconditionally
     * @param array $cues Cues to insert or replace, each with nested gaps/answers/hints
     * @param array $removedcuekeys Cue keys to delete outright
     * @return array The saved draft's new revision, see execute_returns()
     */
    public static function execute(
        int $versionid,
        int $expectedrevision,
        array $cues,
        array $removedcuekeys = []
    ): array {
        [
            'versionid' => $versionid,
            'expectedrevision' => $expectedrevision,
            'cues' => $cues,
            'removedcuekeys' => $removedcuekeys,
        ] = self::validate_parameters(self::execute_parameters(), [
            'versionid' => $versionid,
            'expectedrevision' => $expectedrevision,
            'cues' => $cues,
            'removedcuekeys' => $removedcuekeys,
        ]);

        [, $context] = self::require_manage_version($versionid);

        // The same capability check the wholesale endpoint makes: a regular
        // expression in an accepted answer is a privilege, and a partial save
        // must not be the way around it.
        self::require_useregex_if_needed($cues, $context);

        $version = self::get_version_manager()->save_draft_cues(
            $versionid,
            $cues,
            $removedcuekeys,
            $expectedrevision
        );

        return [
            'versionid' => (int) $version->id,
            'revision' => (int) $version->revision,
        ];
    }

    /**
     * Describe the structure this function returns.
     *
     * @return external_single_structure The description of this function's return value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'versionid' => new external_value(PARAM_INT, 'Id of the saved draft version'),
            'revision' => new external_value(PARAM_INT, 'The draft revision after this save'),
        ]);
    }
}
