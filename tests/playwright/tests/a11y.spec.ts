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
 * Accessibility scans (axe-core, WCAG 2.1 A/AA) of the three mod_elang pages a
 * teacher touches: the activity view (with the player), the attempt report and
 * the Subtitle Studio editor. Each must be free of serious or critical
 * violations.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {test} from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import {CMID, expectNoSeriousA11yViolations, login} from './helpers';

test.beforeEach(async ({page}) => {
    test.skip(!CMID, 'ELANG_CMID is not set — run through "make playwright" to seed it.');
    await login(page);
});

test('activity view is accessible', async ({page}) => {
    await page.goto(`/mod/elang/view.php?id=${CMID}`);
    await expectNoSeriousA11yViolations(new AxeBuilder({page}), 'view.php');
});

test('attempt report is accessible', async ({page}) => {
    await page.goto(`/mod/elang/report.php?id=${CMID}`);
    await expectNoSeriousA11yViolations(new AxeBuilder({page}), 'report.php');
});

test('subtitle studio editor is accessible', async ({page}) => {
    await page.goto(`/mod/elang/edit.php?id=${CMID}`);
    // Wait for the editor bundle to mount its toolbar before scanning.
    await page.getByRole('button', {name: 'Save draft'}).waitFor();
    await expectNoSeriousA11yViolations(new AxeBuilder({page}), 'edit.php');
});
