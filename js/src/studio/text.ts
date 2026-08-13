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
 * Codepoint-based text helpers. The server stores and validates a gap's
 * charstart/charlength as Unicode codepoint offsets (PHP mb_substr), never as
 * UTF-16 code units. A browser <textarea>'s selectionStart/selectionEnd, on the
 * other hand, count UTF-16 code units, so a transcript containing an astral
 * character (an emoji, some CJK extensions) would misalign every gap after it if
 * the two were mixed. These helpers convert between the two so the editor always
 * speaks the same codepoint offsets the grading engine does.
 *
 * @module     mod_elang/studio/text
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Split a string into its Unicode codepoints, so indexing matches the server's
 * mb_substr view of the same text rather than UTF-16 code units.
 *
 * @param text The text to split.
 * @returns The codepoints, in order.
 */
export function codepoints(text: string): string[] {
    return Array.from(text);
}

/**
 * The number of Unicode codepoints in a string.
 *
 * @param text The text to measure.
 * @returns The codepoint length.
 */
export function codepointLength(text: string): number {
    return codepoints(text).length;
}

/**
 * Convert a UTF-16 code-unit index (as a <textarea> reports) into the codepoint
 * index the server uses. Indices at or beyond the end map to the codepoint
 * length.
 *
 * @param text The full text the index refers to.
 * @param u16index A UTF-16 code-unit index into text.
 * @returns The equivalent codepoint index.
 */
export function utf16ToCodepoint(text: string, u16index: number): number {
    if (u16index <= 0) {
        return 0;
    }
    return codepointLength(text.slice(0, u16index));
}

/**
 * Extract a codepoint-addressed substring, mirroring PHP mb_substr with a start
 * and a length.
 *
 * @param text The full text.
 * @param start The codepoint start offset.
 * @param length The number of codepoints to take.
 * @returns The extracted substring.
 */
export function codepointSlice(text: string, start: number, length: number): string {
    return codepoints(text).slice(start, start + length).join('');
}
