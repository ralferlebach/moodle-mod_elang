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
 * Shared helpers for the mod_elang Playwright tests.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Page, expect} from '@playwright/test';
import type AxeBuilder from '@axe-core/playwright';

/** The seeded course-module id under test. */
export const CMID = process.env.ELANG_CMID || '';
/** The seeded editing-teacher login. */
export const USER = process.env.ELANG_USER || 'admin';
export const PASS = process.env.ELANG_PASS || '';

/**
 * Log in through Moodle's standard login form.
 *
 * @param page The Playwright page.
 */
export async function login(page: Page): Promise<void> {
    await page.goto('/login/index.php');
    await page.fill('#username', USER);
    await page.fill('#password', PASS);
    await page.click('#loginbtn');
    await expect(page).toHaveURL(/\/(my|course)\b|\/index\.php/);
}

/**
 * Fail with a readable message listing the serious/critical accessibility
 * violations axe found, if any.
 *
 * @param builder A configured AxeBuilder for the current page.
 * @param label A short label for the page under test.
 */
export async function expectNoSeriousA11yViolations(builder: AxeBuilder, label: string): Promise<void> {
    const results = await builder.withTags(['wcag2a', 'wcag2aa']).analyze();
    const serious = results.violations.filter(
        (v) => v.impact === 'serious' || v.impact === 'critical'
    );
    const summary = serious.map((v) => `${v.id} (${v.nodes.length})`).join(', ');
    expect(serious, `${label} has serious a11y violations: ${summary}`).toEqual([]);
}
