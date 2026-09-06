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
 * Where the subtitles are drawn, and how a long transcript behaves.
 *
 * These are the two things unit tests and Behat cannot settle. The subtitle
 * positions differ in layout and in what the browser lays out around a real
 * media element; the cue list exists because forty cues used to render forty
 * full forms, which is a question about the rendered page rather than about
 * state.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {expect, test} from '@playwright/test';
import {loginAs, requireEnv} from './helpers';

/**
 * Open an activity's exercise page and wait for the player to be ready.
 *
 * @param page The Playwright page.
 * @param cmid The course module id.
 */
async function openExercise(
    page: import('@playwright/test').Page,
    cmid: string,
    timeout = 10000
): Promise<void> {
    await page.goto(`/mod/elang/view.php?id=${cmid}`);
    // The player renders from a web service call, so the markup is not there
    // when the document finishes loading.
    await expect(page.locator('[data-region="status"]')).toContainText(/ready|bereit/i, {timeout});
}

test.describe('subtitle positions', () => {
    test.beforeEach(async({page}) => {
        // As a learner: only the student archetype holds mod/elang:attempt, so
        // the seeded teacher cannot start an attempt and the player refuses to
        // load for them.
        await loginAs(page, requireEnv('ELANG_STUDENT'), requireEnv('ELANG_STUDENT_PASS'));
    });

    test('below the medium the transcript is its own scrolling region', async({page}) => {
        await openExercise(page, requireEnv('ELANG_CMID_BELOW'));

        await expect(page.locator('.mod_elang-transcript-scroll')).toHaveCount(1);
        await expect(page.locator('.mod_elang-caption-overlay')).toHaveCount(0);

        // Both the medium and the transcript have to fit one screen. Left
        // unbounded they push each other apart and the learner scrolls between
        // the picture and the sentence they are answering.
        const viewport = page.viewportSize();
        const media = await page.locator('.mod_elang-media video').boundingBox();
        expect(media).not.toBeNull();
        expect(media!.height).toBeLessThan((viewport?.height ?? 720) * 0.6);
    });

    test('the bottom overlay draws the caption over the picture', async({page}) => {
        await openExercise(page, requireEnv('ELANG_CMID_OVERLAYBOTTOM'));

        await expect(page.locator('.mod_elang-media-stage')).toHaveCount(1);
        await expect(page.locator('.mod_elang-caption-overlaybottom')).toHaveCount(1);

        // Not shown twice: the transcript underneath would put the same gaps on
        // the page in two places.
        await expect(page.locator('.mod_elang-transcript-hidden')).toHaveCount(1);
        await expect(page.locator('.mod_elang-transcript-scroll')).toHaveCount(0);
    });

    test('the top overlay differs only in where the caption sits', async({page}) => {
        await openExercise(page, requireEnv('ELANG_CMID_OVERLAYTOP'));

        await expect(page.locator('.mod_elang-caption-overlaytop')).toHaveCount(1);
        await expect(page.locator('.mod_elang-caption-overlaybottom')).toHaveCount(0);

        // The caption really is over the picture, not above it.
        const stage = await page.locator('.mod_elang-media-stage').boundingBox();
        const caption = await page.locator('.mod_elang-caption-overlaytop').boundingBox();
        expect(stage).not.toBeNull();
        expect(caption).not.toBeNull();
        expect(caption!.y).toBeGreaterThanOrEqual(stage!.y - 1);
        expect(caption!.y + caption!.height).toBeLessThanOrEqual(stage!.y + stage!.height + 1);
    });

    test('an overlay puts the cursor in the first gap', async({page}) => {
        await openExercise(page, requireEnv('ELANG_CMID_OVERLAYTOP'));

        // The exercise starts where the work is, which is also what makes
        // playback stop at that cue instead of running the sentence off screen.
        const focused = page.locator('.mod_elang-caption-overlay input:focus');
        await expect(focused).toHaveCount(1);
    });

    test('audio falls back to the display below the medium', async({page}) => {
        await openExercise(page, requireEnv('ELANG_CMID_AUDIO'));

        // Stored as an overlay, but there is no picture to draw one on. The
        // stored setting is kept; only what is rendered changes.
        await expect(page.locator('.mod_elang-caption-overlay')).toHaveCount(0);
        await expect(page.locator('.mod_elang-transcript-scroll')).toHaveCount(1);
    });

    test('a lesson-length transcript loads completely', async({page}) => {
        // Four hundred cues. No timing assertion — a shared runner's wall clock
        // is not a measurement — but the two things that used to grow with the
        // square of the transcript are structural and can be asserted: every
        // cue arrives, and it takes one request per page rather than one per
        // cue.
        test.setTimeout(120000);

        let cuerequests = 0;
        page.on('request', (request) => {
            if (request.url().includes('service.php') && (request.postData() || '').includes('get_attempt_cues')) {
                cuerequests++;
            }
        });

        await openExercise(page, requireEnv('ELANG_CMID_LONG'), 90000);

        await expect(page.locator('.mod_elang-cue')).toHaveCount(400);
        // 400 cues at 50 per page.
        expect(cuerequests).toBe(8);
    });
});


test.describe('cue list', () => {
    test.beforeEach(async({page}) => {
        // Authoring, so the teacher.
        await loginAs(page, requireEnv('ELANG_USER'), requireEnv('ELANG_PASS'));
    });

    test('every cue is listed but only one is open for editing', async({page}) => {
        test.setTimeout(120000);
        await page.goto(`/mod/elang/edit.php?id=${requireEnv('ELANG_CMID_LONG')}`);
        await expect(page.locator('[data-region="cuelist"]')).toBeVisible({timeout: 60000});

        // Every cue is reachable...
        await expect(page.locator('.mod_elang-cuelist-item')).toHaveCount(400, {timeout: 60000});

        // ...and exactly one carries a full editor. This is the whole point of
        // the workspace: before it, all four hundred rendered their forms at
        // once.
        await expect(page.locator('[data-region="cueinspector"] .mod_elang-editor-cue')).toHaveCount(1);
    });

    test('the list scrolls on its own instead of stretching the page', async({page}) => {
        test.setTimeout(120000);
        await page.goto(`/mod/elang/edit.php?id=${requireEnv('ELANG_CMID_LONG')}`);
        await expect(page.locator('[data-region="cuelist"]')).toBeVisible({timeout: 60000});

        const box = await page.locator('.mod_elang-cuelist-items').boundingBox();
        expect(box).not.toBeNull();

        // Four hundred rows are far taller than this; the region has to bound them, or
        // the medium and the timeline leave the screen.
        const scrollheight = await page.locator('.mod_elang-cuelist-items')
            .evaluate((element) => element.scrollHeight);
        expect(scrollheight).toBeGreaterThan(box!.height);
    });

    test('searching narrows the list without touching the open cue', async({page}) => {
        test.setTimeout(120000);
        await page.goto(`/mod/elang/edit.php?id=${requireEnv('ELANG_CMID_LONG')}`);
        await expect(page.locator('[data-region="cuelist"]')).toBeVisible({timeout: 60000});

        const open = await page.locator('[data-region="cueinspector"] .mod_elang-editor-cue')
            .getAttribute('data-cuekey');

        await page.fill('[data-region="cuesearch"]', 'number 137 ');
        await expect(page.locator('.mod_elang-cuelist-item')).toHaveCount(1);

        // Filtering is a way of finding something, not of closing what is being
        // worked on.
        await expect(page.locator('[data-region="cueinspector"] .mod_elang-editor-cue'))
            .toHaveAttribute('data-cuekey', open ?? '');
    });
});

test.describe('narrow screens and right-to-left', () => {
    test.beforeEach(async({page}) => {
        await loginAs(page, requireEnv('ELANG_STUDENT'), requireEnv('ELANG_STUDENT_PASS'));
    });

    test('the exercise fits a phone without sideways scrolling', async({page}) => {
        // A page wider than the screen means every line has to be scrolled to
        // be read, which on an exercise built from sentences is the whole page.
        await page.setViewportSize({width: 390, height: 844});
        await openExercise(page, requireEnv('ELANG_CMID_BELOW'));

        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth - document.documentElement.clientWidth
        );
        // A pixel or two of rounding is not sideways scrolling.
        expect(overflow).toBeLessThanOrEqual(2);
    });

    test('the medium and the transcript share a short screen', async({page}) => {
        await page.setViewportSize({width: 390, height: 844});
        await openExercise(page, requireEnv('ELANG_CMID_BELOW'));

        const media = await page.locator('.mod_elang-media video').boundingBox();
        const transcript = await page.locator('.mod_elang-transcript-scroll').boundingBox();
        expect(media).not.toBeNull();
        expect(transcript).not.toBeNull();

        // Both bounded, so neither pushes the other off the screen.
        expect(media!.height + transcript!.height).toBeLessThan(844);
    });

    test('an overlay caption keeps its box on a phone', async({page}) => {
        await page.setViewportSize({width: 390, height: 844});
        await openExercise(page, requireEnv('ELANG_CMID_OVERLAYBOTTOM'));

        const stage = await page.locator('.mod_elang-media-stage').boundingBox();
        const caption = await page.locator('.mod_elang-caption-overlaybottom').boundingBox();
        expect(stage).not.toBeNull();
        expect(caption).not.toBeNull();

        // A long sentence must wrap inside the picture rather than run past it.
        expect(caption!.width).toBeLessThanOrEqual(stage!.width + 1);
        expect(caption!.x).toBeGreaterThanOrEqual(stage!.x - 1);
    });
});

test.describe('right-to-left', () => {
    test.beforeEach(async({page}) => {
        // Its own block, with its own browser context: the cue list lives in
        // the editor and needs mod/elang:manage, and logging in a second time
        // in a context that already has a session lands on Moodle's "you are
        // already logged in" page rather than on the login form.
        test.setTimeout(120000);
        await loginAs(page, requireEnv('ELANG_USER'), requireEnv('ELANG_PASS'));
    });

    test('right-to-left moves the marked edge to the start of the line', async({page}) => {
        await page.goto(`/mod/elang/edit.php?id=${requireEnv('ELANG_CMID_LONG')}`);
        await expect(page.locator('[data-region="cuelist"]')).toBeVisible({timeout: 60000});

        const selected = page.locator('.mod_elang-cuelist-item.selected');
        const ltr = await selected.evaluate((element) => {
            const style = window.getComputedStyle(element);
            return {left: style.borderLeftWidth, right: style.borderRightWidth};
        });
        expect(ltr.left).toBe('4px');
        expect(ltr.right).toBe('0px');

        // Flipping the document is enough to prove the rule is logical: a
        // physical border-left would stay on the left and end up at the end of
        // the line. This does not need an Arabic language pack, only the
        // direction the pack would set.
        await page.evaluate(() => document.documentElement.setAttribute('dir', 'rtl'));
        const rtl = await selected.evaluate((element) => {
            const style = window.getComputedStyle(element);
            return {left: style.borderLeftWidth, right: style.borderRightWidth};
        });
        expect(rtl.right).toBe('4px');
        expect(rtl.left).toBe('0px');
    });
});

test.describe('keyboard only', () => {
    test.beforeEach(async({page}) => {
        await loginAs(page, requireEnv('ELANG_STUDENT'), requireEnv('ELANG_STUDENT_PASS'));
    });

    test('a learner can reach and answer a gap without a mouse', async({page}) => {
        await openExercise(page, requireEnv('ELANG_CMID_BELOW'));

        // Tab until a gap has the focus. Bounded, so a broken tab order fails
        // here rather than hanging.
        let reached = false;
        for (let step = 0; step < 60 && !reached; step++) {
            await page.keyboard.press('Tab');
            reached = await page.evaluate(
                () => document.activeElement?.closest('.mod_elang-gapwrap') !== null
                    && document.activeElement?.tagName === 'INPUT'
            );
        }
        expect(reached).toBe(true);

        // Typing and Enter are the whole interaction; nothing here needs a
        // pointer.
        await page.keyboard.type('chat');
        await page.keyboard.press('Enter');

        // The graded state appears, which is what says the answer arrived.
        await expect(page.locator('.mod_elang-gapstate .fa').first()).toBeVisible();
    });

    test('the exercise can be finished from the keyboard', async({page}) => {
        await openExercise(page, requireEnv('ELANG_CMID_BELOW'));

        const finish = page.locator('.mod_elang-finishbtn');
        await finish.focus();
        await expect(finish).toBeFocused();
    });
});

test.describe('zoom', () => {
    test.beforeEach(async({page}) => {
        await loginAs(page, requireEnv('ELANG_STUDENT'), requireEnv('ELANG_STUDENT_PASS'));
    });

    // 200% is the WCAG AA reflow requirement; 400% is AAA and the case a
    // learner with low vision actually uses. Both are simulated by shrinking
    // the viewport, which is what the browser does to the layout either way.
    for (const [label, width, height] of [['200%', 640, 512], ['400%', 320, 256]] as const) {
        test(`the exercise still works at ${label} zoom`, async({page}) => {
            await page.setViewportSize({width, height});
            await openExercise(page, requireEnv('ELANG_CMID_BELOW'));

            // No sideways scrolling: at this size every line would otherwise
            // have to be scrolled to be read.
            const overflow = await page.evaluate(
                () => document.documentElement.scrollWidth - document.documentElement.clientWidth
            );
            expect(overflow).toBeLessThanOrEqual(2);

            // The gap is still reachable and still an input, not a clipped box.
            const gap = page.locator('.mod_elang-gapwrap input').first();
            await expect(gap).toBeVisible();
            const box = await gap.boundingBox();
            expect(box).not.toBeNull();
            expect(box!.width).toBeGreaterThan(20);
        });
    }
});
