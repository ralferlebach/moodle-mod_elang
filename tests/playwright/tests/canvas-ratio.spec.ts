/**
 * The player canvas is the picture, not a box the picture sits in.
 *
 * Issue #24: the stage had no ratio of its own, so its box was whatever the
 * layout gave it while the video sat inside letterboxed by object-fit. The
 * caption is positioned against the stage, so a portrait clip in a wide box put
 * the subtitle over the black band beside the picture and wrapped its lines at
 * the box width rather than the picture width.
 *
 * Four shapes, because the fault is invisible at 16:9 — the one ratio the old
 * fallback happened to be right about. A portrait clip is where it is most
 * obvious and 21:9 is where it goes the other way.
 *
 * Each clip is two seconds of generated test pattern, about 14 KB, served from
 * this site. A URL that does not resolve would do for tests that only need a
 * player to render, but a video element that never loads reports no dimensions
 * — and the whole mechanism here starts at loadedmetadata.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {expect, test} from '@playwright/test';
import {loginAs, requireEnv} from './helpers';

const RATIOS = [
    {name: '16:9', env: 'ELANG_CMID_RATIO_16X9', width: 640, height: 360},
    {name: '4:3', env: 'ELANG_CMID_RATIO_4X3', width: 640, height: 480},
    {name: '9:16 portrait', env: 'ELANG_CMID_RATIO_9X16', width: 360, height: 640},
    {name: '21:9 ultrawide', env: 'ELANG_CMID_RATIO_21X9', width: 840, height: 360},
] as const;

test.describe('the canvas takes the shape of the picture', () => {
    test.beforeEach(async({page}) => {
        await loginAs(page, requireEnv('ELANG_STUDENT'), requireEnv('ELANG_STUDENT_PASS'));
    });

    for (const ratio of RATIOS) {
        test(`${ratio.name}: stage, picture and overlay agree`, async({page}) => {
            await page.setViewportSize({width: 1366, height: 768});
            await page.goto(`/mod/elang/view.php?id=${requireEnv(ratio.env)}`);
            await expect(page.locator('[data-region="status"]'))
                .toContainText(/ready|bereit/i, {timeout: 15000});

            const stage = page.locator('.mod_elang-media-stage');
            await expect(stage).toBeVisible();

            // Wait for the medium to report its dimensions: everything below
            // follows from loadedmetadata, and before it the stage still wears
            // the 16/9 fallback.
            await expect
                .poll(async() => page.evaluate(() => {
                    const video = document.querySelector('.mod_elang-media-stage video') as HTMLVideoElement | null;

                    return video ? video.videoWidth : 0;
                }), {timeout: 15000})
                .toBeGreaterThan(0);

            const measured = await page.evaluate(() => {
                const element = document.querySelector('.mod_elang-media-stage') as HTMLElement | null;
                const video = document.querySelector('.mod_elang-media-stage video') as HTMLVideoElement | null;
                const overlay = document.querySelector('[data-region="captionoverlay"]') as HTMLElement | null;
                if (!element || !video || !overlay) {
                    return null;
                }
                const stagebox = element.getBoundingClientRect();
                const overlaybox = overlay.getBoundingClientRect();

                return {
                    stage: {width: stagebox.width, height: stagebox.height,
                        left: stagebox.left, right: stagebox.right},
                    overlay: {width: overlaybox.width, left: overlaybox.left, right: overlaybox.right},
                    natural: {width: video.videoWidth, height: video.videoHeight},
                };
            });

            expect(measured, 'stage, video and overlay are all present').not.toBeNull();
            if (measured === null) {
                return;
            }

            // The metadata actually read, so a mistake in the fixture shows up
            // as a fixture mistake rather than as a layout fault.
            expect(measured.natural.width, 'the clip has the width it was generated with')
                .toBe(ratio.width);
            expect(measured.natural.height, 'the clip has the height it was generated with')
                .toBe(ratio.height);

            // The claim of the issue. One pixel of slack: a browser rounds a
            // fractional layout to device pixels, and a test that insists on
            // exactness there fails for reasons that have nothing to do with
            // the rule being checked.
            const expected = ratio.width / ratio.height;
            const actual = measured.stage.width / measured.stage.height;
            expect(
                Math.abs(actual - expected),
                `stage ratio ${actual.toFixed(4)} matches the picture ${expected.toFixed(4)}`
            ).toBeLessThan(0.02);

            // No band beside the picture means the caption cannot sit on one.
            expect(
                measured.overlay.left,
                'the overlay starts no further left than the picture'
            ).toBeGreaterThanOrEqual(measured.stage.left - 1);
            expect(
                measured.overlay.right,
                'the overlay ends no further right than the picture'
            ).toBeLessThanOrEqual(measured.stage.right + 1);
            expect(
                measured.overlay.width,
                'subtitles wrap at the picture width, not at a wider container'
            ).toBeLessThanOrEqual(measured.stage.width + 1);
        });
    }

    test('the canvas fits the height the player measured', async({page}) => {
        // A portrait clip in a wide column is where a ratio rule goes wrong in
        // the other direction: honouring the width would make the stage taller
        // than the page. The width has to be derived from the height budget
        // instead, which is what the stylesheet does.
        await page.setViewportSize({width: 1366, height: 768});
        await page.goto(`/mod/elang/view.php?id=${requireEnv('ELANG_CMID_RATIO_9X16')}`);
        await expect(page.locator('[data-region="status"]'))
            .toContainText(/ready|bereit/i, {timeout: 15000});
        await expect
            .poll(async() => page.evaluate(() => {
                const video = document.querySelector('.mod_elang-media-stage video') as HTMLVideoElement | null;

                return video ? video.videoWidth : 0;
            }), {timeout: 15000})
            .toBeGreaterThan(0);

        const fit = await page.evaluate(() => {
            const player = document.querySelector('[data-region="mod_elang/player"]') as HTMLElement | null;
            const element = document.querySelector('.mod_elang-media-stage') as HTMLElement | null;
            if (!player || !element) {
                return null;
            }
            const budget = getComputedStyle(player).getPropertyValue('--mod-elang-media-height').trim();

            return {
                budget: parseFloat(budget),
                height: element.getBoundingClientRect().height,
                width: element.getBoundingClientRect().width,
                parent: (element.parentElement as HTMLElement).getBoundingClientRect().width,
            };
        });

        expect(fit, 'the player published a height budget').not.toBeNull();
        if (fit === null || Number.isNaN(fit.budget)) {
            return;
        }

        expect(
            fit.height,
            `a portrait canvas stays inside the measured height (${fit.height} vs ${fit.budget})`
        ).toBeLessThanOrEqual(fit.budget + 1);
        expect(
            fit.width,
            'and inside the column it sits in'
        ).toBeLessThanOrEqual(fit.parent + 1);
    });
});
