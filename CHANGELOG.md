# Changelog — mod_elang

All notable changes to this project will be documented in this file.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/);
versioning follows [Semantic Versioning](https://semver.org/).

Version 1.x (University of La Rochelle, CeCILL-B, up to Moodle 3.4) is documented
in the historical `ChangeLog` file of the 1.x repository and is not continued here.

---

## [Unreleased]

### Changed
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
