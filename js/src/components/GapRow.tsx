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
 * Editable row for one gap: its position, solution, matching algorithm,
 * accepted variants and graded hints. All edits flow up as a replaced gap
 * object; hint levels are re-sequenced 1..n on every change so a published
 * version always passes validation.
 *
 * @module     mod_elang/components/GapRow
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Answer, Gap, Hint, Translator} from '../types';

/** The hint types the editor offers; the player shows each hint's text. */
const HINTTYPES = ['text', 'firstletter', 'wordlength', 'partial', 'solution', 'translation'];

interface Props {
    gap: Gap;
    t: Translator;
    onChange: (gap: Gap) => void;
    onDelete: () => void;
}

/**
 * Render one gap's editor row.
 *
 * @param props The component props.
 * @returns The gap row element.
 */
export function GapRow({gap, t, onChange, onDelete}: Props): JSX.Element {
    const replaceAnswer = (index: number, answer: Answer): void => {
        const answers = gap.answers.slice();
        answers[index] = answer;
        onChange({...gap, answers});
    };

    const replaceHint = (index: number, hint: Hint): void => {
        const hints = gap.hints.slice();
        hints[index] = hint;
        onChange({...gap, hints});
    };

    const resequenced = (hints: Hint[]): Hint[] => hints.map((hint, index) => ({...hint, level: index + 1}));

    return (
        <div className="mod_elang-editor-gap border rounded p-2 mt-2">
            <div className="text-muted small">
                {t('editor:gaprange')}: {gap.charstart}–{gap.charstart + gap.charlength}
            </div>

            <label className="d-block">
                {t('editor:solution')}
                <input
                    type="text"
                    className="form-control"
                    value={gap.solution}
                    onChange={(event) => onChange({...gap, solution: event.target.value})}
                />
            </label>

            <label className="d-block">
                {t('editor:algorithm')}
                <select
                    className="form-control"
                    value={gap.gradingalgorithm}
                    onChange={(event) => onChange({...gap, gradingalgorithm: event.target.value})}
                >
                    <option value="exact">{t('editor:algoexact')}</option>
                    <option value="wordrecognized">{t('editor:algowordrecognized')}</option>
                </select>
            </label>

            <p className="mb-1 mt-2">{t('editor:answers')}</p>
            <div>
                {gap.answers.map((answer, index) => (
                    <div className="mb-1" key={index}>
                        <input
                            type="text"
                            className="form-control d-inline-block w-75 mr-2"
                            value={answer.answer}
                            aria-label={t('editor:answers')}
                            onChange={(event) => replaceAnswer(index, {...answer, answer: event.target.value})}
                        />
                        <button
                            type="button"
                            className="btn btn-link text-danger p-0"
                            onClick={() => onChange({...gap, answers: gap.answers.filter((_, i) => i !== index)})}
                        >
                            {t('editor:removevariant')}
                        </button>
                    </div>
                ))}
            </div>
            <button
                type="button"
                className="btn btn-link p-0"
                onClick={() => onChange({
                    ...gap,
                    answers: [...gap.answers, {sortorder: gap.answers.length + 1, answer: '', isregex: 0}],
                })}
            >
                {t('editor:addvariant')}
            </button>

            <p className="mb-1 mt-2">{t('editor:hints')}</p>
            <div>
                {gap.hints.map((hint, index) => (
                    <div className="mod_elang-editor-hint border rounded p-2 mt-1" key={index}>
                        <span className="badge badge-secondary mr-2">{index + 1}</span>
                        <select
                            className="form-control d-inline-block w-auto mr-2"
                            aria-label={t('editor:hinttype')}
                            value={hint.hinttype}
                            onChange={(event) => replaceHint(index, {...hint, hinttype: event.target.value})}
                        >
                            {HINTTYPES.map((type) => (
                                <option value={type} key={type}>{t('editor:hinttype_' + type)}</option>
                            ))}
                        </select>
                        <input
                            type="text"
                            className="form-control d-inline-block w-50 mr-2"
                            value={hint.hinttext}
                            placeholder={t('editor:hinttext')}
                            aria-label={t('editor:hinttext')}
                            onChange={(event) => replaceHint(index, {...hint, hinttext: event.target.value})}
                        />
                        <input
                            type="number"
                            className="form-control d-inline-block mr-2"
                            style={{width: '6rem'}}
                            step={0.1}
                            min={0}
                            value={hint.penalty}
                            aria-label={t('editor:penalty')}
                            onChange={(event) => replaceHint(index, {...hint, penalty: parseFloat(event.target.value) || 0})}
                        />
                        <button
                            type="button"
                            className="btn btn-link text-danger p-0"
                            onClick={() => onChange({...gap, hints: resequenced(gap.hints.filter((_, i) => i !== index))})}
                        >
                            {t('editor:removehint')}
                        </button>
                    </div>
                ))}
            </div>
            <button
                type="button"
                className="btn btn-link p-0"
                onClick={() => onChange({
                    ...gap,
                    hints: [...gap.hints, {level: gap.hints.length + 1, hinttype: 'text', hinttext: '', penalty: 0}],
                })}
            >
                {t('editor:addhint')}
            </button>

            <button type="button" className="btn btn-link text-danger p-0 d-block" onClick={onDelete}>
                {t('editor:deletegap')}
            </button>
        </div>
    );
}
