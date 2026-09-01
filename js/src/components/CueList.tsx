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
 * The cue list: every cue at a glance, one of them open for editing.
 *
 * Before this, every cue rendered its whole form at once — timing, transcript,
 * preview, gaps, solutions, algorithms, variants and hints, stacked. Forty
 * cues meant a wall of forms metres long, and the connection between the
 * medium, the timeline and the cue being worked on was lost in the scrolling.
 *
 * The list owns no cue data. Selection is held by EditorApp, so the list, the
 * timeline and the inspector cannot disagree about which cue is open.
 *
 * @module     mod_elang/components/CueList
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useState} from 'react';
import {Cue, Translator} from '../types';
import {formatTime} from '../studio/time';

interface Props {
    cues: Cue[];
    selectedkey: string;
    t: Translator;
    onSelect: (cue: Cue) => void;
    onAdd: () => void;
    onInsertAt: (index: number) => void;
    onDelete: (index: number) => void;
}

/**
 * Describe what is wrong with a cue, or an empty string when nothing is.
 *
 * The same checks publishing applies, surfaced where the author is working
 * rather than only when they try to publish — a problem found late in a long
 * transcript costs a hunt for the cue that caused it.
 *
 * @param cue The cue to check.
 * @param t The string resolver.
 * @returns The warning, or an empty string.
 */
export function cueWarning(cue: Cue, t: Translator): string {
    if (cue.endtime <= cue.starttime) {
        return t('editor:warntiming');
    }
    if (cue.transcript.trim() === '') {
        return t('editor:warnnotranscript');
    }
    if (cue.gaps.some((gap) => gap.solution.trim() === '')) {
        return t('editor:warnemptysolution');
    }

    return '';
}

/**
 * Render the cue list.
 *
 * @param props The component props.
 * @returns The cue list element.
 */
export function CueList({cues, selectedkey, t, onSelect, onAdd, onInsertAt, onDelete}: Props): JSX.Element {
    const [search, setSearch] = useState('');
    const [onlywarnings, setOnlywarnings] = useState(false);

    const needle = search.trim().toLowerCase();
    const visible = cues
        .map((cue, index) => ({cue, index}))
        .filter(({cue}) => needle === '' || cue.transcript.toLowerCase().includes(needle))
        .filter(({cue}) => !onlywarnings || cueWarning(cue, t) !== '');

    return (
        <div className="mod_elang-cuelist card" data-region="cuelist">
            <div className="card-header d-flex flex-wrap align-items-center">
                <input
                    type="search"
                    className="form-control form-control-sm mr-2 me-2 mod_elang-cuelist-search"
                    data-region="cuesearch"
                    placeholder={t('editor:searchcues')}
                    aria-label={t('editor:searchcues')}
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                />

                <label className="mb-0 mr-auto me-auto small">
                    <input
                        type="checkbox"
                        className="mr-1 me-1"
                        data-region="onlywarnings"
                        checked={onlywarnings}
                        onChange={(event) => setOnlywarnings(event.target.checked)}
                    />
                    {t('editor:onlywarnings')}
                </label>

                <span className="badge badge-secondary bg-secondary mr-2 me-2" data-region="cuecount">
                    {t('editor:cuecount').replace('{$a}', String(cues.length))}
                </span>

                {/* Beside the cues it acts on, not in the publish toolbar: adding
                    a cue is routine authoring, and sitting next to "Publish" it
                    read as an equally consequential step. */}
                <button type="button" className="btn btn-sm btn-secondary" data-action="addcue" onClick={onAdd}>
                    {t('editor:addcue')}
                </button>
            </div>

            <ul className="list-group list-group-flush mod_elang-cuelist-items">
                {visible.map(({cue, index}) => {
                    const warning = cueWarning(cue, t);
                    const selected = cue.cuekey === selectedkey;

                    return (
                        <li
                            key={cue.cuekey}
                            className={'list-group-item mod_elang-cuelist-item' + (selected ? ' selected' : '')}
                            data-cuekey={cue.cuekey}
                        >
                            <div className="d-flex align-items-start">
                                {/* A button, not a clickable div: the list has to be
                                    reachable and operable from the keyboard, and
                                    aria-current says which cue is open. */}
                                <button
                                    type="button"
                                    className="btn btn-link text-left flex-grow-1 p-0 mod_elang-cuelist-select"
                                    data-action="selectcue"
                                    aria-current={selected ? 'true' : undefined}
                                    onClick={() => onSelect(cue)}
                                >
                                    <span className="mod_elang-cuelist-times small">
                                        {formatTime(cue.starttime)} – {formatTime(cue.endtime)}
                                    </span>
                                    <span className="d-block mod_elang-cuelist-preview">
                                        {cue.transcript.trim() === ''
                                            ? t('editor:emptytranscript')
                                            : cue.transcript}
                                    </span>
                                    <span className="badge badge-light bg-light text-dark mr-1 me-1">
                                        {t('editor:gapcount').replace('{$a}', String(cue.gaps.length))}
                                    </span>
                                    {warning !== '' && (
                                        // Labelled, not merely coloured: a warning
                                        // that is only a red edge is not a warning
                                        // for everyone.
                                        <span className="badge badge-warning bg-warning text-dark" data-region="cuewarning">
                                            {warning}
                                        </span>
                                    )}
                                </button>

                                <div className="dropdown ml-2 ms-2">
                                    <button
                                        type="button"
                                        className="btn btn-sm btn-link dropdown-toggle"
                                        id={'mod_elang-cueactions-' + cue.cuekey}
                                        data-toggle="dropdown"
                                        data-bs-toggle="dropdown"
                                        aria-haspopup="true"
                                        aria-expanded="false"
                                        aria-label={t('editor:cueactions')}
                                    >
                                        <span aria-hidden="true">⋮</span>
                                    </button>
                                    <div
                                        className="dropdown-menu dropdown-menu-right dropdown-menu-end"
                                        aria-labelledby={'mod_elang-cueactions-' + cue.cuekey}
                                    >
                                        <button
                                            type="button"
                                            className="dropdown-item"
                                            data-action="insertbefore"
                                            onClick={() => onInsertAt(index)}
                                        >
                                            {t('editor:insertbefore')}
                                        </button>
                                        <button
                                            type="button"
                                            className="dropdown-item"
                                            data-action="insertafter"
                                            onClick={() => onInsertAt(index + 1)}
                                        >
                                            {t('editor:insertafter')}
                                        </button>
                                        <div className="dropdown-divider"></div>
                                        <button
                                            type="button"
                                            className="dropdown-item text-danger"
                                            data-action="deletecue"
                                            onClick={() => onDelete(index)}
                                        >
                                            {t('editor:deletecue')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    );
                })}

                {visible.length === 0 && (
                    <li className="list-group-item text-muted" data-region="nocuesmatch">
                        {cues.length === 0 ? t('editor:nocues') : t('editor:nocuesmatch')}
                    </li>
                )}
            </ul>
        </div>
    );
}
