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
import {CueProblem, partitionCues, repairCue} from '../studio/cue-validation';
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
    const [status, setStatus] = useState(t('editor_loading'));
    const [loaded, setLoaded] = useState(false);
    const [cues, setCues] = useState<Cue[]>([]);
    const [media, setMedia] = useState<Media | null>(null);
    const [focusedcuekey, setFocusedcuekey] = useState('');
    const [currentms, setCurrentms] = useState(0);
    const [durationms, setDurationms] = useState(0);

    // Mirrored for the save closure, which the autosave controller captured
    // once and which would otherwise keep reading the duration as it was when
    // the editor mounted — zero, before the medium reported anything.
    const durationRef = useRef(0);
    useEffect(() => {
        durationRef.current = durationms;
    }, [durationms]);
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
                setStatus(t('editor_loaderror') + ' [' + errorMessage(error, '') + ']');
            }
        });
        return () => {
            active = false;
        };
        // The api client is fixed for the lifetime of the mount.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Cues whose latest edit could not be sent, keyed by cue key. Held in a ref
    // as well as in state: the save runs from the autosave controller, which
    // captured its closure once, so it has to read the current value rather
    // than the one that existed when it was created.
    const [problems, setProblems] = useState<Map<string, CueProblem[]>>(new Map());
    const problemsRef = useRef<Map<string, CueProblem[]>>(new Map());

    // Cue keys the author deleted since the last successful save. The partial
    // endpoint treats an absent key as "leave alone", so a deletion has to be
    // reported explicitly or it would never reach the server.
    const removedRef = useRef<Set<string>>(new Set());

    // Persist the latest cue list; reads cuesRef so the autosave controller
    // always saves current content rather than the cues captured when it was
    // created.
    //
    // Only the cues that pass the local checks are sent. The rest are left out,
    // which the partial endpoint reads as "do not touch these" — so the last
    // state the server accepted for a broken cue stays there while the author
    // sees their newer, unsendable version in the browser, marked as unsaved.
    const save = async(): Promise<void> => {
        const {saveable, problems: found} = partitionCues(
            cuesRef.current,
            durationRef.current > 0 ? durationRef.current : null
        );

        problemsRef.current = found;
        setProblems(found);

        const removed = Array.from(removedRef.current);
        if (saveable.length === 0 && removed.length === 0) {
            // Nothing sendable. Not an error and not a save: returning without
            // a request keeps the autosave controller out of its error state,
            // which is for a server that could not be reached.
            return;
        }

        revisionRef.current = await api.saveDraftCues(revisionRef.current, saveable, removed);
        removedRef.current = new Set();
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
            setStatus(t('editor_saved'));
        } catch (error) {
            setStatus(errorMessage(error, t('editor_saveerror')));
        }
    };

    /**
     * Select the first subtitle with an open problem and say why publishing stopped.
     *
     * @returns Always true, so a caller can read it as "publishing was blocked".
     */
    const blockPublish = (): boolean => {
        const first = cuesRef.current.find((cue) => problemsRef.current.has(cue.cuekey));
        if (first) {
            setSelectedcuekey(first.cuekey);
        }
        setStatus(t('editor_publishblocked'));

        return true;
    };

    const handlePublish = async(): Promise<void> => {
        // Checked twice, and the second check is the one that matters.
        //
        // problemsRef is filled by the save, and the save is debounced. An
        // author who breaks a subtitle and reaches for Publish in the same
        // second arrives here before the debounce has run, so the first check
        // sees an empty set and waves them through. The flush then runs the
        // checks, correctly refuses to send the broken subtitle — and publishing
        // would have continued anyway, on a draft the server still holds in its
        // last good state. The activity would go live missing the author's most
        // recent edit, with nothing on screen saying so.
        //
        // The first check is kept because it gives the faster answer in the
        // ordinary case: no request at all when the problem is already known.
        if (problemsRef.current.size > 0) {
            blockPublish();
            return;
        }

        try {
            await (autosaveRef.current ? autosaveRef.current.flush() : save());

            // Now the checks have actually run against what the author typed.
            if (problemsRef.current.size > 0) {
                blockPublish();
                return;
            }

            await api.publish();
            setStatus(t('editor_published'));
            window.setTimeout(() => window.location.reload(), 1200);
        } catch (error) {
            setStatus(errorMessage(error, t('editor_saveerror')));
        }
    };

    /** How long a newly added subtitle lasts until the author gives it a time. */
    const NEW_CUE_MS = 2000;

    const insertCueAt = (index: number): void => {
        // A new subtitle starts where the medium is paused and lasts two
        // seconds. It used to start and end at zero, which is a subtitle shown
        // for no time at all — the local checks now call that what it is, so
        // every freshly added row would have announced itself as broken before
        // the author had typed anything. Starting at the playhead is also what
        // an author means when they add a subtitle while watching.
        const start = capturems() ?? 0;
        const fresh: Cue = {
            cuekey: newKey('c'),
            sortorder: index + 1,
            starttime: start,
            endtime: start + NEW_CUE_MS,
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
        setCues((current) => {
            const going = current[index];
            if (going) {
                removedRef.current.add(going.cuekey);
            }
            return current.filter((_, i) => i !== index);
        });
    };

    /**
     * The correction that would be applied to a cue, or null when there is none.
     *
     * @param cuekey The cue to examine.
     * @returns The corrected cue, or null.
     */
    const repairProposal = (cuekey: string): Cue | null => {
        const index = cuesRef.current.findIndex((cue) => cue.cuekey === cuekey);
        if (index < 0) {
            return null;
        }
        const next = cuesRef.current[index + 1] ?? null;

        return repairCue(
            cuesRef.current[index],
            next ? next.starttime : null,
            capturems(),
            durationRef.current || null
        );
    };

    /**
     * Apply the proposed correction to a cue, at the author's request.
     *
     * Nothing repairs itself. A timing conflict has more than one reasonable
     * reading — move the end, move the start, delete the cue — so the editor
     * offers and the author decides. The corrected cue then autosaves like any
     * other edit.
     *
     * @param cuekey The cue to repair.
     */
    const repairCueByKey = (cuekey: string): void => {
        setCues((current) => {
            const index = current.findIndex((cue) => cue.cuekey === cuekey);
            if (index < 0) {
                return current;
            }
            const next = current[index + 1] ?? null;
            const repaired = repairCue(
                current[index],
                next ? next.starttime : null,
                capturems(),
                durationRef.current || null
            );
            if (repaired === null) {
                return current;
            }
            const updated = current.slice();
            updated[index] = repaired;
            return updated;
        });
    };

    // Appending at the end is the same operation as inserting at the end, so
    // there is one implementation rather than two that could drift.
    const handleAddCue = (): void => insertCueAt(cues.length);

    const handlePreviewImport = async(subtitles: string, parsegaps: boolean): Promise<ImportResult | null> => {
        try {
            return await api.previewImport(subtitles, parsegaps);
        } catch (error) {
            setStatus(errorMessage(error, t('editor_saveerror')));
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

        setStatus(t(replace ? 'editor_importreplacedcues' : 'editor_importedcues')
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
        dirty: 'editor_unsaved',
        saving: 'editor_saving',
        saved: 'editor_autosaved',
        error: 'editor_autosaveerror',
    };

    // Three outcomes, not two. "Everything is saved" and "the server could not
    // be reached" were the only things the status could say, so a cue the
    // author had just made contradictory had to be reported as one or the
    // other — and reporting it as a save failure taught them to distrust an
    // autosave that was working correctly.
    const hasproblems = problems.size > 0;

    // The same set for the list and the timeline. Derived from the map rather
    // than tracked separately: two sources for "which subtitles are unsaved"
    // is how one of them ends up stale.
    const problemkeys = new Set(problems.keys());
    const savestatekey = hasproblems && (savestate === 'saved' || savestate === 'idle')
        ? 'editor_savedwithproblems'
        : savestatekeys[savestate];

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
                    {t('editor_save')}
                </button>

                <span className="mr-auto ms-auto"></span>

                <button
                    type="button"
                    className="btn btn-outline-secondary mr-2 me-2"
                    data-action="openimport"
                    onClick={() => setImportopen(true)}
                >
                    {t('editor_import')}
                </button>
                <button type="button" className="btn btn-success" data-action="publish" onClick={handlePublish}>
                    {t('editor_publish')}
                </button>
            </div>

            <div className="mod_elang-editor-timeline-wrap mb-3" data-region="timelinewrap">
                {showpreview && novideotrack && (
                    <div className="alert alert-warning" role="alert" data-region="novideotrack">
                        {t('editor_novideotrack')}
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
                    problemkeys={problemkeys}
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
                        problemkeys={problemkeys}
                    />
                </div>

                <div className="col-12 col-lg-7" data-region="cueinspector">
                    {selectedcue === null && (
                        <p className="text-muted" data-region="nocueselected">{t('editor_nocueselected')}</p>
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
                            problems={problems.get(selectedcue.cue.cuekey)}
                            onRepair={
                                // Offered only when a correction can actually be
                                // derived. "Repairable" says the kind of problem
                                // has a single answer in principle; whether one
                                // exists here depends on there being a next
                                // subtitle or a playback position to take it
                                // from. Showing the button regardless gave the
                                // author something that looked like a fix and
                                // did nothing when they pressed it.
                                repairProposal(selectedcue.cue.cuekey) !== null
                                    ? () => repairCueByKey(selectedcue.cue.cuekey)
                                    : undefined
                            }
                        />
                    )}
                </div>
            </div>
        </div>
    );
}
