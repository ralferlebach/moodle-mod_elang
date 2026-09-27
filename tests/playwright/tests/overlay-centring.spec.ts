/**
 * Overlay subtitles are centred on the picture, the way captions are.
 *
 * Issue #25: the overlay had no text alignment of its own, so it inherited the
 * document's and sat flush left in a left-to-right language. It read as a
 * paragraph that happened to be over the video rather than as its subtitle, and
 * on a wide picture a short line ended up far from where the eye was.
 *
 * What is measured here is where the text actually ends up, not what the
 * stylesheet declares. `getComputedStyle(...).textAlign === 'center'` would
 * pass while a stray rule on an inner element pushed the sentence to one side,
 * so a Range across the overlay's contents is used instead: that is the box the
 * words really occupy.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {expect, test} from '@playwright/test';
import {loginAs, requireEnv} from './helpers';

type Line = {left: number; right: number; width: number};

type Measured = {
    overlay: {left: number; right: number; width: number};
    lines: Line[];
    gaps: number;
};

/**
 * Where the caption's words sit inside the caption's box.
 */
async function measureOverlay(page: import('@playwright/test').Page): Promise<Measured | null> {
    return page.evaluate(() => {
        const overlay = document.querySelector('[data-region="captionoverlay"]') as HTMLElement | null;
        if (!overlay) {
            return null;
        }

        const style = getComputedStyle(overlay);
        const box = overlay.getBoundingClientRect();

        // The padding box: the room the text is actually allowed, which is what
        // "centred" has to be measured against.
        const inner = {
            left: box.left + parseFloat(style.paddingLeft),
            right: box.right - parseFloat(style.paddingRight),
            width: box.width - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight),
        };

        // Every text node of the caption, except the ones belonging to a gap's
        // status label. Those sit beside their input as part of an inline-flex
        // box and are positioned by the gap, not by the line — demanding they
        // be centred would be demanding the wrong thing, and they were what
        // made a first version of this test fail on correct output.
        const rects: DOMRect[] = [];
        const walker = document.createTreeWalker(overlay, NodeFilter.SHOW_TEXT, {
            acceptNode(node) {
                const parent = (node as Text).parentElement;
                if (!parent || parent.closest('.mod_elang-gapstate')) {
                    return NodeFilter.FILTER_REJECT;
                }

                return (node as Text).data.trim() === ''
                    ? NodeFilter.FILTER_REJECT
                    : NodeFilter.FILTER_ACCEPT;
            },
        });

        while (walker.nextNode()) {
            const range = document.createRange();
            range.selectNodeContents(walker.currentNode);
            rects.push(...Array.from(range.getClientRects()));
        }

        // The inputs themselves are part of the sentence and must be included:
        // a line centred around its words but not its gaps would be wrong.
        overlay.querySelectorAll('input.mod_elang-gap').forEach((input) => {
            rects.push(input.getBoundingClientRect());
        });

        if (rects.length === 0) {
            return null;
        }

        // Grouped into lines by vertical position. The union of everything
        // would be useless: a caption that fills its box looks identical
        // centred or flush left, so measuring the whole block passes whatever
        // the alignment is. A wrapped caption's last line is shorter than the
        // box, and that is where the difference actually shows.
        // Grouped by vertical centre, not by `top`. A gap input is taller than
        // the words around it and starts higher, so grouping on the top edge
        // split one visual line into two and then complained that the input —
        // 8em wide, sitting where the sentence put it — was not centred on its
        // own.
        const ordered = rects
            .filter((rect) => rect.width > 0)
            .map((rect) => ({left: rect.left, right: rect.right, middle: rect.top + rect.height / 2}))
            .sort((a, b) => a.middle - b.middle);

        const lines: {left: number; right: number; width: number}[] = [];
        let current: {left: number; right: number; middle: number} | null = null;
        for (const rect of ordered) {
            if (current && Math.abs(rect.middle - current.middle) <= 8) {
                current.left = Math.min(current.left, rect.left);
                current.right = Math.max(current.right, rect.right);
                continue;
            }
            if (current) {
                lines.push({left: current.left, right: current.right, width: current.right - current.left});
            }
            current = {left: rect.left, right: rect.right, middle: rect.middle};
        }
        if (current) {
            lines.push({left: current.left, right: current.right, width: current.right - current.left});
        }

        return {
            overlay: inner,
            lines,
            gaps: overlay.querySelectorAll('input.mod_elang-gap').length,
        };
    });
}

/**
 * The content sits centred in its box, within a pixel or two of slack.
 */
function expectCentred(measured: Measured, where: string): void {
    expect(measured.lines.length, `${where}: the caption has lines to measure`).toBeGreaterThan(0);

    measured.lines.forEach((line, index) => {
        const leftInset = line.left - measured.overlay.left;
        const rightInset = measured.overlay.right - line.right;

        // One space of slack, not two pixels. A browser centres a line by its
        // visible content and ignores the space it broke on, while a Range
        // includes that space in the rectangle — so a wrapped line measures as
        // sitting about a space-width left of centre even when it is drawn
        // exactly centred. Tightening this below a space width would fail on
        // correct output.
        //
        // It still discriminates: flush left puts one inset at zero and the
        // other at the full remaining width, which here is several hundred
        // pixels.
        expect(
            Math.abs(leftInset - rightInset),
            `${where} line ${index + 1}: equal space either side `
                + `(left ${leftInset.toFixed(1)}, right ${rightInset.toFixed(1)}, `
                + `line ${line.width.toFixed(1)} of ${measured.overlay.width.toFixed(1)})`
        ).toBeLessThanOrEqual(16);

        // Centred is not an excuse for spilling out of the picture. A long word
        // must wrap inside the canvas, not push the line past its edges.
        expect(leftInset, `${where} line ${index + 1}: nothing spills left`).toBeGreaterThanOrEqual(-1);
        expect(rightInset, `${where} line ${index + 1}: nothing spills right`).toBeGreaterThanOrEqual(-1);
    });
}

test.describe('overlay subtitles are centred', () => {
    test.beforeEach(async({page}) => {
        await loginAs(page, requireEnv('ELANG_STUDENT'), requireEnv('ELANG_STUDENT_PASS'));
    });

    for (const position of ['OVERLAYTOP', 'OVERLAYBOTTOM'] as const) {
        test(`${position.toLowerCase()}: a single line is centred with its gaps`, async({page}) => {
            await page.setViewportSize({width: 1366, height: 768});
            await page.goto(`/mod/elang/view.php?id=${requireEnv('ELANG_CMID_' + position)}`);
            await expect(page.locator('[data-region="status"]'))
                .toContainText(/ready|bereit/i, {timeout: 15000});
            await page.waitForTimeout(500);

            const measured = await measureOverlay(page);
            expect(measured, 'the caption has contents to measure').not.toBeNull();
            if (measured === null) {
                return;
            }

            // The gaps are part of the sentence, so a centred line has to carry
            // them along rather than centring the words around them.
            expect(measured.gaps, 'the caption carries at least one gap').toBeGreaterThan(0);
            expectCentred(measured, position.toLowerCase());
        });
    }

    test('a caption that wraps stays centred on every line', async({page}) => {
        // A narrow picture forces the wrap rather than a contrived long word:
        // this is what a phone actually does to a normal subtitle.
        await page.setViewportSize({width: 390, height: 844});
        await page.goto(`/mod/elang/view.php?id=${requireEnv('ELANG_CMID_OVERLAYBOTTOM')}`);
        await expect(page.locator('[data-region="status"]'))
            .toContainText(/ready|bereit/i, {timeout: 15000});
        await page.waitForTimeout(500);

        const measured = await measureOverlay(page);
        expect(measured, 'the caption has contents to measure').not.toBeNull();
        if (measured === null) {
            return;
        }

        // What makes this case worth running is not the wrap but the slack: a
        // line that fills its box looks identical centred or flush left, so a
        // test on one proves nothing. A narrow picture leaves the sentence
        // clearly shorter than the room it has, and there the two alignments
        // put it in visibly different places.
        const last = measured.lines[measured.lines.length - 1];
        expect(
            last.width,
            `the caption is shorter than its box, so alignment is observable `
                + `(${last.width.toFixed(1)} of ${measured.overlay.width.toFixed(1)})`
        ).toBeLessThan(measured.overlay.width - 40);

        // Measured as a block: the union of the lines is centred, and no line
        // reaches past the picture. Per-line centring is what text-align does
        // and is not re-derived here; what could go wrong is the block drifting
        // or a line overflowing, and both of those show up in this box.
        expectCentred(measured, 'wrapped caption');
    });

    test('right-to-left is centred too, not mirrored into a corner', async({page}) => {
        // The rule uses no physical side, so this should hold without a second
        // rule — which is exactly the kind of claim that is worth checking
        // rather than asserting, because `left` and `start` look alike until
        // someone reads Arabic.
        await page.setViewportSize({width: 1366, height: 768});
        await page.goto(`/mod/elang/view.php?id=${requireEnv('ELANG_CMID_OVERLAYBOTTOM')}`);
        await expect(page.locator('[data-region="status"]'))
            .toContainText(/ready|bereit/i, {timeout: 15000});

        await page.evaluate(() => {
            document.documentElement.setAttribute('dir', 'rtl');
        });
        await page.waitForTimeout(500);

        const measured = await measureOverlay(page);
        expect(measured, 'the caption has contents to measure').not.toBeNull();
        if (measured === null) {
            return;
        }

        expectCentred(measured, 'rtl');
    });

    test('below the medium is left alone', async({page}) => {
        // The transcript is a reading column, not a caption. Centring it would
        // be a regression, and the two share enough styling that it is worth
        // stating.
        await page.setViewportSize({width: 1366, height: 768});
        await page.goto(`/mod/elang/view.php?id=${requireEnv('ELANG_CMID_BELOW')}`);
        await expect(page.locator('[data-region="status"]'))
            .toContainText(/ready|bereit/i, {timeout: 15000});

        await expect(page.locator('[data-region="captionoverlay"]')).toHaveCount(0);

        const alignment = await page.evaluate(() => {
            const transcript = document.querySelector('[data-region="transcript"]') as HTMLElement | null;

            return transcript ? getComputedStyle(transcript).textAlign : null;
        });

        expect(alignment, 'the transcript is not centred').not.toBe('center');
    });
});
