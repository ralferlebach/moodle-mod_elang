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
 * Tests for the media panel: the provider selector offers exactly the curated
 * list handed in by the server and saving passes the selected provider key
 * with the raw (server-normalised) reference.
 *
 * @module     mod_elang/tests/media_panel
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import React from 'react';
import {createRoot, Root} from 'react-dom/client';
import {act} from 'react';
import {MediaPanel} from '../src/components/MediaPanel';
import {Media} from '../src/types';

(globalThis as unknown as {IS_REACT_ACT_ENVIRONMENT: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const t = (key: string): string => key;

const emptyMedia: Media = {
    mediakind: '', mediaurl: '', mediaprovider: '', mediaproviderref: '',
    mediafilename: '', mediafileurl: '',
};

describe('MediaPanel provider selection', () => {
    let host: HTMLDivElement;
    let root: Root;

    beforeEach(() => {
        host = document.createElement('div');
        document.body.appendChild(host);
        root = createRoot(host);
    });

    afterEach(() => {
        act(() => {
            root.unmount();
        });
        host.remove();
    });

    test('offers the curated providers and saves key plus raw reference', async() => {
        const saves: string[][] = [];
        await act(async() => {
            root.render(React.createElement(MediaPanel, {
                media: emptyMedia,
                providers: [{key: 'youtube', name: 'YouTube'}, {key: 'vimeo', name: 'Vimeo'}],
                mediauploadurl: '',
                t,
                onSave: (kind, url, provider, providerref) => {
                    saves.push([kind, url, provider, providerref]);
                },
            }));
        });

        // Choose the provider medium kind so the provider fields appear.
        const kindselect = host.querySelector('[data-region="mediakind"]') as HTMLSelectElement;
        await act(async() => {
            const setter = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value')?.set;
            setter?.call(kindselect, 'provider');
            kindselect.dispatchEvent(new Event('change', {bubbles: true}));
        });

        const providerselect = host.querySelector('[data-region="mediaproviderinput"]') as HTMLSelectElement;
        expect(providerselect.tagName).toBe('SELECT');
        const options = Array.from(providerselect.querySelectorAll('option')).map((option) => option.value);
        expect(options).toEqual(['', 'youtube', 'vimeo']);

        await act(async() => {
            const setter = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value')?.set;
            setter?.call(providerselect, 'youtube');
            providerselect.dispatchEvent(new Event('change', {bubbles: true}));
        });
        const refinput = host.querySelector('[data-region="mediaproviderrefinput"]') as HTMLInputElement;
        await act(async() => {
            const setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')?.set;
            setter?.call(refinput, 'https://youtu.be/dQw4w9WgXcQ');
            refinput.dispatchEvent(new Event('input', {bubbles: true}));
        });
        const savebutton = host.querySelector('[data-action="savemedia"]') as HTMLButtonElement;
        await act(async() => {
            savebutton.click();
        });

        // The panel sends the raw reference; normalisation to the canonical
        // video id happens server-side in set_draft_media.
        expect(saves).toEqual([['provider', '', 'youtube', 'https://youtu.be/dQw4w9WgXcQ']]);
    });
});
