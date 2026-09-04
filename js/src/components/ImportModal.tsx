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
 * Subtitle import modal.
 *
 * A file and a pasted text are the same thing once read: WebVTT or SubRip
 * content. Both tabs therefore feed one string into one server-side parse, so
 * the two routes can never disagree about what a file means.
 *
 * The import is a two-step: parse and show what was found, then apply. The
 * summary is what makes "append" versus "replace" an informed choice — before
 * it, a teacher pressing import could only find out afterwards how many cues
 * they had just added to their work.
 *
 * @module     mod_elang/components/ImportModal
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useEffect, useRef, useState} from 'react';
import {ImportResult, Translator} from '../types';

interface Props {
    t: Translator;
    /** Whether the activity already has cues that a replace would discard. */
    hascues: boolean;
    onPreview: (subtitles: string, parsegaps: boolean) => Promise<ImportResult | null>;
    onApply: (result: ImportResult, replace: boolean) => void;
    onClose: () => void;
}

/** What the summary panel reports about a successful parse. */
interface Summary {
    filename: string;
    format: string;
    cuecount: number;
    gapcount: number;
    duration: string;
    warnings: string[];
}

/**
 * Name the subtitle format from the content itself.
 *
 * The file extension is a claim, not evidence, and pasted text has none at
 * all. WebVTT is required to start with its signature; anything else the
 * parser accepts is SubRip.
 *
 * @param subtitles The raw subtitle content.
 * @param t The string resolver.
 * @returns The human-readable format name.
 */
function detectFormat(subtitles: string, t: Translator): string {
    return subtitles.trimStart().startsWith('WEBVTT')
        ? t('editor_formatwebvtt')
        : t('editor_formatsubrip');
}

/**
 * Format a millisecond duration as hh:mm:ss.mmm.
 *
 * @param ms The duration in milliseconds.
 * @returns The formatted duration.
 */
function formatDuration(ms: number): string {
    const pad = (value: number, length: number): string => String(value).padStart(length, '0');
    const hours = Math.floor(ms / 3600000);
    const minutes = Math.floor((ms % 3600000) / 60000);
    const seconds = Math.floor((ms % 60000) / 1000);

    return pad(hours, 2) + ':' + pad(minutes, 2) + ':' + pad(seconds, 2) + '.' + pad(ms % 1000, 3);
}

/**
 * Render the import modal.
 *
 * @param props The component props.
 * @returns The modal element.
 */
export function ImportModal({t, hascues, onPreview, onApply, onClose}: Props): JSX.Element {
    const [tab, setTab] = useState<'file' | 'text'>('file');
    const [text, setText] = useState('');
    const [filename, setFilename] = useState('');
    const [parsegaps, setParsegaps] = useState(false);
    const [summary, setSummary] = useState<Summary | null>(null);
    const [parsed, setParsed] = useState<ImportResult | null>(null);
    const [error, setError] = useState('');
    const [busy, setBusy] = useState(false);
    const dialogref = useRef<HTMLDivElement>(null);
    const closeref = useRef<HTMLButtonElement>(null);

    // Focus moves into the dialog on open and Escape closes it, so the modal
    // is operable without a mouse and does not strand keyboard users behind
    // content they cannot reach.
    useEffect(() => {
        // Remembered before the focus moves, so it can be given back. A dialog
        // that returns the focus to the top of the document leaves a keyboard
        // user to tab their way back to where they were.
        const opener = document.activeElement as HTMLElement | null;

        closeref.current?.focus();

        /**
         * Every element inside the dialog that can hold the focus.
         *
         * Queried on each keypress rather than once: the dialog gains and loses
         * controls as it is used — the apply buttons only become enabled after
         * a check, and the two tabs swap their panes.
         *
         * @returns The focusable elements, in document order.
         */
        const focusable = (): HTMLElement[] => Array.from(
            dialogref.current?.querySelectorAll<HTMLElement>(
                'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]),'
                + ' textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
            ) ?? []
        ).filter((element) => element.offsetParent !== null);

        const onkeydown = (event: KeyboardEvent): void => {
            if (event.key === 'Escape') {
                onClose();
                return;
            }

            if (event.key !== 'Tab') {
                return;
            }

            // The focus is kept inside. Without this, tabbing past the last
            // control lands on the page behind the dialog — which is still
            // there, still clickable, and covered by the backdrop, so the
            // cursor simply disappears.
            const elements = focusable();
            if (elements.length === 0) {
                return;
            }

            const first = elements[0];
            const last = elements[elements.length - 1];
            const active = document.activeElement;

            if (!event.shiftKey && active === last) {
                event.preventDefault();
                first.focus();
            } else if (event.shiftKey && active === first) {
                event.preventDefault();
                last.focus();
            } else if (!dialogref.current?.contains(active)) {
                // The focus was outside to begin with — a click on the backdrop,
                // or a browser restoring it after a reflow.
                event.preventDefault();
                first.focus();
            }
        };
        document.addEventListener('keydown', onkeydown);

        return () => {
            document.removeEventListener('keydown', onkeydown);
            opener?.focus();
        };
    }, [onClose]);

    // Any change to the source invalidates the summary: showing a count that
    // belongs to the previous file would be worse than showing none.
    const resetPreview = (): void => {
        setSummary(null);
        setParsed(null);
        setError('');
    };

    const readFile = (file: File): void => {
        const reader = new FileReader();
        reader.onload = () => {
            setText(String(reader.result || ''));
            setFilename(file.name);
            resetPreview();
        };
        reader.onerror = () => setError(t('editor_importreaderror'));
        reader.readAsText(file);
    };

    const preview = async(): Promise<void> => {
        if (text.trim() === '') {
            return;
        }

        setBusy(true);
        setError('');
        const result = await onPreview(text, parsegaps);
        setBusy(false);

        if (result === null) {
            setSummary(null);
            setParsed(null);
            setError(t('editor_importparseerror'));
            return;
        }

        const gapcount = result.cues.reduce((total, cue) => total + (cue.gaps || []).length, 0);
        const endtime = result.cues.reduce((latest, cue) => Math.max(latest, cue.endtime), 0);

        setParsed(result);
        setSummary({
            filename: filename !== '' ? filename : t('editor_importpastedtext'),
            format: detectFormat(text, t),
            cuecount: result.cuecount,
            gapcount,
            duration: formatDuration(endtime),
            warnings: result.warnings || [],
        });
    };

    const apply = (replace: boolean): void => {
        if (parsed === null) {
            return;
        }
        onApply(parsed, replace);
    };

    const switchTab = (next: 'file' | 'text'): void => {
        setTab(next);
        setText('');
        setFilename('');
        resetPreview();
    };

    return (
        <div
            className="mod_elang-import-backdrop"
            data-region="importmodal"
            onClick={(event) => {
                if (event.target === event.currentTarget) {
                    onClose();
                }
            }}
        >
            <div
                className="mod_elang-import-dialog card"
                role="dialog"
                aria-modal="true"
                aria-label={t('editor_import')}
                ref={dialogref}
            >
                <div className="card-header d-flex justify-content-between align-items-center">
                    <h2 className="h5 mb-0">{t('editor_import')}</h2>
                    <button
                        type="button"
                        className="close btn-close"
                        aria-label={t('editor_importcancel')}
                        data-action="importclose"
                        ref={closeref}
                        onClick={onClose}
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div className="card-body">
                    <ul className="nav nav-tabs mb-3" role="tablist">
                        <li className="nav-item" role="presentation">
                            <button
                                type="button"
                                role="tab"
                                aria-selected={tab === 'file'}
                                className={'nav-link' + (tab === 'file' ? ' active' : '')}
                                data-action="importtabfile"
                                onClick={() => switchTab('file')}
                            >
                                {t('editor_importfromfile')}
                            </button>
                        </li>
                        <li className="nav-item" role="presentation">
                            <button
                                type="button"
                                role="tab"
                                aria-selected={tab === 'text'}
                                className={'nav-link' + (tab === 'text' ? ' active' : '')}
                                data-action="importtabtext"
                                onClick={() => switchTab('text')}
                            >
                                {t('editor_importfromtext')}
                            </button>
                        </li>
                    </ul>

                    {tab === 'file' && (
                        <div data-region="importfile">
                            <p className="text-muted">{t('editor_importfilehint')}</p>
                            <input
                                type="file"
                                className="form-control-file"
                                accept=".vtt,.srt,text/vtt,text/plain"
                                data-region="importfileinput"
                                aria-label={t('editor_importfromfile')}
                                onChange={(event) => {
                                    const file = event.target.files?.[0];
                                    if (file) {
                                        readFile(file);
                                    }
                                }}
                            />
                        </div>
                    )}

                    {tab === 'text' && (
                        <div data-region="importtextpane">
                            <p className="text-muted">{t('editor_importhint')}</p>
                            <textarea
                                className="form-control"
                                rows={8}
                                data-region="importtext"
                                aria-label={t('editor_importfromtext')}
                                value={text}
                                onChange={(event) => {
                                    setText(event.target.value);
                                    setFilename('');
                                    resetPreview();
                                }}
                            />
                        </div>
                    )}

                    <label className="d-block mt-3">
                        <input
                            type="checkbox"
                            className="mr-1"
                            data-region="parsegaps"
                            checked={parsegaps}
                            onChange={(event) => {
                                setParsegaps(event.target.checked);
                                resetPreview();
                            }}
                        />
                        {t('editor_parsegaps')}
                    </label>

                    <button
                        type="button"
                        className="btn btn-secondary mt-2"
                        data-action="importpreview"
                        disabled={busy || text.trim() === ''}
                        onClick={preview}
                    >
                        {busy ? t('editor_importchecking') : t('editor_importcheck')}
                    </button>

                    {error !== '' && (
                        <div className="alert alert-danger mt-3" role="alert" data-region="importerror">
                            {error}
                        </div>
                    )}

                    {summary !== null && (
                        <div className="card mt-3" data-region="importsummary">
                            <div className="card-body">
                                <div className="d-flex justify-content-between align-items-start">
                                    <h3 className="h6">{t('editor_importsummary')}</h3>
                                    <span className="badge badge-success bg-success">{t('editor_importready')}</span>
                                </div>
                                <dl className="row mb-0">
                                    <dt className="col-6">{t('editor_importsource')}</dt>
                                    <dd className="col-6" data-region="summaryfilename">{summary.filename}</dd>
                                    <dt className="col-6">{t('editor_importformat')}</dt>
                                    <dd className="col-6" data-region="summaryformat">{summary.format}</dd>
                                    <dt className="col-6">{t('editor_importcuecount')}</dt>
                                    <dd className="col-6" data-region="summarycues">{summary.cuecount}</dd>
                                    <dt className="col-6">{t('editor_importgapcount')}</dt>
                                    <dd className="col-6" data-region="summarygaps">{summary.gapcount}</dd>
                                    <dt className="col-6">{t('editor_importduration')}</dt>
                                    <dd className="col-6" data-region="summaryduration">{summary.duration}</dd>
                                </dl>

                                {summary.warnings.length > 0 && (
                                    <ul className="mt-2 mb-0" data-region="importwarnings">
                                        {summary.warnings.map((warning, index) => (
                                            <li key={index} className="text-warning">{warning}</li>
                                        ))}
                                    </ul>
                                )}
                            </div>
                        </div>
                    )}
                </div>

                <div className="card-footer d-flex justify-content-end">
                    <button
                        type="button"
                        className="btn btn-secondary mr-2"
                        data-action="importcancel"
                        onClick={onClose}
                    >
                        {t('editor_importcancel')}
                    </button>

                    {/* Replace is only offered when there is something to lose;
                        on an empty version the two buttons would do the same
                        thing and the choice would be noise. */}
                    {hascues && (
                        <button
                            type="button"
                            className="btn btn-outline-danger mr-2"
                            data-action="importreplace"
                            disabled={parsed === null}
                            onClick={() => apply(true)}
                        >
                            {t('editor_importreplace')}
                        </button>
                    )}

                    <button
                        type="button"
                        className="btn btn-primary"
                        data-action="importapply"
                        disabled={parsed === null}
                        onClick={() => apply(false)}
                    >
                        {hascues ? t('editor_importappend') : t('editor_importapply')}
                    </button>
                </div>
            </div>
        </div>
    );
}
