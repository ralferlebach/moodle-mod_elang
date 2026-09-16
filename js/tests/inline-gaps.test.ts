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
 * Tests for splitting a transcript into text runs and gap marks.
 *
 * @module     mod_elang/tests/inline-gaps
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {segmentTranscript} from '../src/studio/inline-gaps';
import type {Gap} from '../src/types';

/**
 * A gap covering the given codepoint range.
 */
function gap(
    gapkey: string,
    charstart: number,
    charlength: number,
    extra: Partial<Gap> = {}
): Gap {
    return {
        gapkey,
        sortorder: 1,
        charstart,
        charlength,
        solution: '',
        gradingalgorithm: 'exact',
        maxlength: 0,
        linkurl: '',
        answers: [],
        hints: [],
        ...extra,
    } as Gap;
}

describe('segmentTranscript', () => {
    it('marks one gap and keeps the text around it', () => {
        const segments = segmentTranscript('Le chat dort', [gap('g1', 3, 4)]);

        expect(segments).toEqual([
            {kind: 'text', text: 'Le '},
            expect.objectContaining({kind: 'gap', gapkey: 'g1', text: 'chat'}),
            {kind: 'text', text: ' dort'},
        ]);
    });

    it('marks several gaps in one cue, in reading order', () => {
        const segments = segmentTranscript('Le chat dort ici', [gap('g2', 8, 4), gap('g1', 3, 4)]);
        const keys = segments.filter((s) => s.kind === 'gap').map((s) => (s as {gapkey: string}).gapkey);

        expect(keys).toEqual(['g1', 'g2']);
    });

    it('counts positions in codepoints, not UTF-16 units', () => {
        // The emoji is one codepoint and two UTF-16 units. Slicing by string
        // index would put the mark one character to the left of the word and
        // split the emoji in half — the failure the whole helper exists to
        // avoid, and one that never shows up in a Latin-only test.
        const segments = segmentTranscript('Hi 👋 chat dort', [gap('g1', 5, 4)]);
        const marked = segments.find((s) => s.kind === 'gap') as {text: string};

        expect(marked.text).toBe('chat');
    });

    it('carries the matching mode and the extras of each gap', () => {
        const segments = segmentTranscript('Le chat dort', [gap('g1', 3, 4, {
            gradingalgorithm: 'wordrecognized',
            answers: [{sortorder: 1, answer: 'chatte', isregex: 0}],
            hints: [{level: 1, hinttype: 'text', hinttext: 'animal', penalty: 0.1}],
        } as Partial<Gap>)]);

        expect(segments[1]).toEqual(expect.objectContaining({
            gradingalgorithm: 'wordrecognized',
            hasalternatives: true,
            hashints: true,
        }));
    });

    it('reports a gap that reaches past the end of the text', () => {
        // Reported, not dropped. A gap that silently vanishes from the view
        // looks deleted, and the author has no reason to go looking for it.
        const segments = segmentTranscript('Le chat', [gap('g1', 3, 40)]);

        expect(segments).toContainEqual({kind: 'invalid', gapkey: 'g1', reason: 'outofrange'});
        expect(segments.some((s) => s.kind === 'gap')).toBe(false);
    });

    it('reports the second of two gaps claiming the same words', () => {
        const segments = segmentTranscript('Le chat dort', [gap('g1', 3, 4), gap('g2', 5, 4)]);

        expect(segments.filter((s) => s.kind === 'gap').map((s) => (s as {gapkey: string}).gapkey))
            .toEqual(['g1']);
        expect(segments).toContainEqual({kind: 'invalid', gapkey: 'g2', reason: 'overlap'});
    });

    it('rejects a zero-length gap rather than marking nothing', () => {
        const segments = segmentTranscript('Le chat', [gap('g1', 3, 0)]);

        expect(segments).toContainEqual({kind: 'invalid', gapkey: 'g1', reason: 'outofrange'});
    });

    it('handles a gap at the very start and one at the very end', () => {
        const segments = segmentTranscript('Le chat', [gap('g1', 0, 2), gap('g2', 3, 4)]);

        expect(segments.map((s) => s.kind)).toEqual(['gap', 'text', 'gap']);
    });

    it('returns the whole transcript as text when there are no gaps', () => {
        expect(segmentTranscript('Le chat dort', [])).toEqual([{kind: 'text', text: 'Le chat dort'}]);
    });
});
