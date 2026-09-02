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
 * Timeline strip: one block per cue positioned by its time, a waveform behind
 * it and a playhead driven by the media preview. A cue's start and end edges are
 * draggable handles that snap to neighbouring edges and the playhead, and are
 * also operable from the keyboard (arrow keys nudge, Shift for coarse steps) as
 * ARIA sliders. Clicking a block still focuses its cue and seeks the media.
 *
 * @module     mod_elang/components/Timeline
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useRef} from 'react';
import {Cue, Translator} from '../types';
import {msToPercent, pxToMs, snapMs} from '../studio/snapping';
import {Waveform} from './Waveform';

interface Props {
    cues: Cue[];
    /** Media duration in ms, 0 when there is no playable medium yet. */
    durationms: number;
    /** Current playback position in ms. */
    currentms: number;
    /** Direct media URL for the waveform, empty when none is playable. */
    mediasrc: string;
    t: Translator;
    onSeek: (cue: Cue) => void;
    onEdit: (cuekey: string, starttime: number, endtime: number) => void;
}

/** Which edge of a cue a handle controls. */
type Edge = 'start' | 'end';

/** Fine keyboard nudge in ms; Shift multiplies it for coarse steps. */
const NUDGE_MS = 100;
const COARSE_MULTIPLIER = 10;

/**
 * Render the timeline strip.
 *
 * @param props The component props.
 * @returns The timeline element.
 */
export function Timeline({cues, durationms, currentms, mediasrc, t, onSeek, onEdit}: Props): JSX.Element {
    const stripRef = useRef<HTMLDivElement>(null);

    let maxend = 0;
    cues.forEach((cue) => {
        if (cue.endtime > maxend) {
            maxend = cue.endtime;
        }
    });
    const total = Math.max(maxend, durationms, 1);

    // Every other cue edge, plus the playhead, is a snap candidate.
    const snapCandidates = (cuekey: string): number[] => {
        const candidates: number[] = [currentms];
        cues.forEach((cue) => {
            if (cue.cuekey !== cuekey) {
                candidates.push(cue.starttime, cue.endtime);
            }
        });
        return candidates;
    };

    const applyEdge = (cue: Cue, edge: Edge, ms: number): void => {
        if (edge === 'start') {
            onEdit(cue.cuekey, Math.min(ms, cue.endtime), cue.endtime);
        } else {
            onEdit(cue.cuekey, cue.starttime, Math.max(ms, cue.starttime));
        }
    };

    const handleDrag = (cue: Cue, edge: Edge, clientX: number): void => {
        const strip = stripRef.current;
        if (!strip) {
            return;
        }
        const rect = strip.getBoundingClientRect();
        const raw = pxToMs(clientX - rect.left, rect.width, total);
        const thresholdMs = rect.width > 0 ? Math.round((10 / rect.width) * total) : 0;
        applyEdge(cue, edge, snapMs(raw, snapCandidates(cue.cuekey), thresholdMs));
    };

    const handleKey = (cue: Cue, edge: Edge, event: React.KeyboardEvent): void => {
        const step = (event.shiftKey ? NUDGE_MS * COARSE_MULTIPLIER : NUDGE_MS)
            * (event.key === 'ArrowLeft' ? -1 : 1);
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
            return;
        }
        event.preventDefault();
        const value = (edge === 'start' ? cue.starttime : cue.endtime) + step;
        applyEdge(cue, edge, Math.max(0, value));
    };

    const handle = (cue: Cue, edge: Edge): JSX.Element => {
        const value = edge === 'start' ? cue.starttime : cue.endtime;
        return (
            <span
                className={'mod_elang-editor-timeline-handle ' + edge}
                data-region="cuehandle"
                data-edge={edge}
                role="slider"
                tabIndex={0}
                aria-label={t(edge === 'start' ? 'editor_starttime' : 'editor_endtime')}
                aria-valuemin={0}
                aria-valuemax={total}
                aria-valuenow={value}
                onPointerDown={(event) => {
                    (event.target as HTMLElement).setPointerCapture(event.pointerId);
                    event.stopPropagation();
                }}
                onPointerMove={(event) => {
                    if (event.buttons === 1) {
                        handleDrag(cue, edge, event.clientX);
                    }
                }}
                onKeyDown={(event) => handleKey(cue, edge, event)}
            />
        );
    };

    return (
        <div className="mod_elang-editor-timeline" data-region="timeline" ref={stripRef}>
            {mediasrc !== '' && (
                <Waveform mediasrc={mediasrc} totalms={total} currentms={currentms} t={t} onSeek={(ms) => {
                    const target = cues.find((cue) => ms >= cue.starttime && ms < cue.endtime);
                    if (target) {
                        onSeek(target);
                    }
                }} />
            )}
            {cues.map((cue) => (
                <div
                    key={cue.cuekey}
                    className={'mod_elang-editor-timeline-cue' + (currentms >= cue.starttime && currentms < cue.endtime
                        ? ' active' : '')}
                    style={{
                        left: msToPercent(cue.starttime, total) + '%',
                        width: msToPercent(Math.max(cue.endtime - cue.starttime, 0), total) + '%',
                    }}
                >
                    {handle(cue, 'start')}
                    <span
                        className="mod_elang-editor-timeline-label"
                        role="button"
                        tabIndex={0}
                        onClick={() => onSeek(cue)}
                        onKeyDown={(event) => {
                            if (event.key === 'Enter' || event.key === ' ') {
                                event.preventDefault();
                                onSeek(cue);
                            }
                        }}
                    >
                        {cue.transcript.slice(0, 20)}
                    </span>
                    {handle(cue, 'end')}
                </div>
            ))}
            <div
                className="mod_elang-editor-timeline-playhead"
                data-region="playhead"
                style={{left: msToPercent(currentms, total) + '%'}}
            />
        </div>
    );
}
