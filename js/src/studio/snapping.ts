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
 * Snapping and pixel/time conversion for the timeline's drag editing.
 *
 * When an author drags a cue boundary, the raw pointer position rarely lands
 * exactly on a neighbouring cue's edge or the playhead. snapMs pulls it onto the
 * nearest such candidate when it is within a small threshold, so cues meet
 * cleanly without pixel-perfect aim; the pixel/time helpers keep the drag maths
 * in one tested place.
 *
 * @module     mod_elang/studio/snapping
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Snap a time to the nearest candidate within a threshold.
 *
 * @param ms The raw time in milliseconds.
 * @param candidates Times to snap onto (other cue edges, the playhead).
 * @param thresholdMs The maximum distance at which snapping applies.
 * @returns The snapped time, or the original when nothing is close enough.
 */
export function snapMs(ms: number, candidates: number[], thresholdMs: number): number {
    let best = ms;
    let bestDistance = thresholdMs;
    candidates.forEach((candidate) => {
        const distance = Math.abs(candidate - ms);
        if (distance <= bestDistance) {
            best = candidate;
            bestDistance = distance;
        }
    });
    return best;
}

/**
 * Convert a horizontal pixel offset within the timeline into a time.
 *
 * @param px The pixel offset from the timeline's left edge.
 * @param widthPx The timeline's pixel width.
 * @param totalMs The time the full width represents.
 * @returns The time in milliseconds, clamped to [0, totalMs].
 */
export function pxToMs(px: number, widthPx: number, totalMs: number): number {
    if (widthPx <= 0) {
        return 0;
    }
    const ms = Math.round((px / widthPx) * totalMs);
    return Math.max(0, Math.min(ms, totalMs));
}

/**
 * Convert a time into a percentage offset across the timeline, for CSS.
 *
 * @param ms The time in milliseconds.
 * @param totalMs The time the full width represents.
 * @returns The offset as a percentage in [0, 100].
 */
export function msToPercent(ms: number, totalMs: number): number {
    if (totalMs <= 0) {
        return 0;
    }
    return Math.max(0, Math.min((ms / totalMs) * 100, 100));
}
