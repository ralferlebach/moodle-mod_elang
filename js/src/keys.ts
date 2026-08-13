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
 * Version-stable key generation for new cues and gaps.
 *
 * @module     mod_elang/keys
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Generate a version-stable key for a new cue or gap: an alphanumeric string
 * (matching PARAM_ALPHANUMEXT) of at most 40 characters.
 *
 * @param prefix A short prefix identifying the kind of key.
 * @returns The generated key.
 */
export function newKey(prefix: string): string {
    const random = Math.random().toString(36).slice(2, 10);
    return (prefix + Date.now().toString(36) + random).slice(0, 40);
}
