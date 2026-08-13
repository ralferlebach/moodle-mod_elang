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
 * Keeps a cue's gap offsets valid while its transcript is being edited.
 *
 * elang_gap.charstart/charlength are codepoint offsets into the cue transcript;
 * when the author edits the text, an offset that used to point at the right word
 * would otherwise silently drift, so the player would blank out the wrong
 * characters (and the publish validator would reject an out-of-range gap). This
 * module remaps every gap's offsets from the old transcript to the new one by
 * diffing their common prefix and suffix, so a gap before an edit is untouched,
 * a gap after it shifts by the length change, and a gap overlapping the edit is
 * remapped monotonically and clamped to the new bounds.
 *
 * @module     mod_elang/studio/resync
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Gap} from '../types';
import {codepoints} from './text';

/** A gap's codepoint span, decoupled from the rest of the gap record. */
export interface Span {
    charstart: number;
    charlength: number;
}

/**
 * The length of the common codepoint prefix shared by two strings.
 *
 * @param a The first codepoint array.
 * @param b The second codepoint array.
 * @returns The number of leading codepoints that are identical.
 */
function commonPrefix(a: string[], b: string[]): number {
    const limit = Math.min(a.length, b.length);
    let i = 0;
    while (i < limit && a[i] === b[i]) {
        i++;
    }
    return i;
}

/**
 * The length of the common codepoint suffix shared by two strings, not counting
 * codepoints already claimed by the prefix.
 *
 * @param a The first codepoint array.
 * @param b The second codepoint array.
 * @param prefix The already-matched common prefix length.
 * @returns The number of trailing codepoints that are identical.
 */
function commonSuffix(a: string[], b: string[], prefix: number): number {
    const limit = Math.min(a.length, b.length) - prefix;
    let i = 0;
    while (i < limit && a[a.length - 1 - i] === b[b.length - 1 - i]) {
        i++;
    }
    return i;
}

/**
 * Map a single codepoint position from the old transcript to the new one.
 *
 * Positions in the unchanged head map to themselves; positions in the unchanged
 * tail shift by the overall length change; positions inside the edited region
 * are mapped monotonically onto the new edited region (collapsing to its start
 * for a pure insertion), so a gap boundary can never cross another. Where the
 * head and tail meet (a pure insertion or deletion at one point), the bias
 * decides the tie: a span's start is pushed along by an insertion at its edge
 * while its end is not, which is the intuitive "text typed at the gap boundary
 * grows the text around the gap, not the gap" behaviour.
 *
 * @param pos The codepoint position in the old text.
 * @param prefix The common prefix length.
 * @param oldTailStart The first codepoint of the common suffix in the old text.
 * @param newTailStart The first codepoint of the common suffix in the new text.
 * @param delta The overall codepoint length change (new minus old).
 * @param bias Which side wins when the position sits on the head/tail boundary.
 * @returns The mapped position in the new text.
 */
function mapPosition(
    pos: number,
    prefix: number,
    oldTailStart: number,
    newTailStart: number,
    delta: number,
    bias: 'start' | 'end'
): number {
    if (bias === 'start') {
        if (pos >= oldTailStart) {
            return pos + delta;
        }
        if (pos <= prefix) {
            return pos;
        }
    } else {
        if (pos <= prefix) {
            return pos;
        }
        if (pos >= oldTailStart) {
            return pos + delta;
        }
    }
    const oldSpan = oldTailStart - prefix;
    const newSpan = newTailStart - prefix;
    if (oldSpan <= 0) {
        return prefix;
    }
    return prefix + Math.round(((pos - prefix) * newSpan) / oldSpan);
}

/**
 * Remap one gap span from the old transcript to the new one.
 *
 * @param span The gap span in the old transcript.
 * @param oldText The transcript before the edit.
 * @param newText The transcript after the edit.
 * @returns The remapped span, with a non-negative length clamped to the new text.
 */
export function resyncSpan(span: Span, oldText: string, newText: string): Span {
    if (oldText === newText) {
        return {charstart: span.charstart, charlength: span.charlength};
    }

    const oldCp = codepoints(oldText);
    const newCp = codepoints(newText);
    const prefix = commonPrefix(oldCp, newCp);
    const suffix = commonSuffix(oldCp, newCp, prefix);
    const oldTailStart = oldCp.length - suffix;
    const newTailStart = newCp.length - suffix;
    const delta = newCp.length - oldCp.length;

    const start = mapPosition(span.charstart, prefix, oldTailStart, newTailStart, delta, 'start');
    const end = mapPosition(span.charstart + span.charlength, prefix, oldTailStart, newTailStart, delta, 'end');

    const clampedStart = Math.max(0, Math.min(start, newCp.length));
    const clampedEnd = Math.max(clampedStart, Math.min(end, newCp.length));

    return {charstart: clampedStart, charlength: clampedEnd - clampedStart};
}

/**
 * Remap every gap of a cue after its transcript changed, dropping any gap whose
 * span collapsed to nothing (its text was entirely deleted).
 *
 * @param gaps The cue's gaps, addressed against the old transcript.
 * @param oldText The transcript before the edit.
 * @param newText The transcript after the edit.
 * @returns The surviving gaps, re-addressed against the new transcript.
 */
export function resyncGaps(gaps: Gap[], oldText: string, newText: string): Gap[] {
    if (oldText === newText) {
        return gaps;
    }

    const remapped: Gap[] = [];
    gaps.forEach((gap) => {
        const span = resyncSpan({charstart: gap.charstart, charlength: gap.charlength}, oldText, newText);
        if (span.charlength > 0) {
            remapped.push({...gap, charstart: span.charstart, charlength: span.charlength});
        }
    });
    return remapped;
}
