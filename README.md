# Language exercise (`mod_elang`) — version 2.0

Video-based gap-fill exercises with time-coded subtitles for Moodle.

Learners watch or listen to a video while working through its transcript. Selected
words or phrases are hidden; learners type them in, may request graded hints and
receive immediate feedback. Teachers import WebVTT or SubRip subtitles, mark the
gaps, configure how strictly answers are compared, and follow progress in reports.

**Repository:** <https://github.com/ralferlebach/moodle-mod_elang/tree/development>

> **Status: data model, domain layer and full write/read API, no player/authoring UI yet.**
> The versioned exercise schema (`elang_version`/`elang_cue`/`elang_gap`/
> `elang_gapanswer`/`elang_gaphint`/`elang_attempt`/`elang_response`), the
> two-algorithm-plus-Jaro-threshold answer evaluator (`classes/local/grading/`,
> including the `elangscript` subplugin type for non-Latin scripts), the
> attempt/version domain layer, all seven External Functions
> (`classes/external/`), the privacy provider, gradebook and
> `completionfinishattempt` completion rule are implemented and tested from
> `2.0.0-alpha.2` through `2.0.0-alpha.9`. The player, transcript view,
> authoring tool, reporting and exports are **not implemented yet**; migration
> from version 1 remains specified but not implemented (see
> `docs/materials/Migration_V1_V2.md`).
> See `docs/materials/Lastenheft_Pflichtenheft_Blueprint.md`.

## Relationship to version 1

Version 1 (`mod_elang` 1.x, University of La Rochelle, up to Moodle 3.4) is
functionally the ancestor of this plugin but shares no implementation with it.
Version 2.0 is a compatible re-implementation under the same component name:

- same activity concept, same subtitle formats, comparable answer comparison rules;
- new data model, new user interface, new API layer, new test architecture;
- one-way migration of version 1 activities and learner data into the 2.0 schema;
- restore of version 1 backups (`.mbz`) into version 2.0 installations.

Version 1 is published under CeCILL-B, version 2.0 under GNU GPL v3 or later.
Any code carried over from version 1 must be cleared and attributed individually;
see `docs/materials/Lizenz_und_Herkunft.md`.

The original plugin was created and maintained by Christophe Demko and
collaborators at the Université de La Rochelle. Continuation of the maintainership
for version 2.0 has been raised with him.

## Requirements

- Moodle **4.5 LTS** up to **5.3 LTS**. Moodle 5.3 is the release target.
- PHP **8.1** to **8.4**, within the bounds of the respective Moodle release
  (4.5 → 8.1–8.3, 5.0 → 8.2–8.3, 5.2/5.3 → 8.3–8.4).
- PostgreSQL or MariaDB/MySQL as supported by the Moodle release.
- No external plugin dependencies.

Because Moodle 4.5 is in the supported range, the code is written against
**PHP 8.1** throughout, and version-dependent APIs are used through capability
checks rather than version comparisons.

## Repository layout

```
mod/elang/
├── version.php                  # component, requires 4.5, supported [405,503]
├── lib.php                      # module features, purpose, instance lifecycle, gradebook
├── mod_form.php                 # instance settings form (general section only)
├── view.php                     # activity page inside the standard page frame
├── index.php                    # instance list — only relevant on Moodle 4.5
├── script/                      # elangscript subplugins live here (none in core)
├── classes/
│   ├── completion/custom_completion.php  # completionfinishattempt custom completion rule
│   ├── event/                   # course_module_viewed, course_module_instance_list_viewed
│   ├── external/                # the seven mod_elang_* External Functions + attempt_helper trait
│   ├── local/domain/             # attempt_manager, version_manager, transcript_masker
│   ├── local/grading/            # answer_evaluator, script_handler(_manager), grading_result
│   ├── plugininfo/elangscript.php  # subplugin type declaration
│   └── privacy/provider.php     # metadata/plugin/userlist provider for elang_attempt and elang_response
├── db/                          # install.xml, access.php, services.php, subplugins.json, install.php, upgrade.php
├── lang/{en,de}/elang.php       # language strings
├── pix/monologo.{svg,png}       # monochrome activity icon
├── tests/                       # PHPUnit (incl. tests/local/, tests/external/), fixtures/, generator, Behat
├── tools/                       # developer helpers (not shipped in releases)
├── docs/                        # blueprint, feasibility studies, prompts, sessions
└── .github/workflows/           # moodle-plugin-ci pipelines
```

## Development

```bash
make check     # phpcs + phpdoc + mustache + phpunit
make fix       # auto-fix code style and PHPDoc
make phpunit   # run the plugin test suite
```

CI runs `moodle-plugin-ci` across a sample of the supported range — Moodle 4.5 /
5.0 / 5.2 and the 5.3 development branch, PHP 8.1 to 8.4, MariaDB and PostgreSQL
(PHPUnit + Behat) — on every branch. The 5.3 jobs are allowed to fail until the
LTS release on 5 October 2026.

## Documentation

| Document | Content |
| --- | --- |
| `docs/materials/Lastenheft_Pflichtenheft_Blueprint.md` | Binding requirements, implementation commitments and architecture |
| `docs/materials/Blueprint_kompakt.md` | One-page summary for day-to-day work |
| `docs/materials/Machbarkeit_Zusatzanforderungen.md` | Feasibility study for exports, documents, icon, YouTube and AI |
| `docs/materials/Migration_V1_V2.md` | Migration and legacy-backup restore concept |
| `docs/materials/Lizenz_und_Herkunft.md` | Licensing, provenance and plugin-directory questions |
| `docs/materials/Ideen_Backlog.md` | Rated backlog of further feature ideas |
| `docs/prompt-templates/` | Session-start, session-end and planning prompts |
| `docs/sessions/` | Session logs |

## License

GNU GPL v3 or later.
