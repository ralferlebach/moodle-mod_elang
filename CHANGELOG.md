# Changelog — mod_elang

All notable changes to this project will be documented in this file.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/);
versioning follows [Semantic Versioning](https://semver.org/).

Version 1.x (University of La Rochelle, CeCILL-B, up to Moodle 3.4) is documented
in the historical `ChangeLog` file of the 1.x repository and is not continued here.

---

## [Unreleased]

## [2.0.0-beta.29] - 2026-09-04

### Fixed
- `get_attempt_exercise::execute()` lost its docblock: the consent helper added in
  beta.28 was inserted between the docblock and the function it belonged to, so
  `moodle.Commenting.MissingDocblock.Function` failed the lint job.

### Added
- `tools/verify.sh` runs the static checks and reports by **exit code**. The
  reason is the mistake above: the checks were being read by eye with the output
  piped through `tail`, and a clean phpcs run ends with a timing line — so does a
  run with findings, because the findings print above it. The two looked
  identical. Nothing in the script is read by eye, a failing check prints its
  full output, and a missing phpcs is a failure rather than a silent skip.

## [2.0.0-beta.28] - 2026-09-04

### Security
- **RR-07.** A YouTube or Vimeo video is no longer embedded when the page opens.
  Until then the provider received every learner's IP address, user agent and any
  cookies it had already set — before anyone pressed play and without their doing
  anything. A notice now stands where the frame would, naming the provider and
  what it receives; the `<iframe>` is created only when the learner agrees, so
  its `src` is never set beforehand and nothing leaves the browser.
- The gate is a site setting (`mod_elang/providerconsent`, on by default), not an
  activity one: whether a provider may be contacted before consent is a question
  an institution answers once, not a didactic choice for whoever creates the
  exercise.
- It is checked as `!== '0'` rather than cast to a boolean. `get_config()` returns
  false for a setting whose default was never written, and a cast would have
  turned "nobody has decided yet" into "no consent needed" — the one answer a
  data-protection control must not give by accident. A Behat run found this.
- Consent lasts for the browser session. A reload does not ask again; a stored
  preference would outlive the session it was given in and stop being something
  the learner is aware of granting.

### Added
- `docs/dev/provider-embeds.md`: what the embed discloses, why routing the stream
  through Moodle is not a real option for YouTube — the terms of service forbid
  it, the signed IP-bound segment URLs make it unstable, and it would turn the
  learning platform into a CDN — and what works instead.
- The "source address" field help now points at institutional media servers
  (Opencast, Panopto, Kaltura): their direct file URL needs no plugin change, keeps
  IP addresses in-house, involves no consent question, and unlike a provider frame
  reports its playback time, so subtitle position and pause mode work fully.
- Four PHPUnit tests and two Behat scenarios covering the gate, its off switch,
  the unset-setting case and the file medium that never asks.

## [2.0.0-beta.27] - 2026-09-04

### Added
- **RR-09.** Moodle 5.1 joins both CI matrices. `supported = [405, 502]` covers
  everything from 4.5 to 5.2, so leaving 5.1 out meant claiming support for a
  version no job had ever installed the plugin on.
- **RR-11.** `docs/dev/dependencies.md`: the audit result on the exact lockfile
  (three scopes, zero findings), why React stays on 18 and what would end that,
  and why Jest stays on 29.
- **RR-12.** Four Playwright gates: a learner reaches and answers a gap with the
  keyboard alone, the finish button is focusable, and the exercise still works at
  200% and 400% zoom without sideways scrolling or a clipped input.
- **RR-13.** `docs/dev/release-policy.md`: one delivery format, the complete
  repository. Moodle installs a plugin by unpacking a ZIP, so a cleanup script
  that only exists in the repository helps nobody who installed one — and two
  formats would make "which one did you install?" the first question after every
  report.

### Removed
- **RR-10.** The JMeter plan, its makefile targets and its documentation.
  It measured the same endpoint as k6, needed a JVM nothing else here needs, and
  had drifted out of step. A second load test that measures the same thing is not
  a second opinion, it is a second maintenance debt. Listed in
  `db/removed_files.txt`; needs an explicit `git rm`.

## [2.0.0-beta.26] - 2026-09-03

### Fixed
- Deleting an attempt took a lock of its own (`attempt:<id>`) while every other
  write to an attempt takes `attempt_write_<id>`. A delete could therefore run
  alongside an answer that was still being graded, and the answer would be
  written back into an attempt that no longer existed. Found while measuring
  RR-08, not by it.

### Added
- **RR-08.** The write path is measured rather than assumed. A full answer run
  over every gap: 2.6 ms per submission at 50 gaps, 2.9 at 200, 3.1 at 400, with
  a **constant 15 queries** per submission throughout. The quadratic growth the
  review predicted is in rows iterated in PHP, not in database round trips —
  eight times the exercise length costs about 20% more per submission. Against a
  threshold of 50 ms p95 per submission, the measured figure is more than an
  order of magnitude clear, so no delta-update was built: it would replace a
  correct, well-tested recalculation with a running total that can drift.
- A guard on the property whose loss would actually hurt: answering the thirtieth
  gap must not cost more queries than answering the first. A wall clock on a
  shared runner is not a measurement; a query count is.

## [2.0.0-beta.25] - 2026-09-03

### Added
- **RR-06.** The subtitle parser enforces its own limits, so they hold for the
  import modal, the web service and any later caller alike: 2 MB of content,
  4000 cues, 5000 characters per line. Content that is not valid UTF-8 is
  refused with an explanation naming the likely cause — a file saved in an older
  encoding — rather than letting broken bytes reach the database and surface
  later as a transcript nobody can account for. Too many cues are refused, not
  truncated: keeping the first few thousand would hand back an exercise missing
  its ending with no way to tell.
- **RR-06.** One absurdly long line skips its own block with a warning instead
  of failing the import, so a single corrupted block costs that block rather
  than the transcript around it.
- **RR-06.** The import modal keeps the keyboard focus. Tabbing past the last
  control used to land on the page behind the backdrop — still there, still
  clickable, and covered — so the cursor simply disappeared. Closing now returns
  the focus to the button that opened the dialog.
- Tests for all of it: five in PHPUnit for the parser limits and the accepted
  UTF-8 case, one in Jest walking the focus into the dialog, around it and back
  out.

## [2.0.0-beta.24] - 2026-09-03

### Changed
- **RR-04.** The pause mode called "Always stop" never stopped at a subtitle
  whose gaps were all filled in — that behaviour was asked for and is right, so
  the name was the thing that was wrong. It is now "Stop at every unanswered
  subtitle", and the help text, the schema comment and the code comment say the
  same. A consequence worth stating: a second run through an exercise stops only
  where something is still missing.
- **RR-05.** Three capability descriptions in the README named the wrong default
  roles: `useregex` is managers only, `exporttranscript` includes students, and
  `deleteattempts` is editing teachers rather than all teachers. The README is
  what an administrator reads before deciding whether to change anything, so a
  wrong entry there is worse than none.
- **RR-05.** `db/services.php` claimed every function was on the official mobile
  service. Only the learner-facing ones are, which is right: the authoring
  editor is a React application for a desktop browser, and publishing from a
  context that cannot show it would be an endpoint with no interface behind it.

### Added
- Two contract tests: README and `db/access.php` must name exactly the same
  capabilities, and no authoring function may appear on the mobile service while
  every learner function must.

## [2.0.0-beta.23] - 2026-09-03

### Security
- **RR-01.** The report's person filter listed everyone with an attempt in the
  activity, ignoring the group scope the report itself applies. In
  separate-groups mode a teacher without `moodle/site:accessallgroups` was shown
  the names of learners whose attempts the report correctly hid — a name is
  personal data, and leaking it through a dropdown is the same disclosure as
  leaking the row. The options now come from `attempt_report::filter_users()`,
  which reuses the same group-scoped query as the listing, count, aggregate and
  export. Naming a foreign user id in the filter parameter already returned
  nothing; there is now a test that says so.

### Fixed
- **RR-02.** `js/vendor/react/editor.bundle.js.map` was a leftover of a
  development build committed in August. `build.mjs` writes a map only in dev
  mode, so the production bundle never referenced it — yet it shipped in every
  release since, carrying the full source of `ImportPanel` and `MediaPanel` long
  after both were deleted. Removed, listed in `db/removed_files.txt`, and
  excluded in `.gitignore`.
- **RR-03.** Cue timings are validated before publishing: a negative start, an
  end that is not after its start, and an end past the medium's duration each
  block the publish and name the cue by sort order and key. The editor checked
  these while typing, but `save_draft_version` and `publish_version` are external
  functions and a published version is what every attempt reads.

### Added
- `tests/artefacts_test.php`: no source map ships, the bundle points at no map
  that is not there, no deleted component survives in any built artefact, and
  every path in `db/removed_files.txt` really is gone. A committed artefact is
  the one file that can fall out of step with its source and stay that way.

## [2.0.0-beta.22] - 2026-09-03

### Fixed
- The load test failed runs it should have passed. The 300 ms figure was written
  as a k6 threshold, and k6 has no notion of a threshold that only reports: any
  crossed threshold sets exit code 99, and `abortOnFail: false` only decides
  whether the run stops early. A run at p95 = 507 ms — comfortably inside the
  800 ms limit — was therefore reported as a failure.
- The limit is now the only latency threshold. The target is a metric,
  `elang_content_within_target`, giving the share of reads that met it, plus a
  plain-language verdict in the summary that says which of the two numbers is a
  gate and which is not. Verified against a live site: target missed, limit kept,
  exit code 0.

## [2.0.0-beta.21] - 2026-09-03

### Added
- A `smoke` scenario for the load test (25 learners), and it is the default. The
  self-contained target is PHP's built-in development server on a shared
  four-core runner: a measured 25-learner run sits at a p95 of 582 ms there, so
  200 learners would cross the 800 ms gate because of the target rather than
  because of the plugin. The workflow warns when the two are combined, and
  `classroom` and `lecturehall` belong in `external` mode against a real
  installation.

## [2.0.0-beta.20] - 2026-09-03

### Fixed
- `get_version_content` still asked for `provider:youtube`. When the string ids
  were flattened in beta.14 the fix reached the working tree but not the
  delivery: the patch collected files by whether they *contained* a renamed
  identifier, and this one assembles its id at run time, so it matched nothing
  and was left out. Three PHPUnit tests failed on it in CI.

### Added
- `tests/lang_strings_test.php` guards the three properties that renaming broke
  or nearly broke: the two language files declare the same identifiers, only
  capability strings may contain a colon, and every identifier the code
  assembles at run time has strings behind it. The last one was verified by
  reintroducing the exact defect and watching the test name the file and the
  prefix.

### Changed
- Deliveries are now the whole codebase rather than a patch of changed files.
  A patch is only as good as the list of files it was built from, and that list
  was assembled by searching for literals — which is precisely what a run-time
  identifier is not.

### Fixed
- Documentation claimed an untested upgrade path from an earlier 2.0 beta. No
  beta was ever published, so outside development machines there is no
  intermediate state to upgrade from; everything else is a fresh install, which
  takes its whole schema from `db/install.xml`. The only real upgrade path is
  version 1 to 2.0, and `tests/upgrade_test.php` builds a real version 1
  database to exercise it. Corrected in `docs/dev/ci-gates.md`,
  `docs/dev/roadmap.md` and the session log.

## [2.0.0-beta.19] - 2026-09-03

### Changed
- The load test has two agreed scenarios instead of a default virtual-user
  count: `classroom` (200 learners) and `lecturehall` (2000 learners), both on a
  50-cue exercise — the length of a real listening exercise rather than of a
  stress fixture. Chosen from the workflow's dropdown; `custom` still frees the
  numbers.
- Two latency thresholds instead of one placeholder: p95 above **800 ms** fails
  the run, and p95 above **300 ms** is reported without failing it. Every answer
  in this exercise is a request, so 800 ms is where a learner starts wondering
  whether their keypress registered; 300 ms is what it should feel like, and
  seeing a drift from 280 ms to 700 ms while it is still a drift is the point of
  reporting it separately.
- The ramp scales with the load: above 500 virtual users it is 60 s rather than
  15 s. Arriving at 2000 that fast measures the ramp, and a cold connection pool
  dominates the p95.

### Added
- `docs/dev/load-testing.md`: the scenarios, both thresholds and the reasoning,
  what the plan measures and what it deliberately does not.

## [2.0.0-beta.18] - 2026-09-02

### Added
- Seven privacy tests closing the gaps the existing eleven left. Erasure was
  tested for attempts and responses but never for the authoring trail, which is
  where the plugin does something other than delete: it detaches the person and
  keeps the content, because the versions belong to the course and deleting them
  would erase other people's work. There are now tests that the stamp is cleared
  while the cues survive, that the migration sign-off is cleared too, that
  another author's trail is untouched, that a course or system context is
  ignored rather than acted on, and that wiping one activity does not reach into
  another.
- A lifecycle test the privacy API itself does not cover: a course cleanup
  deletes activities directly, without going through any provider method, so
  `course_delete_module()` has to take the attempts and responses with it. It
  does; now it is asserted.
- A metadata completeness test derived from `db/install.xml` rather than from a
  list written by hand: a table added later with a column naming a person would
  otherwise be personal data the privacy API never mentions, and nobody would
  notice until an export came back incomplete.

## [2.0.0-beta.17] - 2026-09-02

### Fixed
- The selected cue in the editor list had no visible edge at all. Bootstrap's
  `.list-group-flush > .list-group-item` sets `border-width: 0 0 1px` at the same
  specificity as a two-class selector, and the compiled theme emitted it last, so
  the marker never appeared. The selector now names the container as well. Found
  by the new right-to-left test, which asserted the border before flipping it.

### Changed
- Direction-dependent styles are logical rather than physical:
  `border-inline-start`, `margin-inline-start`, `inset-inline`. The timeline
  handles stay physical on purpose — the timeline draws time, not text, so the
  earlier edge of a cue is on the left in every locale, and flipping it would put
  the "start" handle at the end of the sound it belongs to.

### Added
- Four Playwright tests: the exercise fits a 390px screen without sideways
  scrolling, the medium and the transcript together fit a phone, an overlay
  caption stays inside the picture, and the selected-row marker moves to the
  other side when the document direction flips. The last needs no Arabic
  language pack, only the direction one would set.
- `docs/dev/v1-legacy-exit.md`: when the version 1 tables and `elang.options` may
  be dropped, who decides, and when the migration code itself can go. Nothing is
  automatic — decommissioning runs only from the command line, because a data
  loss triggered by cron is not a migration.

## [2.0.0-beta.16] - 2026-09-02

### Added
- `docs/dev/capabilities.md`: who may do what, where each check happens, and the
  three places where a capability alone is not the whole answer — `attempt` is
  held by every learner so ownership is checked too, `useregex` sits higher than
  the rest of the authoring right because a bad expression is evaluated against
  learner input, and the two transcript exports are gated by activity settings
  on top of their capabilities.
- `docs/dev/roadmap.md`: the deferred work, out of the code. Two features are
  server-side complete but have no UI, three things are deliberately not done,
  and three are gaps to close before a stable release.

### Changed
- Roadmap labels removed from three source comments. `gap_rule_generator` and
  `special_characters` described implemented code as "the 2.1 feature", and a
  schema test justified itself by a milestone number rather than by the property
  it actually proves.

### Notes
- Report query performance was measured rather than assumed: at 20,000 attempts
  in one activity the default listing takes 11 ms, sorting by name 43 ms, the
  count 1.7 ms and the aggregate 4.1 ms, and the plan uses the index on
  `elangid`. No index was added — there is no measured need for one.

## [2.0.0-beta.15] - 2026-09-02

### Fixed
- Restoring a learner's answers walked the whole transcript once per gap. The
  attempt state carries an entry for every gap, and each one was looked up with
  `list.querySelector()`, so the cost grew with the square of the transcript. It
  is one indexed pass now: on a 400-cue exercise the restore phase went from
  791 ms to 419 ms, measured in the browser.
- Cue pages are requested together rather than one after another, and each page's
  markup is built in a document fragment and attached once instead of appending
  every cue to the live list.

### Added
- The Playwright fixture's long transcript is 400 cues, the length of a lesson
  recording, and it is published so the player actually runs against it.
- A regression test asserts what is structural rather than timed: every cue
  arrives, and it takes one request per page of fifty rather than one per cue.
  No wall-clock assertion — a shared runner's clock is not a measurement.

## [2.0.0-beta.14] - 2026-09-02

### Changed
- **Breaking for translations.** 356 language string identifiers lost their
  colons: `player:ready` is now `player_ready`, `report:heading` is
  `report_heading`, and so on throughout. Moodle and AMOS accept only
  `[a-z0-9_]` in a string id, so the colon form could not be published to the
  plugin directory or translated on lang.moodle.org. Any existing local language
  customisation of these strings has to be redone against the new ids; the
  German pack shipped with the plugin is already converted.
- The ten capability strings keep their colons — `elang:manage` and its nine
  siblings name the capabilities themselves and must match them exactly.
- `strings['player_ready']` became `strings.player_ready` throughout the AMD
  modules: with the colons gone the keys are valid identifiers, and ESLint's
  `dot-notation` rule says so.

### Notes
- Three identifiers are built at runtime (`'provider_' . $key`,
  `'report_' . $column`) and are invisible to a search for a literal string. They
  were found by searching for a quoted prefix ending in a colon concatenated with
  a variable, and the same search confirmed the two remaining hits are a lock
  name and a test fixture, not string ids.

## [2.0.0-beta.13] - 2026-09-02

### Added
- `tests/external/security_contract_test.php`: what every external function must
  do, checked for all of them at once. It walks `db/services.php` rather than a
  hand-written list, so a function added later without its guards fails here
  instead of in a review. It asserts that every declared function names a real
  external_api class with the three required methods and declares a capability,
  that no attempt-scoped function accepts another learner's attempt, that no
  authoring function accepts a learner, and that answering or hinting rejects a
  gap from a different exercise.
- `docs/dev/ci-gates.md`: which jobs block a release and which do not, and what a
  green run therefore does and does not prove. Playwright, k6 and the Moodle
  `main` jobs are all outside the blocking set, so their results have to be
  produced deliberately before a stable release.

### Notes
- The audit of the fifteen external functions found no further finding: every
  one already routes through a helper that checks context, capability and the
  object it was handed. That is now enforced by a test rather than asserted in a
  document.

## [2.0.0-beta.12] - 2026-09-02

### Security
- Draft media could be served to any learner who guessed a version id. Two
  file-serving callbacks existed: `elang_pluginfile`, which asks
  `version_manager::user_can_access_version_file()` whether this person may have
  this version, and `mod_elang_pluginfile`, which asked only for
  `mod/elang:view`. `file_pluginfile()` tries `{component}_pluginfile` first and
  only falls back to `{modname}_pluginfile`, so the weaker one was the one that
  ran and the version check was unreachable. The weaker callback is gone and the
  version-aware one carries the name Moodle actually calls. Its access rules,
  and the tests covering them, are unchanged — they simply take effect now.
- A test asserts that only one callback exists, so a second one cannot quietly
  take precedence again.

## [2.0.0-beta.11] - 2026-09-02

### Fixed
- With captions over the picture, the overlay stayed empty until playback
  produced its first `timeupdate`: a learner opening the exercise saw a picture
  and no sentence, and the transcript that would otherwise carry it is not on the
  page in that mode. The player now resolves the active cue once at render time.
- The cursor was placed in a gap that was not on screen. The active cue has by
  then been moved into the caption overlay and the list it came from is hidden,
  so the search has to cover the whole player rather than the list.

### Added
- Playwright covers the three subtitle positions, the audio fallback and the cue
  list: 13 tests where there were 5. These are the two things unit tests and
  Behat cannot settle — where something is drawn, and how forty cues behave on a
  rendered page.
- The fixture seeds an activity per subtitle position, an audio one, a forty-cue
  transcript, and a learner. Only the student archetype holds
  `mod/elang:attempt`, so the seeded teacher cannot start an attempt and the
  player correctly refuses to load for them; anything about what a learner sees
  has to be driven by a learner.
- `requireEnv()` fails with the variable's name instead of letting a missing one
  surface as a 404 three assertions later.

## [2.0.0-beta.10] - 2026-09-01

### Added
- Maximum length, the reference link and per-variant regular-expression matching
  are editable at last. All three are in the schema and in the web service, but
  no control existed for them: the only way to set them was an import or a
  database edit.
- They sit in a collapsed "Advanced settings" section of the gap editor, because
  most gaps never need a decision about them and placing them beside the solution
  suggested otherwise.

### Changed
- Solution, matching algorithm and accepted variants share one row, and the
  variants read as a short list of spellings rather than a column of full-width
  fields each with its own remove link.

### Fixed
- The recurring "File is stale and needs to be rebuilt" reports came from the
  browserslist database, not from a lost file. Rollup's output depends on the
  installed `caniuse-lite` version; Moodle's package-lock pins one from 2022
  while CI refreshes it before building, so identical sources produced different
  artefacts. `tools/check_amd_builds.sh` now updates the database before it
  builds, and the committed artefacts match what CI produces. Note that the
  update reports "No target browser changes" and the output still differs, so
  that message cannot be relied on.

## [2.0.0-beta.9] - 2026-09-01

### Changed
- The attempt detail is a piece of work rather than a row dump. The counts a
  teacher checks first — answered, of those accepted, exactly right, needed a
  hint — lead, and the gaps are grouped under the cue they belong to, because
  "which sentences did this person struggle with" is a question the flat table
  could not answer without mentally regrouping every row. A cue that still has
  something wrong or unanswered carries a left edge, so a long attempt can be
  scanned rather than read.
- The graded result is a check, a cross or a warning triangle carrying its
  wording as an accessible name, matching the player.
- `mod_elang\output\attempt_detail` and `templates/attempt_detail.mustache`;
  the two label closures in `report.php` moved into the renderables that use
  them.

### Fixed
- `amd/build/player.min.js` and its map in the repository did not match
  `amd/src/player.js`. Rebuilding from the repository's own source produces
  exactly the artefacts shipped here, so the source was right all along and the
  build simply never arrived — the rebuilt files were copied into the throwaway
  Moodle tree the check runs in, and the release was packaged from the working
  tree, which still held the old ones.
- `tools/check_amd_builds.sh` takes `--sync=<working tree>` and copies the
  rebuilt artefacts there itself, so the copy that gets packaged cannot fall
  behind the copy that was checked.

### Added
- Concurrency tests for the attempt state, which the review listed as an open
  gate: a repeated finish must not move `timefinish`, a response or a hint that
  loses the race to finish is refused and leaves neither a row nor a changed
  score behind, repeated starts yield one attempt rather than two in-progress
  ones the resume logic could not choose between, and deleting an attempt takes
  its responses with it.

### Fixed
- The "finish anyway" question used `window.confirm()`, which ESLint's `no-alert`
  rejects — and rightly: a native confirm is unthemed, cannot carry a translated
  button label and returns focus nowhere in particular. It is now Moodle's own
  `Notification.saveCancelPromise()`.
- `tools/check_amd_builds.sh` runs Grunt with `--max-lint-warnings=0`, the way
  moodle-plugin-ci does. Without it a plain `grunt` run reports lint warnings and
  still exits 0, which is how two findings reached CI. The value has to be
  attached with "=" — `--max-lint-warnings 0` makes Grunt read the 0 as a task
  name.
- The remaining nine transactions in `version_manager` and `attempt_manager` now
  roll back too. Each one writes several rows that describe one thing between
  them, and half of any of them is a state the plugin has no name for: a
  published version with the previous one archived and nothing in its place, a
  media description that disagrees with the files on disk, a draft branched
  without the content it was branched from, a response stored without the
  aggregates that describe it, a revealed hint whose penalty never reached the
  score.

## [2.0.0-beta.8] - 2026-09-01

### Fixed
- A rejected draft payload wiped the author's existing content.
  `save_draft_content()` deleted the draft's whole content and only then
  validated the new set, so a duplicate cue key or an unknown grading algorithm —
  both things an editing session can produce — left the author with neither their
  old work nor their new. Validation now happens before the delete.
- No transaction in the plugin handled rollback. Moodle's delegated
  transactions do not unwind on their own: starting one and letting an exception
  escape leaves the completed statements in place. `transaction_trait` runs a
  unit of work with a proper `rollback()`, and `save_draft_content()` uses it as
  the backstop for a genuine database failure during the insert.

### Added
- Failure-injection tests for autosave: a rejected save must release the
  in-flight flag, or the controller would treat every later attempt as "already
  saving" and queue it forever — the author would see "error" and never leave it,
  with no sign that further edits were going nowhere. Also that a failed save
  leaves nothing queued, that an edit during a save produces exactly one more
  save, and that cancel really stops a pending one.
- Tests that a rejected payload and a stale draft revision each change nothing,
  and that saving with the revision actually held still works — a guard that
  blocks real work is no better than no guard.

## [2.0.0-beta.7] - 2026-09-01

### Added
- `amd/src/playback.js`: the four decisions the player makes about playback —
  which cue is playing, where to park after pausing, whether a boundary should
  stop, and which gap comes next — extracted as pure functions with no imports.
  Every playback bug reported against this plugin has lived in one of them, and
  each was found in a browser because they were reachable only through a media
  element and a cue list. 18 Jest tests now ask them directly, written from those
  reports: a cue owns its start and not its end, pausing parks inside the cue
  rather than on its edge, a fully answered cue never holds playback, and
  advancing does not wrap around.

### Fixed
- The cue list's timestamps used Bootstrap's `.text-muted`, which at #6a737b
  reaches 4.36:1 on the selected row's tinted background — under the 4.5:1 WCAG AA
  threshold, and a shortfall this stylesheet introduced by tinting that row. They
  now carry a colour that clears the threshold on both backgrounds.
- `esbuild` moved from the `0.23.x` range to `^0.25`, out of GHSA-67mh-4wv8-2f99.
  Build tooling only, not the plugin runtime, but `npm audit` now reports nothing.
  The bundle still builds reproducibly.

### Changed
- CI: Node 22 is selected before Moodle's own npm install rather than after it,
  so Moodle's Grunt runs on it too, and Moodle's dependencies are installed with
  `npm ci` plus a browserslist refresh. Applied to both pipelines.

## [2.0.0-beta.6] - 2026-09-01

### Fixed
- The caption vanished at the instant playback paused. Pausing at a cue boundary
  parked playback exactly on the edge, where no cue is active, and the overlay
  cleared — taking the sentence off the screen at the moment the learner was
  asked to fill it in. Playback now lands just inside the cue it stopped at, and
  an overlay keeps its caption until another cue replaces it.
- Subtitles were lost in fullscreen. A fullscreened media element is drawn alone,
  without the sibling overlay that carries the gaps; the request is now moved up
  to the stage that holds both. Where a platform refuses that — notably iOS,
  whose fullscreen is a system player that cannot contain HTML — the medium plays
  without captions and the exercise continues unharmed on exit.
- The medium and the transcript were not bounded, so on a normal screen the
  learner scrolled between the picture and the sentence they were answering —
  the coupling the "below the medium" mode exists to avoid. Both are now bounded
  in viewport units.
- A cue whose gaps were all filled in still held playback at its end, and Enter
  still stopped on answered gaps.

### Changed
- Settings sits directly after the activity in the tab bar, then Media,
  Subtitles & gaps, Attempts, Export.
- An overlay caption always pauses at a cue boundary and the pause-mode setting
  is hidden for it: the caption shows only the cue that is playing, so running on
  would take the sentence being answered off the screen. There is nothing to
  choose, so nothing is offered.
- With captions over the picture, the transcript is no longer repeated below it.
- The exercise starts with the cursor in the first unanswered gap when captions
  are over the picture, which is also what makes playback stop at that cue.
- Graded gaps show a check, a cross or a warning triangle instead of a word, and
  "check answer" and "show hint" are quiet icon buttons. The wording is not lost:
  it is the accessible name and the tooltip.
- "Finish attempt" is preceded by how many gaps are answered, and finishing with
  gaps still empty asks first. Finishing incomplete stays possible — an exercise
  nobody can hand in unfinished is one people abandon instead.

### Fixed
- Two lint findings the local `grunt amd` run does not cover: a comment in
  `amd/src/player.js` starting with a lowercase word, and a trailing blank line in
  `tests/behat/report.feature`. The full `grunt` task set — which adds
  `gherkinlint` and fails on warnings — is what CI runs, and is now what is run
  locally.
- `amd/build/player.min.js.map` was left stale by that comment change. A source
  map embeds the original source in `sourcesContent`, so a comment-only edit
  leaves the .min.js byte-identical and still changes the map — which is what
  made it easy to miss twice.

### Added
- `tools/check_amd_builds.sh`: runs the full Grunt task set and compares every
  file under `amd/build/`, maps included, instead of the one whose change was
  expected.

## [2.0.0-beta.5] - 2026-09-01

Subtitle editor workspace (issue #7). Every cue used to render its whole form at
once — timing, transcript, preview, gaps, solutions, algorithms, variants and
hints, stacked — so forty cues meant a wall of forms metres long and the
connection between the medium, the timeline and the cue being worked on was lost
in the scrolling.

### Added
- A compact cue list beside a single open cue inspector. Each row shows its
  formatted times, a text preview, its gap count and any warning; exactly one cue
  is open at a time. Selection is held by `EditorApp`, so the list, the timeline
  and the inspector cannot disagree about which cue that is.
- Search and a "only cues with warnings" filter over the list, and a per-cue
  action menu with insert-before, insert-after and delete.
- `js/src/studio/time.ts` plus `TimeField`: cue boundaries are entered as
  `mm:ss.SSS` (`hh:mm:ss.SSS` past an hour). The field keeps its own draft while
  typing and only commits on blur or Enter, so it does not reformat mid-keystroke;
  an unparseable entry is rejected rather than silently rounded. Milliseconds
  remain the stored representation.
- 12 Jest tests for the time module and 3 for the workspace, plus a Jest setup
  file stubbing `scrollIntoView`, which jsdom does not implement.

### Changed
- Autosave leads the toolbar and manual save is a link, not the primary button.
  Presenting "Save" as the main action taught authors to distrust the autosave
  that was already running. The toolbar is now save state, import and publish.
- "Add cue" moved out of the publish toolbar and next to the cues it acts on.
  Adding a cue and publishing are not comparable steps.
- Adding, inserting or importing a cue opens it, so the action has a visible
  result.
- The gap's character offsets are no longer shown as a field. They are
  maintained by selecting text and by `resyncGaps()`; showing them made an
  internal coordinate look like something to fill in. They remain in the DOM for
  support work.

### Removed
- `js/src/components/MediaPanel.tsx` and its test. Media configuration belongs to
  the media tab, which took it over in beta.2. Needs an explicit `git rm`.

## [2.0.0-beta.4] - 2026-09-01

### Fixed
- The Playwright login helper filled the password before the login page's own
  scripts had run. Moodle initialises a "show password" control on that field
  after load, and the initialisation resets it: the form posted `password=`
  empty and Moodle answered "Invalid login" with correct credentials. The helper
  now waits for the page to settle, and asserts the value survived to the moment
  of submitting.
- That failure went unnoticed because the helper accepted any URL containing
  "/index.php" as proof of a successful login — which matches
  `/login/index.php?loginredirect=1`, exactly where a failed login lands. It now
  asserts it left the login page, so a broken login fails at the login rather
  than three assertions later against a page nobody reached.
- The authoring timeline failed WCAG AA: white labels on the cue colours reached
  3.3:1 and 4.0:1 against a 4.5:1 threshold, and the inactive cue's opacity
  washed the text against whatever sat behind it. Both colours are darkened, the
  opacity is gone, and the active cue additionally carries an outline so the
  distinction does not rest on colour alone.
- `amd/build/editor.min.js` was left behind when `amd/src/editor.js` changed in
  the import-modal work, so `moodle-plugin-ci grunt` failed the build as stale.

### Added
- The attempt report leads with four figures — attempts shown, finished, average
  score of finished attempts, and how many used a hint — computed in one
  aggregate query over the same FROM/WHERE as the table, so the header and the
  rows can never describe different sets.
- Server-side filters for person, state, start date range and attempt number,
  plus sortable column headings. Both are whitelisted: `clean_filters()` and a
  `SORT_COLUMNS` map decide, so no request parameter can choose SQL.
- Filters travel as canonical URL parameters, so a filtered view is a link that
  can be bookmarked or passed on, and paging and sorting simply carry them.
- The export honours the filters in force. An export that ignored them would
  hand out more than the teacher was looking at — in separate-groups mode that
  is a disclosure.
- `mod_elang\output\report_overview`, `templates/report_overview.mustache` and
  `mod_elang\form\report_filter_form`, keeping query logic in the report class
  and presentation in a template.

### Changed
- The German report heading is "Versuche".
- Attempt state is a labelled badge and "answered" carries a bar beside its
  numbers, so neither is read by colour alone.
- The four export formats sit behind one "Export" menu, spreadsheet formats
  first, instead of four equal links.
- Deleting moved out of the row into an action menu. The capability, the
  confirmation, the sesskey and the object access check are unchanged.

## [2.0.0-beta.3] - 2026-09-01

### Added
- The three subtitle positions of issue #3 are live in the player. One cue
  renderer serves all of them: in the overlay modes the active cue element is
  *moved* over the picture rather than re-rendered, so the gap inputs keep their
  restored values, their listeners and their graded state. A second render would
  have meant two gap implementations that could disagree about what was typed.
- Below the medium, the transcript is a bounded self-scrolling region rather than
  page content as long as the exercise, and automatic scrolling stays out of the
  way for four seconds after a learner scrolls. Our own `scrollIntoView()` also
  fires a scroll event, so a flag separates it from a learner reaching for the
  scrollbar — without that the first automatic scroll would suppress every one
  after it.
- The player follows the medium on `seeked` as well as `timeupdate`: a seek while
  paused produces no `timeupdate` in every browser, so the visible cue lagged
  behind the position the learner had just chosen.
- The cue pause modes of issue #4 are live: `stop` pauses at the end of every cue,
  `nostop` never does, and `auto` pauses only at the end of the cue being worked
  on — clicked, or holding the keyboard focus in one of its gaps. After pausing,
  playback lands exactly on the boundary rather than a fraction past it, so
  resuming does not clip the first word of the next cue.
- Enter now checks the answer and moves to the next gap. It is bound to the
  submit's own promise rather than fired alongside it: moving the focus triggers
  the blur handler, and without waiting the same answer would be sent twice. When
  the next gap belongs to another cue, playback jumps there and runs to that
  cue's end marker; within the same cue it does not rewind.

### Fixed
- Playwright: fail with a message naming the cause when the lockfile is missing
  from the checked-out ref, instead of `npm ci` printing its usage text.

### Added
- Subtitle import is a modal with a file tab and a paste tab, replacing the
  collapsible textarea buried between the timeline and the cue list. Both tabs
  feed one string into one server-side parse, so a file and pasted text can never
  be understood differently.
- Import is now two steps: "check content" reports the source, format, cue count,
  gap count and duration before anything is applied. Without that summary,
  choosing between appending and replacing was a guess.
- "Replace all cues" alongside "append", offered only when there are cues to lose.

### Fixed
- Playwright never got past dependency installation: `tests/playwright/package-lock.json`
  was listed in `.gitignore` from when the browser tests were a local-only tool, and
  `npm ci` refuses to run without a lockfile. The lockfile is now committed, which also
  stops a Playwright release from silently changing what the scheduled run tests.
- The Playwright fixture published a version with no medium, so both editor tests would
  have landed on the "add a medium first" notice that `edit.php` has shown since the
  navigation rework. The seed now sets a URL medium before publishing.
- `playwright.config.ts` declared only the `list` reporter, so `playwright-report/` was
  never written and the green-run artefact would have been empty. It now also writes the
  HTML report, records video, and keeps a trace on failure.

### Added
- The media page is a working area rather than a bare upload form: the form on
  the left, the medium currently set on the right, with a preview, its file name
  and its type, size and duration. Replacing the medium of an activity that
  already has cues is a decision, and it should not be made without seeing what
  is being replaced.
- A source address can be given instead of an upload, in one field rather than a
  provider selector plus a reference field: `provider_registry::detect()` works
  out from the address itself whether it is a YouTube or Vimeo link, and anything
  else is kept as a direct media URL. An address that is neither is refused.
- `mod_elang\output\media_page` and `templates/media_page.mustache`.
- Ten tests for address detection, including that a bare video id names no
  provider and must not be claimed by the first one in the list.

### Added
- The transcript export page is now two product cards rather than a heading and a
  row of four equal format links: each leads with PDF and keeps DOCX, ODT and TXT
  in a menu, because PDF is the one teachers actually print.
- `mod_elang\output\transcript_page` and `templates/transcript_page.mustache`, so
  the page's markup lives in a template rather than in `html_writer` calls.
- The solution card states who may take it, derived from the activity's own
  setting. The previous wording claimed teachers only, which two of the three
  settings make untrue.

### Changed
- The export tab is labelled "Export" rather than "Export transcript"; the page
  heading carries the full name.

### Fixed
- Behat: the helper that publishes a version called `create_draft()`, which always
  branches a fresh version from the published one and ignores a draft that is
  already open. A scenario that first gave the activity a medium and then stated a
  transcript left that medium behind on an orphan draft, which `edit.php` then
  received back — empty and without a medium. It now builds on the open draft.
- CI: both pipelines uploaded Behat failure screenshots from `moodle/behatfaildumps/`,
  a path that does not exist, so every failed Behat run collected nothing and logged
  only "No files were found". `moodle-plugin-ci` writes its fail dumps to
  `<data dir>/behat_dump`.
- CI: the experimental jobs against Moodle `main` failed at install because Moodle 5.3
  requires PostgreSQL 17 and the service provided 16. The blocking jobs stay on 16.
- CI: `moodle-release.yml` carried two `concurrency` blocks, which GitHub rejects
  outright — the whole workflow failed to parse on every push, on any branch.
- CI: the editor steps run in the plugin checkout, so their relative `tee ci-logs/…`
  targets resolved to a directory that does not exist; `tee` failed and took the step
  with it, losing exactly the output needed to diagnose it. Anchored to the workspace.
- CI: every Behat job now pre-pulls the Selenium image with retries. `docker run`
  pulls implicitly and gives up on the first failure, so a transient Docker Hub 500
  surfaced as "Can't start Selenium server" and read like a Behat fault.

### Added
- `elang.subtitleposition` (`below` | `overlaybottom` | `overlaytop`) and
  `elang.cuepausemode` (`auto` | `stop` | `nostop`), set per activity in a new
  "Playback and subtitles" section of the settings form. Both default to the
  behaviour activities had before they existed, so an upgraded activity plays
  exactly as it did.
- `mod_elang\local\player\playback_settings`: resolves what the activity asked
  for against what the medium can honour. An audio track has no picture to draw
  captions on, and a provider embed reports no playback time and takes no pause
  command, so those degrade — without touching the stored setting, which applies
  again as soon as the activity uses a video file or direct URL.
- `get_attempt_exercise` returns a `playback` structure carrying both the stored
  and the resolved values, so the player renders the resolved pair and can still
  explain why an overlay was not used.
- `tests/local/player/playback_settings_test.php` (9 tests) and four payload
  tests covering the defaults and both degradation paths.
- CI: Jest, `tsc --noEmit` and a reproducibility gate for the committed React bundle.
  None of the three ran in CI before; they were verified by hand before a release.
- CI: `.github/workflows/playwright.yml` — browser and axe accessibility tests against
  a live site, covering what Behat structurally cannot (media playback against a real
  clock, overlay placement, fullscreen, real focus events).
- CI: `.github/workflows/load-k6.yml` — manual load runs, available on every branch,
  in a self-contained or external mode.
- CI: a `stale-files` job and `db/removed_files.txt`, so a file removed in an earlier
  release cannot survive in an installation updated by unpacking a ZIP.

### Changed
- CI: every check step keeps its full output under `ci-logs/` and runs even when an
  earlier step failed, so a run reports the whole picture instead of one problem per
  attempt. Diagnostics upload on failure; Playwright and k6 artefacts upload always.
- CI: `concurrency` with `cancel-in-progress` and a Composer download cache.

## [2.0.0-beta.2] - 2026-09-01

Activity navigation (issue #2). The working areas of the activity — the exercise,
media, subtitles and gaps, reports, settings and the transcript export — are now
modes of one activity in Moodle's own secondary navigation instead of a row of
action buttons above the player.

### Fixed
- `elang_extend_settings_navigation()` returned immediately on every page, so the
  activity contributed no navigation entries at all. Its guard used
  `empty($PAGE->cm)`, and `moodle_page` serves every property through `__get()`
  without defining `__isset()` — which makes `empty()` and `isset()` on such a
  property always report "empty" and "not set" whatever the page holds. The guard
  now tests the read value, and it reads the page from `$settingsnav->get_page()`
  rather than the `$PAGE` global, which is not necessarily the page the tree is
  being built for.
- The German solution-export hint claimed learners could never download the
  solution transcript, which the new per-activity setting makes untrue.
- Behat: the test helper that republishes an exercise built its new version on top
  of the content `create_draft()` inherits from the current version, so the "new"
  version kept the old cue alongside the new one. The helper now clears the
  inherited draft content first, so a scenario that states a whole new transcript
  gets exactly that. Test only; the re-pin behaviour itself was already correct.

### Added
- `mod_elang\navigation\views\secondary`: orders the activity's own modes ahead
  of the generic administrative entries and shows six tabs rather than core's
  five, so the transcript export stays a tab instead of falling into "More".
- Separate "Media" and "Subtitles & gaps" modes, both gated on `mod/elang:manage`.
- `elang.allowtranscriptdownload` and `elang.solutionavailability`: per-activity
  control over what learners may download. Both default to the closed setting, so
  existing activities keep handing out nothing. Learners hold
  `mod/elang:exporttranscript` by default, so the capability alone could not carry
  this decision.
- `elang_can_export:worksheet()`, `elang_can_export:solution()` and
  `elang_can_export_transcript()` in `lib.php`, the single place that decision is
  made. `transcript.php` calls them before it streams anything.
- `edit.php` refuses to mount the editor while the draft has no medium and points
  at the media mode instead, including on a direct URL call.
- `tests/navigation_test.php`: tab visibility per role read from the real
  navigation tree, plus the full export access matrix.
- `docs/dev/deutsche-bezeichnung-sprachpaket.md`: why a downloaded language pack
  overrides the plugin's own German strings, and how to override it in turn.

### Changed
- German activity name is now "Video-Diktat" ("Video-Diktate"). Note that a site
  with the German language pack installed needs a local language customisation for
  this to take effect; see the document above.
- `view.php` no longer renders "Edit content", "Reports" and "Export transcript"
  as buttons. Nothing became unreachable; all three are modes now.
- Every activity page marks its own secondary tab active via
  `set_secondary_active_tab()`.

## [2.0.0-beta.1] - 2026-08-13

First beta. All P0/P1/P2 items from the pre-beta release review are closed and the
full CI matrix (Moodle 4.5 / 5.0 / 5.2, PostgreSQL and MariaDB, PHPUnit + Behat +
lint) is green.

### Changed
- Maturity raised to MATURITY_BETA.

### Fixed
- Behat: the player scenario that asserted an in-progress attempt keeps its
  version is split to match the current behaviour — a *touched* attempt (an answer
  or hint exists) stays pinned, while an *untouched* attempt follows a republished
  version. Test only.

## [2.0.0-alpha.87] - 2026-08-13

### Fixed
- Republishing an exercise now reaches learners whose attempt exists but is
  untouched. An attempt is pinned to the version it started on so content edits
  never change a running attempt — but that pin also meant a learner who had
  merely opened the exercise once kept resuming stale content forever (for
  example a broken medium the author had already replaced). Resuming an attempt
  with no response and no hint yet now follows the current published version;
  touched attempts stay pinned and the player shows a notice that the attempt
  continues on the earlier content (new `outdated` flag in the exercise web
  service).

## [2.0.0-alpha.86] - 2026-08-13

### Added
- Friendly handling of media whose video track the browser cannot decode (for
  example MPEG-4 Part 2 / Xvid-era files, which VLC plays fine but browsers show
  as a black picture with working audio). The authoring editor warns the author
  with a re-encode hint (H.264/MP4) as soon as the preview loads, and the player
  shows learners a notice that the audio still plays instead of leaving a silent
  black frame. Audio files are recognised and never trigger the warning.

### Fixed
- Behat: the keyboard-nudge scenario used a quoted key name (`"ArrowRight"`, then
  `"right"`) for the key press. Moodle's named-key step takes the key unquoted and
  keyed by its own name, so it is now `I press the right key`, which the browser
  delivers to the slider as `KeyboardEvent.key` `ArrowRight`. Test only. (Behat
  dry-run cannot catch this: the step regex matches any text, so only a real
  @javascript run validates the key name.)
- Rebuilt `amd/build/player.min.js` so it matches its source again. A comment in
  `amd/src/player.js` was updated during the documentation cleanup without
  regenerating the AMD bundle; Moodle's build keeps the leading docblock in the
  minified file, so the stale bundle failed the CI Grunt staleness check. No
  runtime code changed — the minified code after the banner is byte-identical.


## [2.0.0-alpha.85] - 2026-08-12

### Changed
- Supported Moodle range is declared as 4.5 through 5.2 (`[405, 502]`). Moodle 5.3
  is not yet stable, so it is no longer declared as supported; it continues to be
  exercised in CI against its development branch, and the upper bound will rise
  once 5.3 is released.
- The whole-activity grade rebuild now processes learners in bounded batches, so
  neither the best-score query nor the grades array grows with the total number
  of learners on the activity.
- Schema (`install.xml`), `version.php`, `services.php` and the editor's React
  loader now carry technical documentation only: internal planning references,
  phase numbering and a product/business note ("separate paid subplugin") are
  removed, and the React loader records the intended switch to core's React once
  the minimum supported version reaches 5.2.
- README is self-contained: the provenance and licensing (the two licences, the
  CeCILL-B attribution, and that version 2.0 carries over no version 1 source) is
  now a section in the README itself instead of a link into `docs/`, and the
  supported-version statement matches the declared range.

## [2.0.0-alpha.84] - 2026-08-12

### Fixed
- The player now loads the exercise medium. The plugin declared media and poster
  file areas and generated pluginfile URLs for them, but shipped no
  `mod_elang_pluginfile()` callback, so Moodle refused every such request and the
  video (or audio) never appeared — the transcript and controls showed, the frame
  stayed empty. The callback serves the `media` and `poster` areas after checking
  the viewer may see the activity and that the requested version belongs to it, so
  a token for one activity cannot read another's media. Covered by lib tests and a
  player Behat scenario.

## [2.0.0-alpha.83] - 2026-08-12

### Fixed
- Messages that reach teachers in the browser are localisable instead of
  hardcoded English: every publish-validation problem (no cues, empty solution,
  unknown grading algorithm, bad or overlapping character range, non-contiguous
  hint levels, no gaps) and every migration-verification discrepancy now goes
  through `get_string()`. 23 new strings in English and German (280/280).

### Changed
- Every `@param` and `@return` tag in the shipped code now carries a description,
  as the Moodle coding style requires — 93 bare tags filled in. phpcs now reports
  zero errors *and* zero warnings, which is what the CI gate enforces.
- The over-long docblock line in `grading_result.php` is wrapped, so no shipped
  non-language line exceeds the 132-character guidance.
- `version_manager::create_draft()` is documented as the low-level entry point it
  is: it does not enforce the one-draft-per-activity invariant, and ordinary code
  must use `get_or_create_draft()`.

## [2.0.0-alpha.82] - 2026-08-12

### Changed
- Comments across the code base now explain the current logic instead of the
  development process: references to internal planning documents, review notes,
  phase and slice numbering are gone, and the statements that had become untrue
  (the player, authoring editor, reporting, exports, language field, timeline and
  migration admin tooling all described as "not yet built") now describe what the
  code actually does.
- Third-party documentation for the bundled React runtime is accurate again: the
  vendor README described the superseded AMD architecture, and there was no
  `readme_moodle.txt`. Both are rewritten/added with provenance, upstream URLs and
  reproducible build steps, the MIT licence text is shipped alongside, and
  `thirdpartylibs.xml` no longer describes the bundle as an AMD module.
- The production JavaScript bundle no longer ships a source map, which roughly
  tripled the payload and exposed the unminified sources. A map is still produced
  for a development build (`node build.mjs --dev`).

### Removed
- Two dead language strings (`skeletonnotice`, `editor:gaps`), the first of which
  still claimed the player was unimplemented.

## [2.0.0-alpha.81] - 2026-08-12

### Security
- Attempt actions in the report are now authorised per object through one shared
  check: in separate-groups mode a teacher can no longer delete (or inspect) an
  attempt belonging to a learner outside their groups. Previously the delete path
  verified only the capability and that the attempt belonged to the activity.
- Restoring a backup no longer falls back to the source site's numeric user id
  when a user cannot be mapped. That fallback could attribute a content version's
  authorship, or a migration sign-off, to an unrelated user who happened to hold
  the same id on the destination site; unmapped now means unknown.

### Fixed
- The privacy provider now covers every personal field it declares. Users who
  only authored content (`elang_version.usermodified`) or approved a 1.x
  migration (`elang.migrationapproveduserid`) are found by context discovery and
  the userlist, included in exports, and detached on erasure — the content itself
  is kept, only the identifying reference is cleared. `migrationapproveduserid`
  is now declared in the metadata too.
- The attempt report export streams rows from a recordset (with the learner name
  joined in SQL) instead of materialising every attempt and user in memory, so
  exporting a long attempt history no longer scales in memory with its size.
- Migration admin paths are batched and bounded: the dry-run report reads its
  activities, cues and learner counts in one query each and covers a bounded
  block per run; the migration verifier resolves cues, gaps, hints, attempts and
  response counts in batched queries instead of one per row; and decommission
  blocker messages quote an exact count plus a bounded sample of ids rather than
  every id on the site.
- CI robustness: the workflow configures npm retry/backoff so a transient GitHub
  download failure during node setup no longer fails the build. Coding-style fix
  in the generate_rule_gaps web service, and the load/browser seeders now pass
  `introeditor` to `create_module()` as a real Moodle site requires.

### Changed
- Documentation: README rewritten to the standard plugin template; the license and
  provenance note translated to English and renamed to `License_and_Provenance.md`.
- Load tests: realistic default seed size and p95 latency budget (latency scales
  with payload size; the error rate is the hard gate), and the JMeter plan now
  asserts the response actually contains the content.

## [2.0.0-alpha.80] - 2026-08-12

### Added
- Special-character bar foundation: a language-derived provider of accented and
  other special characters (French, German, Spanish, Italian, Portuguese), which
  an activity can later override. The exercise web service now returns the set for
  the exercise language, so the player can offer an insert bar for answers.

### Tests
- A @javascript scenario covering the rule-based gap control end to end
  (generate, then apply).

## [2.0.0-alpha.79] - 2026-08-12

### Added
- Editor UI for rule-based gaps: a per-cue control in the Subtitle Studio to
  generate gaps from a rule (a vocabulary word list, or every nth word). It
  reports how many gaps the rule would create and only replaces the cue's gaps
  once the author confirms, so a rule never silently discards hand-placed gaps.

## [2.0.0-alpha.78] - 2026-08-12

### Added
- Web service `mod_elang_generate_rule_gaps`: applies a rule-based gap rule
  (a vocabulary word list, or every nth word) to a transcript and returns the
  gap spans for the editor to create, without saving. Requires the manage
  capability; the second step of the 2.1 rule-based-gaps feature.

## [2.0.0-alpha.77] - 2026-08-12

### Added
- Activity overview for the Moodle 5.0+ course overview page
  (`mod_elang\courseformat\overview`): a teacher action linking to the attempt
  report and a count of attempts. Inert on 4.5, where the overview feature does
  not exist.
- A rule-based gap generator (`mod_elang\local\authoring\gap_rule_generator`):
  the foundation for 2.1 "rule-based gaps", turning a transcript plus a rule
  (a vocabulary word list, or every nth word) into codepoint-correct gap spans.
  Pure logic; not yet wired into the editor.

### Tests
- A @javascript scenario for nudging a cue's start edge with the keyboard
  (the timeline handles are ARIA sliders).

## [2.0.0-alpha.76] - 2026-08-12

### Changed
- Authoring web services now report an unknown exercise version with a clear
  message ("That exercise version no longer exists.") instead of a raw database
  record-not-found exception. This only affects direct web-service calls with a
  stale or invalid version id; the editor always passes a valid one.

### Tests
- Deeper @javascript Behat coverage of the Subtitle Studio editor: adding a cue
  creates a cue row and autosaves it, and the learner preview hides a gap's
  solution.
- Load and browser/accessibility test artefacts: k6 and JMeter read-endpoint load
  tests for `mod_elang_get_version_content` (with seeders that build a large
  exercise and mint a REST token) under `tests/load`, and a Playwright + axe-core
  suite scanning the view, report and Subtitle Studio pages for WCAG 2.1 A/AA
  violations plus an editor smoke, with a fixture seeder, under `tests/playwright`.
- A real V1-to-2.0 upgrade test (`tests/upgrade_test.php`) that reconstructs the
  V1 database state, runs the actual `xmldb_elang_upgrade()` DDL through every
  savepoint — catching schema clashes a fresh install never exercises — and then
  asserts the one-way content migration still runs with stable cue/gap keys on
  the upgraded schema.

## [2.0.0-alpha.75] - 2026-08-12

### Added — course backup and restore (production gate)
- The activity now supports Moodle course backup and restore, so course backups,
  imports and duplications no longer silently lose its content. `elang_supports`
  now advertises `FEATURE_BACKUP_MOODLE2`, backed by a full `backup/moodle2/`
  implementation. The backup carries the whole content tree (versions, cues,
  gaps, accepted-answer variants and hints) plus the media and poster files, and
  — only when the backup includes user information — every learner attempt and
  response. On restore, all internal references are remapped: a version's author,
  an attempt's version and user, a response's gap, and the activity's forward
  reference to its current published version (`currentversionid`). Verified by a
  PHPUnit backup-and-restore round trip, including the user-info-off case that
  restores the content but no attempts.

### Tests
- Behat coverage for the attempt report (empty state, a finished attempt showing
  up, and the action bar offering learners only the learner actions) plus
  @javascript scenarios for the Subtitle Studio editor (onboarding empty state
  and the authoring toolbar), with a reusable step that seeds a finished attempt
  through the domain layer.

## [2.0.0-alpha.74] - 2026-08-12

### Changed — authoring write-path hardening (E3)
- Saving a draft now rejects a structurally corrupt payload with a clear message
  instead of letting it surface as a raw database write error mid-save. The three
  identity constraints backed by unique indexes are checked up front: a repeated
  cue key (versionid-cuekey), a repeated gap key within a cue (cueid-gapkey) and a
  repeated hint level within a gap (gapid-level). A negative gap offset or length
  — structurally impossible and able to break codepoint slicing in the editor
  preview — is rejected as well. These join the existing save-time checks (known
  grading algorithm, valid regex, penalty range, known hint type). Draft saves
  otherwise remain permissive: incomplete, not-yet-valid content is still allowed,
  with full semantic validation deferred to publish.

### Fixed
- Completed the PHPDoc for `attempt_report::list_for_activity`: the `$page` and
  `$perpage` parameters added in 2.0.0-alpha.72 were missing `@param` lines, which
  the Moodle PHPDoc checker (moodle-plugin-ci `phpdoc`) reports as an incomplete
  parameter list. This unblocks the JS/Mustache/PHPDoc lint job.

## [2.0.0-alpha.73] - 2026-08-11

### Added — Subtitle Studio (authoring UX, AP-D / E2)
- The transcript editor now keeps every gap aligned with its word while the text
  is edited: a gap before an edit is untouched, a gap after it shifts, a gap the
  edit grows or shrinks is remapped, and a gap whose text is deleted outright is
  dropped. Offsets are handled in Unicode codepoints (matching the server's
  mb_substr grading view), so a transcript containing an emoji or other astral
  character no longer misaligns the gaps after it. Gaps created from a textarea
  selection are converted from UTF-16 to codepoint offsets for the same reason.
- An inline masked "learner preview" per cue shows exactly what a learner sees —
  the transcript with every gap blanked out — without a round trip to the server,
  mirroring the server-side masker.
- The timeline gained an audio waveform (decoded once via the Web Audio API and
  drawn as an SVG band; it degrades silently for provider embeds, cross-origin
  URLs or browsers without AudioContext) and draggable cue-edge handles that snap
  to neighbouring edges and the playhead. The handles are ARIA sliders and are
  fully keyboard-operable (arrow keys nudge, Shift for coarse steps).
- Content now autosaves: edits are debounced and coalesced into one save, with a
  live "unsaved / saving… / all changes saved / error" indicator; manual Save and
  Publish flush the same controller so the two never disagree.
- A guided empty state walks a first-time author through choosing a medium,
  importing subtitles or adding cues, and marking gaps, instead of a bare "no
  cues yet" line.

### Notes
- Pure studio logic (gap re-sync, masking, snapping, waveform peak extraction,
  the autosave state machine) lives in framework-free modules under js/src/studio
  with 22 new Jest tests; the React bundle is rebuilt reproducibly and the AMD
  loader regenerated. No web-service or grading logic changed.

## [2.0.0-alpha.72] - 2026-08-11

### Performance (scaling on the normal author/report paths)
- `version_validator::validate()` no longer issues one query per cue and one per
  gap on the publish path: it loads all cues, gaps and hint levels in three
  batched queries and groups them in memory. A query-count budget test asserts
  the read count stays constant as the version grows.
- Copying a published version into a new draft (`create_draft`) reads the whole
  source subtree in four batched queries instead of one per cue and two per gap;
  the inserts stay per-row because each child needs its new parent's id.
- The teacher report overview is paginated (50 attempts per page, with a paging
  bar) and backed by a `COUNT` query, so it no longer loads an activity's entire
  attempt history into memory. Data export keeps its full, unpaged behaviour.
- V1 migration detection resolves and bounds the pending set in the database: a
  single left join replaces the per-id `get_field` lookup, `pending_activity_ids()`
  accepts a limit applied server-side, and a new `count_pending_activities()`
  reports the total without materialising the ids. The scheduled task now fetches
  a bounded block rather than the whole pending list.

### Changed
- `version_manager`'s class documentation now states the actual one-draft-per-
  activity invariant (maintained by `get_or_create_draft()` under a per-activity
  lock, not by a non-portable partial unique index) instead of a stale "authoring
  is a future phase" note. `get_or_create_draft()` tolerates a stray second draft
  by returning the most recent one rather than failing.
- README updated to the current feature state: the authoring studio, reports and
  exports are documented as implemented, the `mod/elang:exportsolution`
  capability and the `mod_elang/allowedlanguages` site setting are described, and
  the worksheet-versus-solution export boundary is spelled out.

## [2.0.0-alpha.71] - 2026-08-11

### Security (release blockers closed with tests)
- Transcript export (P0): the learner worksheet is always masked (no gap
  solution in any of TXT/PDF/DOCX/ODT); the full solution transcript now
  requires the new `mod/elang:exportsolution` capability, which learners do not
  hold. Regression tests assert no accepted-answer text can leak through the
  default or worksheet export, and that the solution copy still carries it.
- Regex answer variants (P0): saving a draft that stores an `isregex` variant
  now enforces `mod/elang:useregex` server-side (not just in the React UI). An
  editing teacher without the capability is refused; a manager is allowed.
- Domain validation (P1): `save_draft_version` now rejects a hand-crafted
  payload with a hint penalty outside `[0, 1]`, an `isregex` flag other than 0/1,
  an uncompilable regex variant, or an unknown grading algorithm or hint type.
  Attempt and rawgrade scores are additionally clamped to `[0, 1]` defensively.

### Added
- `mod/elang:deleteattempts` and `mod/elang:exportreports` are implemented, not
  just declared: the teacher report can export attempts through the Dataformat
  API (CSV/Excel/ODS/JSON, honouring the group filter) and delete an attempt
  through a confirmed, sesskey-protected POST that also regrades the learner.
- New unbranded activity monologo plus a colour plugin logo (`pix/logo.svg`,
  `pix/logo.png`), referenced from the README.
- `package-lock.json` is now committed so `npm ci` reproduces the exact
  esbuild/grunt toolchain that builds the shipped bundles.

### Fixed
- `view.php` referenced `player:nocontent`, which did not exist in either
  language pack; the string is now present in English and German.
- `report.php` referenced `report:score` and `report:answered`, which were never
  defined; both strings are now present, closing a latent debugging() call.

### Changed
- The `makefile` is replaced with the fuller mod_vimipad suite, adapted to the
  elang paths (`js/vendor/react/editor.bundle.js`, `mod/elang`) and elang-named
  Playwright/JMeter/k6 load targets ready for the E5/E6 work packages.
- English and German language packs are back at full parity (229/229).

## [2.0.0-alpha.70] - 2026-08-11

### ACTION REQUIRED (one-time git cleanup)
- The repository still carries two stale files from before the alpha.66
  relocation: `amd/build/editor_lazy.min.js` and
  `amd/build/editor_lazy.min.js.map`. Extracting a release ZIP over the repo
  adds and updates files but never deletes ones the ZIP omits, so these lingered
  and keep breaking the CI JS lint job ("File no longer generated and likely
  should be deleted"): moodle-plugin-ci wipes `amd/build/`, Grunt regenerates
  editor.min.js and player.min.js from `amd/src`, but nothing regenerates
  editor_lazy (it has no `amd/src` counterpart — it moved to
  `js/vendor/react/editor.bundle.js` in alpha.66). Remove them once and commit:

      git rm amd/build/editor_lazy.min.js amd/build/editor_lazy.min.js.map
      git commit -m "Remove stale editor_lazy build artefacts (moved to js/vendor/react)"

  A helper is included: `bash tools/cleanup_stale.sh` runs exactly those git rm
  commands. After this, `amd/build/` contains only editor.min.js / player.min.js
  (+ maps), and the JS lint job passes.

### Fixed
- `tools/fix_phpdoc.php` carried a copied-in `@package local_instantcoursecompletion`
  tag and component constant from another plugin; corrected to `mod_elang` so a
  phpcs run over the developer tools no longer reports an incorrect package tag.

## [2.0.0-alpha.69] - 2026-08-11

### Changed
- Removed the duplicated setUp() scaffolding and `payload()` helper that the
  copy/paste detector flagged across the authoring external-function tests
  (get_version_content, save_draft_version, set_draft_media, preview_import).
  A new `tests/fixtures/authoring_test_fixture.php` provides a
  `authoring_test_fixture_builder::create()` (course + editing teacher +
  student + fresh draft) and a shared `payload()`, following the established
  plain-class-with-static-factory fixture pattern (loaded via require_once in
  setUp, which avoids the "Trait not found" pitfall on Moodle 4.5). phpcpd now
  reports no clones; the five authoring tests still pass unchanged.

### Note
- This release also carries the alpha.68 `.gitignore` fix (anchoring `/vendor/`
  so `js/vendor/react/` is committed). If a CI run still fails on
  `amd/build/editor_lazy.min.js` or a missing `js/vendor/react`, the checked-out
  commit predates these fixes — verify `$plugin->release` in the checkout is
  alpha.69 and that `js/vendor/react/editor.bundle.js` is present in git.

## [2.0.0-alpha.68] - 2026-08-11

### Fixed
- The real reason the React bundle kept vanishing from CI checkouts across
  alpha.65–67: `.gitignore` contained an unanchored `vendor/` rule (meant for
  the Composer directory at the plugin root), which also matched `js/vendor/`,
  so git silently refused to add `js/vendor/react/` (bundle and README). Every
  delivery therefore shipped a directory that could never be committed, and CI
  failed on the now-missing `thirdpartylibs.xml` location (`ENOENT` in Grunt's
  ignorefiles task; `Vendors.php: non-existent path` in moodle-plugin-ci
  phplint/validate). The rule is now anchored to `/vendor/`, so it ignores only
  the root Composer directory and `js/vendor/react/` is committed. Verified with
  `git check-ignore` that the bundle, its README and all `amd/build/` artefacts
  are trackable, and that the root `vendor/` is still ignored.

## [2.0.0-alpha.67] - 2026-08-11

### Changed
- Removed the last stale references to the old `amd/build/editor_lazy.min.js`
  path (a follow-up to the alpha.66 relocation): the `thirdpartylibs.xml`
  description, the `.gitignore` comment and the `makefile` `react` target echo
  now all name `js/vendor/react/editor.bundle.js`, so nothing points a rebuild
  or a reader back at the retired location. No functional change to the built
  code; re-verified that `moodle-plugin-ci grunt --max-lint-warnings 0`,
  `phpcs --max-warnings 0` and `validate` all exit 0 both with the bundle
  present and with it removed.

## [2.0.0-alpha.66] - 2026-08-10

### Fixed
- CI JS lint job failed on the prebuilt React bundle in two ways that both stem
  from `amd/build/` being the wrong home for an esbuild artefact: Moodle's Grunt
  and moodle-plugin-ci `stat()` every `thirdpartylibs.xml` `<location>` (so an
  absent `amd/build/editor_lazy.min.js` aborted with ENOENT / a non-existent
  path), and moodle-plugin-ci wipes `amd/build/` before re-running Grunt and
  then flags any file Grunt did not regenerate (so a *present* bundle failed
  with "no longer generated"). The React bundle now lives in
  `js/vendor/react/editor.bundle.js` (a plain directory Grunt never touches),
  is declared in `thirdpartylibs.xml` as the `js/vendor/react` directory (with a
  README documenting React/ReactDOM/Scheduler), and is loaded by `edit.php` as a
  regular page script (`$PAGE->requires->js`) exposing `window.mod_elang_editor`;
  `amd/src/editor.js` reads that global instead of `require()`-ing an AMD module.
  Verified in both states — bundle present and bundle absent — that
  `moodle-plugin-ci grunt --max-lint-warnings 0`, `phpcs --max-warnings 0` and
  `validate` all exit 0.

### Changed
- `build.mjs` now emits `js/vendor/react/editor.bundle.js` exposing a global,
  instead of an AMD-wrapped `amd/build/editor_lazy.min.js`.

## [2.0.0-alpha.65] - 2026-08-10

### Fixed
- CI JS/Mustache/PHPDoc lint job failed because Moodle's Grunt stats every
  `amd/build/` path declared in `thirdpartylibs.xml`, and the esbuild bundle
  `editor_lazy.min.js` could go missing from a checkout (a text patch cannot
  carry the binary min.js). The editor bootstrap now takes the bundle's module
  id from the editor root's `data-editormodule` attribute (set in
  editor.mustache) instead of a JS literal, so the reference lives in one place;
  `editor.min.js` rebuilt. Ship the plugin as a full archive (not a patch) so
  the prebuilt bundle is always present.

## [2.0.0-alpha.64] - 2026-08-10

### Added
- Transcript export now offers Word (DOCX) and OpenDocument (ODT) alongside the
  existing PDF and plain text, from the chooser on `transcript.php` and via
  `?format=docx` / `?format=odt`.
- `classes/local/export/docx_writer.php`: builds a minimal, valid OOXML
  WordprocessingML container (content types, package/document relationships,
  document body, styles) with no third-party library, packing through Moodle's
  core `zip_packer`. Title as Heading 1, one paragraph per cue, text
  XML-escaped.
- `classes/local/export/odt_writer.php`: builds a minimal, valid OpenDocument
  Text container with no third-party library, using PHP's core `ZipArchive` so
  the `mimetype` part is stored uncompressed and first (verified externally:
  `file` identifies the result as "OpenDocument Text").
- `transcript_exporter` gained `paragraphs()` (ordered non-empty cue
  transcripts), the shared basis for every output format; `plain_text()` now
  builds on it. New language strings `export:docx` / `export:odt` (en/de).
- PHPUnit coverage for both writers (container parts, escaping, empty
  transcript; ODT mimetype stored-first) and for `paragraphs()`.

### Docs
- `docs/materials/Arbeitsplanung_Authoring_und_Subtitle_Studio.md`: P3 marked
  done (minimal self-built DOCX/ODT, no external library).

## [2.0.0-alpha.63] - 2026-08-10

### Added
- Admin setting `mod_elang/allowedlanguages` (Site administration > Plugins >
  Activity modules > eLang): a multiselect that narrows the content languages
  the activity settings form offers. Empty (the default) means no restriction.
- `classes/local/settings/language_options.php`: the single place that builds
  the offered language list, honouring the admin restriction, mapping lang-pack
  variants to their base code, and always keeping an activity's stored language
  in the dropdown even after the admin tightens the list. PHPUnit-covered.

### Changed
- `mod_form.php` now builds its language dropdown through `language_options`
  instead of inline logic, so the restriction and base-code mapping are shared
  and tested.

### Docs
- `docs/materials/Arbeitsplanung_Authoring_und_Subtitle_Studio.md`: P2 marked
  done.

## [2.0.0-alpha.62] - 2026-08-10

### Added
- Curated media provider registry (`classes/local/media/provider_registry.php`):
  the single source of truth for which external providers the player embeds
  (YouTube, Vimeo — OAuth-free only, by design) and for normalising a
  teacher-supplied reference to the canonical video id. It accepts a bare id or
  any common URL shape: watch URLs with tracking parameters, `youtu.be` short
  links, `/shorts/`, `/embed/`, `/live/`, `player.vimeo.com`, channel paths,
  with or without scheme/`www`. Covered by a 27-case PHPUnit matrix.
- `set_draft_media` now validates a provider medium against the registry and
  stores the normalised id, rejecting unknown providers
  (`error:unknownmediaprovider`) and unparseable references
  (`error:invalidproviderref`).
- `get_version_content` returns the curated `mediaproviders` list, so the
  editor's media panel offers a provider dropdown (localised
  `provider:youtube`/`provider:vimeo`) instead of a free-text field, with a
  reference hint (`editor:mediaproviderref` / `editor:mediaproviderrefhint`).
  New Jest test `media_panel.test.tsx`.

### Changed
- Activity settings: the language field now defaults to the course language
  (falling back to the site language, mapping lang-pack variants like `de_du`
  onto their base code) for new instances, when that language is offered;
  editing an existing instance keeps its stored value.
- `amd/src/player.js`: comment clarifying that provider refs arrive already
  normalised, keeping the embed table in step with the registry (minified
  output unchanged).

### Docs
- `docs/materials/Arbeitsplanung_Authoring_und_Subtitle_Studio.md`: records the
  P1 provider-registry / reference-normalisation and language pre-selection.

## [2.0.0-alpha.61] - 2026-08-10

### Added
- React/TypeScript authoring frontend (adopted from mod_vimipad): sources in
  `js/src` (typed API client with injectable transport, `EditorApp` with cue/
  gap/hint/media/import/timeline components), bundled by esbuild (`build.mjs`)
  into the committed AMD artefact `amd/build/editor_lazy.min.js` (React 18.3.1,
  ReactDOM, Scheduler — declared in the new `thirdpartylibs.xml`). The learner
  player stays framework-free. Jest/jsdom test suite in `js/tests` (service
  payload shape, full mount/import/save flow); `package.json`/`tsconfig.json`
  for the development-only toolchain.
- Subtitle import can recognise the mod_elang 1.x inline gap syntax: the new
  `gap_syntax_parser` turns `[word]` into a gap with hints allowed (seeding
  one solution hint, matching the V1 migration semantics) and `{word}` into a
  gap without hints; unusable markers stay literal. `preview_import` gained an
  optional `parsegaps` parameter and a per-cue `gaps` return structure
  (backwards compatible); the editor's import panel offers it as a checkbox.
  New language string `editor:parsegaps` (en/de).

### Changed
- `amd/src/editor.js` is now a thin bootstrap (strings via core/str, transport
  via core/ajax, mounts the bundled React editor); `templates/editor.mustache`
  reduced to a server-rendered shell (heading + loading status + mount region).
- `.phpcsignore`/`phpcs.xml` exclude `js/` (TypeScript sources are linted by
  tsc/ESLint, not PHPCS).
- `.github/workflows/moodle-ci.yml`: the `lint-js` job no longer uses a
  `mariadb:10.11` Docker service; it starts the MySQL that ships preinstalled
  on the ubuntu-24.04 runner instead (`sudo systemctl start mysql.service`,
  `mysqli` driver). That job needs a database only as a throwaway install
  target, so the engine is irrelevant, and this removes its dependence on a
  Docker Hub image pull. A `services:` image is pulled before any step runs
  with no retry, so a transient Hub timeout (`context deadline exceeded` on
  registry-1.docker.io) was failing the whole job at container-start and
  reading as a lint failure though no lint ran. The blocking `phpunit`/`behat`
  jobs keep their Docker service containers deliberately — they must exercise
  real MariaDB *and* PostgreSQL — and so remain reliant on Docker Hub by
  design; a transient Hub pull failure there is infrastructure, cleared by
  re-running the job. No installed plugin file changed, so `version.php` is
  intentionally not bumped.

### Fixed
- `db/install.xml`: removed the empty `<INDEXES>` block on `elang_gapanswer`
  (rejected by core's XMLDB normalisation check `plugin_checks_test`).
- Privacy metadata now covers `elang_version.usermodified` (core
  `provider_test::test_table_coverage`); new `privacy:metadata:elang_version*`
  strings (en/de).

### Docs
- Session 004 closed: `docs/sessions/session-004.md` (end-of-session log),
  `docs/prompt-templates/sessionstart.txt` (current state updated to alpha.60),
  and `docs/materials/Arbeitsplanung_Authoring_und_Subtitle_Studio.md` (work
  plan: current-package remainder P1-P3 and the new "Subtitle Studio &
  Authoring-UX" work package). No installed plugin file changed, so
  `version.php` is intentionally not bumped.
- `docs/materials/Arbeitsplanung_Authoring_und_Subtitle_Studio.md`: records
  the approved AP-D technology decision (bundled React/TS authoring tool,
  framework-free player, migration path to core React once 4.5 support
  ends) and the implemented V1 gap-syntax import option.

## [2.0.0-alpha.60] - 2026-07-26

### Fixed
- The content editor could not load its draft: `edit.php` passed the course
  module id and the draft version id to the AMD module, but `init()` takes only
  the version id, so it queried `elang_version` with the cmid and got "record
  not found". It now passes only the draft version id. Added a `@javascript`
  Behat scenario that opens the editor and asserts it loads without the error.

## [2.0.0-alpha.59] - 2026-07-26

### Changed
- The content editor now appends the real error to its "could not be loaded"
  status (e.g. a missing web service or a server error), so a failed
  `get_version_content` call is diagnosable without the browser console. Bumping
  the version also re-runs `external_update_services` on upgrade, which
  registers the authoring web services the editor calls.

### Note
- After deploying, run Site administration -> Notifications (registers the web
  services and purges the navigation cache). If the editor still shows an error,
  the bracketed message now names the cause.

## [2.0.0-alpha.58] - 2026-07-26

### Fixed
- The authoring tools are now reachable directly from the activity page: for
  users with the capability, `view.php` shows an action bar with "Edit content"
  (manage), "Reports" (viewreports) and "Export transcript" (exporttranscript)
  buttons. This no longer depends solely on the secondary-navigation "More"
  menu, which needs a navigation-cache purge to pick up new nodes.
- Reverted `makefile` to its committed state (a hard-gate refactor had made
  `lint-mustache` fail on the tool's "Total errors: 0" summary line).

### Tests
- Behat: a teacher reaches the editor, the report and the transcript export from
  the activity page, and the settings form shows the "Answer grading" section.

### Note
- After deploying, visit Site administration -> Notifications to run the upgrade
  (this purges the navigation cache), or purge all caches, so the "More" menu
  items also appear. The bundled `amd/build/editor.min.js` is a hand-built AMD
  wrapper; `grunt amd` regenerates it canonically.

## [2.0.0-alpha.57] - 2026-07-26

### Added
- Content editor timeline and media upload. The editor shows a media preview (a
  native player) for a playable file or url medium and a timeline strip where
  each cue sits at its time; during playback a playhead moves and the current
  cue highlights, clicking a cue's block seeks the media there, and each cue row
  has "Set start/end from playback" buttons. A new `media.php` page (a Moodle
  form with two file managers, gated on `mod/elang:manage`, linked from the
  editor's media panel as "Upload media files") uploads the video/audio and an
  optional poster through Moodle's file picker and stores them via
  `version_manager::set_draft_media()`. New `media_form`, editor/timeline strings
  and styles.

### Note
- `amd/build/editor.min.js` ships as a hand-built AMD wrapper; `grunt amd`
  (e.g. `make amd`) regenerates it canonically. Provider media (e.g. YouTube)
  and drag-to-resize timing are later timeline increments.

## [2.0.0-alpha.56] - 2026-07-26

### Added
- Transcript export. A new `transcript.php` (gated on
  `mod/elang:exporttranscript`, which learners hold too, linked from the
  settings navigation as "Export transcript") streams the published version's
  transcript as a PDF (Moodle's bundled TCPDF) or a plain-text file, or shows a
  small format chooser. Backed by a new `transcript_exporter` domain class that
  assembles the transcript from the version's cues. New export strings (en, de).

### Changed
- The teacher attempt report now honours the activity's group mode: it shows the
  standard group selector and lists only the chosen group's attempts, and under
  separate groups a teacher without access-all-groups can only open an attempt
  by a learner who shares one of their groups. `attempt_report::list_for_activity`
  gained an optional group filter.

### Tests
- transcript_exporter joins cue transcripts in order and is empty for a version
  with no cues; the attempt listing filters to a group's members.

### Note
- Export currently produces PDF and text. Word (.docx) and OpenDocument (.odt)
  need a document writer that Moodle core does not provide and are a planned
  follow-up format; the PDF covers the formatted-document need in the meantime.

## [2.0.0-alpha.55] - 2026-07-26

### Changed
- The content language in the settings form is now a dropdown of the languages
  Moodle knows (`get_string_manager()->get_list_of_languages()`), with a
  "Generic (not specified)" first option, instead of a free-text field. Unknown
  or generic codes still fall back to the default (Latin) script handling, so
  grading behaviour is unchanged. New string `language_none` (en, de).

## [2.0.0-alpha.54] - 2026-07-26

### Added
- Teacher attempt report. A new `report.php` (gated on `mod/elang:viewreports`,
  linked from the activity's settings navigation as "Reports") lists every
  learner attempt with its state, score and gap aggregates, and drills into one
  attempt to show it gap by gap — each gap's solution, the learner's response,
  the result, the number of tries and the hint level reached. It is read only;
  grade overrides remain in the Moodle gradebook. Backed by a new
  `attempt_report` domain class (server-rendered, no JavaScript build needed).
  New report UI strings (en, de).

### Tests
- attempt_report: a finished attempt is listed and its detail pairs each gap
  with the learner's response and result in order; an activity with no attempts
  lists nothing.

## [2.0.0-alpha.53] - 2026-07-26

### Added
- Hint editing in the content editor, completing gap authoring. Each gap now
  lists its graded hints as editable rows: a manager can add and remove hints
  and set each one's type (free text, first letter, word length, partial,
  solution or translation), its text and its score penalty. Hint levels are
  kept as a contiguous 1..n sequence automatically, so a published version
  always satisfies the validator. New editor UI strings (en, de).

### Note
- `amd/src/editor.js` changed but `amd/build/` is not included: run `grunt amd`
  (e.g. `make amd`) to rebuild the module.

## [2.0.0-alpha.52] - 2026-07-26

### Added
- Gap editing in the content editor — the core authoring step. Each cue now
  shows its gaps as editable rows instead of a read-only summary: a manager can
  mark a new gap by selecting the word to blank out in the transcript (the
  selection becomes the gap's character range and default solution), edit each
  gap's solution and matching algorithm (exact or recognise-close-answers), add
  and remove accepted answer variants, and delete gaps. All of this round-trips
  through `save_draft_version`; existing hints on a gap are preserved untouched
  (hint editing is a later increment). New editor UI strings (en, de).

### Note
- `amd/src/editor.js` changed but `amd/build/` is not included: run `grunt amd`
  (e.g. `make amd`) to rebuild the module.
- Gap ranges are captured from the transcript textarea selection (UTF-16
  offsets), which match character offsets for the common (BMP) case; editing a
  transcript after marking gaps can move their ranges, which publish-time
  validation catches. Re-syncing on transcript edits comes with the timeline
  editor.

## [2.0.0-alpha.51] - 2026-07-26

### Added
- Editor media panel (first increment). The content editor now shows the
  draft's current medium — including an existing uploaded file, as a link — and
  lets a manager set a direct URL medium, an embeddable provider medium, or none
  through `set_draft_media`. The type selector reveals only the relevant fields,
  and saving refreshes the current-medium line. New editor UI strings (en, de).

### Note
- `amd/src/editor.js` changed but `amd/build/` is not included: run `grunt amd`
  (e.g. `make amd`) to rebuild the module.
- File upload (video/poster) needs Moodle's file picker wired into the page and
  is the next media increment; URL and provider media are fully functional now.

## [2.0.0-alpha.50] - 2026-07-26

### Added
- Activity-level content settings in the module form: a **Content language**
  field (`language`) and a **Fuzzy-match threshold** field (`jarothreshold`),
  each with help text, under a new "Answer grading" section. These were
  previously only reachable through the database — the form defaulted language
  to empty and the threshold to 1. They are the activity defaults new draft
  versions inherit (see `version_manager::create_draft`); grading a published
  version still uses that version's own pinned copy. The threshold is validated
  to the 0–1 range (`jarothresholdrange`).

### Tests
- The submitted language and threshold are stored on create and update
  (`elang_add_instance` / `elang_update_instance`).

## [2.0.0-alpha.49] - 2026-07-26

### Added
- Content editor foundation (frontend). A new authoring page `edit.php`
  (gated on `mod/elang:manage`) ensures the activity has a draft to edit
  (branching a copy from the published version) and hands off to a new
  `mod_elang/editor` AMD module, which loads the draft through
  `get_version_content` and renders its cues as an editable list. Cue timings
  and transcripts can be edited, cues added, removed and imported from
  WebVTT/SubRip (via `preview_import`), and the draft saved (with its revision
  as an optimistic-concurrency token) or validated and published (via
  `save_draft_version` / `publish_version`). Existing gaps are preserved and
  round-tripped untouched; gap authoring, the media panel and the full timeline
  are layered on in later slices.
- An "Edit content" link in the activity's settings navigation for managers
  (`elang_extend_settings_navigation`), a `mod_elang/editor` Mustache shell and
  the editor's UI strings (en, de).

### Note
- `amd/src/editor.js` is new but `amd/build/` is not included: run `grunt amd`
  (e.g. `make amd`) to build `amd/build/editor.min.js` and its source map. CI
  builds AMD; the editor page needs the built module to run.

## [2.0.0-alpha.48] - 2026-07-26

### Added
- Authoring media handling. `version_manager::set_draft_media()` sets a draft's
  medium — an uploaded file (video/audio plus optional poster), a direct url, an
  embeddable provider reference, or none — versioned like all other content:
  file uploads are saved into the version's own 'media'/'poster' areas (itemid =
  the version id), and whichever columns and file areas do not belong to the
  chosen kind are cleared, so switching medium never leaves a stale upload
  behind. Only a draft can be changed; an unknown kind is rejected
  (`error:invalidmediakind`).
- `mod_elang_set_draft_media` exposes this to the editor (gated on
  `mod/elang:manage`), taking the ids of prepared draft file areas for file
  media and returning the resulting media descriptor.
- `mod_elang_get_version_content` now also returns the current media and poster
  file name and pluginfile URL, so the editor can display an existing upload.

### Tests
- set_draft_media: url and provider media set the right columns, an uploaded
  file is saved and marks the version file-kind, switching medium clears the old
  files, and a published version is immutable. The external function sets url
  media, round-trips a file upload out through get_version_content, and denies a
  non-manager.

## [2.0.0-alpha.47] - 2026-07-26

### Added
- Subtitle import (preview). A new `subtitle_parser` reads WebVTT and SubRip
  (.srt) files in a single lenient pass: it splits on blank lines, treats the
  line containing `-->` as the timing line (ignoring a SubRip index or a WebVTT
  cue identifier before it and any cue settings after it), skips the WEBVTT
  header and NOTE/STYLE/REGION sections, and accepts timestamps with either a
  comma or a dot before the milliseconds and an optional hours component. Blocks
  with an unparseable timing line or no transcript text are skipped and
  reported as warnings (`import:badtiming`, `import:emptytranscript`).
- `mod_elang_preview_import` exposes the parser to the editor (gated on
  `mod/elang:manage`): it returns the parsed cues, a count and any warnings
  without writing anything, so the teacher previews the result, marks gaps and
  then persists the draft through `save_draft_version`.

### Tests
- subtitle_parser: WebVTT and SubRip parsing, hours-optional timestamps, cue
  settings ignored, header/NOTE blocks skipped, and unparseable-timing and
  empty-transcript blocks skipped with a warning. preview_import: a manager
  previews parsed cues and a non-manager is denied.

## [2.0.0-alpha.46] - 2026-07-26

### Added
- Authoring read endpoint. `mod_elang_get_version_content` returns a version's
  metadata (status, revision, language, Jaro threshold, media columns) and its
  full content — every cue, gap, accepted answer and hint, INCLUDING solutions.
  It is the manager-facing counterpart to `get_attempt_cues` (which masks
  solutions for learners), gated on `mod/elang:manage`, and its shape mirrors
  `save_draft_version`'s input exactly so the editor can load, edit and save the
  same structure round-trip. Backed by a new
  `version_manager::load_version_content()` that assembles the view in a bounded
  number of queries.

### Changed
- The cue/gap/answer/hint external structure builders now live once on the
  `authoring_helper` trait and are shared by the read endpoint's return and the
  save endpoint's parameters, keeping the two contracts in lockstep.

### Tests
- get_version_content: a manager reads content with solutions, content saved
  through save_draft_version reads back unchanged (round-trip), and a
  non-manager is denied. load_version_content returns the full nested view and
  is empty for a version with no cues.

## [2.0.0-alpha.45] - 2026-07-26

### Added
- Authoring web services. `mod_elang_save_draft_version` overwrites a draft's
  content (cues, gaps, accepted answers, hints) with the editor's current state
  in one transaction under the activity lock, taking the version-stable
  cuekey/gapkey identities from the payload and advancing the draft's `revision`
  counter. It carries an optimistic-concurrency token (`expectedrevision`): a
  save whose expected revision no longer matches the stored one is refused
  (`error:draftrevisionmismatch`) so a concurrent edit is not clobbered, and a
  non-draft version is immutable (`error:versionnotadraft`). No content
  validation happens on save — a half-finished draft is a legitimate state.
- `mod_elang_publish_version` validates and publishes a draft, calling
  `version_manager::publish()` with validation on, so an incoherent draft is
  refused with its problems rather than shipped.
- Both services require `mod/elang:manage` in the activity context (new
  `version_manager::save_draft_content()` and an `authoring_helper` trait that
  resolves the context and authorises the caller).

### Tests
- publish_version: a manager publishes a valid draft, an invalid draft is
  refused, and a non-manager is denied. save_draft_version: saving persists the
  nested content and bumps the revision, a second save replaces the content, a
  stale expected revision and a published version are both refused, and a
  non-manager is denied.

## [2.0.0-alpha.44] - 2026-07-26

### Added
- Authoring foundation — a `version_validator` that inspects a draft's content
  and reports every problem that would make it unsafe to publish: no cues, no
  gaps to answer, empty solutions, gaps whose character range falls outside or
  overlaps within a transcript, unknown grading algorithms, and hint levels
  that are not a contiguous sequence from 1. It is read-only and never changes
  content.
- `version_manager::publish()` gained an opt-in `$validate` flag: when true it
  runs the validator first and refuses to publish an incoherent draft with the
  collected problems (`error:versionnotpublishable`). The default stays false,
  so the existing publish lifecycle and V1 migration — which migrates imperfect
  legacy data as-is and reports issues through `v1_verifier` — are unchanged;
  the forthcoming authoring web services will pass true.

### Tests
- Validator coverage for each rule (valid version, no cues, no gaps, empty
  solution, out-of-bounds gap, overlapping gaps, unknown algorithm,
  non-contiguous vs contiguous hint levels); publish rejects an invalid draft
  when validation is requested, accepts a valid one, and still publishes an
  empty version when validation is off.

## [2.0.0-alpha.43] - 2026-07-26

### Added
- Authoring foundation — copy-on-write draft creation. When an activity already
  has a published version, `version_manager::create_draft()` now branches from
  it: the grading settings, media columns, cues, gaps, accepted answers, hints
  and media/poster files are deep-copied into the new draft, preserving the
  version-stable `cuekey`/`gapkey` identities and remapping only row ids and
  parent keys. Editing therefore produces a new version that starts as a
  faithful copy of what learners currently see, while their in-progress
  attempts stay on the version they began. With no published version yet (a
  brand-new activity, or the first version built during V1 migration) the draft
  still starts empty and seeds its grading settings from the activity defaults.

### Tests
- A draft branched from a published version copies content, keys, grading
  settings and media files while leaving the source intact; a draft with no
  published version to branch from starts empty.

## [2.0.0-alpha.42] - 2026-07-26

### Fixed
- `version_manager::compute_content_hash()` no longer requires the activity to
  have a course module: it resolves the file context with `IGNORE_MISSING`
  (mirroring `v1_media_migrator`) and skips media/poster file hashing when
  there is no module — where there is no module there are no files anyway. This
  restores publishing during V1 migration, which builds a version before a
  course module context is available in the test fixtures, fixing the migrator,
  verifier, decommissioner, sign-off and migration-task test failures
  introduced in alpha.41.
- Player: the in-flight submit tracking no longer leaves a floating
  `Promise.finally()` (ESLint `promise/catch-or-return`); the tracked promise's
  cleanup is now returned, keeping `grunt` green (`amd/src/player.js`).

## [2.0.0-alpha.41] - 2026-07-25

### Added
- `elang_version` now carries the grading settings previously read from the
  activity: `language` and `jarothreshold` are copied onto each version when a
  draft is created, plus a `revision` counter for the authoring layer. An
  upgrade step adds the columns and backfills existing versions from their
  parent activity.

### Changed
- Grading an in-progress attempt reads the language and Jaro threshold from the
  attempt's pinned version (`attempt_manager::submit_response()`), not the
  activity, so editing an activity's settings and publishing a new version no
  longer changes how an existing attempt is scored.
- `version_manager::compute_content_hash()` now folds the media and poster
  files into the hash by their stored content hashes, so swapping a video or
  poster invalidates cached worksheets and player payloads.
- The V1 migrator writes the threshold mapped from a V1 activity's options onto
  the activity row before creating its first version, so the migrated version
  (which inherits it via create_draft) scores answers exactly as V1 did.

### Tests
- create_draft seeds language/jarothreshold/revision from the activity; the
  content hash reflects added media files; grading follows the version's Jaro
  threshold rather than the activity's.

## [2.0.0-alpha.40] - 2026-07-25

### Fixed
- Player: finishing an attempt now waits for any in-flight answer submissions
  before calling `finish_attempt`, so an answer typed immediately before
  pressing "Finish attempt" can no longer lose a race and be rejected as
  belonging to an already-finished attempt (`amd/src/player.js`).
- The optimistic-concurrency retry guards for submitting a response and
  requesting a hint now run inside the attempt write lock, in
  `attempt_manager::submit_response()` and `::request_hint()`, instead of in a
  pre-lock read in the External Function layer. Two genuinely concurrent
  retries can no longer both pass the check and each count a try or advance a
  hint level. The External Functions pass `expectedtries` / `expectedlevel`
  straight through; the observable web-service contract is unchanged.

### Added
- Player: an explicit "Check answer" submit button on each gap, alongside the
  existing submit-on-Enter and submit-on-blur, giving an unambiguous submit
  action (`player:check`).
- Player: gaps that carry an associated link (`elang_gap.linkurl`, already
  returned by `get_attempt_cues`) now render an "Open link" anchor
  (`player:gaplink`); previously the value was fetched but never shown.
- Domain tests for the moved concurrency guards: stale/ahead `expectedtries`
  on submit, and replay/stale `expectedlevel` on hint request.

### Changed
- New language strings `player:check` and `player:gaplink` (en, de).

### Note
- `amd/src/player.js` changed but `amd/build/` is **not** included in this
  patch: run `grunt amd` (e.g. `make amd`) to regenerate
  `amd/build/player.min.js` and its source map. CI regenerates AMD builds; the
  previous minified player runs until the rebuild happens.

## [2.0.0-alpha.39] - 2026-07-25

### Security
- `elang_pluginfile()` now enforces version status when serving media and poster
  files, not just activity membership. Previously any user with `mod/elang:view`
  could fetch a file from *any* of the activity's versions — including an
  unpublished draft — if they knew the version id and file name, which would have
  let draft media uploaded by the upcoming authoring tool leak to learners. The
  file API now mirrors the attempt-bound read API's version protection: a learner
  may fetch the published version's files, or an archived version's files only
  while one of their own attempts is pinned to it; draft media is reserved for
  users with `mod/elang:manage`.

### Added
- `version_manager::user_can_access_version_file()`: the reusable access decision
  behind the `elang_pluginfile()` hardening (manager → any version; learner →
  published, or own-pinned archived; never a draft), confined to the owning
  activity so a crafted URL cannot borrow one module context for another
  activity's files. Covered by four new `version_manager_test` cases.

### Changed
- `make check` is now a real local gate instead of an advisory run: `lint-js` is
  part of the suite; `lint-php`, `lint-phpdoc` and `lint-mustache` fail the target
  on findings instead of swallowing their exit status; `grunt amd --force` is
  reduced to `grunt amd` so a broken build surfaces; and the build step is dropped
  from `check` (the source is still linted) so `check` stays a pure verification
  gate. `lint-cpd` remains informational. This matches the stricter GitHub CI.
- `README.md` rewritten to the moodle-an-hochschulen README template layout and
  corrected: it no longer claims the player, migration and authoring UI are
  unimplemented — the player and the version 1 migration ship, and the authoring
  studio is the current work in progress.

## [2.0.0-alpha.38] - 2026-07-25

Behat fix and documentation alignment (session close).

### Fixed
- Behat: the resume scenario failed at `the field "Gap 1" matches value "chat"`
  because Moodle's field locator matches by label/name/id/placeholder, not
  `aria-label`, and the gaps carry an aria-label (correct for an inline
  transcript). Replaced that step with a custom `elang gap "X" should contain
  "Y"` step that locates the input by aria-label and reads its value through
  Mink.

### Changed
- Documentation aligned with the shipped API: `docs/materials/` now uses the
  attempt-bound names `get_attempt_exercise` / `get_attempt_cues` throughout,
  with a note on the attempt binding (attemptid, `mod/elang:attempt`, ownership,
  reading strictly from `elang_attempt.versionid`); phases 2 and 3 are marked
  complete in the roadmap.
- `docs/prompt-templates/sessionstart.txt` updated to the alpha.37 state (Phase
  3 done, next is Phase 4), and `docs/sessions/session-003.md` records this
  session.

## [2.0.0-alpha.37] - 2026-07-25

Phase 3: Behat coverage for the player's two most important behaviours.

### Added
- `tests/behat/player.feature` (@javascript): the player renders the transcript
  with a gap; a learner's submitted answer and graded state survive a page
  reload (resume); and an in-progress attempt keeps reading the version it
  started on after a newer version is published (version pinning). The pinning
  scenario distinguishes versions by the words left visible around the gap
  ("dort" for the started version vs "court" for the one published mid-attempt).
- `tests/behat/behat_mod_elang.php`: custom steps to publish a version with one
  cue and gap (through the domain layer, since there is no authoring UI yet) and
  to answer a gap by driving the player's own Enter-to-submit path.

### Note
- These scenarios need the built `amd/build/` player (grunt) and a real browser,
  so unlike the PHP work they could not be exercised here at all — the `behat`
  CI job is their first real run, and step/selector adjustments are more likely
  than with the PHPUnit suites.

## [2.0.0-alpha.36] - 2026-07-25

Phase 3, slice 3E: resume.

### Added
- On load, after rendering the gaps, the player calls `get_attempt_state` and
  replays the learner's own saved state into each gap — previously typed text,
  tries count, hint level, the "hint used" marker and the graded result state —
  and refreshes the score. A reload mid-attempt now continues where the learner
  left off instead of starting blank. (`start_attempt` resumes an existing
  in-progress attempt, so the state belongs to that same attempt.)

### Note
- Only in-progress work is resumed: reloading after finishing starts a fresh
  attempt, as `start_attempt` creates a new one when none is in progress. The
  saved hint *text* is not re-shown on resume (the state carries the hint level,
  not the text); the gap is marked hint-used and the penalty is already in the
  score. Media playback position is not restored. Same grunt build step for
  `amd/build/` as the previous slices.

## [2.0.0-alpha.35] - 2026-07-25

Phase 3, slice 3C: media and cue synchronisation.

### Added
- The player now loads every cue page (looping `get_attempt_cues` by
  `offset`/`limit` up to the version's `totalcues`) and appends them all, so the
  whole transcript is present rather than only the first 50 cues.
- Native audio/video playback drives the transcript: a `timeupdate` listener
  highlights the cue covering the current time (comparing `currentTime * 1000`
  against the cues' millisecond `starttime`/`endtime`), marks it `aria-current`
  and scrolls it into view. Clicking a cue (outside a gap) seeks the medium to
  that cue's start.
- The active cue is styled by weight and a leading logical border (RTL-safe),
  not colour alone.

### Note
- Synchronisation applies to native `file`/`url` media. Provider embeds
  (YouTube/Vimeo iframes) do not expose playback time cross-origin, so they are
  intentionally left unsynchronised for now; wiring their player APIs would be
  a separate piece. Same grunt build step for `amd/build/` as the previous
  slices.

## [2.0.0-alpha.34] - 2026-07-25

Phase 3, slice 3D: answering. The player is now interactive.

### Added
- Gap answering in `amd/src/player.js`: each gap submits on explicit action
  only (Enter, or leaving the field — never per keystroke), sending the tries
  count last seen as `expectedtries` so a lost-response retry is idempotent.
  The graded result is shown as an accessible, colour-independent state — the
  status text (Correct / Accepted / Incorrect) plus a distinct border style
  (solid / dotted / dashed) — announced through an aria-live region.
- Hints: a per-gap hint button calls `request_hint` with `expectedlevel`,
  reveals the hint text, marks the gap hint-used, and refreshes the score.
- Finish: a finish button calls `finish_attempt`, locks every input, hint and
  finish control, and shows the final score. A live score region updates as
  answers and hints come in.
- Template gains `score` and `controls` regions; `styles.css` gains the
  colour-independent gap-state styling; `player:*` strings for the finish,
  hint, state and score labels in English and German. `player:gaplabel` now
  uses a `%gap%` marker (the module prefetches strings and substitutes
  client-side), consistent with the new `%score%` marker.

### Note
- Same grunt build step as slice 3B applies: regenerate and commit
  `amd/build/player.min.js` (or let the `lint-js` CI job build it). Behat for
  the end-to-end scenarios (resume, publish-during-attempt) still follows slice
  3E as planned.

## [2.0.0-alpha.33] - 2026-07-25

Phase 3, slice 3B: the Moodle-native player shell. First learner-facing UI on
top of the hardened backend. Answering, media/cue synchronisation and resume
of prior input are the next slices.

### Added
- `view.php` now renders the player shell (`templates/player.mustache`) and
  bootstraps the `mod_elang/player` AMD module instead of showing the skeleton
  notice.
- `amd/src/player.js`: starts (or resumes) the attempt via the external API,
  renders the pinned version's medium (file with multiple encodings, direct
  URL, or a YouTube/Vimeo embed) and the first page of cues as an accessible
  transcript, replacing each solution-masked `{{gap:key}}` token with a
  labelled text input. Transcript text is only ever added as text nodes, never
  as markup.
- `templates/player.mustache` (accessible media/status/transcript regions,
  aria-live status), `styles.css` (layout only; colours, dark mode and RTL left
  to the theme), and `player:*` language strings in English and German.

### Note
- **Build step:** Moodle serves AMD from `amd/build/`, which is generated by
  `grunt` from `amd/src/`. That build cannot be produced in this patch
  environment, so after applying this patch run `grunt amd` (or let the CI
  `lint-js` job build it) and commit `amd/build/player.min.js` before the player
  will load in a browser. This is the one place the usual apply-and-run flow
  needs an extra grunt step.
- No Behat yet: the meaningful end-to-end scenarios (resume, and reading the
  same version after a publish) arrive with the answering slice, where there is
  interaction to drive; this slice is the render-only shell.

## [2.0.0-alpha.32] - 2026-07-25

DRY refactor of duplicated code flagged by phpcpd. No behaviour change.

### Changed
- `attempt_helper` trait gains `require_owned_attempt()`,
  `require_inprogress_attempt()` and `require_gap_in_attempt_version()`,
  collapsing the load-attempt/verify-ownership/require-capability(/in-progress)
  (/gap-belongs-to-version) preamble that was repeated across
  `submit_response`, `request_hint`, `finish_attempt`, `get_attempt_state`,
  `get_attempt_exercise` and `get_attempt_cues`. Each function now opens with a
  one- or two-line helper call instead of the full block. This removes the
  submit_response/request_hint clone phpcpd reported and the same pattern
  elsewhere; the exact sequence of checks, order and thrown errors is
  unchanged.
- `tests/lib_test.php`: the identical full-attempt-then-read-grade block shared
  by the point-grade and scale-grade `update_grades` tests is extracted into a
  `finalgrade_after_perfect_attempt()` helper; each test now differs only in
  its grade setup and expected value.

### Note
- Two of the four phpcpd clones (get_attempt_cues ↔ get_cues, and their tests,
  132 of the 185 duplicated lines) come from the superseded `get_exercise.php`,
  `get_cues.php` and their test files never being removed from the working
  tree. They carry no registered service and are dead code; deleting them (the
  `git rm` noted with alpha.25) clears those clones entirely:
  `git rm mod/elang/classes/external/get_exercise.php mod/elang/classes/external/get_cues.php mod/elang/tests/external/get_exercise_test.php mod/elang/tests/external/get_cues_test.php`

## [2.0.0-alpha.31] - 2026-07-25

Completion notification and canonical content hash (reviewer items 7, 8).

### Changed
- **Active completion update (item 7):** `finish_attempt` now calls
  `completion_info::update_state()` for the learner after finishing (guarded by
  `is_enabled()`), so a finished attempt is reflected in the course page and
  any downstream availability immediately instead of on the next page load or
  cron run.
- **Canonical content hash including hints (item 8):**
  `version_manager::compute_content_hash()` now hashes the JSON encoding of a
  single canonical structure rather than fields concatenated with chosen
  delimiters (which content could itself contain), and folds in each gap's
  hints (level, type, text, penalty). A hint change now invalidates the cache
  key, and the pre-hash string is no longer ambiguous. Media columns remain
  included; timestamps, row ids and file-kind media bytes remain excluded.

### Added
- Tests: activity completion after `finish_attempt` in `finish_attempt_test`,
  and a hint-changes-the-hash test in `version_manager_test`.

## [2.0.0-alpha.30] - 2026-07-25

Attempt-API hardening (reviewer items 6, 10, 11).

### Changed
- **Stable error codes (item 11):** the runtime-conflict states in
  `attempt_manager` that a player legitimately hits through parallel tabs or
  retries — submitting or requesting a hint or finishing an attempt that is no
  longer in progress, a gap from the wrong version, and no further hint level —
  now throw `moodle_exception` with the existing stable string keys
  (`attemptnotinprogress`, `gapnotinattemptversion`, `nomorehints`) instead of
  `coding_exception`. The two genuine invariant violations in `start_attempt`
  (a version that is not this activity's, or not published) stay
  `coding_exception`, since the external API can never reach them. Six
  `attempt_manager_test` expectations updated accordingly.
- **Server-side maxlength (item 6):** `submit_response` now enforces the
  effective per-gap response-length limit — the gap's `maxlength` override when
  set, otherwise the hard system cap — matching the limit `get_attempt_cues`
  already advertises to the player, rather than only the global ceiling. Over
  the limit raises the new `error:responsetoolong` (with the limit as `{$a}`).
- **Link hardening (item 10):** `get_attempt_cues` only returns a gap link when
  it is a plain http(s) URL (via a new `safe_linkurl()`), dropping
  javascript:/data:/relative/malformed values, and the return type is now
  `PARAM_URL` instead of `PARAM_RAW`.

### Added
- Tests: per-gap maxlength enforcement in `submit_response_test`, unsafe-link
  dropping in `get_attempt_cues_test`.

## [2.0.0-alpha.29] - 2026-07-25

Version 1 media migration — Patch B of the media/file work. A migrated V1
activity now keeps its video and poster after the upgrade.

### Added
- `classes/local/migration/v1_media_migrator.php`: copies a V1 activity's
  media files into the versioned V2 areas — V1 `videos` (itemid 0, one or more
  encodings) → V2 `media`, V1 `poster` → V2 `poster`, both at itemid = the
  migrated version id — and marks the version `mediakind = 'file'` when a video
  was copied. The copy is non-destructive (V1 originals stay until the legacy
  data is decommissioned). The V1 `subtitle` area is intentionally not copied:
  its VTT/SRT is already the source the cue migration turned into cues/gaps.
  V1 file-area names were read from the uploaded mod_elang 2018091012 source.
- `v1_migrator::migrate_activity()` runs the media migration between cue
  migration and publish (so the published content hash already reflects
  file-kind media), and reports `mediafilecount` / `posterfilecount`, which the
  scheduled migration task now logs.
- `v1_media_migrator_test`: copy of several video encodings + poster into the
  versioned areas with non-destructive originals, a poster-without-video case
  (no file kind set), and a no-course-module no-op.

### Note
- An activity with no real course module (a DB-only simulated activity, as in
  some migration fixtures) has no file areas and is a harmless no-op, so the
  existing DB-only migrator tests are unaffected. Patch C (login-gated provider
  access and caption import) remains a separate paid subplugin.

## [2.0.0-alpha.28] - 2026-07-25

Versioned media data model — Patch A of the media/file work (no-login scope).
Login-gated providers (private videos, YouTube caption import) are deliberately
out of core and will be a separate paid subplugin; no OAuth column is added.

### Added
- `elang_version` gains versioned media columns (`mediakind` file|url|provider,
  `mediaurl`, `mediaprovider`, `mediaproviderref`, `mediamime`, `mediaduration`)
  via `db/install.xml` and a `db/upgrade.php` step. Media belongs to the
  version, so swapping a medium publishes a new version and in-progress
  attempts keep the medium they started on.
- `elang_pluginfile()` and `elang_get_file_areas()` in `lib.php` serving the
  versioned `media` and `poster` file areas (itemid = version id) with
  require_login + `mod/elang:view` + a check that the version belongs to the
  activity. `filearea_media` / `filearea_poster` language strings.
- `get_attempt_exercise` now returns a `media` block for the attempt's pinned
  version: kind, provider/ref, direct url, mimetype, duration, the pluginfile
  URLs of any file-kind media (several encodings supported) and a poster URL.
  Supports file, direct url, and public provider embeds (youtube, vimeo,
  mediasite, …) — all without login.
- Media columns are folded into `version_manager::compute_content_hash()` so a
  medium change invalidates the content-hash cache key. (File *bytes* are not
  yet hashed — deferred to the media-migration work; see the method note.)
- Tests: url/provider/file+poster media in `get_attempt_exercise_test`, and a
  media-column hash test in `version_manager_test`.

### Note
- This is Patch A. Patch B will migrate existing version 1 media
  (`videos`/`poster` file areas at itemid 0 in mod_elang 1.x) into the new
  versioned areas. Patch C (a separate paid subplugin) would add login-gated
  provider access and caption import.

## [2.0.0-alpha.27] - 2026-07-25

Retry-safety for the two mutating learner functions, so a lost response on the
network can no longer turn one action into two.

### Added
- `submit_response` gains an optional `expectedtries` parameter and
  `request_hint` an optional `expectedlevel` parameter (both default `-1` =
  unconditional / legacy behaviour). When supplied, the function uses
  optimistic concurrency against the gap's stored `tries` / `hintlevel`: a
  request whose expected value is exactly one step behind the server is treated
  as a lost-response retry and replays the stored outcome without counting
  another try or advancing (and re-penalising) another hint level; a value
  further out of step raises the new `error:staleattemptstate` so the client
  reloads rather than the server guessing. `request_hint`'s fresh and
  replayed returns are unified through a private `format_hint()` helper.
- `error:staleattemptstate` language string.
- Retry-safety tests in `submit_response_test` and `request_hint_test`
  covering idempotent replay, a genuine next step still counting, the
  unconditional `-1` default, and rejection of an ahead-of-server caller.

### Note
- This deliberately departs from the reviewer's `requestid`/mutation-id
  suggestion for `submit_response`: optimistic concurrency needs no new schema
  column, is symmetric with the hint path, and directly implements the
  reviewer's own `expectedhintlevel` idea. The guard currently lives in the
  external layer, which is sufficient for sequential network retries; making it
  atomic against two *simultaneously* in-flight duplicate requests would mean
  pushing the compare-and-act inside `attempt_manager`'s existing per-attempt
  lock, and is left as a later hardening.

## [2.0.0-alpha.26] - 2026-07-25

### Fixed
- `tests/external/get_attempt_exercise_test.php`,
  `tests/external/get_attempt_cues_test.php`: the capability-test comment
  opened with the lowercase token `mod/elang:attempt`, tripping phpcs
  `InlineComment.NotCapital`. Reworded to start with "The mod/elang:attempt
  capability ...". No functional change.

## [2.0.0-alpha.25] - 2026-07-25

Phase 3 groundwork: bind the learner read API to the attempt's pinned version
so the player can never render a different version than the one a learner's
saved responses belong to.

### Changed
- **BREAKING (alpha):** the learner read API is now attempt-scoped, not
  activity-scoped. `mod_elang_get_exercise(cmid)` → `mod_elang_get_attempt_exercise(attemptid)`
  and `mod_elang_get_cues(cmid, ...)` → `mod_elang_get_attempt_cues(attemptid, ...)`.
  Both now take an `attemptid`, require `mod/elang:attempt` (was
  `mod/elang:view`), verify the attempt belongs to the calling user via the
  existing `attempt_helper::require_attempt_ownership()`, and read content
  strictly from `elang_attempt.versionid` instead of
  `version_manager::get_published()`. This matches the pattern the five other
  attempt functions already use and makes the previously possible mismatch
  (read side serving a newly published version while the write side rejects
  its gaps as not belonging to the attempt's version) structurally
  impossible. `get_attempt_exercise` additionally returns `attemptid`. A
  teacher-facing preview API for arbitrary versions is intentionally deferred.

### Removed
- `classes/external/get_exercise.php`, `classes/external/get_cues.php` and
  their tests `tests/external/get_exercise_test.php`,
  `tests/external/get_cues_test.php`, replaced by the attempt-bound
  functions and tests above. (Delete these four files from the working tree —
  see the session note; a patch archive cannot remove files on its own.)

### Added
- `tests/external/get_attempt_exercise_test.php`,
  `tests/external/get_attempt_cues_test.php`, including the key V2 regression
  test — start an attempt on version A, publish a structurally different
  version B, and assert both read functions still return version A — plus
  cross-user ownership rejection.

## [2.0.0-alpha.24] - 2026-07-25

### Fixed
- `tests/local/migration/v1_decommissioner_test.php`: alpha.23 capitalised
  the wrong line — the phpcs `InlineComment.NotCapital` warning was on the
  comment's actual first line, which opened with the lowercase identifier
  `decommission()`, not the later "(a real, ..." continuation line edited
  previously. Reworded the opening sentence to "The decommission()
  method..." and rewrapped the whole block to fit the line-length limit.
  No functional change.

## [2.0.0-alpha.23] - 2026-07-25

### Fixed
- `tests/local/migration/v1_decommissioner_test.php`: capitalised an inline
  comment introduced in alpha.22 (phpcs `InlineComment.NotCapital`
  warning), no functional change.

## [2.0.0-alpha.22] - 2026-07-25

CI-stabilisation gate ahead of Phase 3: the PHPUnit matrix was red on every
MariaDB/MySQL job (Moodle 4.5/5.0/5.2) while every PostgreSQL job stayed green.
Root cause was a test-isolation defect, not a plugin or portability bug — the
production `v1_decommissioner::decommission()` behaviour is unchanged and
correct.

### Fixed
- `tests/local/migration/v1_decommissioner_test.php`: restore the
  `elang.options` column in `tearDown()` after any test method that exercises
  a successful `decommission()`. `decommission()` drops that column with a
  `DROP COLUMN` statement; on MySQL/MariaDB DDL auto-commits and is never
  reverted by `resetAfterTest()` (data-only reset), so the column stayed gone
  for the rest of the process and every subsequent test inserting into `elang`
  failed with "Unknown column 'options'" (~29–30 cascading errors). On
  PostgreSQL the same DDL is transactional and never leaked, which is why only
  the MariaDB/MySQL jobs were red. The restore re-adds the field exactly as
  `db/install.xml` declares it (nullable text, after `jarothreshold`) and is a
  harmless no-op on PostgreSQL.



Phase 2, seventh and final content increment: custom completion. With this,
Phase 2's actively developable scope (schema, grading, domain, all seven
External Functions, hints, gradebook, completion) is functionally complete.
V1→V2 migration remains separately blocked on a data simulator, not part of
this completion.

### Added
- `classes/completion/custom_completion.php`: implements
  `\core_completion\activity_custom_completion`. Defines exactly one custom
  rule, `completionfinishattempt` — core already provides `completionview`
  (via `FEATURE_COMPLETION_TRACKS_VIEWS`) and a pass-grade condition (via
  `FEATURE_GRADE_HAS_GRADE`/the standard grade section) for free; what core
  has no way to know on its own is whether the learner actually *finished*
  an attempt, as opposed to merely opening the page or reaching a grade some
  other way. `get_state()` checks for an `elang_attempt` row in the
  `finished` state for the user; `get_sort_order()` places it between
  `completionview` and `completionusegrade`.
- `lib.php`: `FEATURE_COMPLETION_HAS_RULES` now `true`.
- `mod_form.php`: `add_completion_rules()`/`completion_rule_enabled()`
  add the rule's checkbox to the completion section. The field name carries
  `$this->get_suffix()` — required since Moodle 4.3/4.4 (MDL-78516) so that
  multiple module instances editable on one page (e.g. bulk activity
  completion) don't collide on field name. Checked this explicitly against
  a real community-plugin bug report before writing it (a different plugin
  broke exactly this way on Moodle 4.4 by omitting the suffix) rather than
  assuming the older, unsuffixed pattern still shown in some tutorials still
  applies — our whole supported range (4.5 LTS and up) is already past the
  version where the suffix became mandatory, so there is no legacy branch to
  fall back to here, unlike the third-party example that inspired this.
- Two new language strings (EN+DE): `completionfinishattempt` (the
  mod_form checkbox label) and `completiondetail:completionfinishattempt`
  (the shorter description shown in the activity-information UI) — these
  are deliberately two different strings, matching core's own convention
  for its custom-completion modules.
- PHPUnit coverage: `tests/completion/custom_completion_test.php` (defined
  rules, incomplete without any attempt, incomplete with only an
  in-progress attempt — it must be *finished*, complete with a finished
  attempt, an undefined rule is rejected, a description and the sort-order
  placement are both present). Confirmed the exact exception type
  `validate_rule()` throws for an undefined rule directly against Moodle
  core source (`coding_exception`, not `moodle_exception`, though the
  latter would technically also have passed via the exception hierarchy —
  asserted the precise one instead of relying on that).
- `tests/lib_test.php`: the previously-passing assertion that
  `FEATURE_COMPLETION_HAS_RULES` is *not* declared was corrected to assert
  the opposite, now that it is.
- `version.php`: 2026072306 -> 2026072307 (2.0.0-alpha.8) — required for
  Moodle to pick up the newly-declared custom completion rule; confirmed via
  a real-world report of the same "nothing happens until you bump the
  version" symptom for a different plugin's custom completion rules. No
  schema change this round, so no new `db/upgrade.php` savepoint.

### Fixed

Found running the real test suite against a Moodle 4.5.12 instance for the
first time — corrected without a version bump (still 2.0.0-alpha.8).

- **`custom_completion_test`: three `get_state()` tests threw
  `core\exception\moodle_exception: Custom completion rule
  'completionfinishattempt' is not used by this activity.`** Not a bug in
  `custom_completion.php`: `validate_rule()` (Moodle core) checks two
  separate things — that the rule is *defined* by the plugin at all
  (`get_defined_custom_rules()`, which passed), and separately that it is
  actually *enabled* for the specific course module instance under test
  (which failed).
  - **First attempted fix (incomplete):** assumed the test's `setUp()`
    merely needed `enablecompletion => 1` on the course and `'completion' =>
    COMPLETION_TRACKING_AUTOMATIC, 'completionfinishattempt' => 1` passed to
    `create_module()`, matching the pattern in Moodle core's own
    `completion/tests/bulk_update_test.php`. This did not fix the real run —
    same three failures, same message, on the next test pass.
  - **Actual root cause:** the second `validate_rule()` check reads
    `cm_info->customdata['customcompletionrules']`, which is populated by a
    `{modname}_get_coursemodule_info($coursemodule)` callback that this
    plugin never implemented — confirmed against core's own documented
    pattern for this callback (`forum_get_coursemodule_info()`) and its
    reference to a dedicated column on the module's own table
    (`forum.completiondiscussions` etc.). Without that callback,
    `customdata['customcompletionrules']` is never populated at all, so
    `validate_rule()` rejects the rule regardless of any generator/instance
    data passed at creation time — the first fix addressed how the value
    would have reached the database, but nothing existed yet to read it back
    into `cm_info`.
  - **Actual fix:** added a real `elang.completionfinishattempt` column
    (`db/install.xml`, plus a `db/upgrade.php` savepoint targeting the
    already-current 2026072307 — not a further version bump, but a real,
    functional upgrade step for whenever a live site next runs an admin-UI
    upgrade, since its recorded version is still behind that value) and
    `lib.php::elang_get_coursemodule_info()`, which populates
    `customdata['customcompletionrules']['completionfinishattempt']` from
    that column, but only when `$coursemodule->completion ==
    COMPLETION_TRACKING_AUTOMATIC` (matching core's own convention). With
    the real storage and callback in place, the original test fix (the
    `create_module()` options) turned out to be correct after all — it just
    had nothing to write to or read from until now.
  - New coverage added directly for the callback itself:
    `tests/lib_test.php::test_get_coursemodule_info_populates_custom_
    completion_rules` and `test_get_coursemodule_info_omits_rules_without_
    automatic_completion`, so this specific mechanism has its own test
    independent of the completion state test that surfaced the gap.
- **Third real run: the schema/callback fix above was confirmed correct**
  (`custom_completion_test` fully passed) — the only two remaining failures
  were both in the two brand new tests just added for the callback itself,
  both test-only mistakes, not further product bugs:
  - `test_get_coursemodule_info_populates_custom_completion_rules` was
    missing its own `$this->resetAfterTest()` call entirely (each test
    method needs one; there is no shared `setUp()` in this class providing
    it), triggering Moodle's "unexpected database modification" test-
    isolation guard.
  - `test_get_coursemodule_info_omits_rules_without_automatic_completion`
    called `assertArrayNotHasKey('customcompletionrules', $info->customdata)`,
    but `customdata` is `null` on a fresh `cached_cm_info` until something
    is assigned to it — `elang_get_coursemodule_info()` only ever touches it
    inside the automatic-completion branch, so in this test it was never an
    array at all. `assertArrayNotHasKey()` requires an actual array/
    ArrayAccess argument and throws otherwise; switched to
    `assertTrue(empty(...))`, which handles `null` gracefully.
  - Swept every other test file for the same missing-`resetAfterTest()`
    pattern before concluding this was isolated to the two new tests; three
    apparent hits turned out to be false positives from the sweep script
    itself (matching `setUpBeforeClass()` as if it were `setUp()`), and four
    more were correctly `resetAfterTest()`-free `\basic_testcase` subclasses
    that don't touch the database at all — verified each individually
    rather than trusting the first grep-level pass.

### Known gaps
- No `elang_get_completion_state()` legacy callback — deliberately not
  implemented; Moodle core has flagged it for deprecation (MDL-71144) in
  favour of the class-based approach used here, and this is a greenfield
  2.0 codebase with no legacy callback to carry forward.
- `elang.answermaxlength` still does not exist (unchanged).
- `classes/courseformat/overview.php` still does not exist (unchanged).
- Migration V1→V2 remains blocked on a data simulator (unchanged) — this is
  explicitly not part of "Phase 2 complete" and needs input from the plugin
  maintainer, not further development alone.
- Now run against a real Moodle 4.5.12 instance (118 tests, 3 real failures,
  all in this increment's own new test file — see "Fixed" above; every
  other suite, including all prior increments, passed). The `mod_form.php`
  completion-rule checkbox itself (the actual settings-page UI, as opposed
  to `get_state()`'s logic) still has not been exercised through a real
  browser/form submission — only through direct generator/instance data, as
  `custom_completion_test` now does.

## [2.0.0-alpha.7] - 2026-07-23

Phase 2, sixth content increment: gradebook integration — the last piece
before Phase 2 is functionally complete (Completion is the remaining item).

### Added
- `db/install.xml`/`db/upgrade.php` (savepoint 2026072306): new
  `elang.grade` field, the standard Moodle grade/gradetype column (positive
  = maximum points, 0 = ungraded, negative = `-scaleid`). No key or index
  touches it, checked explicitly against the same collision pattern that
  broke the alpha.6 upgrade path — none introduced here.
- `lib.php`: `FEATURE_GRADE_HAS_GRADE` now `true`.
  `elang_grade_item_update()` creates/updates the activity's grade item via
  `grade_update()`. `elang_update_grades()` computes each user's grade as
  the highest score among their **finished** attempts
  (`attempt_manager::get_best_score()`), scaled to `elang.grade`, and pushes
  it through. No configurable grading method (best/average/first/last
  across attempts) yet — always "highest finished attempt" — documented as
  a known gap, not an oversight. `elang_add_instance()`/
  `elang_update_instance()` now call `elang_grade_item_update()`;
  `elang_delete_instance()` now calls `grade_update(..., ['deleted' =>
  true])` to remove the grade item when the activity itself is deleted.
- `classes/local/domain/attempt_manager.php`: new `get_best_score()`, a pure
  query over `elang_attempt` kept in the domain layer (same responsibility
  as everything else on this class) rather than in `lib.php`, so it stays
  independently testable without a gradebook bootstrap. Caught and fixed a
  real subtlety before it could become a bug: `SELECT MAX(score) ... WHERE
  ...` always returns exactly one row in SQL, with the aggregate `NULL` when
  nothing matched — never "no row" — a distinction that has bitten this
  project's DB-layer assumptions before (see alpha.2/alpha.3's
  int-vs-string findings) and was checked explicitly, not assumed. The
  standalone domain smoke script's fake DB was extended to simulate this
  faithfully, with four new checks (no attempts at all, an in-progress
  attempt does not count, first finished attempt counts, highest of several
  finished attempts wins over the latest) — all passing before this landed.
- `classes/external/finish_attempt.php`: now calls `elang_update_grades()`
  for the attempting user immediately after `finish_attempt()`, so the
  gradebook reflects a result the moment an attempt is finished rather than
  waiting for a separate regrade trigger. Explicitly `require_once`s
  `lib.php` first — gradebook callbacks are plain global functions, not
  guaranteed to already be loaded in an external-function context.
- `mod_form.php`: `standard_grading_coursemodule_elements()` added (grade,
  grade category, grade to pass — the standard Moodle grade section), right
  below the intro, above the standard course-module elements, matching
  `mod_assign`/`mod_quiz` convention.
- PHPUnit coverage: `tests/lib_test.php` gained gradebook cases (creating an
  instance creates a grade item with the configured max, `elang_update_
  grades()` pushes the correct scaled grade for a finished attempt, a user
  with no finished attempts gets no positive grade, deleting an instance
  removes its grade item); the previously-passing assertion that
  `FEATURE_GRADE_HAS_GRADE` is *not* declared was corrected to assert the
  opposite, now that it is; `tests/local/domain/attempt_manager_test.php`
  gained matching PHPUnit cases for `get_best_score()` alongside the smoke
  script coverage.
- `version.php`: 2026072305 -> 2026072306 (2.0.0-alpha.7) — required both
  for the new `elang.grade` schema field and, as with every increment since
  alpha.4, for `db/services.php` (unchanged this round, but the version
  gate is schema-driven here, not services-driven).

### Known gaps
- No configurable grading method across multiple attempts (best/average/
  first/last) — always the highest finished attempt. `mod_quiz`'s
  `QUIZ_GRADEHIGHEST`/`QUIZ_GRADEAVERAGE`/etc. pattern would be the natural
  model for a later refinement.
- No maximum attempt count exists yet (unchanged from earlier increments),
  so nothing currently limits how many finished attempts contribute to
  "the highest".
- Completion (`FEATURE_COMPLETION_HAS_RULES`,
  `\mod_elang\completion\custom_completion`) is still not implemented; a
  natural completion rule ("achieved the pass grade") now has real grade
  data to read once it exists.
- This increment has not been run against a real Moodle instance yet —
  verified with `php -l`, `phpcs --standard=moodle` (0/0), `phpcpd` (no
  clones), a KEY/INDEX collision re-check on the new field (none), and the
  standalone smoke script only. Given alpha.6's upgrade-path lesson, the
  new `db/upgrade.php` savepoint in particular has not been exercised
  against a real admin-UI upgrade.

## [2.0.0-alpha.6] - 2026-07-23

Phase 2, fifth content increment: hint requests — the last piece of the
attempt lifecycle that was still a stub.

### Added
- `classes/local/domain/attempt_manager.php`: new `request_hint()`.
  Hint levels are revealed strictly in order — always the level one above
  whatever was last revealed for that gap, never a specific level chosen by
  the caller — and fail if no `elang_gaphint` row exists at that level.
  Requesting a hint before ever answering creates an empty `elang_response`
  row to hold the revealed level (`responsetext` has no schema-level
  default, same reasoning as `elang.language`, so it is set explicitly).
  `recalculate_attempt_aggregates()` reworked to be hint-penalty-aware:
  each `elang_gaphint.penalty` (0..1, the fraction of a gap's point value
  given up by having revealed hints up to and including that level — not
  additive across levels) is looked up for a response's current hint level
  and applied to that response's own `score` (also recomputed and persisted
  now, not just the attempt-level aggregate), then summed into the attempt
  score. With no hint used, this reduces exactly to the pre-hint
  `correctgaps`/`totalgaps` scoring — verified explicitly, not just assumed.
  Requesting a hint *after* an already-accepted answer retroactively reduces
  that response's and the attempt's score, since scores are recalculated
  from current state on every submission or hint request, never fixed at
  submission time.
- `classes/external/request_hint.php`: reveals the next hint level for a
  gap within an in-progress attempt, returning the hint content alongside
  the updated `hintedgaps`/`score` so a caller never needs a separate round
  trip to find out what the hint cost. Same ownership check
  (`attempt_helper::require_attempt_ownership()`), same gap-belongs-to-
  attempted-version check, same friendly-error-before-hitting-the-domain-
  layer's-`coding_exception` pattern as `submit_response`. If a hint's
  `hinttype` happens to be `solution`, its `hinttext` *is* the solution —
  that is by design (a learner who deliberately exhausts hints up to a
  solution-type level explicitly asked for it, penalty included) and is
  unrelated to `get_exercise`/`get_cues` never exposing solutions regardless
  of hint state.
- `db/services.php`: `mod_elang_request_hint` registered as `type: write`,
  `ajax: true`, Moodle-App-capable, gated by `mod/elang:attempt`.
- New language string `error:nomorehints`.
- PHPUnit coverage: `tests/local/domain/attempt_manager_test.php` gained
  hint-specific cases (first/second hint reveals the correct level, beyond-
  last-level rejection, not-in-progress rejection, accepted/unaccepted
  scoring with a hint applied, retroactive penalty after an already-correct
  answer); `tests/external/request_hint_test.php` (happy path, penalty
  reflected in a subsequent `submit_response` call, beyond-last-level
  rejection, ownership violation, cross-version gap rejection, finished-
  attempt rejection). The standalone domain smoke script gained a dedicated
  hint section (its own fresh version, to avoid the totalgaps miscount that
  comes from adding a gap to a version an earlier smoke-test attempt had
  already counted against — caught and fixed before shipping, not after) —
  34 checks in total, all passing, independent of a Moodle bootstrap.
- `version.php`: 2026072304 -> 2026072305 (2.0.0-alpha.6) — required for the
  same reason as alpha.4/alpha.5's bumps: an already-installed site only
  re-scans `db/services.php` on a version change.

### Fixed

Found trying an actual site upgrade via the Moodle admin UI for the first
time — every previous real-instance test run (sessions 002-005) exercised
`db/install.xml` only, via PHPUnit's fresh-install bootstrap, never
`db/upgrade.php`'s incremental path. Corrected without a version bump (still
2.0.0-alpha.6).

- **`coding_exception`: "Key gapid collides with index gapid specified in
  table elang_gapanswer"**, thrown from `xmldb_table::addIndex()` during
  `xmldb_elang_upgrade()`. Root cause predates this increment entirely — it
  has been latent in the schema since 2.0.0-alpha.2, when `elang_gapanswer`
  and `elang_attempt` were first defined, and simply never had a code path
  that exercised it: both tables declared a `KEY` (foreign key) *and* a
  separate `INDEX` covering the exact same single field
  (`elang_gapanswer.gapid`, `elang_attempt.versionid`). A foreign key
  already creates an implicit index, so the extra explicit index is both
  redundant and, when added via the imperative `xmldb_table`/`xmldb_field`
  API `db/upgrade.php` uses, explicitly rejected by Moodle as a collision.
  `db/install.xml`'s declarative XML loader apparently does not perform the
  same check — every prior fresh install via PHPUnit had the identical
  redundant indexes and never complained, which is why this went unnoticed
  through five previous real-instance test rounds. Removed the redundant
  index from **both** `db/install.xml` and `db/upgrade.php` (a fresh install
  and an upgraded install must produce the identical final schema), keeping
  the foreign key in each case. A systematic check of every other table for
  the same pattern (not just the one reported) found one more instance
  (`elang_attempt`/`versionid`) before it could surface as a second,
  separate failure on a follow-up upgrade attempt.
- **Recovery note for anyone who already hit this**: the failing
  `add_index()` call happens while building the `elang_gapanswer` table
  definition in memory, *before* `$dbman->create_table()` is invoked for it,
  so `elang_gapanswer` itself was never partially created. Everything before
  it in the same upgrade step (`elang.language`/`elang.currentversionid`,
  `elang_version`, `elang_cue`, `elang_gap`) had almost certainly already
  been created in the database by the time the failure occurred, since MySQL/
  MariaDB DDL auto-commits per statement and cannot be rolled back by a
  surrounding PHP exception. Every table/field creation in
  `xmldb_elang_upgrade()` was already guarded with `$dbman->table_exists()`/
  `field_exists()` checks from the start, so re-running the upgrade with
  this fix correctly skips what already exists and only creates what the
  failed run never reached — no manual cleanup should be necessary.

### Known gaps
- No enforcement of a maximum attempt count (unchanged).
- No completion or gradebook integration reads the aggregates yet
  (unchanged). Gradebook integration will need to read the now
  hint-penalty-aware `elang_attempt.score`, not recompute it independently.
- `elang.answermaxlength` still does not exist; `submit_response`'s 500-
  character cap remains a hard global default, not a configurable one.
- The real-instance run that surfaced the schema fix above (see "Fixed")
  exercised the upgrade path, not this increment's own PHPUnit test suite —
  `tests/local/domain/attempt_manager_test.php`'s hint cases and
  `tests/external/request_hint_test.php` have not been run against a real
  Moodle instance yet, only `php -l`, `phpcs --standard=moodle` (0/0),
  `phpcpd` (no clones) and the standalone smoke script.

## [2.0.0-alpha.5] - 2026-07-23

Phase 2, fourth content increment: the read-side External Functions a player
needs to actually display an exercise — completing the API surface alongside
the write path from alpha.4.

### Added
- `classes/local/domain/transcript_masker.php`: redacts every gap's
  character range out of a cue's transcript, replacing it with a
  `{{gap:<gapkey>}}` token. `elang_cue.transcript` stores the full original
  text — `elang_gap.charstart`/`charlength` are offsets *into that text*, so
  the raw column literally contains the solution. Every external function
  returning a transcript now goes through this first; nothing else in the
  codebase is allowed to send a transcript to a client directly. Offsets are
  interpreted as Unicode codepoints (`mb_substr`), not bytes, and overlapping
  gaps raise a `coding_exception` rather than silently producing a transcript
  that might still expose solution text.
- `classes/external/get_exercise.php`: the published version's identifiers
  and counts (`elangid`, `versionid`, `language`, `totalcues`, `totalgaps`,
  `contenthash`) — no content, so a player can decide whether its cached
  copy (keyed on `contenthash`) is still valid before fetching anything.
- `classes/external/get_cues.php`: a page of cues with their gaps for the
  published version, `offset`/`limit` paginated (capped at 200 per page).
  Every transcript is `transcript_masker`-redacted. Gaps deliberately do
  **not** include `charstart`/`charlength`: the masked transcript's token
  already tells the player where to place an input, and returning the
  original character length would hand out the solution's length as an
  unrequested, unpenalised "wordlength" hint — exactly the kind of hint
  `elang_gaphint` models as something a learner has to deliberately request
  (not yet implemented, see "Known gaps"). Solutions and accepted answer
  variants (`elang_gap.solution`, `elang_gapanswer`) are never queried for
  this function at all, let alone returned.
- `classes/external/get_attempt_state.php`: an attempt's aggregate counters
  plus, per gap answered so far, its `resultstate`/`accepted`/`tries`/
  `hintlevel` and the learner's own previously typed `responsetext` (so a
  player resuming an in-progress attempt can restore what was typed —
  this is the learner's own text, never derived from a solution). Reuses
  `attempt_helper::require_attempt_ownership()` from alpha.4, so the same
  "capability alone is not enough" ownership check applies here too.
- `db/services.php`: all three registered as `type: read`, `ajax: true`,
  Moodle-App-capable, gated by `mod/elang:view` (`get_exercise`/`get_cues`)
  or `mod/elang:attempt` (`get_attempt_state`, matching the write functions'
  capability since attempt data is inherently per-attempt).
- PHPUnit coverage: `tests/local/domain/transcript_masker_test.php` (no
  gaps, single/multiple gaps, gap at the very start/end, Unicode codepoint
  vs byte offsets — verified against `mb_strpos()` output rather than
  counted by hand, array vs object gap representations, overlapping-gap
  rejection) plus a standalone smoke script (9 checks, all passing,
  independent of a Moodle bootstrap); `tests/external/{get_exercise_test,
  get_cues_test,get_attempt_state_test}.php` (published-version counts,
  missing-published-version rejection, capability checks, end-to-end
  confirmation that the solution word never appears in a returned
  transcript and that gaps carry no character-position fields, pagination
  correctness, out-of-range pagination rejection, response/aggregate
  correctness, cross-user ownership rejection).
- `version.php`: 2026072303 -> 2026072304 (2.0.0-alpha.5) — required for the
  same reason as alpha.4's bump: an already-installed site only re-scans
  `db/services.php` on a version change.

### Fixed

Found by running the real test suite against a Moodle 4.5.12 instance for
the first time — corrected without a version bump (still 2.0.0-alpha.5).

- **`get_exercise_test`/`get_cues_test`: `test_requires_capability`** expected
  `required_capability_exception` but a real run threw
  `\core\exception\require_login_exception` ("Course or activity not
  accessible. (Activity is hidden)") instead. Root cause, not a bug in
  `get_exercise.php`/`get_cues.php`: `mod/elang:view` is, by Moodle's own
  naming convention, the capability its core "uservisible" machinery checks
  when deciding whether an activity is visible to the current user.
  Prohibiting it makes `require_login()` — called internally by
  `self::validate_context($context)`, which both functions call *before*
  their own explicit `require_capability('mod/elang:view', ...)` — consider
  the activity hidden and deny access right there, so the explicit call is
  never reached. Both exceptions correctly deny access; only the test's
  expectation was wrong. Fixed by asserting the exception that actually
  occurs. (`start_attempt_test`'s equivalent test was unaffected: it
  prohibits `mod/elang:attempt`, which is not the visibility-gating
  capability, so its own `require_capability()` call is reached as
  expected.)

### Known gaps
- Hint requests still are not implemented (unchanged from alpha.3/alpha.4):
  `mod_elang_request_hint` does not exist yet, and `elang_gaphint` is
  consulted nowhere in the codebase.
- No enforcement of a maximum attempt count (unchanged).
- No completion or gradebook integration reads the aggregates yet (unchanged).
- `get_cues`' pagination is plain offset/limit, not the "current and
  neighbouring window" access pattern the blueprint describes (chapter 16)
  for very long transcripts; correct and adequate for the load-test target
  (≥1500 cues), but a smarter windowed fetch keyed on playback position is
  a possible later refinement, not a correctness gap.
- These external functions have not been run against a real Moodle instance
  yet — verified with `php -l`, `phpcs --standard=moodle` (0/0) and the
  standalone masker smoke script only.

## [2.0.0-alpha.4] - 2026-07-23

Phase 2, third content increment: the first real, reachable write path —
External Functions calling into the domain layer — and, as a direct
consequence, a full privacy provider replacing the null provider.

### Added
- `classes/external/{start_attempt,submit_response,finish_attempt}.php`
  and `classes/external/attempt_helper.php` (shared trait): three
  `core/ajax`- and Moodle-App-capable external functions
  (`mod_elang_start_attempt`, `mod_elang_submit_response`,
  `mod_elang_finish_attempt`), declared in the new `db/services.php`. Every
  write is capability-checked (`mod/elang:attempt`) **and** ownership-checked
  (`attempt_helper::require_attempt_ownership()`) — a capable user still
  cannot act on another learner's attempt by guessing its id, which a
  capability check alone cannot prevent. `submit_response` additionally
  rejects a gap that does not belong to the attempted version, and enforces
  a defence-in-depth 500-character response cap ahead of the grading engine
  (the configurable per-gap/site-wide limit is still open, see "Known gaps").
  Player-facing solution text never appears in any parameter or return
  value, matching Lastenheft P12.
- Full privacy provider (`classes/privacy/provider.php`), replacing the null
  provider that was correct only for the schema-only skeleton: implements
  `metadata\provider`, `request\plugin\provider` and
  `request\core_userlist_provider` for `elang_attempt` and `elang_response`.
  Deletion reuses the subquery-based pattern already used by
  `elang_delete_instance()` rather than loading id lists into PHP.
- `mod/elang:attempt`'s existing capability now has real code paths that
  enforce it; no new capabilities were needed.
- New language strings: four `error:*` strings for the external functions'
  failure cases, and the full set of `privacy:metadata:elang_attempt*` /
  `privacy:metadata:elang_response*` field descriptions, replacing the
  removed null-provider `privacy:metadata` string.
- PHPUnit coverage: `tests/external/{start_attempt_test,submit_response_test,
  finish_attempt_test}.php` (happy path, attempt resumption, capability
  denial, missing published version, ownership violation, cross-version gap
  rejection, finished-attempt rejection, oversized response rejection) and
  `tests/privacy/provider_test.php` (context discovery, export, userlist,
  context-wide deletion, per-user deletion, per-approved-userlist deletion).
- `version.php`: 2026072302 -> 2026072303 (2.0.0-alpha.4). No schema change,
  but a version bump is required regardless: without it, an already-installed
  site never re-scans `db/services.php` and the new external functions are
  never registered.

### Fixed

Found by running the real test suite against a Moodle 4.5.12 instance for the
first time — corrected without a version bump (this is still 2.0.0-alpha.4,
not a new release).

- **`Fatal error: Class "externallib_advanced_testcase" not found`**, in all
  three external function test files. That legacy base class lives in
  `webservice/tests/helpers.php` and needs an explicit `require_once()` to be
  defined — the same class of mistake as the fixture-loading issue from the
  previous round, this time hitting a Moodle core class rather than a fixture
  of ours. Rather than adding the `require_once()`, all three test classes
  now extend `\advanced_testcase` directly: nothing in these tests used any
  `externallib_advanced_testcase`-specific functionality (only inherited
  `resetAfterTest()`/`create_and_enrol()`/`setUser()`), and
  `externallib_advanced_testcase` is scheduled for deprecation from Moodle
  4.6 onwards and recommends running in an isolated process to avoid its
  `class_alias()` calls leaking into other tests — none of which is worth
  taking on for functionality that was never used.
- **`phpcpd` found 35 duplicated lines** between `submit_response_test.php`
  and `finish_attempt_test.php` (their nearly-identical `setUp()` methods).
  Extracted into `tests/fixtures/attempt_test_fixture.php`. First attempt: a
  trait (`attempt_test_fixture`) consumed via `use attempt_test_fixture;`
  inside each class, loaded via `require_once()` in `setUpBeforeClass()` —
  this shipped but was never actually tested against a real instance before
  release and failed immediately with `Fatal error: Trait
  "mod_elang\fixtures\attempt_test_fixture" not found`. Root cause: a trait
  consumed via `use` inside a class body is resolved when that class is
  *compiled*, i.e. as soon as the file is loaded — before any method,
  including a static `setUpBeforeClass()`, can run. `require_once()` inside
  a method is therefore too late for a trait, even though the identical
  pattern works for `fake_script_handler.php` (a plain class referenced only
  via `new` inside method bodies, which *is* resolved lazily). Moving the
  `require_once()` to file scope, before the class declaration, does load
  the trait in time, but then trips a *different* real check:
  `phpcs --standard=moodle` flags a file-scope `require_once()` outside a
  `MOODLE_INTERNAL`-guarded context as an unwanted global-state change.
  Final fix: `attempt_test_fixture` is a plain class
  (`attempt_test_fixture_builder`) with a public static `create()` factory
  method, referenced only inside `setUp()` — same lazy-loading shape as
  `fake_script_handler.php`, so the existing `require_once()`-in-
  `setUpBeforeClass()` pattern works unmodified, with no file-scope
  statement needed. Verified this time with a standalone script that
  reproduces PHPUnit's exact call order (class declared and parsed, *then*
  `setUpBeforeClass()` invoked, *then* the fixture class used) before
  shipping, rather than only running `php -l`/`phpcs`, which cannot catch a
  "resolved too late" ordering bug like this one.
- **`tests/privacy/provider_test.php` assertion failure** (`Failed asserting
  that an array contains 110003`): the same string-vs-int mismatch we have
  hit repeatedly in production code (`get_record()`/`get_field()` returning
  strings for integer columns on MariaDB/PDO), this time in a test
  assertion. `PHPUnit\Framework\Assert::assertContains()` has used *strict*
  (`===`) comparison since PHPUnit 9 (previously loose in PHPUnit 8 and
  earlier — see `sebastianbergmann/phpunit#3426`), so a `context->id` that
  arrived as a genuine `int` no longer matched a
  `contextlist::get_contextids()` array whose values came back as strings
  from a raw SQL query. Both sides now cast to `int` explicitly. Verified
  with a standalone script simulating PHPUnit 9's exact `in_array(...,
  true)` comparison before shipping, reproducing the failure with the old
  code and confirming the fix with the new code.
- `tools/mustache_check.php`: fixed a leftover `@package
  local_instantcoursecompletion` from the stub template it was copied from.
  Also changed its behaviour when `templates/` does not exist yet — from an
  `ERROR:`-prefixed message with exit code 1 to an `OK:`-prefixed message
  with exit code 0. This was never actually blocking anything (the Makefile
  target ignores this tool's exit code, and CI's `grep -v '^OK:'` already
  filtered it), but the wording was misleading: mod_elang has no
  renderer/output layer yet (phase 3, not started), so there being no
  `templates/` directory is the correct, expected state, not an error.

### Known gaps
- Hint requests still are not implemented (unchanged from alpha.3).
- No enforcement of a maximum attempt count (unchanged from alpha.3).
- No completion or gradebook integration reads the aggregates yet
  (unchanged from alpha.3).
- The 500-character response cap in `submit_response` is a hard, global
  safety net, not the configurable per-gap (`elang_gap.maxlength`) or
  site-wide default the blueprint specifies; that needs the authoring UI
  (phase 4) to actually set a value.
- No read-side external functions yet (`mod_elang_get_exercise`,
  `mod_elang_get_cues`, `mod_elang_get_attempt_state`): a player cannot yet
  fetch what to display, only submit against gap ids it would have to know
  in advance. These are next.
- These external functions and the privacy provider have not been run
  against a real Moodle instance yet — verified with `php -l` and
  `phpcs --standard=moodle` (0 errors, 0 warnings) only. Given the pattern of
  the last two increments, a first real PHPUnit run is likely to surface at
  least minor issues (as it twice did before) and should be treated as
  expected, not alarming.

## [2.0.0-alpha.3] - 2026-07-23

Phase 2, second content increment: the domain layer sitting on top of the
schema and grading engine — version publishing and the attempt lifecycle.

### Added
- `classes/local/domain/version_manager.php`: draft/publish lifecycle for
  exercise versions. `get_or_create_draft()`/`create_draft()` manage the
  single in-progress draft per activity; `publish()` marks a version
  published, archives whichever version was previously published (never
  deletes it — existing attempts stay linked to the version they were
  started on), and updates `elang.currentversionid`. `compute_content_hash()`
  produces a deterministic SHA-1 over a version's cues, gaps, accepted
  answers and grading algorithms, intended as the cache key for rendered
  worksheets and player payloads (hints and timestamps are deliberately
  excluded, since neither affects what a learner is shown to solve).
- `classes/local/domain/attempt_manager.php`: the attempt lifecycle.
  `start_attempt()` resumes an existing in-progress attempt or creates one,
  recording `totalgaps` from the attempted version. `submit_response()`
  evaluates a response through `answer_evaluator`, looking up the activity's
  language itself so callers cannot pass a mismatched one, upserts
  `elang_response` (resubmitting to the same gap replaces the row and
  increments `tries` rather than creating a duplicate), and recomputes
  `elang_attempt`'s aggregate counters (`answeredgaps`, `exactgaps`,
  `correctgaps`, `hintedgaps`, `score`) from the full set of responses so
  they never drift out of sync. `finish_attempt()` transitions the attempt
  and rejects being called on an attempt that is not in progress, as does
  `submit_response()`.
- PHPUnit coverage: `tests/local/domain/{version_manager_test,
  attempt_manager_test}.php` (draft/publish lifecycle, archiving, content
  hash determinism and change-detection, attempt resumption, exact vs
  incorrect responses, resubmission/upsert behaviour, aggregate recalculation,
  state-guard exceptions). Additionally verified with a standalone PHP script
  that loads the real class files against a minimal in-memory `$DB` stand-in,
  independent of a Moodle bootstrap — 27 checks, all passing.

### Fixed

Found by running the actual test suite against a real Moodle 4.5.12 instance
(PHP 8.2.30, MariaDB 10.11.14) for the first time — everything below was
previously only verified with `php -l`, `phpcs` and standalone smoke scripts
against fake stand-ins, which could not catch these.

- **`Invalid subtype directory 'mod/elang/script' detected`**, logged on
  every plugin scan: `db/subplugins.json` declares the `elangscript`
  subplugin type at `script/`, but that directory only ever existed in
  documentation, never as an actual tracked path (Git does not track empty
  directories, so it silently never made it into a checkout). Added
  `script/README.md`, documenting the subplugin contract, so the directory
  exists on disk.
- **XMLDB `debugging()` call on install**, for both `elang.language` and
  `elang_version.contenthash`: a `NOTNULL CHAR` column with an empty-string
  `DEFAULT` is rejected by Moodle's XMLDB validator ("must have one
  meaningful DEFAULT declared or none"); Moodle auto-corrected it to `NULL`
  at `debugging()` severity, which is exactly the kind of output
  `moodle-plugin-ci`'s install step (correctly) treats as a failure. Removed
  the `DEFAULT=""` from both fields in `db/install.xml` and the matching
  `xmldb_field`/`add_field` calls in `db/upgrade.php`. Since `contenthash` is
  already always supplied explicitly by `version_manager`, only `language`
  needed a corresponding code-level fallback: `elang_add_instance()` now
  defaults it to `''` when not set, so instance creation cannot fail with a
  "no default value" constraint violation now that the schema itself has none.
- **Four `assertSame()` test failures** of the shape
  `Failed asserting that '237000' is identical to 237000`: Moodle's DB layer
  does not guarantee integer PHP types for integer columns — `insert_record()`
  reliably returns a genuine `int`, but `get_record()`/`get_field()` often
  return raw driver values, which are strings on MariaDB/PDO. Added `(int)`
  casts at the four call sites the real test run actually flagged
  (`tests/exercise_schema_test.php`,
  `tests/local/domain/{attempt_manager_test,version_manager_test}.php`);
  left everywhere else untouched, since the same run confirmed those
  comparisons were already fine as written.
- **`Error: Class "...\fake_script_handler" not found`** in
  `answer_evaluator_test.php` and `script_handler_manager_test.php`: Moodle's
  PHPUnit test discovery only `require`s files matching the `*_test.php`
  suffix, so the shared fixture class in its own, differently-named file was
  simply never loaded. Moved it to `tests/fixtures/fake_script_handler.php`
  and load it explicitly via `require_once()` from each consuming test's
  `setUpBeforeClass()` — the same convention Moodle core itself uses (see
  `lib/phpunit/tests/advanced_test.php`). An intermediate attempt to embed
  the fixture directly inside the consuming test files was also tried and
  reverted: Moodle's coding standard requires exactly one class per file
  ("Each class must be in a file by itself"), so that approach traded one
  real problem for a `phpcs` violation instead of fixing it.
- phpcs (`moodle` standard) cleanup on the above: two inline comments not
  starting with a capital letter, three missing docblocks on the relocated
  fixture's interface method implementations.


  `attempt_manager` yet, so `hintlevel`/`hintedgaps` stay at their defaults
  and no score penalty for hint use is applied.
- No enforcement of a maximum attempt count: `elang` does not have that field
  yet (see the blueprint's data model, chapter 6.1, `[offen]` items).
- No completion or gradebook integration reads these aggregates yet.
- No External Functions call into this domain layer yet, so it remains
  unreachable from any real learner-facing code path — exercised only by
  tests. The null privacy provider is therefore still correct, but this is
  now one layer closer to needing to change; see the alpha.2 entry below.

## [2.0.0-alpha.2] - 2026-07-23

Phase 2, first content increment: the versioned exercise schema and the
two-algorithm answer evaluator, plus a CI pipeline fix.

### Added
- `db/install.xml` extended with the versioned exercise and grading schema:
  `elang_version`, `elang_cue` (with a version-stable `cuekey`), `elang_gap`
  (with a version-stable `gapkey` and `gradingalgorithm`), `elang_gapanswer`,
  `elang_gaphint`, `elang_attempt` and `elang_response` (with a `resultstate` /
  `accepted` split — see below). `elang` gains `language` and
  `currentversionid` (the latter deliberately without a declared foreign key,
  to avoid a circular DDL dependency with `elang_version.elangid`).
- `db/upgrade.php`: a full XMLDB upgrade step from `2026072300` to
  `2026072301` creating the above, exercised by
  `tests/exercise_schema_test.php`.
- `classes/local/grading/`: `answer_evaluator` implementing exactly two named
  grading algorithms — `exact` ("komplett-richtig", character-perfect
  including diacritics, case and apostrophes) and `wordrecognized` ("Wort
  erkannt", matches after case folding and diacritic/transliteration
  reduction). `grading_result` separates the finest classification the
  evaluator found (`resultstate`) from whether it is accepted under the gap's
  configured algorithm (`accepted`), so an exact answer on a lenient gap is
  still reported as exact rather than as a generic "correct". Regular
  expression answer variants (`elang_gapanswer.isregex`) are matched with
  `preg_match`, always count as exact, and are bounded by a defence-in-depth
  input-length cap.
- A new `elangscript` subplugin type (`db/subplugins.json`, using both the
  legacy `plugintypes` and the Moodle-5.0+ `subplugintypes` keys so the
  declaration works across the whole 4.5–5.3 range;
  `classes/plugininfo/elangscript.php`) lets non-Latin scripts (Korean,
  Chinese, Japanese, Sanskrit, Cyrillic, …) supply their own transliteration
  without touching the core plugin. `latin_script_handler` is the built-in
  default covering Latin-alphabet languages (NFKD decomposition plus a
  fallback table for non-decomposing letters: ß, æ, œ, ø, ð/đ, þ, ł, ħ, ı, ĳ);
  `script_handler_manager` discovers installed subplugins and routes by
  `elang.language`, falling back to the Latin handler when nothing claims a
  code.
- `lib.php`: `elang_delete_instance()` now cascades through the full versioned
  schema (response → attempt, gaphint/gapanswer/gap → cue → version) using
  subquery-based deletes rather than PHP-side id lists, before deleting the
  `elang` row itself, all inside the existing transaction.
- Test generator (`tests/generator/lib.php`) extended with
  `create_version()`, `create_cue()`, `create_gap()`, `create_gapanswer()` and
  `create_gaphint()`.
- PHPUnit coverage: `tests/local/grading/{latin_script_handler_test,
  script_handler_manager_test,answer_evaluator_test}.php` (reference cases
  including café/cafe, Straße/strasse, œuf/oeuf, kız/kiz, apostrophe variants,
  regex variants, and script-handler routing/fallback) and
  `tests/exercise_schema_test.php` (schema round-trip, `cuekey`/`gapkey`
  reuse across versions); `tests/lib_test.php` gains a cascading-delete test.
  All reference cases were additionally verified with a standalone PHP script
  loading the real class files directly, independent of a Moodle bootstrap.
- `subplugintype_elangscript`(`_plural`) language strings.

### Fixed
- **CI:** removed the `moodle-plugin-ci install --no-init` plus manual
  `cd moodle && php admin/tool/{phpunit,behat}/cli/init.php` pattern from both
  workflows. That pattern is a pre-v4 `moodle-plugin-ci` workaround for a bug
  that has since been fixed upstream; `moodle-plugin-ci phpunit` and
  `moodle-plugin-ci behat` now initialise their environment internally, per
  the current official `gha.dist.yml` reference. This was producing
  `Could not open input file: admin/tool/phpunit/cli/init.php` on several
  matrix jobs. Added a `moodle-plugin-ci savepoints` check alongside
  `validate` while at it.
- phpcs (`moodle` standard) cleanup across the new code: multi-line `if`
  condition formatting, missing docblocks on three `script_handler`
  implementations, a backtick-in-string warning (switched to a `\u{0060}`
  escape), an inline comment not starting with a capital letter. The plugin
  now passes `phpcs --standard=moodle --severity=1` with zero errors and zero
  warnings, verified locally against `moodlehq/moodle-cs` 3.7.0.
- **CI, follow-up fix:** the first CI fix above marked the `main`/5.3-dev
  matrix entries as non-blocking via a job-level
  `continue-on-error: ${{ matrix.experimental }}`. That does not reliably keep
  the overall workflow run green — job-level `continue-on-error` masks the
  job's *result* for `needs:` gating, but the workflow run itself can still
  show as failed, which is exactly as alarming as a real failure. Confirmed
  against `moodlehq/moodle-plugin-ci`'s own `MoodleProcess.php`: Moodle
  `main` under PHP 8.4 does trigger a genuine `debugging()` call during a
  fresh install (matched by `hasDebuggingMessages()`'s regex for Moodle's own
  `++ message ++` / `* line` trace format) — expected instability on an
  unreleased development branch, not a bug in this plugin. `main`/5.3-dev
  testing is now split into dedicated `phpunit-experimental`,
  `behat-experimental` (moodle-ci.yml) and `ci-experimental`
  (moodle-release.yml) jobs, with `continue-on-error: true` set on every
  individual step rather than on the job. The `ci-complete` gate in both
  workflows now depends only on the blocking jobs.

### Known gaps
- No External Functions yet, so nothing outside PHPUnit tests can actually
  write an `elang_attempt` or `elang_response` row. The null privacy provider
  is still correct for that reason, but this is now schema-adjacent enough
  that it is tracked explicitly: the privacy provider must become a full
  provider in the same increment that adds the attempt/response write path.
- No `answermaxlength` field on `elang` yet (only the per-gap override
  exists); the site-wide default and its enforcement belong to the
  not-yet-implemented attempt API.
- No `attempt_manager` / `version_manager` domain classes yet: the schema and
  the evaluator exist, nothing yet writes attempts through them.
- No real Moodle instance has run these tests yet — only `php -l`, a
  standalone smoke script and `phpcs` against the raw class files. The first
  real CI run against this increment is still pending.

## [2.0.0-alpha.1] - 2026-07-23

First artefact of the 2.0 rewrite: infrastructure only, no exercise domain.

### Added
- `version.php` for the new component baseline: Moodle 4.5 LTS minimum
  (`requires = 2024100700`), `supported = [405, 503]`, `MATURITY_ALPHA`, no plugin
  dependencies. The supported range spans PHP 8.1 to 8.4, so the code targets the
  PHP 8.1 language level throughout.
- `lib.php` with `elang_supports()` declaring intro, description, view
  completion, groups, groupings and `FEATURE_MOD_PURPOSE` =
  `MOD_PURPOSE_ASSESSMENT`; `elang_is_branded()` returns false. Backup, custom
  completion rules and grading are deliberately declared as **not** supported
  until their callbacks exist — declaring them earlier makes Moodle call
  `backup_elang_activity_task`, `\mod_elang\completion\custom_completion` and
  `elang_grade_item_update()`, none of which are shipped yet.
- `classes/event/course_module_viewed.php` and view completion recording in
  `view.php`, so the one completion condition that is declared actually works.
- Minimal installable module: `mod_form.php` (general section), `view.php`
  rendering inside the standard Moodle page frame, `index.php` plus the
  `course_module_instance_list_viewed` event for Moodle 4.5 (where the activities
  overview does not exist yet), `db/install.xml` with the base `elang` table only,
  `db/install.php`, `db/upgrade.php` skeleton.
- `db/access.php` with the full planned capability set: `addinstance`,
  `attempt`, `deleteattempts`, `exportreports`, `exporttranscript`, `manage`,
  `useregex`, `view`, `viewreports`.
- `pix/monologo.svg` and `pix/monologo.png`: monochrome 24×24 activity icon
  (stroke `#212529`), rendered on the assessment purpose background.
- English and German language strings.
- Null privacy provider, with an explicit note that it must be replaced by a
  metadata, plugin and userlist provider before any learner data is stored.
- PHPUnit tests for module features, purpose, icon and instance lifecycle;
  test data generator; Behat scenarios for adding and opening the activity.
- GitHub Actions pipelines `moodle-ci.yml` (dev branches) and
  `moodle-release.yml` (main), sampling Moodle 4.5 / 5.0 / 5.2 / 5.3-dev against
  the PHP versions each release supports, on MariaDB and PostgreSQL; the 5.3 jobs
  are non-blocking until the LTS release. PHP lint and phpcs run against both ends
  of the range (8.1 and 8.4).
- Makefile mirroring the CI check suite, `phpcs.xml`, developer tools.
- Documentation set under `docs/materials/` and `docs/prompt-templates/`.

### Deliberately not carried over from version 1
- `src/server/server.php` (custom AJAX endpoint without sesskey checks),
  the Enyo/Bower/Bootstrap 3 front end, `play.php` with its own HTML document,
  the JSONP callback parameter, `unserialize()` on file metadata, the
  `$plugin->cron` declaration, the `@VERSION@` build placeholders and the
  release process that discards `composer.lock`.

### Committed for 2.1
- Rule-based gap generation, retroactive acceptance of answer variants with
  re-grading, a special-character input palette, a translation track as a hint
  level, and blind / audio-only modes. Specified in
  `docs/materials/Lastenheft_Pflichtenheft_Blueprint.md`, chapter 19. The 2.0 data
  model already provides the prerequisites: stable `cuekey` / `gapkey`, persisted
  response text and state-free evaluation.

### Known gaps
- No exercise domain: no versioned exercise definitions, cues, gaps, attempts,
  responses, grading, player, authoring tool, reports or exports.
- No privacy provider for learner data, because no learner data is stored yet.
- No migration of version 1 data and no restore path for version 1 backups.
- No `classes/courseformat/overview.php` yet, so on Moodle 5.0+ the activity does
  not contribute to the activities overview.
- Nothing has been executed against a real Moodle instance: PHPUnit, phpcs, phpdoc
  and Behat have not been run. The first CI run is still pending.
