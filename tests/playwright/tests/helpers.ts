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

/**
 * Read a seeded environment variable, failing loudly when it is missing.
 *
 * The seed script prints these and CI copies them into the job environment. A
 * missing one used to surface as an empty id in a URL and a 404 three
 * assertions later, which reads as a broken page rather than as a broken
 * setup.
 *
 * @param name The variable name.
 * @returns Its value.
 */
export function requireEnv(name: string): string {
    const value = process.env[name];
    if (!value) {
        throw new Error(
            `${name} is not set. Run tests/playwright/seed.php and export what it prints.`
        );
    }

    return value;
}

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
    await loginAs(page, USER, PASS);
}

/**
 * Log in as a specific person.
 *
 * Only the student archetype holds mod/elang:attempt, so the seeded teacher
 * cannot start an attempt and the player refuses to load for them. Anything
 * about what a learner sees has to be driven by the seeded learner.
 *
 * @param page The Playwright page.
 * @param username The username.
 * @param password The password.
 */
export async function loginAs(page: Page, username: string, password: string): Promise<void> {
    await page.goto('/login/index.php');

    // Wait for the page's own scripts before typing. Moodle initialises a
    // "show password" control on the login field after load, and that
    // initialisation resets the field: a value filled before it runs is
    // silently discarded, the form posts `password=` empty, and Moodle answers
    // with "Invalid login" — with the correct credentials.
    await page.waitForLoadState('networkidle');

    await page.fill('#username', username);
    await page.fill('#password', password);
    // Confirm the value survived to the moment of submitting, rather than
    // trusting that filling it was enough.
    await expect(page.locator('#password')).toHaveValue(password);

    await page.click('#loginbtn');

    // Assert we actually left the login page. The previous check accepted any
    // URL containing "/index.php", which matches /login/index.php?loginredirect=1
    // — precisely where a failed login lands. A wrong password therefore looked
    // like a successful one, and every later assertion failed against the login
    // page instead of the page under test.
    await expect(page).not.toHaveURL(/\/login\/index\.php/);
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
