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
 * The authoring editor application: loads the draft version through the
 * external API and drives cue/gap/hint editing, subtitle import (optionally
 * recognising V1 gap markers), the media panel, the timeline strip and
 * saving/publishing with the revision as an optimistic-concurrency token.
 *
 * @module     mod_elang/components/EditorApp
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useEffect, useRef, useState} from 'react';
import {ApiClient} from '../api/service';
import {Cue, FORMAT_PLAIN, Media, ProviderOption, Translator} from '../types';
import {newKey} from '../keys';
import {AutosaveController, AutosaveState, createAutosave} from '../studio/autosave';
import {CueRow} from './CueRow';
import {ImportPanel} from './ImportPanel';
import {MediaPanel} from './MediaPanel';
import {Onboarding} from './Onboarding';
import {Timeline} from './Timeline';

interface Props {
    api: ApiClient;
    t: Translator;
    mediauploadurl: string;
}

/**
 * Extract a display message from a rejected service call.
 *
 * @param error The rejection reason.
 * @param fallback The message to use when the error carries none.
 * @returns The message to show.
 */
function errorMessage(error: unknown, fallback: string): string {
    if (error && typeof error === 'object' && 'message' in error && typeof error.message === 'string' && error.message) {
        return error.message;
    }
    return fallback;
}

/**
 * Render the editor application.
 *
 * @param props The component props.
 * @returns The editor element.
 */
export function EditorApp({api, t, mediauploadurl}: Props): JSX.Element {
    const [status, setStatus] = useState(t('editor:loading'));
    const [loaded, setLoaded] = useState(false);
    const [cues, setCues] = useState<Cue[]>([]);
    const [media, setMedia] = useState<Media | null>(null);
    const [providers, setProviders] = useState<ProviderOption[]>([]);
    const [focusedcuekey, setFocusedcuekey] = useState('');
    const [currentms, setCurrentms] = useState(0);
    const [durationms, setDurationms] = useState(0);
    const [savestate, setSavestate] = useState<AutosaveState>('idle');

    const revisionRef = useRef(0);
    const mediaRef = useRef<HTMLVideoElement>(null);
    const cuesRef = useRef<Cue[]>([]);
    cuesRef.current = cues;
    const autosaveRef = useRef<AutosaveController | null>(null);
    const justLoadedRef = useRef(false);

    useEffect(() => {
        let active = true;
        api.getVersionContent().then((content) => {
            if (!active) {
                return;
            }
            revisionRef.current = content.revision;
            justLoadedRef.current = true;
            setCues(content.cues);
            setMedia(content);
            setProviders(content.mediaproviders || []);
            setLoaded(true);
            setStatus('');
            return;
        }).catch((error: unknown) => {
            if (active) {
                setStatus(t('editor:loaderror') + ' [' + errorMessage(error, '') + ']');
            }
        });
        return () => {
            active = false;
        };
        // The api client is fixed for the lifetime of the mount.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Persist the latest cue list; reads cuesRef so the autosave controller
    // always saves current content rather than the cues captured when it was
    // created.
    const save = async(): Promise<void> => {
        revisionRef.current = await api.saveDraft(revisionRef.current, cuesRef.current);
    };

    // One debounced autosave controller for the mount's lifetime.
    useEffect(() => {
        const controller = createAutosave({
            save,
            onState: setSavestate,
        });
        autosaveRef.current = controller;
        return () => controller.cancel();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Schedule an autosave on every content change, skipping the initial load.
    useEffect(() => {
        if (!loaded) {
            return;
        }
        if (justLoadedRef.current) {
            justLoadedRef.current = false;
            return;
        }
        autosaveRef.current?.markDirty();
    }, [cues, loaded]);

    const replaceCue = (index: number, cue: Cue): void => {
        setCues((current) => {
            const next = current.slice();
            next[index] = cue;
            return next;
        });
    };

    const capturems = (): number | null => {
        const video = mediaRef.current;
        return video ? Math.round(video.currentTime * 1000) : null;
    };

    const editCueTiming = (cuekey: string, starttime: number, endtime: number): void => {
        setCues((current) => current.map(
            (cue) => cue.cuekey === cuekey ? {...cue, starttime, endtime} : cue
        ));
    };

    const handleSave = async(): Promise<void> => {
        try {
            await (autosaveRef.current ? autosaveRef.current.flush() : save());
            setStatus(t('editor:saved'));
        } catch (error) {
            setStatus(errorMessage(error, t('editor:saveerror')));
        }
    };

    const handlePublish = async(): Promise<void> => {
        try {
            await (autosaveRef.current ? autosaveRef.current.flush() : save());
            await api.publish();
            setStatus(t('editor:published'));
            window.setTimeout(() => window.location.reload(), 1200);
        } catch (error) {
            setStatus(errorMessage(error, t('editor:saveerror')));
        }
    };

    const handleAddCue = (): void => {
        setCues((current) => [...current, {
            cuekey: newKey('c'),
            sortorder: current.length + 1,
            starttime: 0,
            endtime: 0,
            transcript: '',
            transcriptformat: FORMAT_PLAIN,
            gaps: [],
        }]);
    };

    const handleImport = async(subtitles: string, parsegaps: boolean): Promise<boolean> => {
        try {
            const result = await api.previewImport(subtitles, parsegaps);
            setCues((current) => [...current, ...result.cues.map((imported, index) => ({
                cuekey: newKey('c'),
                sortorder: current.length + index + 1,
                starttime: imported.starttime,
                endtime: imported.endtime,
                transcript: imported.transcript,
                transcriptformat: imported.transcriptformat,
                // Gaps recognised from V1 inline markers become real gaps; the
                // help-allowed bracket form seeds one solution hint, mirroring
                // the V1 migration semantics.
                gaps: (imported.gaps || []).map((gap, gapindex) => ({
                    gapkey: newKey('g'),
                    sortorder: gapindex + 1,
                    charstart: gap.charstart,
                    charlength: gap.charlength,
                    solution: gap.solution,
                    gradingalgorithm: 'exact',
                    maxlength: 0,
                    linkurl: '',
                    answers: [],
                    hints: gap.hintsallowed
                        ? [{level: 1, hinttype: 'solution', hinttext: gap.solution, penalty: 0}]
                        : [],
                })),
            }))]);
            setStatus(t('editor:importedcues').replace('{$a}', String(result.cuecount)));
            return true;
        } catch (error) {
            setStatus(errorMessage(error, t('editor:saveerror')));
            return false;
        }
    };

    const handleSaveMedia = async(kind: string, url: string, provider: string, providerref: string): Promise<void> => {
        try {
            const stored = await api.setMedia(kind, url, provider, providerref);
            setMedia((current) => current ? {...current, ...stored} : stored);
            setStatus(t('editor:mediasaved'));
        } catch (error) {
            setStatus(errorMessage(error, t('editor:saveerror')));
        }
    };

    const seekToCue = (cue: Cue): void => {
        setFocusedcuekey(cue.cuekey);
        window.setTimeout(() => setFocusedcuekey(''), 1500);
        const row = document.querySelector('[data-cuekey="' + cue.cuekey + '"]');
        if (row) {
            row.scrollIntoView({behavior: 'smooth', block: 'center'});
        }
        const video = mediaRef.current;
        if (video) {
            video.currentTime = cue.starttime / 1000;
        }
    };

    const mediasrc = media ? (media.mediafileurl || media.mediaurl || '') : '';
    const showpreview = media !== null && (media.mediakind === 'file' || media.mediakind === 'url') && mediasrc !== '';

    const savestatekeys: Record<AutosaveState, string> = {
        idle: '',
        dirty: 'editor:unsaved',
        saving: 'editor:saving',
        saved: 'editor:autosaved',
        error: 'editor:autosaveerror',
    };
    const savestatekey = savestatekeys[savestate];

    return (
        <div>
            <p className="mod_elang-status" data-region="status" role="status" aria-live="polite">{status}</p>

            <div className="mod_elang-editor-toolbar mb-3">
                <button type="button" className="btn btn-primary" data-action="save" onClick={handleSave}>
                    {t('editor:save')}
                </button>
                <button type="button" className="btn btn-success" data-action="publish" onClick={handlePublish}>
                    {t('editor:publish')}
                </button>
                <button type="button" className="btn btn-secondary" data-action="addcue" onClick={handleAddCue}>
                    {t('editor:addcue')}
                </button>
                <span
                    className={'mod_elang-editor-savestate ' + savestate}
                    data-region="savestate"
                    role="status"
                    aria-live="polite"
                >
                    {savestatekey !== '' ? t(savestatekey) : ''}
                </span>
            </div>

            <div className="mod_elang-editor-timeline-wrap mb-3" data-region="timelinewrap">
                {showpreview && (
                    <video
                        ref={mediaRef}
                        className="mod_elang-editor-media-preview"
                        data-region="mediapreview"
                        controls
                        preload="metadata"
                        src={mediasrc}
                        onTimeUpdate={() => setCurrentms(Math.round((mediaRef.current?.currentTime || 0) * 1000))}
                        onLoadedMetadata={() => setDurationms(Math.round((mediaRef.current?.duration || 0) * 1000))}
                    />
                )}
                <Timeline
                    cues={cues}
                    durationms={durationms}
                    currentms={currentms}
                    mediasrc={showpreview ? mediasrc : ''}
                    t={t}
                    onSeek={seekToCue}
                    onEdit={editCueTiming}
                />
            </div>

            {loaded && cues.length === 0 && (
                <Onboarding t={t} hasmedia={media !== null && media.mediakind !== '' && media.mediakind !== 'none'} />
            )}

            {loaded && (
                <ImportPanel t={t} onImport={handleImport} />
            )}

            {loaded && media !== null && (
                <MediaPanel media={media} providers={providers} mediauploadurl={mediauploadurl} t={t} onSave={handleSaveMedia} />
            )}

            <div className="mod_elang-editor-cues" data-region="cues">
                {cues.map((cue, index) => (
                    <CueRow
                        key={cue.cuekey}
                        cue={cue}
                        t={t}
                        focused={cue.cuekey === focusedcuekey}
                        capturems={capturems}
                        onChange={(updated) => replaceCue(index, updated)}
                        onDelete={() => setCues((current) => current.filter((_, i) => i !== index))}
                        onStatus={setStatus}
                        onGenerateGaps={(transcript, rule) => api.generateRuleGaps(transcript, rule)}
                    />
                ))}
            </div>
        </div>
    );
}
