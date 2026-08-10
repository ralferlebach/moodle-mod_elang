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
 * Editable row for one cue: its timing (with capture-from-playback buttons),
 * transcript and gaps. Gaps are created from the current text selection in
 * the transcript, defaulting the solution to the selected text.
 *
 * @module     mod_elang/components/CueRow
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useRef} from 'react';
import {Cue, Gap, Translator} from '../types';
import {newKey} from '../keys';
import {GapRow} from './GapRow';

interface Props {
    cue: Cue;
    t: Translator;
    focused: boolean;
    /** Returns the current playback position in ms, or null without a medium. */
    capturems: () => number | null;
    onChange: (cue: Cue) => void;
    onDelete: () => void;
    onStatus: (text: string) => void;
}

/**
 * Render one cue's editor card.
 *
 * @param props The component props.
 * @returns The cue row element.
 */
export function CueRow({cue, t, focused, capturems, onChange, onDelete, onStatus}: Props): JSX.Element {
    const textareaRef = useRef<HTMLTextAreaElement>(null);

    const replaceGap = (index: number, gap: Gap): void => {
        const gaps = cue.gaps.slice();
        gaps[index] = gap;
        onChange({...cue, gaps});
    };

    const addGapFromSelection = (): void => {
        const textarea = textareaRef.current;
        if (!textarea) {
            return;
        }
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        if (start === end) {
            onStatus(t('editor:selecttext'));
            return;
        }
        onChange({...cue, gaps: [...cue.gaps, {
            gapkey: newKey('g'),
            sortorder: cue.gaps.length + 1,
            charstart: start,
            charlength: end - start,
            solution: textarea.value.substring(start, end),
            gradingalgorithm: 'exact',
            maxlength: 0,
            linkurl: '',
            answers: [],
            hints: [],
        }]});
    };

    const capture = (field: 'starttime' | 'endtime'): void => {
        const ms = capturems();
        if (ms !== null) {
            onChange({...cue, [field]: ms});
        }
    };

    return (
        <div className={'mod_elang-editor-cue card mb-2' + (focused ? ' focused' : '')} data-cuekey={cue.cuekey}>
            <div className="card-body">
                <div className="mb-2">
                    <label className="mr-2">
                        {t('editor:starttime')}{' '}
                        <input
                            type="number"
                            className="form-control d-inline-block"
                            style={{width: '8rem'}}
                            min={0}
                            value={cue.starttime}
                            onChange={(event) => onChange({...cue, starttime: parseInt(event.target.value, 10) || 0})}
                        />
                    </label>
                    <button type="button" className="btn btn-link btn-sm p-0 mr-3" onClick={() => capture('starttime')}>
                        {t('editor:capturestart')}
                    </button>
                    <label className="mr-2">
                        {t('editor:endtime')}{' '}
                        <input
                            type="number"
                            className="form-control d-inline-block"
                            style={{width: '8rem'}}
                            min={0}
                            value={cue.endtime}
                            onChange={(event) => onChange({...cue, endtime: parseInt(event.target.value, 10) || 0})}
                        />
                    </label>
                    <button type="button" className="btn btn-link btn-sm p-0 mr-3" onClick={() => capture('endtime')}>
                        {t('editor:captureend')}
                    </button>
                </div>

                <label className="d-block">
                    {t('editor:transcript')}
                    <textarea
                        ref={textareaRef}
                        className="form-control"
                        rows={2}
                        value={cue.transcript}
                        onChange={(event) => onChange({...cue, transcript: event.target.value})}
                    />
                </label>

                <div className="mod_elang-editor-gaps mt-2">
                    {cue.gaps.length === 0
                        ? <p className="text-muted small mb-0">{t('editor:nogaps')}</p>
                        : cue.gaps.map((gap, index) => (
                            <GapRow
                                key={gap.gapkey}
                                gap={gap}
                                t={t}
                                onChange={(updated) => replaceGap(index, updated)}
                                onDelete={() => onChange({...cue, gaps: cue.gaps.filter((_, i) => i !== index)})}
                            />
                        ))}
                </div>

                <button type="button" className="btn btn-link p-0 d-block" onClick={addGapFromSelection}>
                    {t('editor:addgap')}
                </button>
                <button type="button" className="btn btn-link text-danger p-0" onClick={onDelete}>
                    {t('editor:deletecue')}
                </button>
            </div>
        </div>
    );
}
