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
 * A small debounced autosave controller.
 *
 * The editor calls markDirty() on every content change; the controller waits for
 * a quiet period, then runs the injected save function, coalescing a burst of
 * edits into one save and queuing exactly one more run if edits arrive while a
 * save is in flight. It reports its state so the UI can show "unsaved",
 * "saving…", "saved" or an error. Timers are injected so the state machine is
 * fully testable without real time.
 *
 * @module     mod_elang/studio/autosave
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** The observable states of the autosave controller. */
export type AutosaveState = 'idle' | 'dirty' | 'saving' | 'saved' | 'error';

export interface AutosaveOptions {
    /** Persist the current content; rejects on failure. */
    save: () => Promise<void>;
    /** Called whenever the state changes, for the UI. */
    onState: (state: AutosaveState) => void;
    /** Quiet period in milliseconds before a save runs (default 1500). */
    delayMs?: number;
    /** Timer scheduler, injected for testing (defaults to window.setTimeout). */
    setTimer?: (callback: () => void, ms: number) => number;
    /** Timer canceller, injected for testing (defaults to window.clearTimeout). */
    clearTimer?: (handle: number) => void;
}

export interface AutosaveController {
    /** Record that the content changed; schedules a save. */
    markDirty: () => void;
    /** Cancel any pending debounce and save immediately. */
    flush: () => Promise<void>;
    /** Cancel any pending save (e.g. on unmount). */
    cancel: () => void;
    /** The current state. */
    state: () => AutosaveState;
}

/**
 * Create an autosave controller.
 *
 * @param options The save function, state callback and optional timing hooks.
 * @returns The controller.
 */
export function createAutosave(options: AutosaveOptions): AutosaveController {
    const delayMs = options.delayMs ?? 1500;
    const setTimer = options.setTimer ?? ((callback, ms) => window.setTimeout(callback, ms));
    const clearTimer = options.clearTimer ?? ((handle) => window.clearTimeout(handle));

    let state: AutosaveState = 'idle';
    let handle: number | null = null;
    let saving = false;
    let queued = false;

    const setState = (next: AutosaveState): void => {
        state = next;
        options.onState(next);
    };

    const run = async(): Promise<void> => {
        handle = null;
        if (saving) {
            // A save is already in flight; remember that fresh edits arrived so
            // exactly one more save runs when it finishes.
            queued = true;
            return;
        }
        saving = true;
        setState('saving');
        try {
            await options.save();
            saving = false;
            if (queued) {
                queued = false;
                setState('dirty');
                await run();
                return;
            }
            setState('saved');
        } catch (error) {
            saving = false;
            queued = false;
            setState('error');
        }
    };

    const markDirty = (): void => {
        if (state !== 'saving') {
            setState('dirty');
        } else {
            queued = true;
        }
        if (handle !== null) {
            clearTimer(handle);
        }
        handle = setTimer(() => {
            void run();
        }, delayMs);
    };

    const flush = async(): Promise<void> => {
        if (handle !== null) {
            clearTimer(handle);
            handle = null;
        }
        await run();
    };

    const cancel = (): void => {
        if (handle !== null) {
            clearTimer(handle);
            handle = null;
        }
        queued = false;
    };

    return {markDirty, flush, cancel, state: () => state};
}
