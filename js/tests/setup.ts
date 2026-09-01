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
 * Browser APIs jsdom does not implement.
 *
 * These are stubbed here rather than guarded in the components. A component
 * that checks whether scrollIntoView exists is carrying a test concern into
 * production code, and the check would then also hide a real mistake — an
 * element that genuinely cannot be scrolled to.
 *
 * @module     mod_elang/tests/setup
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// jsdom has no layout, so it implements no scrolling. Every browser the plugin
// supports has had this since 2015.
Element.prototype.scrollIntoView = Element.prototype.scrollIntoView || function scrollIntoView(): void {
    // Intentionally empty: the tests assert on state and markup, not on
    // scroll position, which jsdom could not report anyway.
};

export {};
