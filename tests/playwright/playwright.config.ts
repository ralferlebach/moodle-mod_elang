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
 * Playwright configuration for the mod_elang browser + accessibility tests.
 *
 * The base URL and login come from environment variables that seed.php prints
 * (ELANG_BASE_URL, ELANG_USER, ELANG_PASS, ELANG_CMID), so `make playwright`
 * seeds a running site and runs against it with no hand editing.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {defineConfig, devices} from '@playwright/test';

export default defineConfig({
    testDir: './tests',
    timeout: 45000,
    expect: {timeout: 10000},
    fullyParallel: false,
    workers: 1,
    // Two reporters: 'list' for the readable job log, 'html' because CI uploads
    // playwright-report/ after a green run — without the html reporter that
    // directory is never written and the artefact would be empty.
    reporter: [['list'], ['html', {open: 'never'}]],
    use: {
        baseURL: process.env.ELANG_BASE_URL || 'http://localhost',
        headless: true,
        ignoreHTTPSErrors: true,
        screenshot: 'only-on-failure',
        // A green run's videos are the point of keeping the report: they
        // document the intended journey. CI strips them from the failure
        // bundle, where a trace is the more useful artefact anyway.
        video: 'on',
        trace: 'retain-on-failure',
    },
    projects: [
        {name: 'chromium', use: {...devices['Desktop Chrome']}},
    ],
});
