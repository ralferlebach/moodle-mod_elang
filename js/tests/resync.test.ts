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
 * Tests for the gap re-sync logic: gap offsets must stay aligned with the word
 * they mark as the transcript is edited before, after, inside and across them.
 *
 * @module     mod_elang/tests/resync
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {resyncGaps, resyncSpan} from '../src/studio/resync';
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

describe('resyncSpan', () => {
    // "Le chat dort" — the gap covers "chat" at codepoints 3..6.
    it('leaves a gap untouched when the edit is after it', () => {
        const span = resyncSpan({charstart: 3, charlength: 4}, 'Le chat dort', 'Le chat dort ici');
        expect(span).toEqual({charstart: 3, charlength: 4});
    });

    it('shifts a gap right when text is inserted before it', () => {
        const span = resyncSpan({charstart: 3, charlength: 4}, 'Le chat dort', 'Oui, Le chat dort');
        // Five characters ("Oui, ") were inserted at the front.
        expect(span).toEqual({charstart: 8, charlength: 4});
    });

    it('shifts a gap left when text is deleted before it', () => {
        const span = resyncSpan({charstart: 3, charlength: 4}, 'Le chat dort', 'chat dort');
        // "Le " (3 chars) was removed from the front.
        expect(span).toEqual({charstart: 0, charlength: 4});
    });

    it('grows a gap when text is inserted inside it', () => {
        // "chat" -> "chXXat": two chars inserted inside the gap span.
        const span = resyncSpan({charstart: 3, charlength: 4}, 'Le chat dort', 'Le chXXat dort');
        expect(span.charstart).toBe(3);
        expect(span.charlength).toBe(6);
    });

    it('keeps codepoint offsets across an astral character', () => {
        // A musical-note emoji (one codepoint, two UTF-16 units) sits before the
        // gap; the gap must move by one codepoint, not two.
        const span = resyncSpan({charstart: 3, charlength: 4}, 'Le chat dort', '\uD83C\uDFB5Le chat dort');
        expect(span).toEqual({charstart: 4, charlength: 4});
    });
});

describe('resyncGaps', () => {
    it('returns the same gaps when nothing changed', () => {
        const gaps = [gap(3, 4)];
        expect(resyncGaps(gaps, 'Le chat dort', 'Le chat dort')).toBe(gaps);
    });

    it('remaps every gap and preserves the ones that survive', () => {
        const gaps = [gap(0, 2), gap(3, 4)];
        const result = resyncGaps(gaps, 'Le chat dort', 'XXLe chat dort');
        expect(result).toHaveLength(2);
        expect(result[0]).toMatchObject({charstart: 2, charlength: 2});
        expect(result[1]).toMatchObject({charstart: 5, charlength: 4});
    });

    it('drops a gap whose text was entirely deleted', () => {
        const gaps = [gap(3, 4)];
        // Delete exactly "chat".
        const result = resyncGaps(gaps, 'Le chat dort', 'Le  dort');
        expect(result).toHaveLength(0);
    });
});
