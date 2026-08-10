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
 * Timeline strip: one block per cue, positioned by its time, plus a playhead
 * driven by the media preview. Clicking a block focuses its cue and seeks the
 * media to the cue's start.
 *
 * @module     mod_elang/components/Timeline
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Cue} from '../types';

interface Props {
    cues: Cue[];
    /** Media duration in ms, 0 when there is no playable medium yet. */
    durationms: number;
    /** Current playback position in ms. */
    currentms: number;
    onSeek: (cue: Cue) => void;
}

/**
 * Render the timeline strip.
 *
 * @param props The component props.
 * @returns The timeline element.
 */
export function Timeline({cues, durationms, currentms, onSeek}: Props): JSX.Element {
    let maxend = 0;
    cues.forEach((cue) => {
        if (cue.endtime > maxend) {
            maxend = cue.endtime;
        }
    });
    const total = Math.max(maxend, durationms, 1);

    return (
        <div className="mod_elang-editor-timeline" data-region="timeline">
            {cues.map((cue) => (
                <div
                    key={cue.cuekey}
                    className={'mod_elang-editor-timeline-cue' + (currentms >= cue.starttime && currentms < cue.endtime
                        ? ' active' : '')}
                    style={{
                        left: (cue.starttime / total * 100) + '%',
                        width: (Math.max(cue.endtime - cue.starttime, 0) / total * 100) + '%',
                    }}
                    onClick={() => onSeek(cue)}
                >
                    {cue.transcript.slice(0, 20)}
                </div>
            ))}
            <div
                className="mod_elang-editor-timeline-playhead"
                data-region="playhead"
                style={{left: (currentms / total * 100) + '%'}}
            />
        </div>
    );
}
