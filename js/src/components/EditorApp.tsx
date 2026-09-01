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
import {Cue, FORMAT_PLAIN, ImportResult, Media, Translator} from '../types';
import {newKey} from '../keys';
import {AutosaveController, AutosaveState, createAutosave} from '../studio/autosave';
import {videoTrackUndecodable} from '../studio/mediacheck';
import {CueRow} from './CueRow';
import {CueList} from './CueList';
import {ImportModal} from './ImportModal';
import {Onboarding} from './Onboarding';
import {Timeline} from './Timeline';

interface Props {
    api: ApiClient;
    t: Translator;
    /**
     * Where the media page lives.
     *
     * Kept as a prop although the editor no longer configures media: the
     * mount point in amd/src/editor.js supplies it, and the "no medium yet"
     * guard in edit.php sends authors there. Removing it from the contract
     * would be a change to that page, not to this component.
     */
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
export function EditorApp({api, t}: Props): JSX.Element {
    const [status, setStatus] = useState(t('editor:loading'));
    const [loaded, setLoaded] = useState(false);
    const [cues, setCues] = useState<Cue[]>([]);
    const [media, setMedia] = useState<Media | null>(null);
    const [focusedcuekey, setFocusedcuekey] = useState('');
    const [currentms, setCurrentms] = useState(0);
    const [durationms, setDurationms] = useState(0);
    const [savestate, setSavestate] = useState<AutosaveState>('idle');
    const [importopen, setImportopen] = useState(false);
    const [selectedcuekey, setSelectedcuekey] = useState('');

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
            // Open the first cue straight away: an empty inspector next to a
            // full list makes the author's first act a click that tells them
            // nothing they did not already know.
            if (content.cues.length > 0) {
                setSelectedcuekey(content.cues[0].cuekey);
            }
            setMedia(content);
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

    const insertCueAt = (index: number): void => {
        const fresh: Cue = {
            cuekey: newKey('c'),
            sortorder: index + 1,
            starttime: 0,
            endtime: 0,
            transcript: '',
            transcriptformat: FORMAT_PLAIN,
            gaps: [],
        };
        setCues((current) => {
            const next = current.slice();
            next.splice(index, 0, fresh);
            return next.map((cue, i) => ({...cue, sortorder: i + 1}));
        });
        setSelectedcuekey(fresh.cuekey);
    };

    const deleteCueAt = (index: number): void => {
        setCues((current) => current.filter((_, i) => i !== index));
    };

    // Appending at the end is the same operation as inserting at the end, so
    // there is one implementation rather than two that could drift.
    const handleAddCue = (): void => insertCueAt(cues.length);

    const handlePreviewImport = async(subtitles: string, parsegaps: boolean): Promise<ImportResult | null> => {
        try {
            return await api.previewImport(subtitles, parsegaps);
        } catch (error) {
            setStatus(errorMessage(error, t('editor:saveerror')));
            return null;
        }
    };

    const handleApplyImport = (result: ImportResult, replace: boolean): void => {
        let firstimportedkey = '';

        setCues((current) => {
            const base = replace ? [] : current;
            return [...base, ...result.cues.map((imported, index) => {
                const cuekey = newKey('c');
                if (index === 0) {
                    firstimportedkey = cuekey;
                }
                return {
                cuekey,
                sortorder: base.length + index + 1,
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
                };
            })];
        });

        setStatus(t(replace ? 'editor:importreplacedcues' : 'editor:importedcues')
            .replace('{$a}', String(result.cuecount)));
        setImportopen(false);
        // Open the first cue that just arrived: an import that changed nothing
        // visible would leave the author wondering whether it worked.
        setSelectedcuekey(firstimportedkey);
    };


    // One entry point for "work on this cue", whether it was clicked in the
    // list or on the timeline: list highlight, open inspector and media
    // position move together, which is the whole point of the workspace.
    const seekToCue = (cue: Cue): void => {
        setSelectedcuekey(cue.cuekey);
        setFocusedcuekey(cue.cuekey);
        window.setTimeout(() => setFocusedcuekey(''), 1500);
        const row = document.querySelector('[data-cuekey="' + cue.cuekey + '"]');
        if (row) {
            row.scrollIntoView({behavior: 'smooth', block: 'nearest'});
        }
        const video = mediaRef.current;
        if (video) {
            video.currentTime = cue.starttime / 1000;
        }
    };


    const [novideotrack, setNovideotrack] = useState(false);
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

    // Derived, never stored: keeping a copy of the selected cue in state would
    // be a second source of truth that could drift from the list EditorApp owns.
    const selectedindex = cues.findIndex((cue) => cue.cuekey === selectedcuekey);
    const selectedcue = selectedindex >= 0 ? {cue: cues[selectedindex], index: selectedindex} : null;

    return (
        <div>
            <p className="mod_elang-status" data-region="status" role="status" aria-live="polite">{status}</p>

            <div className="mod_elang-editor-toolbar mb-3 d-flex flex-wrap align-items-center">
                {/* Autosave is the way work is kept, so the save state leads and
                    the manual save is a link rather than the primary button it
                    used to be. Presenting "Save" as the main action taught
                    authors to distrust the autosave that was already running. */}
                <span
                    className={'mod_elang-editor-savestate mr-3 me-3 ' + savestate}
                    data-region="savestate"
                    role="status"
                    aria-live="polite"
                >
                    {savestatekey !== '' ? t(savestatekey) : ''}
                </span>
                <button type="button" className="btn btn-link btn-sm" data-action="save" onClick={handleSave}>
                    {t('editor:save')}
                </button>

                <span className="mr-auto ms-auto"></span>

                <button
                    type="button"
                    className="btn btn-outline-secondary mr-2 me-2"
                    data-action="openimport"
                    onClick={() => setImportopen(true)}
                >
                    {t('editor:import')}
                </button>
                <button type="button" className="btn btn-success" data-action="publish" onClick={handlePublish}>
                    {t('editor:publish')}
                </button>
            </div>

            <div className="mod_elang-editor-timeline-wrap mb-3" data-region="timelinewrap">
                {showpreview && novideotrack && (
                    <div className="alert alert-warning" role="alert" data-region="novideotrack">
                        {t('editor:novideotrack')}
                    </div>
                )}
                {showpreview && (
                    <video
                        ref={mediaRef}
                        className="mod_elang-editor-media-preview"
                        data-region="mediapreview"
                        controls
                        preload="metadata"
                        src={mediasrc}
                        onTimeUpdate={() => setCurrentms(Math.round((mediaRef.current?.currentTime || 0) * 1000))}
                        onLoadedMetadata={() => {
                            const el = mediaRef.current;
                            setDurationms(Math.round((el?.duration || 0) * 1000));
                            setNovideotrack(el !== null
                                && videoTrackUndecodable(mediasrc, el.videoWidth, el.readyState));
                        }}
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

            {loaded && importopen && (
                <ImportModal
                    t={t}
                    hascues={cues.length > 0}
                    onPreview={handlePreviewImport}
                    onApply={handleApplyImport}
                    onClose={() => setImportopen(false)}
                />
            )}

            <div className="row mod_elang-editor-workspace" data-region="cues">
                <div className="col-12 col-lg-5 mb-3">
                    <CueList
                        cues={cues}
                        selectedkey={selectedcuekey}
                        t={t}
                        onSelect={seekToCue}
                        onAdd={handleAddCue}
                        onInsertAt={insertCueAt}
                        onDelete={deleteCueAt}
                    />
                </div>

                <div className="col-12 col-lg-7" data-region="cueinspector">
                    {selectedcue === null && (
                        <p className="text-muted" data-region="nocueselected">{t('editor:nocueselected')}</p>
                    )}
                    {selectedcue !== null && (
                        <CueRow
                            key={selectedcue.cue.cuekey}
                            cue={selectedcue.cue}
                            t={t}
                            focused={selectedcue.cue.cuekey === focusedcuekey}
                            capturems={capturems}
                            onChange={(updated) => replaceCue(selectedcue.index, updated)}
                            onDelete={() => deleteCueAt(selectedcue.index)}
                            onStatus={setStatus}
                            onGenerateGaps={(transcript, rule) => api.generateRuleGaps(transcript, rule)}
                        />
                    )}
                </div>
            </div>
        </div>
    );
}
