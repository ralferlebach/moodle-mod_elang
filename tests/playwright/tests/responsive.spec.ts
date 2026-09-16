/**
 * The exercise fits the screen it is given.
 *
 * Issue #23: heights were bounded in viewport units, which cannot see the page
 * they sit in. Above the player there is a Moodle header, a secondary
 * navigation, the activity name, the intro and whatever a theme adds, so
 * `45vh + 35vh` left the medium and the sentence being answered on separate
 * screens of a 1366×768 laptop — the one thing the below-the-medium layout
 * exists to prevent. On a tall screen the same numbers left a band of empty
 * page.
 *
 * What is asserted here is that claim and not the numbers behind it: the
 * medium and the first gap are visible together, without scrolling, in every
 * subtitle position and at every screen size in the matrix. A test that checked
 * the computed pixel heights would pass while the layout was wrong, because
 * those are the implementation and this is the promise.
 *
 * The sizes are chosen for what each one breaks rather than for coverage:
 *
 *   1366×768   the common institutional laptop, and the case from the issue
 *   1024×600   a netbook — the smallest height still in classroom use
 *   1280×1440  tall and narrow, where fixed vh left empty page
 *   1920×1080  a desktop, the case that always worked
 *   768×1024   a tablet held upright
 *   390×844    a phone, where almost nothing fits and the priority has to show
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {expect, test} from '@playwright/test';
import {loginAs, requireEnv} from './helpers';

const SIZES = [
    {name: 'laptop 1366x768', width: 1366, height: 768},
    {name: 'netbook 1024x600', width: 1024, height: 600},
    {name: 'tall 1280x1440', width: 1280, height: 1440},
    {name: 'desktop 1920x1080', width: 1920, height: 1080},
    {name: 'tablet 768x1024', width: 768, height: 1024},
    {name: 'phone 390x844', width: 390, height: 844},
] as const;

const MODES = [
    {name: 'below', env: 'ELANG_CMID_BELOW'},
    {name: 'overlay top', env: 'ELANG_CMID_OVERLAYTOP'},
    {name: 'overlay bottom', env: 'ELANG_CMID_OVERLAYBOTTOM'},
] as const;

/**
 * Whether an element lies inside the viewport, top and bottom.
 *
 * `toBeInViewport` would do for one element, but the claim here is about two of
 * them at once and about the same scroll position, so the rectangles are read
 * together.
 */
async function boxOf(
    page: import('@playwright/test').Page,
    selector: string
): Promise<{top: number; bottom: number; height: number} | null> {
    return page.evaluate((sel) => {
        const element = document.querySelector(sel);
        if (!element) {
            return null;
        }
        const rect = element.getBoundingClientRect();

        return {top: rect.top, bottom: rect.bottom, height: rect.height};
    }, selector);
}

test.describe('the exercise fits the screen', () => {
    test.beforeEach(async({page}) => {
        await loginAs(page, requireEnv('ELANG_STUDENT'), requireEnv('ELANG_STUDENT_PASS'));
    });

    for (const size of SIZES) {
        for (const mode of MODES) {
            test(`${mode.name} at ${size.name}: medium and first gap are visible together`, async({page}) => {
                await page.setViewportSize({width: size.width, height: size.height});
                await page.goto(`/mod/elang/view.php?id=${requireEnv(mode.env)}`);
                await expect(page.locator('[data-region="status"]'))
                    .toContainText(/ready|bereit/i, {timeout: 15000});

                // Settle: the heights are published from a requestAnimationFrame
                // and again from a ResizeObserver, so the first paint is not the
                // final one.
                await page.waitForTimeout(500);

                const medium = await boxOf(page, '[data-region="media"] video, [data-region="media"] audio');
                expect(medium, 'the medium is rendered').not.toBeNull();

                const gap = await boxOf(page, 'input.mod_elang-gap');
                expect(gap, 'at least one gap is rendered').not.toBeNull();

                if (medium === null || gap === null) {
                    return;
                }

                // The promise of the layout, stated as the learner would notice
                // it breaking: both things are on the screen at the same time.
                expect(
                    medium.top,
                    `the medium starts above the fold (top ${medium.top})`
                ).toBeLessThan(size.height);
                expect(
                    gap.bottom,
                    `the first gap ends within the viewport (bottom ${gap.bottom}, viewport ${size.height})`
                ).toBeLessThanOrEqual(size.height);
                expect(
                    gap.top,
                    `the first gap is not scrolled off the top (top ${gap.top})`
                ).toBeGreaterThanOrEqual(0);
            });
        }
    }

    test('below the medium, a short screen gives the picture up before the transcript', async({page}) => {
        // The rule the issue asks for, and the one that inverts the old
        // behaviour: when there is not enough room for both, the transcript is
        // served first. It is where the answering happens; the medium can be
        // small and still be watchable.
        await page.setViewportSize({width: 1024, height: 600});
        await page.goto(`/mod/elang/view.php?id=${requireEnv('ELANG_CMID_BELOW')}`);
        await expect(page.locator('[data-region="status"]'))
            .toContainText(/ready|bereit/i, {timeout: 15000});
        await page.waitForTimeout(500);

        await expect(page.locator('.mod_elang-transcript-scroll')).toBeVisible();

        // What is asserted is the room the transcript is *allowed*, not the
        // height it happens to render at. The stylesheet caps it with
        // max-height, so a short transcript is a short box — correctly, since a
        // tall empty region would be worse than a small full one. The promise
        // is that the allowance never falls below the floor, which is what a
        // long transcript would then be able to use.
        const allowance = await page.evaluate(() => {
            const player = document.querySelector('[data-region="mod_elang/player"]') as HTMLElement | null;

            return player
                ? getComputedStyle(player).getPropertyValue('--mod-elang-transcript-height').trim()
                : '';
        });

        expect(allowance, 'the transcript allowance was published').toMatch(/^\d+(\.\d+)?px$/);
        expect(
            parseFloat(allowance),
            `the transcript keeps its floor (allowance ${allowance})`
        ).toBeGreaterThanOrEqual(140);
    });

    test('a tall screen is used rather than left empty', async({page}) => {
        // The other half of the issue, and the half a "does it fit" test would
        // miss entirely: fixed viewport units also left room unused.
        await page.setViewportSize({width: 1280, height: 1440});
        await page.goto(`/mod/elang/view.php?id=${requireEnv('ELANG_CMID_BELOW')}`);
        await expect(page.locator('[data-region="status"]'))
            .toContainText(/ready|bereit/i, {timeout: 15000});
        await page.waitForTimeout(500);

        const published = await page.evaluate(() => {
            const player = document.querySelector('[data-region="mod_elang/player"]') as HTMLElement | null;
            if (!player) {
                return null;
            }
            const styles = getComputedStyle(player);

            return {
                available: styles.getPropertyValue('--mod-elang-available-height').trim(),
                media: styles.getPropertyValue('--mod-elang-media-height').trim(),
                transcript: styles.getPropertyValue('--mod-elang-transcript-height').trim(),
            };
        });

        expect(published, 'the player publishes its measurements').not.toBeNull();
        if (published === null) {
            return;
        }

        // Measured, not guessed: on a 1440-pixel screen the old rule would have
        // capped the medium at 45vh = 648px whatever the page above it looked
        // like. The published value is derived from where the player actually
        // starts, so it answers for this page rather than for a fraction.
        expect(published.available, 'available height was measured').toMatch(/^\d+(\.\d+)?px$/);
        expect(published.media, 'the medium was given a measured height').toMatch(/^\d+(\.\d+)?px$/);
        expect(published.transcript, 'the transcript was given a measured height').toMatch(/^\d+(\.\d+)?px$/);

        const available = parseFloat(published.available);
        const media = parseFloat(published.media);
        const transcript = parseFloat(published.transcript);

        expect(available, 'a tall screen yields more room than a short one would').toBeGreaterThan(600);
        expect(
            media + transcript,
            'the two together stay within what was measured'
        ).toBeLessThanOrEqual(available + 1);
    });
});
