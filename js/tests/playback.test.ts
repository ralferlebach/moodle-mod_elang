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
 * Tests for the player's playback decisions.
 *
 * Every playback bug reported against this plugin so far lives in one of these
 * four functions, and each was found in a browser rather than here. The cases
 * below are written from those reports.
 *
 * @module     mod_elang/tests/playback
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {
    activeCueIndex,
    autoScrollSuppressed,
    needsSeekToCue,
    nextOpenGapIndex,
    pauseLandingTime,
    shouldStopAtBoundary,
} from '../../amd/src/playback';

/** Two adjacent cues and a gap in the recording after them. */
const CUES = [
    {starttime: 0, endtime: 2000},
    {starttime: 2000, endtime: 5000},
    {starttime: 7000, endtime: 9000},
];

describe('activeCueIndex()', () => {
    test('a cue owns its start', () => {
        expect(activeCueIndex(CUES, 0)).toBe(0);
        expect(activeCueIndex(CUES, 2000)).toBe(1);
    });

    test('a cue does not own its end', () => {
        // Half-open, so the instant between two cues belongs to exactly one of
        // them. Both claiming it would make the active cue depend on iteration
        // order.
        expect(activeCueIndex(CUES, 1999)).toBe(0);
        expect(activeCueIndex(CUES, 5000)).toBe(-1);
    });

    test('there is no active cue in a silence between cues', () => {
        expect(activeCueIndex(CUES, 6000)).toBe(-1);
    });

    test('there is no active cue past the end of the recording', () => {
        expect(activeCueIndex(CUES, 99000)).toBe(-1);
    });
});

describe('pauseLandingTime()', () => {
    test('parks inside the cue that just played, not on its edge', () => {
        // The reported symptom was a caption vanishing "in the millisecond the
        // video stops": parking on the boundary left no cue active at all.
        const cue = CUES[0];

        expect(pauseLandingTime(cue)).toBe(1999);
        expect(activeCueIndex(CUES, pauseLandingTime(cue))).toBe(0);
    });

    test('never lands before the start of the recording', () => {
        expect(pauseLandingTime({starttime: 0, endtime: 0})).toBe(0);
    });
});

describe('shouldStopAtBoundary()', () => {
    test('"nostop" never stops', () => {
        expect(shouldStopAtBoundary({mode: 'nostop', engaged: true, hasopengaps: true})).toBe(false);
    });

    test('"stop" stops at a cue that still has something to answer', () => {
        expect(shouldStopAtBoundary({mode: 'stop', engaged: false, hasopengaps: true})).toBe(true);
    });

    test('"auto" stops only at the cue being worked on', () => {
        expect(shouldStopAtBoundary({mode: 'auto', engaged: true, hasopengaps: true})).toBe(true);
        expect(shouldStopAtBoundary({mode: 'auto', engaged: false, hasopengaps: true})).toBe(false);
    });

    test('a cue whose gaps are all answered never holds playback', () => {
        // Finished work: stopping there asks for a keypress that achieves
        // nothing, in every mode.
        expect(shouldStopAtBoundary({mode: 'stop', engaged: true, hasopengaps: false})).toBe(false);
        expect(shouldStopAtBoundary({mode: 'auto', engaged: true, hasopengaps: false})).toBe(false);
    });
});

describe('nextOpenGapIndex()', () => {
    const gaps = [
        {answered: true},
        {answered: true},
        {answered: false},
        {answered: true},
        {answered: false},
    ];

    test('skips past everything already answered', () => {
        expect(nextOpenGapIndex(gaps, 0)).toBe(2);
        expect(nextOpenGapIndex(gaps, 2)).toBe(4);
    });

    test('reports none left rather than wrapping around', () => {
        // Wrapping would send a learner who finished the last gap back to the
        // top of the transcript with no way to tell they had finished.
        expect(nextOpenGapIndex(gaps, 4)).toBe(-1);
        expect(nextOpenGapIndex([{answered: true}], -1)).toBe(-1);
    });

    test('starting before the first gap finds the first open one', () => {
        expect(nextOpenGapIndex(gaps, -1)).toBe(2);
    });
});

describe('needsSeekToCue()', () => {
    test('no seek while playback is already inside the cue', () => {
        // Otherwise moving between two gaps of one sentence would replay it.
        expect(needsSeekToCue(CUES[1], 3000)).toBe(false);
        expect(needsSeekToCue(CUES[1], 2000)).toBe(false);
    });

    test('seeks when playback is before or past the cue', () => {
        expect(needsSeekToCue(CUES[1], 500)).toBe(true);
        expect(needsSeekToCue(CUES[1], 5000)).toBe(true);
    });
});

describe('autoScrollSuppressed()', () => {
    test('holds off during the grace period after a manual scroll', () => {
        expect(autoScrollSuppressed(1000, 5000)).toBe(true);
    });

    test('resumes once the grace period has passed', () => {
        expect(autoScrollSuppressed(5000, 5000)).toBe(false);
        expect(autoScrollSuppressed(6000, 5000)).toBe(false);
    });

    test('is not suppressed when nothing was scrolled by hand', () => {
        expect(autoScrollSuppressed(1000, 0)).toBe(false);
    });
});
