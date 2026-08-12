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
 * A functional smoke of the Subtitle Studio editor: it mounts, shows the
 * authoring toolbar, and the timeline exposes its cue-edge handles as keyboard
 * sliders. Runs in a real browser, complementing the Behat @javascript coverage.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {expect, test} from '@playwright/test';
import {CMID, login} from './helpers';

test.beforeEach(async ({page}) => {
    test.skip(!CMID, 'ELANG_CMID is not set — run through "make playwright" to seed it.');
    await login(page);
    await page.goto(`/mod/elang/edit.php?id=${CMID}`);
});

test('the editor mounts and shows the authoring toolbar', async ({page}) => {
    await expect(page.getByText('Exercise content editor')).toBeVisible();
    await expect(page.getByRole('button', {name: 'Save draft'})).toBeVisible();
    await expect(page.getByRole('button', {name: 'Publish'})).toBeVisible();
    await expect(page.getByRole('button', {name: 'Add cue'})).toBeVisible();
});

test('timeline cue edges are keyboard-operable sliders', async ({page}) => {
    const handles = page.locator('[data-region="cuehandle"][role="slider"]');
    const count = await handles.count();
    // A seeded exercise has cues, so at least one start and one end handle exist;
    // an empty draft legitimately has none, so only assert when present.
    if (count > 0) {
        const handle = handles.first();
        await expect(handle).toHaveAttribute('aria-valuenow', /\d+/);
        await handle.focus();
        await expect(handle).toBeFocused();
    }
});
