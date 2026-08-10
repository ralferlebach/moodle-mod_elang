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
 * Editor entry point.
 *
 * `mount(element, config)` is the single, stable embedding contract: the host
 * supplies a DOM element and configuration. The persistence transport and the
 * string resolver are injectable, so the same editor works against Moodle's
 * core/ajax in production and against in-memory stubs in tests.
 *
 * @module     mod_elang/mount
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {createRoot} from 'react-dom/client';
import {ApiClient} from './api/service';
import {EditorApp} from './components/EditorApp';
import {MountConfig, ServiceTransport} from './types';

/**
 * Resolve a string from Moodle's M.str store, falling back to the raw key so
 * a missing language string is visible instead of silently blank.
 *
 * @param key The editor string key.
 * @returns The resolved string.
 */
function resolveString(key: string): string {
    const moodle = window as unknown as {M?: {str?: {mod_elang?: Record<string, string>}}};
    const store = moodle.M?.str?.mod_elang;
    if (store && store[key] !== undefined) {
        return store[key];
    }
    return key;
}

/**
 * A last-resort transport for standalone use outside Moodle's AMD loader; the
 * production path always injects a core/ajax-bound transport instead.
 *
 * @returns A transport that rejects every call.
 */
function unavailableTransport(): ServiceTransport {
    return (methodname: string): Promise<unknown> => Promise.reject(new Error('No transport for ' + methodname));
}

/**
 * Mount the editor into the given element.
 *
 * @param element The container element.
 * @param config The mount configuration.
 */
export function mount(element: HTMLElement, config: MountConfig): void {
    const transport = config.callService ?? unavailableTransport();
    const api = new ApiClient(transport, config.versionid);
    const provided = config.getString;
    const t = provided ? (key: string): string => provided(key) ?? resolveString(key) : resolveString;

    const root = createRoot(element);
    root.render(<EditorApp api={api} t={t} mediauploadurl={config.mediauploadurl ?? ''} />);
}

export default {mount};
