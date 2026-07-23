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
 * Upgrade steps for mod_elang.
 *
 * The migration of version 1 data (elang_cues, elang_users, elang_help,
 * elang_check) into the versioned version 2 data model is keyed on the presence
 * of the legacy tables rather than on a version number, because sites may have
 * skipped several plugin releases. It runs as a resumable ad-hoc task and is
 * introduced in phase 2; see docs/materials/Migration_V1_V2.md.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute the mod_elang upgrade steps.
 *
 * @param int $oldversion The version we are upgrading from
 * @return bool Success
 */
function xmldb_elang_upgrade(int $oldversion): bool {
    // No upgrade steps yet: 2.0.0-alpha.1 is the first version of the new schema.
    return true;
}
