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
 * The decisions the player makes about playback, without the DOM.
 *
 * These four questions — which cue is playing, where to park after pausing,
 * whether a boundary should stop at all, and whether automatic scrolling is
 * currently unwelcome — are where every playback bug in this plugin has been
 * so far, and they were reachable only through a media element, a cue list and
 * a browser. They are pure functions here so a test can ask them directly.
 *
 * This module deliberately imports nothing: it is unit-testable exactly
 * because it knows about numbers and plain objects and nothing else.
 *
 * @module     mod_elang/playback
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Which cue covers a moment in the recording.
 *
 * The interval is half-open — a cue owns its start and not its end — so that
 * two adjacent cues never both claim the instant between them.
 *
 * @param {Array} cues Cues as {starttime, endtime} in milliseconds, in order
 * @param {Number} ms The moment, in milliseconds
 * @returns {Number} The index of the cue, or -1 when the moment is between cues
 */
export const activeCueIndex = (cues, ms) => cues.findIndex(
    (cue) => ms >= cue.starttime && ms < cue.endtime
);

/**
 * Where playback should park after stopping at the end of a cue.
 *
 * Not on the boundary itself. `activeCueIndex()` treats a cue's end as
 * belonging to the next one, so parking exactly there would leave no cue
 * active — which blanked the overlay caption at the very moment the learner
 * was asked to fill the sentence in. One millisecond earlier is still inside
 * the cue that just played and is inaudibly different.
 *
 * @param {Object} cue The cue that was crossed
 * @returns {Number} The time to park at, in milliseconds
 */
export const pauseLandingTime = (cue) => Math.max(0, cue.endtime - 1);

/**
 * Whether crossing the end of a cue should stop playback.
 *
 * Three things decide it:
 *
 * - the mode: "stop" at every boundary, "nostop" at none, "auto" only at the
 *   end of the cue being worked on;
 * - whether that cue still has anything to answer — a cue whose gaps are all
 *   filled in is finished work, and holding playback there asks for a
 *   keypress that achieves nothing;
 * - for "auto", whether this is in fact the engaged cue.
 *
 * @param {Object} options The decision inputs
 * @param {String} options.mode The effective pause mode: auto, stop or nostop
 * @param {Boolean} options.engaged Whether this is the cue being worked on
 * @param {Boolean} options.hasopengaps Whether the cue still has an unanswered gap
 * @returns {Boolean} True when playback should pause here
 */
export const shouldStopAtBoundary = ({mode, engaged, hasopengaps}) => {
    if (mode === 'nostop' || !hasopengaps) {
        return false;
    }

    return mode === 'stop' || (mode === 'auto' && engaged);
};

/**
 * The next gap worth moving to.
 *
 * Anything already answered is skipped: Enter means "on to the next thing to
 * do", and stopping on a filled gap would make the learner press it again for
 * every word they had got right.
 *
 * @param {Array} gaps Gaps as {answered: Boolean}, in reading order
 * @param {Number} from The index just left
 * @returns {Number} The index of the next unanswered gap, or -1 when there is none
 */
export const nextOpenGapIndex = (gaps, from) => {
    for (let index = from + 1; index < gaps.length; index++) {
        if (!gaps[index].answered) {
            return index;
        }
    }

    return -1;
};

/**
 * Whether playback needs to move before a cue can be worked on.
 *
 * Only when it is not already inside that cue. Seeking regardless would rewind
 * the sentence being heard every time a learner moved between two gaps of the
 * same cue.
 *
 * @param {Object} cue The cue to work on
 * @param {Number} ms The current playback position, in milliseconds
 * @returns {Boolean} True when playback should seek to the cue's start
 */
export const needsSeekToCue = (cue, ms) => ms < cue.starttime || ms >= cue.endtime;

/**
 * Whether automatic scrolling is currently unwelcome.
 *
 * After a learner scrolls by hand, automatic scrolling stays out of the way
 * for a while rather than snatching the view back at the next cue boundary.
 *
 * @param {Number} now The current timestamp, in milliseconds
 * @param {Number} suppressuntil When the grace period ends, in milliseconds
 * @returns {Boolean} True while automatic scrolling should be skipped
 */
export const autoScrollSuppressed = (now, suppressuntil) => now < suppressuntil;
