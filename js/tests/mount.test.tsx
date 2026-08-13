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
 * End-to-end mount tests against an in-memory transport: the editor loads the
 * draft, renders its cues, and materialises gaps from a V1-marker import —
 * the help-allowed bracket form seeding one solution hint.
 *
 * @module     mod_elang/tests/mount
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {act} from 'react';
import {mount} from '../src/mount';
import {ServiceTransport} from '../src/types';

(globalThis as unknown as {IS_REACT_ACT_ENVIRONMENT: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const t = (key: string): string => key;

function memoryTransport(): {transport: ServiceTransport; saved: Record<string, unknown>[]} {
    const saved: Record<string, unknown>[] = [];
    const transport: ServiceTransport = async(method, args) => {
        if (method === 'mod_elang_get_version_content') {
            return {
                versionid: 7, revision: 1,
                mediakind: '', mediaurl: '', mediaprovider: '', mediaproviderref: '',
                mediafilename: '', mediafileurl: '',
                mediaproviders: [{key: 'youtube', name: 'YouTube'}, {key: 'vimeo', name: 'Vimeo'}],
                cues: [{
                    cuekey: 'c1', sortorder: 1, starttime: 0, endtime: 1000,
                    transcript: 'Bonjour le monde', transcriptformat: 2, gaps: [],
                }],
            };
        }
        if (method === 'mod_elang_preview_import') {
            // The server-side preview with parsegaps: markers stripped,
            // one help-allowed gap reported.
            expect(args.parsegaps).toBe(true);
            return {
                cuecount: 1,
                warnings: [],
                cues: [{
                    sortorder: 1, starttime: 2000, endtime: 3000,
                    transcript: 'Der Hund läuft', transcriptformat: 2,
                    gaps: [{charstart: 4, charlength: 4, solution: 'Hund', hintsallowed: true}],
                }],
            };
        }
        if (method === 'mod_elang_save_draft_version') {
            saved.push(args);
            return {versionid: 7, revision: 2};
        }
        return {};
    };
    return {transport, saved};
}

describe('editor mount()', () => {
    let host: HTMLDivElement;

    beforeEach(() => {
        host = document.createElement('div');
        document.body.appendChild(host);
    });

    afterEach(() => {
        host.remove();
    });

    test('loads the draft and renders its cues', async() => {
        const {transport} = memoryTransport();

        await act(async() => {
            mount(host, {versionid: 7, callService: transport, getString: t});
        });

        const textarea = host.querySelector('[data-region="cues"] textarea') as HTMLTextAreaElement;
        expect(textarea).not.toBeNull();
        expect(textarea.value).toBe('Bonjour le monde');
        expect(host.querySelector('[data-region="status"]')?.textContent).toBe('');
    });

    test('imports V1 markers as real gaps with a seeded solution hint', async() => {
        const {transport, saved} = memoryTransport();

        await act(async() => {
            mount(host, {versionid: 7, callService: transport, getString: t});
        });

        // Enable gap parsing, paste subtitle text and import it.
        const checkbox = host.querySelector('[data-region="parsegaps"]') as HTMLInputElement;
        const importtext = host.querySelector('[data-region="importtext"]') as HTMLTextAreaElement;
        const importbutton = host.querySelector('[data-action="import"]') as HTMLButtonElement;
        await act(async() => {
            checkbox.click();
        });
        await act(async() => {
            const setter = Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value')?.set;
            setter?.call(importtext, 'WEBVTT stub');
            importtext.dispatchEvent(new Event('input', {bubbles: true}));
        });
        await act(async() => {
            importbutton.click();
        });

        // The imported cue is rendered with a gap whose solution came from
        // the marker and whose bracket form seeded one solution hint.
        const gaprow = host.querySelectorAll('.mod_elang-editor-gap');
        expect(gaprow.length).toBe(1);
        const solution = gaprow[0].querySelector('input') as HTMLInputElement;
        expect(solution.value).toBe('Hund');
        expect(gaprow[0].querySelectorAll('.mod_elang-editor-hint').length).toBe(1);

        // Saving sends the materialised gap through the declared wire shape.
        const savebutton = host.querySelector('[data-action="save"]') as HTMLButtonElement;
        await act(async() => {
            savebutton.click();
        });
        expect(saved.length).toBe(1);
        const cues = saved[0].cues as Array<{transcript: string; gaps: Array<{solution: string; hints: unknown[]}>}>;
        expect(cues.length).toBe(2);
        expect(cues[1].transcript).toBe('Der Hund läuft');
        expect(cues[1].gaps[0].solution).toBe('Hund');
        expect(cues[1].gaps[0].hints.length).toBe(1);
    });
});
