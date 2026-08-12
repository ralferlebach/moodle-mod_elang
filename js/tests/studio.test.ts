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
 * Tests for the studio's pure helpers: masked preview, timeline snapping/scale
 * and waveform peak extraction.
 *
 * @module     mod_elang/tests/studio
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {GAP_PLACEHOLDER, maskTranscript} from '../src/studio/mask';
import {msToPercent, pxToMs, snapMs} from '../src/studio/snapping';
import {extractPeaks, peaksToPolyline} from '../src/studio/waveform';
import {Gap} from '../src/types';

function gap(charstart: number, charlength: number): Gap {
    return {
        gapkey: 'g' + charstart,
        sortorder: 1,
        charstart,
        charlength,
        solution: 'x',
        gradingalgorithm: 'exact',
        maxlength: 0,
        linkurl: '',
        answers: [],
        hints: [],
    };
}

describe('maskTranscript', () => {
    it('replaces a gap range with the placeholder and never leaks the solution', () => {
        const masked = maskTranscript('Le chat dort', [gap(3, 4)]);
        expect(masked).toBe('Le ' + GAP_PLACEHOLDER + ' dort');
        expect(masked).not.toContain('chat');
    });

    it('masks multiple gaps in order', () => {
        const masked = maskTranscript('Le chat dort', [gap(8, 4), gap(3, 4)]);
        expect(masked).toBe('Le ' + GAP_PLACEHOLDER + ' ' + GAP_PLACEHOLDER);
    });

    it('skips an out-of-range gap defensively', () => {
        expect(maskTranscript('Le chat', [gap(3, 40)])).toBe('Le chat');
    });

    it('counts offsets in codepoints, not UTF-16 units', () => {
        // The gap covers "chat" which starts one codepoint after the emoji.
        const masked = maskTranscript('\uD83C\uDFB5 chat', [gap(2, 4)]);
        expect(masked).toBe('\uD83C\uDFB5 ' + GAP_PLACEHOLDER);
    });
});

describe('snapping', () => {
    it('snaps to the nearest candidate within the threshold', () => {
        expect(snapMs(1020, [1000, 2000], 50)).toBe(1000);
    });

    it('leaves the value alone when nothing is close enough', () => {
        expect(snapMs(1200, [1000, 2000], 50)).toBe(1200);
    });

    it('converts pixels to a clamped time', () => {
        expect(pxToMs(50, 100, 10000)).toBe(5000);
        expect(pxToMs(-10, 100, 10000)).toBe(0);
        expect(pxToMs(200, 100, 10000)).toBe(10000);
    });

    it('converts a time to a clamped percentage', () => {
        expect(msToPercent(5000, 10000)).toBe(50);
        expect(msToPercent(20000, 10000)).toBe(100);
        expect(msToPercent(1000, 0)).toBe(0);
    });
});

describe('waveform', () => {
    it('reduces samples to the requested number of normalised peaks', () => {
        const samples = [0, 0.5, -0.9, 0.2, 0.1, -0.3];
        const peaks = extractPeaks(samples, 3);
        expect(peaks).toEqual([0.5, 0.9, 0.3]);
    });

    it('returns no peaks for empty input', () => {
        expect(extractPeaks([], 10)).toEqual([]);
        expect(extractPeaks([0.5], 0)).toEqual([]);
    });

    it('builds a mirrored polyline with two points per peak', () => {
        const points = peaksToPolyline([1, 0], 2, 10).split(' ');
        expect(points).toHaveLength(4);
        // First peak (full amplitude) reaches the top edge at x=0.
        expect(points[0]).toBe('0.00,0.00');
    });
});
