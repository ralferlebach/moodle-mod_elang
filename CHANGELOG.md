# Changelog — mod_elang

All notable changes to this project will be documented in this file.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/);
versioning follows [Semantic Versioning](https://semver.org/).

Version 1.x (University of La Rochelle, CeCILL-B, up to Moodle 3.4) is documented
in the historical `ChangeLog` file of the 1.x repository and is not continued here.

---

## [Unreleased]

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
