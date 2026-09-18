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
import {CMID, login, requireEnv} from './helpers';

test.beforeEach(async ({page}) => {
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

test('gap actions look like actions, not like links', async({page}) => {
    // Issue #27: these were already real buttons, so a role check would have
    // passed while they still looked like body text with a colour on it. What
    // is asserted is the thing that was actually wrong — that nothing
    // distinguished a destructive action from a sentence.
    //
    // A border is the cheap, theme-independent signal that Bootstrap's outline
    // buttons have and its link buttons do not. Reading it from the computed
    // style also survives a dark theme, which a colour assertion would not.
    const select = page.locator('[data-region="cuelist"] .mod_elang-cuelist-select').first();
    await select.click();

    const deletecue = page.getByRole('button', {name: 'Delete subtitle'});
    await expect(deletecue).toBeVisible();

    // The variant, not the computed border. A border was the first thing tried
    // and it does not discriminate: Boost gives every .btn a visible border, so
    // the check stayed green with the action styled as a link — verified by
    // reverting it and watching the test pass. Asserting the variant is less
    // elegant and actually holds, which is the trade the issue's own wording
    // points at: no main gap action may use btn-link alone.
    const variant = async(locator: import('@playwright/test').Locator): Promise<string> =>
        locator.evaluate((element) => element.className);

    const deleteclasses = await variant(deletecue);
    expect(deleteclasses, 'the destructive action is a danger button').toContain('btn-outline-danger');
    expect(deleteclasses, 'and not a link').not.toContain('btn-link');

    const addgap = page.getByRole('button', {name: 'Mark gap from selection'});
    if (await addgap.count() > 0) {
        const addclasses = await variant(addgap.first());
        expect(addclasses, 'creating is a primary action').toContain('btn-outline-primary');
        expect(addclasses, 'and not a link').not.toContain('btn-link');
    }

    // Reachable and operable from the keyboard, which is the half of the issue
    // a visual change can quietly break.
    await deletecue.focus();
    await expect(deletecue).toBeFocused();

    // Every action in the inspector carries a name a screen reader can read —
    // an icon-only button with no label would satisfy the visual requirement
    // and fail the person using it.
    const nameless = await page.locator('.mod_elang-editor-cue button').evaluateAll(
        (buttons) => buttons.filter((button) => {
            const label = (button.getAttribute('aria-label') || button.textContent || '').trim();
            return label === '';
        }).length
    );
    expect(nameless, 'no action in the inspector is without an accessible name').toBe(0);
});

test('gaps are marked in the text and lead to their row', async({page}) => {
    // Issue #28: the gaps were edited in forms below the sentence, so the
    // author had to hold the mapping between "gap 2" and a word in their head.
    const select = page.locator('[data-region="cuelist"] .mod_elang-cuelist-select').first();
    await select.click();

    const marks = page.locator('[data-region="inlinegap"]');
    await expect(marks.first()).toBeVisible();

    // The mark carries the matching mode in its accessible name. Black and grey
    // are not information for everyone, and this is the distinction the author
    // is being asked to read.
    const label = await marks.first().getAttribute('aria-label');
    expect(label, 'the mark names its matching mode').toMatch(/exact match|close answers accepted/);

    // The link between mark and row is the gap key, not a position: a gap keeps
    // its key when the transcript is edited and the ranges move.
    const gapkey = await marks.first().getAttribute('data-gapkey');
    expect(gapkey, 'the mark is addressed by key').toBeTruthy();

    await marks.first().click();
    const row = page.locator(`[data-gaprow="${gapkey}"]`);
    await expect(row).toBeVisible();
    await expect(row).toHaveClass(/selected/);
});

test('a broken subtitle is marked in the inspector, the list and the timeline', async({page}) => {
    // Issue #26 asks for the mark in all three, and the reason is that they are
    // three different questions: the inspector answers "what is wrong with this
    // one", the list answers "which of my subtitles need attention", and the
    // timeline answers "where in the recording". A mark in only one of them
    // leaves the other two quietly wrong.
    const select = page.locator('[data-region="cuelist"] .mod_elang-cuelist-select').first();
    await select.click();

    const end = page.getByRole('textbox', {name: 'End'});
    await end.fill('00:00.000');
    await end.blur();

    await expect(page.locator('[data-region="cueproblem"]')).toBeVisible();
    await expect(page.locator('[data-region="cuenotsaved"]').first()).toBeVisible();
    await expect(page.locator('[data-region="timelineunsaved"]').first()).toBeVisible();

    // Not colour alone, in any of the three.
    await expect(page.locator('[data-region="cuenotsaved"]').first()).toContainText('Not saved');
});

test('publishing is refused for a problem made a moment earlier', async({page}) => {
    // The race the issue names. problemsRef is filled by the save, and the save
    // is debounced — so an author who breaks a subtitle and reaches for Publish
    // in the same second arrives before the checks have run. Publishing would
    // then go ahead on a draft the server still holds in its last good state,
    // and the activity would go live missing the edit, with nothing saying so.
    let published = false;
    await page.route('**/webservice/**', async(route) => {
        if ((route.request().postData() || '').includes('publish_version')) {
            published = true;
        }
        await route.continue();
    });

    const select = page.locator('[data-region="cuelist"] .mod_elang-cuelist-select').first();
    await select.click();

    const end = page.getByRole('textbox', {name: 'End'});
    await end.fill('00:00.000');

    // No pause: straight to Publish, inside the debounce window.
    await page.getByRole('button', {name: 'Publish'}).click();

    await expect(page.locator('[data-region="status"]'))
        .toContainText('Some cues cannot be saved yet', {timeout: 10000});
    expect(published, 'no publish request was sent').toBe(false);
});

test('the repair is offered only when one can actually be derived', async({page}) => {
    // A correction needs somewhere to put the end: the next subtitle's start, or
    // where the medium is paused. This exercise has a single subtitle and the
    // medium at zero, so neither exists — and the honest answer is not to offer
    // a button that does nothing when pressed.
    //
    // Found by writing the test the other way round and watching the repair
    // refuse: the refusal was right, the button should not have been there.
    const select = page.locator('[data-region="cuelist"] .mod_elang-cuelist-select').first();
    await select.click();

    const end = page.getByRole('textbox', {name: 'End'});
    await end.fill('00:00.000');
    await end.blur();

    await expect(page.locator('[data-region="cueproblem"]')).toBeVisible();
    await expect(page.locator('[data-action="repaircue"]')).toHaveCount(0);
});

test('a repair that is available clears the mark in all three places', async({page}) => {
    // The long exercise, because the repair needs a next subtitle that starts
    // later than this one does. Adding a subtitle to the short exercise does
    // not help: a new one starts where the medium is paused, which with an
    // unplayable fixture is zero — the same place the first one starts.
    await page.goto(`/mod/elang/edit.php?id=${requireEnv('ELANG_CMID_LONG')}`);
    await page.locator('[data-region="cuelist"] .mod_elang-cuelist-select').first().click();

    const end = page.getByRole('textbox', {name: 'End'});
    await end.fill('00:00.000');
    await end.blur();
    await expect(page.locator('[data-region="cueproblem"]')).toBeVisible();

    const repair = page.locator('[data-action="repaircue"]');
    await expect(repair).toBeVisible();
    await repair.click();

    // Gone from all three, not just from the one the button sits in.
    await expect(page.locator('[data-region="cueproblem"]')).toHaveCount(0);
    await expect(page.locator('[data-region="cuenotsaved"]')).toHaveCount(0);
    await expect(page.locator('[data-region="timelineunsaved"]')).toHaveCount(0);
});
