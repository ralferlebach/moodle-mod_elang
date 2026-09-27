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
 * Tests for the local cue checks the editor runs before saving.
 *
 * @module     mod_elang/tests/cue-validation
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {findCueProblems, partitionCues, repairCue} from '../src/studio/cue-validation';
import type {Cue} from '../src/types';

/**
 * A cue with the given timings and nothing else of interest.
 */
function cue(cuekey: string, starttime: number, endtime: number): Cue {
    return {
        cuekey,
        sortorder: 1,
        starttime,
        endtime,
        transcript: 'Le chat dort',
        transcriptformat: 0,
        gaps: [],
    };
}

describe('findCueProblems', () => {
    it('accepts a cue whose end follows its start', () => {
        expect(findCueProblems(cue('a', 1000, 2000))).toEqual([]);
    });

    it('rejects an end time equal to the start time', () => {
        // Equal, not just earlier: a cue of no length is shown for no time, so
        // it is as unusable as a reversed one.
        expect(findCueProblems(cue('a', 1000, 1000)).map((p) => p.code)).toEqual(['endbeforestart']);
    });

    it('rejects an end time before the start time', () => {
        const problems = findCueProblems(cue('a', 2000, 1000));
        expect(problems.map((p) => p.code)).toEqual(['endbeforestart']);
        expect(problems[0].repairable).toBe(true);
    });

    it('rejects a start before the recording, and calls it unrepairable', () => {
        // There is no defensible place to move it to: the author may have meant
        // any time at all, so the editor must not pick one.
        const problems = findCueProblems(cue('a', -500, 2000));
        expect(problems.map((p) => p.code)).toEqual(['negativestart']);
        expect(problems[0].repairable).toBe(false);
    });

    it('rejects a cue that runs past the medium when the duration is known', () => {
        expect(findCueProblems(cue('a', 1000, 9000), 5000).map((p) => p.code)).toEqual(['afterduration']);
    });

    it('says nothing about duration when the duration is unknown', () => {
        // A provider embed never reports one. Treating unknown as zero would
        // mark every cue in the draft as broken.
        expect(findCueProblems(cue('a', 1000, 9000), null)).toEqual([]);
        expect(findCueProblems(cue('a', 1000, 9000), 0)).toEqual([]);
    });
});

describe('partitionCues', () => {
    it('separates the saveable cues from the problematic ones', () => {
        const {saveable, problems} = partitionCues([
            cue('a', 0, 1000),
            cue('b', 2000, 1000),
            cue('c', 3000, 4000),
        ]);

        expect(saveable.map((c) => c.cuekey)).toEqual(['a', 'c']);
        expect([...problems.keys()]).toEqual(['b']);
    });

    it('reports a problematic cue without listing it as removed', () => {
        // The distinction the partial save depends on. A held-back cue is
        // absent from the payload, and absence must mean "leave alone" — if it
        // were reported as a deletion the server would destroy exactly the
        // state being protected.
        const {saveable, problems} = partitionCues([cue('a', 2000, 1000)]);

        expect(saveable).toEqual([]);
        expect([...problems.keys()]).toEqual(['a']);
    });
});

describe('repairCue', () => {
    it('ends a reversed cue where the next one begins', () => {
        const repaired = repairCue(cue('a', 2000, 1000), 5000, null);
        expect(repaired?.endtime).toBe(5000);
        expect(repaired?.starttime).toBe(2000);
    });

    it('falls back to the playback position when there is no next cue', () => {
        expect(repairCue(cue('a', 2000, 1000), null, 3500)?.endtime).toBe(3500);
    });

    it('refuses when neither the next cue nor the playhead is usable', () => {
        // The next cue starts before this one, and the medium is paused before
        // it too. Both candidates would produce another reversed cue.
        expect(repairCue(cue('a', 2000, 1000), 1500, 900)).toBeNull();
    });

    it('refuses to repair a problem that has no single answer', () => {
        expect(repairCue(cue('a', -500, 2000), 5000, 3000)).toBeNull();
    });

    it('does not move a repaired end past the medium', () => {
        expect(repairCue(cue('a', 2000, 1000), 9000, null, 5000)?.endtime).toBe(5000);
    });

    it('pulls a cue that overruns the medium back to its end', () => {
        expect(repairCue(cue('a', 1000, 9000), null, null, 5000)?.endtime).toBe(5000);
    });

    it('leaves a healthy cue alone', () => {
        expect(repairCue(cue('a', 1000, 2000), 5000, 3000)).toBeNull();
    });
});
