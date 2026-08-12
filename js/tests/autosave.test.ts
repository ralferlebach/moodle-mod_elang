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
});
