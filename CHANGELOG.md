# Changelog — mod_elang

All notable changes to this project will be documented in this file.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/);
versioning follows [Semantic Versioning](https://semver.org/).

Version 1.x (University of La Rochelle, CeCILL-B, up to Moodle 3.4) is documented
in the historical `ChangeLog` file of the 1.x repository and is not continued here.

---

## [Unreleased]

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
