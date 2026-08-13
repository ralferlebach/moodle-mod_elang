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
 * Subtitle import panel. Pasted WebVTT/SubRip text is previewed through the
 * server-side parser; with the gap-syntax option enabled, mod_elang 1.x
 * inline markers ([word] with help, {word} without) become real gaps instead
 * of literal text.
 *
 * @module     mod_elang/components/ImportPanel
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useState} from 'react';
import {Translator} from '../types';

interface Props {
    t: Translator;
    onImport: (subtitles: string, parsegaps: boolean) => Promise<boolean>;
}

/**
 * Render the import panel.
 *
 * @param props The component props.
 * @returns The import panel element.
 */
export function ImportPanel({t, onImport}: Props): JSX.Element {
    const [text, setText] = useState('');
    const [parsegaps, setParsegaps] = useState(false);

    const submit = async(): Promise<void> => {
        if (text.trim() === '') {
            return;
        }
        const imported = await onImport(text, parsegaps);
        if (imported) {
            setText('');
        }
    };

    return (
        <details className="mod_elang-editor-import mb-3">
            <summary>{t('editor:import')}</summary>
            <p className="text-muted">{t('editor:importhint')}</p>
            <textarea
                className="form-control"
                rows={4}
                data-region="importtext"
                aria-label={t('editor:import')}
                value={text}
                onChange={(event) => setText(event.target.value)}
            />
            <label className="d-block mt-2">
                <input
                    type="checkbox"
                    className="mr-1"
                    data-region="parsegaps"
                    checked={parsegaps}
                    onChange={(event) => setParsegaps(event.target.checked)}
                />
                {t('editor:parsegaps')}
            </label>
            <button type="button" className="btn btn-secondary mt-2" data-action="import" onClick={submit}>
                {t('editor:import')}
            </button>
        </details>
    );
}
