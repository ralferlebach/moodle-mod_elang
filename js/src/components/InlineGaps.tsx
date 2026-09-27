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
 * The cue's text with its gaps marked where the words actually are.
 *
 * The textarea above stays the place text is typed; this is a reading view of
 * the same sentence that happens to be clickable. Keeping them apart is what
 * makes it safe: a contenteditable would have to reconcile an author's caret
 * with codepoint offsets on every keystroke, and getting that wrong moves a gap
 * onto the wrong word without anyone noticing.
 *
 * @module     mod_elang/components/InlineGaps
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Gap, Translator} from '../types';
import {segmentTranscript} from '../studio/inline-gaps';

interface Props {
    transcript: string;
    gaps: Gap[];
    t: Translator;
    /** The gap currently open in the inspector, so the view can show which. */
    selectedgapkey?: string;
    /** Open a gap's row. Addressed by key, never by position. */
    onSelectGap: (gapkey: string) => void;
}

/**
 * Render the transcript with its gaps as interactive marks.
 *
 * @param props The component props.
 * @returns The inline gap view.
 */
export function InlineGaps({transcript, gaps, t, selectedgapkey, onSelectGap}: Props): JSX.Element | null {
    if (gaps.length === 0) {
        return null;
    }

    const segments = segmentTranscript(transcript, gaps);

    return (
        <div className="mod_elang-inline-gaps mt-1" data-region="inlinegaps">
            <p className="mod_elang-inline-gaps-text mb-1">
                {segments.map((segment, index) => {
                    if (segment.kind === 'text') {
                        return <span key={'t' + index}>{segment.text}</span>;
                    }

                    if (segment.kind === 'invalid') {
                        // Named, not hidden. A gap that cannot be placed is
                        // still a gap the author created, and leaving it out of
                        // the view would make it look as though it had gone.
                        return (
                            <button
                                type="button"
                                key={segment.gapkey}
                                className="btn btn-sm btn-outline-danger mx-1 mod_elang-inline-gap-invalid"
                                data-gapkey={segment.gapkey}
                                data-region="inlinegapinvalid"
                                title={t('editor_gapunplaceable_' + segment.reason)}
                                aria-label={t('editor_gapunplaceable_' + segment.reason)}
                                onClick={() => onSelectGap(segment.gapkey)}
                            >
                                <span aria-hidden="true">{'\u26A0'}</span>
                            </button>
                        );
                    }

                    // The matching mode is in the accessible name as well as in
                    // the styling: "black" and "grey" are not information
                    // anyone can rely on.
                    const mode = segment.gradingalgorithm === 'wordrecognized'
                        ? t('editor_gapmode_wordrecognized')
                        : t('editor_gapmode_exact');
                    const extras = [
                        segment.hasalternatives ? t('editor_gaphasalternatives') : '',
                        segment.hashints ? t('editor_gaphashints') : '',
                    ].filter((extra) => extra !== '');
                    const label = [segment.text, mode].concat(extras).join(' — ');

                    return (
                        <button
                            type="button"
                            key={segment.gapkey}
                            className={
                                'mod_elang-inline-gap '
                                + (segment.gradingalgorithm === 'wordrecognized' ? 'recognized' : 'exact')
                                + (segment.gapkey === selectedgapkey ? ' selected' : '')
                            }
                            data-gapkey={segment.gapkey}
                            data-region="inlinegap"
                            title={label}
                            aria-label={label}
                            onClick={() => onSelectGap(segment.gapkey)}
                        >
                            <span className="mod_elang-inline-gap-word">{segment.text}</span>
                            {segment.hasalternatives && (
                                <span className="mod_elang-inline-gap-icon" aria-hidden="true">{'\u21C4'}</span>
                            )}
                            {segment.hashints && (
                                <span className="mod_elang-inline-gap-icon" aria-hidden="true">{'\u2726'}</span>
                            )}
                        </button>
                    );
                })}
            </p>
        </div>
    );
}
