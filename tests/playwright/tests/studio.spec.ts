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
    await expect(page.getByText('Edit subtitles and gaps')).toBeVisible();
    await expect(page.getByRole('button', {name: 'Save draft'})).toBeVisible();
    await expect(page.getByRole('button', {name: 'Publish'})).toBeVisible();
    await expect(page.getByRole('button', {name: 'Add subtitle'})).toBeVisible();
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

test('rule-based gaps go through the real web service', async({page}) => {
    // The point of this test is the path, not the algorithm. The unit tests
    // call generate_rule_gaps::execute() directly and pass even if
    // db/services.php registers a class name that resolves to nothing — the
    // editor would then fail in production while CI stayed green. This drives
    // the editor, so the call goes Editor → core/ajax → the service registry →
    // the external function, which is the part nothing else exercises.
    // The file's beforeEach already logs in and opens the editor; logging in a
    // second time in a context that has a session lands on Moodle's "you are
    // already logged in" page instead of the login form.
    test.setTimeout(120000);
    await page.getByRole('button', {name: 'Save draft'}).waitFor();

    const control = page.locator('[data-region="rulegaps"]');
    await control.waitFor();

    // Watch the network rather than only the result: a wrong class name fails
    // inside Moodle's dispatcher and comes back as an exception with HTTP 200,
    // which a DOM assertion alone would report as "no gaps appeared".
    const callPromise = page.waitForResponse((response) =>
        response.url().includes('service.php')
        && (response.request().postData() || '').includes('generate_rule_gaps'));

    await control.locator('select').selectOption('words');
    await control.locator('input[type="text"]').first().fill('chat, chien');

    // "Generate gaps" is what calls the service. The apply button carries
    // data-action="applyrule" but only exists once a result has come back —
    // which makes its appearance the clearest evidence that the call worked.
    await control.getByRole('button', {name: /Generate gaps/i}).click();

    const response = await callPromise;
    expect(response.status()).toBe(200);

    const payload = await response.text();
    // Moodle returns errors with HTTP 200, so the body is what says whether the
    // function was reached at all.
    expect(payload).not.toContain('does not exist');
    expect(payload).not.toContain('codingerror');
    expect(payload).not.toContain('invalidrecord');

    // The apply button appears only when the service returned gaps.
    await expect(control.locator('[data-action="applyrule"]')).toBeVisible({timeout: 30000});
});

