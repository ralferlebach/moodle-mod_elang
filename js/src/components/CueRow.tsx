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

import {useRef, useState} from 'react';
import {Cue, Gap, GapRule, RuleGapSpan, Translator} from '../types';
import {newKey} from '../keys';
import {resyncGaps} from '../studio/resync';
import {maskTranscript} from '../studio/mask';
import {utf16ToCodepoint} from '../studio/text';
import {GapRow} from './GapRow';
import {TimeField} from './TimeField';
import {RuleGapControl} from './RuleGapControl';

interface Props {
    cue: Cue;
    t: Translator;
    focused: boolean;
    /** Returns the current playback position in ms, or null without a medium. */
    capturems: () => number | null;
    onChange: (cue: Cue) => void;
    onDelete: () => void;
    onStatus: (text: string) => void;
    onGenerateGaps: (transcript: string, rule: GapRule) => Promise<RuleGapSpan[]>;
}

/**
 * Render one cue's editor card.
 *
 * @param props The component props.
 * @returns The cue row element.
 */
export function CueRow({cue, t, focused, capturems, onChange, onDelete, onStatus, onGenerateGaps}: Props): JSX.Element {
    const textareaRef = useRef<HTMLTextAreaElement>(null);
    const [showpreview, setShowpreview] = useState(false);

    const replaceGap = (index: number, gap: Gap): void => {
        const gaps = cue.gaps.slice();
        gaps[index] = gap;
        onChange({...cue, gaps});
    };

    // Editing the transcript must keep every gap pointing at the same word: the
    // offsets are remapped from the old text to the new one, and any gap whose
    // text was deleted outright is dropped.
    const handleTranscriptChange = (value: string): void => {
        onChange({...cue, transcript: value, gaps: resyncGaps(cue.gaps, cue.transcript, value)});
    };

    const addGapFromSelection = (): void => {
        const textarea = textareaRef.current;
        if (!textarea) {
            return;
        }
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        if (start === end) {
            onStatus(t('editor_selecttext'));
            return;
        }
        // Convert the textarea's UTF-16 selection offsets to the codepoint
        // offsets the server stores and grades against.
        const charstart = utf16ToCodepoint(textarea.value, start);
        const charend = utf16ToCodepoint(textarea.value, end);
        onChange({...cue, gaps: [...cue.gaps, {
            gapkey: newKey('g'),
            sortorder: cue.gaps.length + 1,
            charstart,
            charlength: charend - charstart,
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
                        {t('editor_starttime')}{' '}
                        <TimeField
                            value={cue.starttime}
                            label={t('editor_starttime')}
                            invalidmessage={t('editor_invalidtime')}
                            onChange={(ms) => onChange({...cue, starttime: ms})}
                        />
                    </label>
                    <button type="button" className="btn btn-link btn-sm p-0 mr-3" onClick={() => capture('starttime')}>
                        {t('editor_capturestart')}
                    </button>
                    <label className="mr-2">
                        {t('editor_endtime')}{' '}
                        <TimeField
                            value={cue.endtime}
                            label={t('editor_endtime')}
                            invalidmessage={t('editor_invalidtime')}
                            onChange={(ms) => onChange({...cue, endtime: ms})}
                        />
                    </label>
                    <button type="button" className="btn btn-link btn-sm p-0 mr-3" onClick={() => capture('endtime')}>
                        {t('editor_captureend')}
                    </button>
                </div>

                <label className="d-block">
                    {t('editor_transcript')}
                    <textarea
                        ref={textareaRef}
                        className="form-control"
                        rows={2}
                        value={cue.transcript}
                        onChange={(event) => handleTranscriptChange(event.target.value)}
                    />
                </label>

                {cue.gaps.length > 0 && (
                    <div className="mod_elang-editor-preview mt-1">
                        <button
                            type="button"
                            className="btn btn-link btn-sm p-0"
                            aria-expanded={showpreview}
                            onClick={() => setShowpreview((value) => !value)}
                        >
                            {t('editor_preview')}
                        </button>
                        {showpreview && (
                            <p
                                className="mod_elang-editor-preview-text text-muted small mb-0"
                                data-region="maskedpreview"
                            >
                                {maskTranscript(cue.transcript, cue.gaps)}
                            </p>
                        )}
                    </div>
                )}

                <div className="mod_elang-editor-gaps mt-2">
                    {cue.gaps.length === 0
                        ? <p className="text-muted small mb-0">{t('editor_nogaps')}</p>
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
                    {t('editor_addgap')}
                </button>

                <RuleGapControl
                    cue={cue}
                    t={t}
                    onGenerate={onGenerateGaps}
                    onApply={(gaps) => onChange({...cue, gaps})}
                    onStatus={onStatus}
                />

                <button type="button" className="btn btn-link text-danger p-0" onClick={onDelete}>
                    {t('editor_deletecue')}
                </button>
            </div>
        </div>
    );
}
