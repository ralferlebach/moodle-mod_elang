mod_elang — browser & accessibility tests (E5)
==============================================

End-to-end and accessibility tests driven by [Playwright](https://playwright.dev)
with [axe-core](https://github.com/dequelabs/axe-core) via
`@axe-core/playwright`. They open the real pages in Chromium and:

- **a11y.spec.ts** — scan the activity view (player), the attempt report and the
  Subtitle Studio editor for WCAG 2.1 A/AA violations, failing on any serious or
  critical finding.
- **studio.spec.ts** — smoke-test that the editor mounts, shows the authoring
  toolbar, and exposes the timeline cue-edge handles as focusable ARIA sliders.

> **Run against a disposable dev/staging site only** — `seed.php` creates a
> course, an activity and a login user.

Quick start
-----------

From the plugin root (needs a running Moodle with mod_elang installed):

```
make playwright-setup     # first run: npm install + download Chromium
make playwright           # seeds a fixture, then runs the tests
```

`make playwright` runs `seed.php` (which prints
`ELANG_BASE_URL/USER/PASS/CMID/VERSIONID`), evaluates those into the environment,
and runs `playwright test`. To run against a fixture you created yourself, set
those variables and run `npm test` directly:

```
cd tests/playwright
ELANG_BASE_URL=https://moodle.example ELANG_USER=... ELANG_PASS=... ELANG_CMID=12 \
  npx playwright test
```

Notes
-----

- The tests skip themselves when `ELANG_CMID` is unset, so a bare `npx playwright
  test` without seeding is a no-op rather than a failure.
- `node_modules/`, `playwright-report/` and `test-results/` are git-ignored; only
  the specs, config, seeder and this README are tracked.
