# Changelog — mod_elang

All notable changes to this project will be documented in this file.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/);
versioning follows [Semantic Versioning](https://semver.org/).

Version 1.x (University of La Rochelle, CeCILL-B, up to Moodle 3.4) is documented
in the historical `ChangeLog` file of the 1.x repository and is not continued here.

---

## [Unreleased]

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
