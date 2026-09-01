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
 * Tests for the autosave controller: debounced coalescing of edits, correct
 * state transitions, error handling and a single follow-up save when edits
 * arrive mid-flight. Timers are driven manually so no real time passes.
 *
 * @module     mod_elang/tests/autosave
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {AutosaveState, createAutosave} from '../src/studio/autosave';

/** A manual timer queue so debounce can be advanced deterministically. */
function fakeTimers() {
    const pending = new Map<number, () => void>();
    let nextId = 1;
    return {
        setTimer: (callback: () => void): number => {
            const id = nextId++;
            pending.set(id, callback);
            return id;
        },
        clearTimer: (id: number): void => {
            pending.delete(id);
        },
        runAll: (): void => {
            const callbacks = Array.from(pending.values());
            pending.clear();
            callbacks.forEach((callback) => callback());
        },
        count: (): number => pending.size,
    };
}

describe('createAutosave', () => {
    it('coalesces a burst of edits into a single save', async() => {
        const timers = fakeTimers();
        const states: AutosaveState[] = [];
        let saves = 0;
        const controller = createAutosave({
            save: async() => {
                saves++;
            },
            onState: (state) => states.push(state),
            setTimer: timers.setTimer,
            clearTimer: timers.clearTimer,
        });

        controller.markDirty();
        controller.markDirty();
        controller.markDirty();
        // Only the last debounce timer should survive.
        expect(timers.count()).toBe(1);

        timers.runAll();
        await Promise.resolve();
        await Promise.resolve();

        expect(saves).toBe(1);
        expect(states).toContain('dirty');
        expect(states).toContain('saving');
        expect(states[states.length - 1]).toBe('saved');
    });

    it('reports an error state when saving fails', async() => {
        const timers = fakeTimers();
        const states: AutosaveState[] = [];
        const controller = createAutosave({
            save: async() => {
                throw new Error('nope');
            },
            onState: (state) => states.push(state),
            setTimer: timers.setTimer,
            clearTimer: timers.clearTimer,
        });

        controller.markDirty();
        timers.runAll();
        await Promise.resolve();
        await Promise.resolve();

        expect(states[states.length - 1]).toBe('error');
        expect(controller.state()).toBe('error');
    });

    it('flush saves immediately without waiting for the debounce', async() => {
        const timers = fakeTimers();
        let saves = 0;
        const controller = createAutosave({
            save: async() => {
                saves++;
            },
            onState: () => undefined,
            setTimer: timers.setTimer,
            clearTimer: timers.clearTimer,
        });

        controller.markDirty();
        await controller.flush();

        expect(saves).toBe(1);
        // The pending debounce timer was cancelled by the flush.
        expect(timers.count()).toBe(0);
    });

    it('recovers after a failed save instead of jamming', async() => {
        // A save that rejects must release the in-flight flag. Left set, the
        // controller would treat every later attempt as "already saving" and
        // queue it forever — the author would see "error" and never get out of
        // it, with no indication that further edits were going nowhere.
        const timers = fakeTimers();
        const states: AutosaveState[] = [];
        let attempts = 0;
        const controller = createAutosave({
            save: async() => {
                attempts++;
                if (attempts === 1) {
                    throw new Error('network down');
                }
            },
            onState: (state) => states.push(state),
            setTimer: timers.setTimer,
            clearTimer: timers.clearTimer,
        });

        controller.markDirty();
        timers.runAll();
        await Promise.resolve();
        await Promise.resolve();
        expect(states).toContain('error');

        // The very next edit gets a real attempt, and reaches saved.
        controller.markDirty();
        timers.runAll();
        await Promise.resolve();
        await Promise.resolve();

        expect(attempts).toBe(2);
        expect(states[states.length - 1]).toBe('saved');
    });

    it('a failed save leaves nothing queued, so a later flush saves once', async() => {
        // Edits that arrived while a failing save was in flight are not
        // replayed automatically: the failure is shown, and the next edit or an
        // explicit save carries the current content. What must not happen is a
        // stale queue flag turning one later flush into two saves.
        const timers = fakeTimers();
        const pending: Array<{resolve: () => void; reject: (error: Error) => void}> = [];
        let saves = 0;
        const controller = createAutosave({
            save: () => {
                saves++;
                return new Promise<void>((resolve, reject) => {
                    pending.push({resolve, reject});
                });
            },
            onState: () => undefined,
            setTimer: timers.setTimer,
            clearTimer: timers.clearTimer,
        });

        controller.markDirty();
        timers.runAll();
        expect(saves).toBe(1);

        // An edit lands while that first save is still in flight.
        controller.markDirty();

        pending[0].reject(new Error('rejected'));
        await Promise.resolve();
        await Promise.resolve();

        // One explicit save, one call — not two.
        const flushed = controller.flush();
        expect(saves).toBe(2);
        pending[1].resolve();
        await flushed;
    });

    it('an edit during a save produces exactly one more save', async() => {
        // The content of that extra save is read when it runs, so a single
        // follow-up carries everything typed in the meantime. Two would write
        // the same state twice and bump the draft revision for nothing.
        const timers = fakeTimers();
        let saves = 0;
        const controller = createAutosave({
            save: async() => {
                saves++;
            },
            onState: () => undefined,
            setTimer: timers.setTimer,
            clearTimer: timers.clearTimer,
        });

        controller.markDirty();
        timers.runAll();
        controller.markDirty();
        controller.markDirty();
        await Promise.resolve();
        await Promise.resolve();
        await Promise.resolve();

        expect(saves).toBe(2);
    });

    it('cancel stops a pending save from ever running', async() => {
        // Used when the editor unmounts: a save firing into a page that has
        // gone would report its state to a component nobody is looking at, and
        // could overwrite a draft the author has since reopened elsewhere.
        const timers = fakeTimers();
        let saves = 0;
        const controller = createAutosave({
            save: async() => {
                saves++;
            },
            onState: () => undefined,
            setTimer: timers.setTimer,
            clearTimer: timers.clearTimer,
        });

        controller.markDirty();
        controller.cancel();
        timers.runAll();
        await Promise.resolve();

        expect(saves).toBe(0);
        expect(timers.count()).toBe(0);
    });
});
