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

        // Open the import modal, switch to the paste tab, enable gap parsing,
        // paste subtitle text, have it checked, then apply it. Import is now a
        // two-step: nothing reaches the cue list until the parse summary has
        // been shown.
        await act(async() => {
            (host.querySelector('[data-action="openimport"]') as HTMLButtonElement).click();
        });
        expect(host.querySelector('[data-region="importmodal"]')).not.toBeNull();

        await act(async() => {
            (host.querySelector('[data-action="importtabtext"]') as HTMLButtonElement).click();
        });
        await act(async() => {
            (host.querySelector('[data-region="parsegaps"]') as HTMLInputElement).click();
        });
        await act(async() => {
            const importtext = host.querySelector('[data-region="importtext"]') as HTMLTextAreaElement;
            const setter = Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value')?.set;
            setter?.call(importtext, 'WEBVTT stub');
            importtext.dispatchEvent(new Event('input', {bubbles: true}));
        });

        // Applying is refused until the content has actually been checked.
        expect((host.querySelector('[data-action="importapply"]') as HTMLButtonElement).disabled).toBe(true);

        await act(async() => {
            (host.querySelector('[data-action="importpreview"]') as HTMLButtonElement).click();
        });
        expect(host.querySelector('[data-region="summarycues"]')?.textContent).toBe('1');
        expect(host.querySelector('[data-region="summarygaps"]')?.textContent).toBe('1');

        await act(async() => {
            (host.querySelector('[data-action="importapply"]') as HTMLButtonElement).click();
        });

        // Applying closes the modal.
        expect(host.querySelector('[data-region="importmodal"]')).toBeNull();

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

    test('the cue list shows every cue but opens only one editor', async() => {
        const {transport} = memoryTransport();

        await act(async() => {
            mount(host, {versionid: 7, callService: transport, getString: t});
        });

        // Every cue is listed...
        const items = host.querySelectorAll('[data-region="cuelist"] .mod_elang-cuelist-item');
        expect(items.length).toBeGreaterThan(0);

        // ...but the inspector holds exactly one open cue editor, which is what
        // replaced the wall of fully expanded forms.
        expect(host.querySelectorAll('[data-region="cueinspector"] .mod_elang-editor-cue').length).toBe(1);
    });

    test('selecting a cue in the list opens that cue in the inspector', async() => {
        const {transport} = memoryTransport();

        await act(async() => {
            mount(host, {versionid: 7, callService: transport, getString: t});
        });

        // Add a second cue so there is something to switch between.
        await act(async() => {
            (host.querySelector('[data-action="addcue"]') as HTMLButtonElement).click();
        });

        const items = Array.from(host.querySelectorAll('[data-region="cuelist"] .mod_elang-cuelist-item'));
        expect(items.length).toBe(2);

        const secondkey = (items[1] as HTMLElement).dataset.cuekey;
        await act(async() => {
            (items[1].querySelector('[data-action="selectcue"]') as HTMLButtonElement).click();
        });

        const open = host.querySelector('[data-region="cueinspector"] .mod_elang-editor-cue') as HTMLElement;
        expect(open.dataset.cuekey).toBe(secondkey);
        // The list marks the same cue, so list and inspector cannot disagree.
        expect(items[1].querySelector('[aria-current="true"]')).not.toBeNull();
    });

    test('cue times are shown as timestamps, not as milliseconds', async() => {
        const {transport} = memoryTransport();

        await act(async() => {
            mount(host, {versionid: 7, callService: transport, getString: t});
        });

        const field = host.querySelector('[data-region="timefield"]') as HTMLInputElement;
        expect(field).not.toBeNull();
        expect(field.value).toMatch(/^\d{2}:\d{2}\.\d{3}$/);
    });

    test('a gap keeps its rarely-needed settings behind an advanced section', async() => {
        const {transport} = memoryTransport();

        await act(async() => {
            mount(host, {versionid: 7, callService: transport, getString: t});
        });

        // The seeded draft has no gap yet, so one is imported. The import opens
        // the cue it created, which is what puts a gap in the inspector.
        await act(async() => {
            (host.querySelector('[data-action="openimport"]') as HTMLButtonElement).click();
        });
        await act(async() => {
            (host.querySelector('[data-action="importtabtext"]') as HTMLButtonElement).click();
        });
        await act(async() => {
            (host.querySelector('[data-region="parsegaps"]') as HTMLInputElement).click();
        });
        await act(async() => {
            const importtext = host.querySelector('[data-region="importtext"]') as HTMLTextAreaElement;
            const setter = Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value')?.set;
            setter?.call(importtext, 'WEBVTT stub');
            importtext.dispatchEvent(new Event('input', {bubbles: true}));
        });
        await act(async() => {
            (host.querySelector('[data-action="importpreview"]') as HTMLButtonElement).click();
        });
        await act(async() => {
            (host.querySelector('[data-action="importapply"]') as HTMLButtonElement).click();
        });

        const advanced = host.querySelector('[data-region="gapadvanced"]') as HTMLDetailsElement;
        expect(advanced).not.toBeNull();

        // Closed to begin with: maximum length, the reference link and regular
        // expressions are decisions most gaps never need.
        expect(advanced.open).toBe(false);

        // They are reachable, and they are the fields that previously had no
        // control at all — the only way to set them was an import.
        expect(advanced.querySelector('[data-region="maxlength"]')).not.toBeNull();
        expect(advanced.querySelector('[data-region="linkurl"]')).not.toBeNull();
    });

    test('the import modal keeps the focus and gives it back', async() => {
        const {transport} = memoryTransport();

        await act(async() => {
            mount(host, {versionid: 7, callService: transport, getString: t});
        });

        const trigger = host.querySelector('[data-action="openimport"]') as HTMLButtonElement;
        trigger.focus();
        expect(document.activeElement).toBe(trigger);

        await act(async() => {
            trigger.click();
        });

        // The focus moves into the dialog rather than staying behind it.
        const dialog = host.querySelector('[data-region="importmodal"]') as HTMLElement;
        expect(dialog.contains(document.activeElement)).toBe(true);

        // Tab from the last control wraps to the first instead of leaving for
        // the page behind the backdrop, where the cursor would simply vanish.
        const focusable = Array.from(dialog.querySelectorAll<HTMLElement>(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]),'
            + ' textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        ));
        expect(focusable.length).toBeGreaterThan(1);

        focusable[focusable.length - 1].focus();
        await act(async() => {
            document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Tab', bubbles: true}));
        });
        expect(dialog.contains(document.activeElement)).toBe(true);

        // Escape closes it and the trigger gets the focus back, so a keyboard
        // user resumes where they were rather than at the top of the document.
        await act(async() => {
            document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape', bubbles: true}));
        });
        expect(host.querySelector('[data-region="importmodal"]')).toBeNull();
        expect(document.activeElement).toBe(trigger);
    });

    /**
     * Build a File the way a browser hands one to a change handler.
     */
    const makeFile = (name: string, type: string, size: number): File => {
        const file = new File(['x'], name, {type});
        Object.defineProperty(file, 'size', {value: size});
        return file;
    };

    /**
     * Open the import modal and hand it a file.
     */
    const chooseFile = async(file: File): Promise<HTMLElement> => {
        await act(async() => {
            (host.querySelector('[data-action="openimport"]') as HTMLButtonElement).click();
        });
        const input = host.querySelector('[data-region="importfileinput"]') as HTMLInputElement;
        Object.defineProperty(input, 'files', {value: [file], configurable: true});
        await act(async() => {
            input.dispatchEvent(new Event('change', {bubbles: true}));
        });
        return host.querySelector('[data-region="importmodal"]') as HTMLElement;
    };

    test('a file that is not a subtitle file is refused before it is read', async() => {
        const {transport} = memoryTransport();
        await act(async() => {
            mount(host, {versionid: 7, callService: transport, getString: t});
        });

        // accept="" on the input is a filter the browser applies to its own
        // dialog; a dragged file or a switched filter arrives regardless.
        const dialog = await chooseFile(makeFile('holiday.mp4', 'video/mp4', 1000));

        expect(dialog.querySelector('[data-region="importerror"]')).not.toBeNull();
        // Nothing was read, so there is nothing to check.
        expect((dialog.querySelector('[data-action="importpreview"]') as HTMLButtonElement).disabled).toBe(true);
    });

    test('an oversized file is refused before it is read', async() => {
        const {transport} = memoryTransport();
        await act(async() => {
            mount(host, {versionid: 7, callService: transport, getString: t});
        });

        // readAsText() would load all of it into memory before anything could
        // object, which is the whole reason the check is in front of it.
        const dialog = await chooseFile(makeFile('huge.vtt', 'text/vtt', 3 * 1024 * 1024));

        expect(dialog.querySelector('[data-region="importerror"]')).not.toBeNull();
    });

    test('a subtitle file with no MIME type is accepted', async() => {
        const {transport} = memoryTransport();
        await act(async() => {
            mount(host, {versionid: 7, callService: transport, getString: t});
        });

        // Browsers report an empty type for .vtt often enough that rejecting on
        // it would turn away valid files.
        const dialog = await chooseFile(makeFile('lesson.vtt', '', 1000));

        expect(dialog.querySelector('[data-region="importerror"]')).toBeNull();
    });

    test('the file field is cleared so the same file can be chosen again', async() => {
        const {transport} = memoryTransport();
        await act(async() => {
            mount(host, {versionid: 7, callService: transport, getString: t});
        });

        await chooseFile(makeFile('holiday.mp4', 'video/mp4', 1000));

        // Without clearing, picking the corrected file — same name — fires no
        // change event at all, and the error stays as if it had failed twice.
        const input = host.querySelector('[data-region="importfileinput"]') as HTMLInputElement;
        expect(input.value).toBe('');
    });
});
