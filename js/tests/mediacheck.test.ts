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
 * Tests for the undecodable-video-track detection.
 *
 * @module     mod_elang/tests/mediacheck
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {looksLikeAudioFile, videoTrackUndecodable} from '../src/studio/mediacheck';

describe('looksLikeAudioFile', () => {
    test('recognises common audio extensions, case-insensitively and with query strings', () => {
        expect(looksLikeAudioFile('https://x/clip.mp3')).toBe(true);
        expect(looksLikeAudioFile('https://x/clip.M4A?token=abc')).toBe(true);
        expect(looksLikeAudioFile('https://x/pluginfile.php/1/mod_elang/media/7/voice.ogg#t=3')).toBe(true);
    });

    test('does not flag video containers', () => {
        expect(looksLikeAudioFile('https://x/clip.mp4')).toBe(false);
        expect(looksLikeAudioFile('https://x/clip.webm')).toBe(false);
        // An mp3 segment in the path is not the file's extension.
        expect(looksLikeAudioFile('https://x/mp3-course/clip.mp4')).toBe(false);
    });
});

describe('videoTrackUndecodable', () => {
    test('flags a loaded video with no picture size', () => {
        expect(videoTrackUndecodable('https://x/divx.mp4', 0, 1)).toBe(true);
        expect(videoTrackUndecodable('https://x/divx.mp4', 0, 4)).toBe(true);
    });

    test('does not flag a decodable video, unloaded metadata, or audio files', () => {
        // Picture size present: decodes fine.
        expect(videoTrackUndecodable('https://x/ok.mp4', 640, 4)).toBe(false);
        // Metadata not loaded yet: no verdict.
        expect(videoTrackUndecodable('https://x/slow.mp4', 0, 0)).toBe(false);
        // Audio in a video element legitimately has no picture.
        expect(videoTrackUndecodable('https://x/voice.mp3', 0, 4)).toBe(false);
    });
});
