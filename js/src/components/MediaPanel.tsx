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
 * Media panel: shows the version's current medium and sets a url, provider or
 * no medium. A file medium is shown in the current-medium line but not
 * offered in the type selector; files are uploaded through the separate
 * moodleform upload page linked below the panel.
 *
 * @module     mod_elang/components/MediaPanel
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useState} from 'react';
import {Media, ProviderOption, Translator} from '../types';

interface Props {
    media: Media;
    providers: ProviderOption[];
    mediauploadurl: string;
    t: Translator;
    onSave: (kind: string, url: string, provider: string, providerref: string) => void;
}

/**
 * Render the current-medium line.
 *
 * @param media The media descriptor.
 * @param t The string resolver.
 * @returns The line's content element.
 */
function currentMedia(media: Media, t: Translator): JSX.Element {
    if (media.mediakind === 'file' && media.mediafileurl) {
        return (
            <a href={media.mediafileurl} target="_blank" rel="noopener noreferrer">
                {t('editor:mediafile')} ({media.mediafilename})
            </a>
        );
    }
    if (media.mediakind === 'url' && media.mediaurl) {
        return <a href={media.mediaurl} target="_blank" rel="noopener noreferrer">{media.mediaurl}</a>;
    }
    if (media.mediakind === 'provider') {
        return <span>{media.mediaprovider} ({media.mediaproviderref})</span>;
    }
    return <span>{t('editor:nomedia')}</span>;
}

/**
 * Render the media panel.
 *
 * @param props The component props.
 * @returns The media panel element.
 */
export function MediaPanel({media, providers, mediauploadurl, t, onSave}: Props): JSX.Element {
    const [kind, setKind] = useState((media.mediakind === 'url' || media.mediakind === 'provider') ? media.mediakind : '');
    const [url, setUrl] = useState(media.mediaurl || '');
    const [provider, setProvider] = useState(media.mediaprovider || '');
    const [providerref, setProviderref] = useState(media.mediaproviderref || '');

    return (
        <details className="mod_elang-editor-media mb-3">
            <summary>{t('editor:media')}</summary>
            <p className="text-muted" data-region="currentmedia">
                {t('editor:currentmedia')} {currentMedia(media, t)}
            </p>
            <div className="form-group">
                <label className="d-block">
                    {t('editor:mediakind')}
                    <select
                        className="form-control"
                        data-region="mediakind"
                        value={kind}
                        onChange={(event) => setKind(event.target.value)}
                    >
                        <option value="">{t('editor:medianone')}</option>
                        <option value="url">{t('editor:mediaurl')}</option>
                        <option value="provider">{t('editor:mediaprovider')}</option>
                    </select>
                </label>
            </div>
            {kind === 'url' && (
                <div className="form-group">
                    <label className="d-block">
                        {t('editor:mediaurl')}
                        <input
                            type="url"
                            className="form-control"
                            data-region="mediaurlinput"
                            value={url}
                            onChange={(event) => setUrl(event.target.value)}
                        />
                    </label>
                </div>
            )}
            {kind === 'provider' && (
                <div className="form-group">
                    <label className="d-block">
                        {t('editor:mediaprovider')}
                        <select
                            className="form-control"
                            data-region="mediaproviderinput"
                            value={provider}
                            onChange={(event) => setProvider(event.target.value)}
                        >
                            <option value="">{t('editor:medianone')}</option>
                            {providers.map((option) => (
                                <option value={option.key} key={option.key}>{option.name}</option>
                            ))}
                        </select>
                    </label>
                    <label className="d-block">
                        {t('editor:mediaproviderref')}
                        <input
                            type="text"
                            className="form-control"
                            data-region="mediaproviderrefinput"
                            value={providerref}
                            onChange={(event) => setProviderref(event.target.value)}
                        />
                    </label>
                    <p className="text-muted small mb-0">{t('editor:mediaproviderrefhint')}</p>
                </div>
            )}
            <button
                type="button"
                className="btn btn-secondary"
                data-action="savemedia"
                onClick={() => onSave(kind, url, provider, providerref)}
            >
                {t('editor:savemedia')}
            </button>
            {mediauploadurl !== '' && (
                <p className="mt-2">
                    <a href={mediauploadurl}>{t('editor:uploadmedia')}</a>
                </p>
            )}
        </details>
    );
}
