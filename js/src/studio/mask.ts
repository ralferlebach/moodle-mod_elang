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
 * Builds the masked learner view of a cue transcript for the authoring preview.
 *
 * This mirrors the server's transcript_masker: each gap's codepoint range is
 * replaced by a blank placeholder so the author sees exactly what a learner will
 * — the running text with the gaps hidden — without the editor ever having to
 * ask the server. Overlapping or out-of-range gaps are skipped defensively; the
 * publish validator is the authority on those.
 *
 * @module     mod_elang/studio/mask
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Gap} from '../types';
import {codepoints} from './text';

/** The placeholder a masked gap is rendered as in the preview. */
export const GAP_PLACEHOLDER = '\u2588\u2588\u2588\u2588';

/**
 * Mask a cue transcript by replacing every gap range with a placeholder.
 *
 * @param transcript The full cue transcript.
 * @param gaps The cue's gaps, addressed in codepoint offsets.
 * @param placeholder The text a gap is replaced with (defaults to a solid block).
 * @returns The transcript with each gap blanked out.
 */
export function maskTranscript(transcript: string, gaps: Gap[], placeholder: string = GAP_PLACEHOLDER): string {
    const cp = codepoints(transcript);
    const ordered = gaps
        .filter((gap) => gap.charlength > 0 && gap.charstart >= 0 && gap.charstart + gap.charlength <= cp.length)
        .slice()
        .sort((a, b) => a.charstart - b.charstart);

    let result = '';
    let cursor = 0;
    ordered.forEach((gap) => {
        if (gap.charstart < cursor) {
            // Overlaps a gap already emitted; skip it rather than risk exposing
            // part of a solution through a miscalculated splice.
            return;
        }
        result += cp.slice(cursor, gap.charstart).join('');
        result += placeholder;
        cursor = gap.charstart + gap.charlength;
    });
    result += cp.slice(cursor).join('');
    return result;
}
