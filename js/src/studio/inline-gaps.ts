/**
 * Turn a cue transcript into the pieces the inline gap view renders.
 *
 * Issue #28: the gaps of a cue were edited in forms below the text, so an
 * author had to hold the mapping between a row called "gap 2" and a word in the
 * sentence in their head. Showing the marks where the words are removes that
 * step, but only if the positions are right — and the positions are codepoint
 * offsets, not UTF-16 indices, which is the thing that quietly breaks on any
 * transcript containing an emoji or a character outside the basic plane.
 *
 * The segmentation is kept apart from the component that draws it so it can be
 * tested on its own, including the cases that are awkward to reach through a
 * browser: overlaps, ranges past the end of the text, several gaps in one cue.
 *
 * @module     mod_elang/studio/inline-gaps
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Gap} from '../types';
import {codepoints} from './text';

/** A run of plain transcript between gaps. */
export interface TextSegment {
    kind: 'text';
    text: string;
}

/** A gap, at the place in the text it covers. */
export interface GapSegment {
    kind: 'gap';
    /** The stable key, so a click resolves to a gap rather than to a position. */
    gapkey: string;
    /** The words the gap covers, as they stand in the transcript. */
    text: string;
    /** How answers are compared, which is what the visual coding shows. */
    gradingalgorithm: string;
    /** Whether the gap accepts answers beyond the solution. */
    hasalternatives: boolean;
    /** Whether the gap offers any hint. */
    hashints: boolean;
}

/**
 * A gap whose range cannot be drawn: it overlaps another or falls outside the
 * text. Reported rather than skipped — a gap silently missing from the view is
 * worse than one shown as broken, because the author has no reason to look for
 * it.
 */
export interface InvalidGapSegment {
    kind: 'invalid';
    gapkey: string;
    reason: 'outofrange' | 'overlap';
}

export type Segment = TextSegment | GapSegment | InvalidGapSegment;

/**
 * Split a transcript into text runs and gap marks.
 *
 * @param transcript The full cue transcript.
 * @param gaps The cue's gaps, addressed in codepoint offsets.
 * @returns The segments in reading order, with invalid gaps reported at the end.
 */
export function segmentTranscript(transcript: string, gaps: Gap[]): Segment[] {
    const cp = codepoints(transcript);
    const segments: Segment[] = [];
    const invalid: InvalidGapSegment[] = [];

    const ordered = gaps.slice().sort((a, b) => a.charstart - b.charstart);
    let cursor = 0;

    for (const gap of ordered) {
        const start = gap.charstart;
        const end = gap.charstart + gap.charlength;

        if (gap.charlength <= 0 || start < 0 || end > cp.length) {
            invalid.push({kind: 'invalid', gapkey: gap.gapkey, reason: 'outofrange'});
            continue;
        }
        if (start < cursor) {
            // Two gaps claiming the same words. Drawing either one would put a
            // mark somewhere the author did not ask for, so neither is drawn
            // and the conflict is named instead.
            invalid.push({kind: 'invalid', gapkey: gap.gapkey, reason: 'overlap'});
            continue;
        }

        if (start > cursor) {
            segments.push({kind: 'text', text: cp.slice(cursor, start).join('')});
        }

        segments.push({
            kind: 'gap',
            gapkey: gap.gapkey,
            text: cp.slice(start, end).join(''),
            gradingalgorithm: gap.gradingalgorithm,
            hasalternatives: gap.answers.length > 0,
            hashints: gap.hints.length > 0,
        });

        cursor = end;
    }

    if (cursor < cp.length) {
        segments.push({kind: 'text', text: cp.slice(cursor).join('')});
    }

    return segments.concat(invalid);
}
