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
 * Detecting a video track the browser cannot decode.
 *
 * A codec the browser does not support (for example MPEG-4 Part 2 from the
 * DivX/Xvid era) typically still plays its audio track while the picture stays
 * black; desktop players like VLC decode it fine, so the file looks healthy to
 * the author. The reliable runtime signal is a video element whose loaded
 * metadata reports a zero-width picture. An audio file mounted in a video
 * element reports zero width too, which is not a problem — so sources that look
 * like plain audio files are excluded.
 *
 * @module     mod_elang/studio/mediacheck
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const AUDIOFILE = /\.(mp3|m4a|aac|oga|ogg|opus|wav|flac)(\?|#|$)/i;

/**
 * Whether a source URL looks like a plain audio file.
 *
 * @param src The media source URL.
 * @returns True for audio-looking sources.
 */
export function looksLikeAudioFile(src: string): boolean {
    return AUDIOFILE.test(src);
}

/**
 * Whether loaded media metadata indicates an undecodable video track.
 *
 * @param src The media source URL.
 * @param videowidth The element's reported videoWidth.
 * @param readystate The element's readyState (>= 1 once metadata is loaded).
 * @returns True when the browser loaded the medium but cannot show a picture.
 */
export function videoTrackUndecodable(src: string, videowidth: number, readystate: number): boolean {
    if (looksLikeAudioFile(src)) {
        return false;
    }
    return readystate >= 1 && videowidth === 0;
}
