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
 * Cue times as people read them.
 *
 * Milliseconds are how a cue time is stored, sent and compared — that does not
 * change. They are simply the wrong thing to put in front of an author:
 * "83450" is not a position in a recording that anyone can picture, and
 * spotting that one cue starts before the previous one ends means doing
 * arithmetic by eye.
 *
 * Parsing is deliberately forgiving about how much of a timestamp is written
 * ("5", "1:05", "1:05.5", "01:02:03.456" all work) and strict about what it
 * accepts, so a typo becomes a rejected value rather than a silently wrong one.
 *
 * @module     mod_elang/studio/time
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Accepts [[hh:]mm:]ss[.mmm], with 1-2 digits per unit and 1-3 for the fraction. */
const TIMESTAMP = /^(?:(?:(\d{1,2}):)?(\d{1,2}):)?(\d{1,2})(?:[.,](\d{1,3}))?$/;

/**
 * Pad a number with leading zeroes.
 *
 * @param value The number.
 * @param length The target width.
 * @returns The padded string.
 */
function pad(value: number, length: number): string {
    return String(value).padStart(length, '0');
}

/**
 * Format a millisecond time as mm:ss.SSS, or hh:mm:ss.SSS past an hour.
 *
 * The hour is only shown when there is one, so the common case stays as short
 * as it can be while a long recording still reads unambiguously.
 *
 * @param ms The time in milliseconds.
 * @returns The formatted timestamp.
 */
export function formatTime(ms: number): string {
    const total = Math.max(0, Math.round(ms));
    const hours = Math.floor(total / 3600000);
    const minutes = Math.floor((total % 3600000) / 60000);
    const seconds = Math.floor((total % 60000) / 1000);
    const millis = total % 1000;

    const tail = pad(minutes, 2) + ':' + pad(seconds, 2) + '.' + pad(millis, 3);

    return hours > 0 ? pad(hours, 2) + ':' + tail : tail;
}

/**
 * Parse a timestamp back to milliseconds.
 *
 * Returns null rather than 0 for anything unparseable: 0 is a legitimate cue
 * time, so using it as the failure value would turn a typo into a cue that
 * silently jumps to the start of the recording.
 *
 * @param value The timestamp as typed.
 * @returns The time in milliseconds, or null when it cannot be read.
 */
export function parseTime(value: string): number | null {
    const trimmed = value.trim();
    if (trimmed === '') {
        return null;
    }

    const match = TIMESTAMP.exec(trimmed);
    if (!match) {
        return null;
    }

    const [, hours, minutes, seconds, fraction] = match;

    // Minutes and seconds beyond 59 would make two different strings mean the
    // same instant, so "1:75" is a typo rather than a synonym for "2:15".
    const mm = Number(minutes || 0);
    const ss = Number(seconds);
    if (mm > 59 || (minutes !== undefined && ss > 59)) {
        return null;
    }

    // ".5" is half a second, not five milliseconds: the fraction is padded on
    // the right, the way a decimal is read.
    const millis = fraction ? Number(fraction.padEnd(3, '0')) : 0;

    return Number(hours || 0) * 3600000 + mm * 60000 + ss * 1000 + millis;
}
