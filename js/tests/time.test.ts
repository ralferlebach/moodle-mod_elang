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
 * Tests for cue time formatting and parsing.
 *
 * @module     mod_elang/tests/time
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {formatTime, parseTime} from '../src/studio/time';

describe('formatTime()', () => {
    test('shows minutes, seconds and milliseconds', () => {
        expect(formatTime(0)).toBe('00:00.000');
        expect(formatTime(5000)).toBe('00:05.000');
        expect(formatTime(65432)).toBe('01:05.432');
    });

    test('adds the hour only once there is one', () => {
        expect(formatTime(3599999)).toBe('59:59.999');
        expect(formatTime(3600000)).toBe('01:00:00.000');
        expect(formatTime(3723456)).toBe('01:02:03.456');
    });

    test('a negative time cannot be a position in a recording', () => {
        expect(formatTime(-1)).toBe('00:00.000');
    });
});

describe('parseTime()', () => {
    test('accepts a timestamp in any of its shortened forms', () => {
        expect(parseTime('5')).toBe(5000);
        expect(parseTime('1:05')).toBe(65000);
        expect(parseTime('01:02:03.456')).toBe(3723456);
    });

    test('reads the fraction as a decimal, not as milliseconds', () => {
        expect(parseTime('0:00.5')).toBe(500);
        expect(parseTime('0:00.05')).toBe(50);
        expect(parseTime('0:00.005')).toBe(5);
    });

    test('accepts a comma as the decimal separator', () => {
        expect(parseTime('1:05,432')).toBe(65432);
    });

    test('returns null rather than zero for anything unreadable', () => {
        // Zero is a legitimate cue time, so it cannot double as the failure
        // value: a typo would silently move the cue to the start.
        expect(parseTime('')).toBeNull();
        expect(parseTime('abc')).toBeNull();
        expect(parseTime('1:2:3:4')).toBeNull();
        expect(parseTime('-5')).toBeNull();
    });

    test('rejects units that overflow into the next one', () => {
        // "1:75" would otherwise mean the same instant as "2:15", and two
        // spellings of one time is how a rounding difference becomes a bug.
        expect(parseTime('1:75')).toBeNull();
        expect(parseTime('75:00')).toBeNull();
    });

    test('round-trips whatever formatTime produced', () => {
        [0, 999, 65432, 3723456, 3599999].forEach((ms) => {
            expect(parseTime(formatTime(ms))).toBe(ms);
        });
    });
});
