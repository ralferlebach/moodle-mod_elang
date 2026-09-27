/**
 * Local checks on a single cue, run before the editor tries to save it.
 *
 * Issue #26: the editor sent whatever the author had typed and let the server
 * decide. The server accepts it — a half-finished draft is a legitimate save —
 * so a cue whose end time sits before its start time was persisted quietly and
 * only became a problem later, at publish, far from where it was made.
 *
 * What belongs here is narrow on purpose: only what can be decided from one cue
 * and the medium's duration, with no reference to the rest of the draft and no
 * call to the server. Duplicate keys, overlapping gap ranges and grading
 * algorithms are all decidable too, but not from one cue in isolation, and the
 * server already refuses them as a whole payload. Re-implementing that here
 * would be a second opinion that could disagree with the first.
 *
 * The server stays the authority. These checks exist so the author is told at
 * the moment they create the contradiction, and so the editor knows which cue
 * to hold back — not to replace validation.
 */

import type {Cue} from '../types';

/** What can be wrong with a cue on its own. */
export type CueProblemCode =
    | 'negativestart'
    | 'endbeforestart'
    | 'afterduration';

/** A problem found in one cue. */
export interface CueProblem {
    /** Which problem, for choosing a message and a repair. */
    code: CueProblemCode;
    /**
     * Whether a single unambiguous correction exists.
     *
     * An end time before its start can be moved; a cue that starts before the
     * recording does cannot be placed without guessing what the author meant.
     */
    repairable: boolean;
}

/**
 * Check one cue against itself and, when known, the medium's duration.
 *
 * @param cue The cue to check.
 * @param duration The medium's length in milliseconds, or null when unknown.
 * @returns Every problem found, in the order they would be reported.
 */
export function findCueProblems(cue: Cue, duration: number | null = null): CueProblem[] {
    const problems: CueProblem[] = [];

    if (cue.starttime < 0) {
        problems.push({code: 'negativestart', repairable: false});
    }

    if (cue.endtime <= cue.starttime) {
        problems.push({code: 'endbeforestart', repairable: true});
    }

    // Only when the duration is actually known. A provider embed never reports
    // one, and treating "unknown" as zero would mark every cue in the draft.
    if (duration !== null && duration > 0 && cue.endtime > duration) {
        problems.push({code: 'afterduration', repairable: true});
    }

    return problems;
}

/**
 * Split a draft into the cues that may be saved and the ones that may not.
 *
 * The keys matter as much as the cues. A cue held back must not be sent, and
 * must not be reported as deleted either — the partial save endpoint treats an
 * absent key as "leave alone", which is the whole reason it exists.
 *
 * @param cues The editor's current cues.
 * @param duration The medium's length in milliseconds, or null when unknown.
 * @returns The saveable cues, and the problems keyed by cue key.
 */
export function partitionCues(
    cues: Cue[],
    duration: number | null = null
): {saveable: Cue[]; problems: Map<string, CueProblem[]>} {
    const saveable: Cue[] = [];
    const problems = new Map<string, CueProblem[]>();

    for (const cue of cues) {
        const found = findCueProblems(cue, duration);
        if (found.length === 0) {
            saveable.push(cue);
        } else {
            problems.set(cue.cuekey, found);
        }
    }

    return {saveable, problems};
}

/**
 * Work out a correction for a cue, or return null when none is unambiguous.
 *
 * Nothing here is applied on its own: the result is offered to the author as a
 * button. A timing conflict has more than one reasonable reading — the author
 * may have meant to move the end, or the start, or to delete the cue — so the
 * editor proposes and the author decides. Silently repairing data the author
 * did not ask to have repaired is how a draft ends up saying something nobody
 * wrote.
 *
 * @param cue The cue to repair.
 * @param nextStart The start time of the following cue, when there is one.
 * @param playhead The current playback position, used when there is no next cue.
 * @param duration The medium's length in milliseconds, or null when unknown.
 * @returns The corrected cue, or null when no single correction is obvious.
 */
export function repairCue(
    cue: Cue,
    nextStart: number | null,
    playhead: number | null,
    duration: number | null = null
): Cue | null {
    const problems = findCueProblems(cue, duration);
    if (problems.length === 0 || !problems.every((problem) => problem.repairable)) {
        return null;
    }

    let endtime = cue.endtime;

    if (cue.endtime <= cue.starttime) {
        // The next cue's start is the most defensible end: it is where this cue
        // has to stop anyway, and it is a number already in the draft rather
        // than one invented here.
        if (nextStart !== null && nextStart > cue.starttime) {
            endtime = nextStart;
        } else if (playhead !== null && playhead > cue.starttime) {
            // Otherwise where the author has the medium paused — they are
            // almost certainly looking at the frame they mean.
            endtime = playhead;
        } else {
            return null;
        }
    }

    if (duration !== null && duration > 0 && endtime > duration) {
        endtime = duration;
    }

    if (endtime <= cue.starttime) {
        return null;
    }

    return {...cue, endtime};
}
