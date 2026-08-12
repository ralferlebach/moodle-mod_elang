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
 * Tests for the rule-gap control's span-to-gap mapping.
 *
 * @module     mod_elang/tests/rulegaps
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {spansToGaps} from '../src/components/RuleGapControl';

describe('spansToGaps', () => {
    test('maps generated spans to full gap records with unique keys', () => {
        const gaps = spansToGaps([
            {charstart: 3, charlength: 4, solution: 'chat'},
            {charstart: 10, charlength: 4, solution: 'dort'},
        ]);

        expect(gaps).toHaveLength(2);
        expect(gaps[0]).toMatchObject({
            charstart: 3,
            charlength: 4,
            solution: 'chat',
            gradingalgorithm: 'exact',
            sortorder: 1,
            maxlength: 0,
            linkurl: '',
            answers: [],
            hints: [],
        });
        expect(gaps[1].sortorder).toBe(2);
        // Each gap gets its own key.
        expect(gaps[0].gapkey).not.toBe(gaps[1].gapkey);
    });

    test('returns an empty list for no spans', () => {
        expect(spansToGaps([])).toEqual([]);
    });
});
