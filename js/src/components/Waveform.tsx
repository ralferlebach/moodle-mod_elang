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
 * A waveform strip drawn under the timeline from the medium's audio.
 *
 * The medium is fetched and decoded once through the Web Audio API; the decoded
 * samples are reduced to one peak per column (see studio/waveform) and drawn as
 * a mirrored SVG band, with a playhead and click-to-seek. Everything degrades
 * silently: a provider embed, a cross-origin URL that refuses a fetch, or a
 * browser without AudioContext simply shows no waveform, leaving the cue blocks
 * on the timeline untouched.
 *
 * @module     mod_elang/components/Waveform
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useEffect, useRef, useState} from 'react';
import {Translator} from '../types';
import {extractPeaks, peaksToPolyline} from '../studio/waveform';
import {msToPercent, pxToMs} from '../studio/snapping';

interface Props {
    mediasrc: string;
    totalms: number;
    currentms: number;
    t: Translator;
    onSeek: (ms: number) => void;
}

/** Number of peak columns to render across the strip. */
const BUCKETS = 800;
/** SVG view height the mirrored band is drawn into. */
const VIEW_HEIGHT = 100;

/** Minimal typing for the optional, prefixed AudioContext constructor. */
type AudioContextConstructor = new () => AudioContext;

/**
 * Resolve an AudioContext constructor across browsers, or null when the Web
 * Audio API is unavailable (so the component can degrade to no waveform).
 *
 * @returns The constructor, or null when unavailable.
 */
function audioContextConstructor(): AudioContextConstructor | null {
    const scope = window as unknown as {
        AudioContext?: AudioContextConstructor;
        webkitAudioContext?: AudioContextConstructor;
    };
    return scope.AudioContext || scope.webkitAudioContext || null;
}

/**
 * Render the waveform strip.
 *
 * @param props The component props.
 * @returns The waveform element, or an empty strip when no audio could be drawn.
 */
export function Waveform({mediasrc, totalms, currentms, t, onSeek}: Props): JSX.Element | null {
    const [peaks, setPeaks] = useState<number[]>([]);
    const svgRef = useRef<SVGSVGElement>(null);

    useEffect(() => {
        let active = true;
        setPeaks([]);

        const Ctor = audioContextConstructor();
        if (!mediasrc || !Ctor || typeof fetch !== 'function') {
            return undefined;
        }

        const context = new Ctor();
        fetch(mediasrc)
            .then((response) => response.arrayBuffer())
            .then((buffer) => context.decodeAudioData(buffer))
            .then((audio) => {
                if (active) {
                    setPeaks(extractPeaks(audio.getChannelData(0), BUCKETS));
                }
                return undefined;
            })
            .catch(() => {
                // No waveform is a perfectly good outcome here; the timeline's
                // cue blocks remain fully usable without it.
                if (active) {
                    setPeaks([]);
                }
            })
            .finally(() => {
                if (typeof context.close === 'function') {
                    void context.close();
                }
            });

        return () => {
            active = false;
        };
    }, [mediasrc]);

    if (peaks.length === 0) {
        return null;
    }

    const handleClick = (event: React.MouseEvent<SVGSVGElement>): void => {
        const svg = svgRef.current;
        if (!svg || totalms <= 0) {
            return;
        }
        const rect = svg.getBoundingClientRect();
        onSeek(pxToMs(event.clientX - rect.left, rect.width, totalms));
    };

    return (
        <svg
            ref={svgRef}
            className="mod_elang-editor-waveform"
            data-region="waveform"
            viewBox={'0 0 ' + BUCKETS + ' ' + VIEW_HEIGHT}
            preserveAspectRatio="none"
            role="img"
            aria-label={t('editor_waveform')}
            onClick={handleClick}
        >
            <polyline className="mod_elang-editor-waveform-band" points={peaksToPolyline(peaks, BUCKETS, VIEW_HEIGHT)} />
            <line
                className="mod_elang-editor-waveform-playhead"
                data-region="waveformplayhead"
                x1={(msToPercent(currentms, totalms) / 100) * BUCKETS}
                x2={(msToPercent(currentms, totalms) / 100) * BUCKETS}
                y1={0}
                y2={VIEW_HEIGHT}
            />
        </svg>
    );
}
