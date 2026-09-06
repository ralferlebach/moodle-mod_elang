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
            {/* The character offsets are how a gap is stored and graded, and they
                are maintained by selecting text and by resyncGaps(); nobody
                types them. Showing them made an internal coordinate look like a
                field to fill in. They remain available for support work through
                the data attribute below. */}
            <div className="sr-only visually-hidden" data-gaprange={gap.charstart + '-' + (gap.charstart + gap.charlength)}>
                {t('editor_gaprange')}: {gap.charstart}–{gap.charstart + gap.charlength}
            </div>

            <div className="row">
                <div className="col-12 col-md-4">
                    <label className="d-block">
                        {t('editor_solution')}
                        <input
                            type="text"
                            className="form-control"
                            value={gap.solution}
                            onChange={(event) => onChange({...gap, solution: event.target.value})}
                        />
                    </label>
                </div>

                <div className="col-12 col-md-4">
                    <label className="d-block">
                        {t('editor_algorithm')}
                        <select
                            className="form-control"
                            value={gap.gradingalgorithm}
                            onChange={(event) => onChange({...gap, gradingalgorithm: event.target.value})}
                        >
                            <option value="exact">{t('editor_algoexact')}</option>
                            <option value="wordrecognized">{t('editor_algowordrecognized')}</option>
                        </select>
                    </label>
                </div>

                <div className="col-12 col-md-4">
                    <span className="d-block">{t('editor_answers')}</span>
                    {/* Variants read as a list of accepted spellings rather than
                        as a column of full-width text fields: there are usually
                        one or two, and each used to take a row of its own with a
                        "remove" link beside it. */}
                    <div className="mod_elang-editor-variants" data-region="variants">
                        {gap.answers.map((answer, index) => (
                            <span className="mod_elang-editor-variant" key={index}>
                                <input
                                    type="text"
                                    className="form-control form-control-sm d-inline-block w-auto"
                                    value={answer.answer}
                                    aria-label={t('editor_answers')}
                                    onChange={(event) => replaceAnswer(index, {...answer, answer: event.target.value})}
                                />
                                <button
                                    type="button"
                                    className="btn btn-link btn-sm text-danger p-0 ml-1"
                                    aria-label={t('editor_removevariant')}
                                    title={t('editor_removevariant')}
                                    data-action="removevariant"
                                    onClick={() => onChange({
                                        ...gap,
                                        answers: gap.answers.filter((_, i) => i !== index),
                                    })}
                                >
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </span>
                        ))}
                    </div>
                    <button
                        type="button"
                        className="btn btn-link btn-sm p-0"
                        data-action="addvariant"
                        onClick={() => onChange({
                            ...gap,
                            answers: [...gap.answers, {sortorder: gap.answers.length + 1, answer: '', isregex: 0}],
                        })}
                    >
                        {t('editor_addvariant')}
                    </button>
                </div>
            </div>

            <p className="mb-1 mt-2">{t('editor_hints')}</p>
            <div>
                {gap.hints.map((hint, index) => (
                    <div className="mod_elang-editor-hint border rounded p-2 mt-1" key={index}>
                        <span className="badge badge-secondary bg-secondary mr-2 me-2">{index + 1}</span>
                        <select
                            className="form-control d-inline-block w-auto mr-2 me-2"
                            aria-label={t('editor_hinttype')}
                            value={hint.hinttype}
                            onChange={(event) => replaceHint(index, {...hint, hinttype: event.target.value})}
                        >
                            {HINTTYPES.map((type) => (
                                <option value={type} key={type}>{t('editor_hinttype_' + type)}</option>
                            ))}
                        </select>
                        <input
                            type="text"
                            className="form-control d-inline-block w-50 mr-2 me-2"
                            value={hint.hinttext}
                            placeholder={t('editor_hinttext')}
                            aria-label={t('editor_hinttext')}
                            onChange={(event) => replaceHint(index, {...hint, hinttext: event.target.value})}
                        />
                        <input
                            type="number"
                            className="form-control d-inline-block mr-2 me-2"
                            style={{width: '6rem'}}
                            step={0.1}
                            min={0}
                            value={hint.penalty}
                            aria-label={t('editor_penalty')}
                            onChange={(event) => replaceHint(index, {...hint, penalty: parseFloat(event.target.value) || 0})}
                        />
                        <button
                            type="button"
                            className="btn btn-link btn-sm text-danger p-0"
                            data-action="removehint"
                            onClick={() => onChange({...gap, hints: resequenced(gap.hints.filter((_, i) => i !== index))})}
                        >
                            {t('editor_removehint')}
                        </button>
                    </div>
                ))}
            </div>
            <button
                type="button"
                className="btn btn-link btn-sm p-0"
                data-action="addhint"
                onClick={() => onChange({
                    ...gap,
                    hints: [...gap.hints, {level: gap.hints.length + 1, hinttype: 'text', hinttext: '', penalty: 0}],
                })}
            >
                {t('editor_addhint')}
            </button>

            {/* Collapsed by default. These three exist in the schema and in the
                web service but had no control at all, so the only way to set
                them was an import or a database edit — and putting them beside
                the solution would have suggested every gap needs a decision
                about them. */}
            <details className="mod_elang-editor-advanced mt-2" data-region="gapadvanced">
                <summary>{t('editor_advanced')}</summary>

                <div className="row mt-2">
                    <div className="col-12 col-md-4">
                        <label className="d-block">
                            {t('editor_maxlength')}
                            <input
                                type="number"
                                className="form-control"
                                min={0}
                                value={gap.maxlength}
                                data-region="maxlength"
                                onChange={(event) => onChange({
                                    ...gap,
                                    maxlength: Math.max(0, parseInt(event.target.value, 10) || 0),
                                })}
                            />
                        </label>
                        <p className="text-muted small">{t('editor_maxlength_help')}</p>
                    </div>

                    <div className="col-12 col-md-8">
                        <label className="d-block">
                            {t('editor_linkurl')}
                            <input
                                type="url"
                                className="form-control"
                                value={gap.linkurl}
                                data-region="linkurl"
                                onChange={(event) => onChange({...gap, linkurl: event.target.value})}
                            />
                        </label>
                        <p className="text-muted small">{t('editor_linkurl_help')}</p>
                    </div>
                </div>

                {gap.answers.length > 0 && (
                    <div data-region="variantregex">
                        <p className="mb-1">{t('editor_variantmatching')}</p>
                        {gap.answers.map((answer, index) => (
                            <label className="d-block mb-1" key={index}>
                                <input
                                    type="checkbox"
                                    className="mr-1 me-1"
                                    checked={answer.isregex === 1}
                                    onChange={(event) => replaceAnswer(index, {
                                        ...answer,
                                        isregex: event.target.checked ? 1 : 0,
                                    })}
                                />
                                {t('editor_variantisregex').replace('{$a}', answer.answer || String(index + 1))}
                            </label>
                        ))}
                    </div>
                )}
            </details>

            <button type="button" className="btn btn-link text-danger p-0 d-block" onClick={onDelete}>
                {t('editor_deletegap')}
            </button>
        </div>
    );
}
