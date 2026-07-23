# Language exercise (`mod_elang`) — version 2.0

Video-based gap-fill exercises with time-coded subtitles for Moodle.

Learners watch or listen to a video while working through its transcript. Selected
words or phrases are hidden; learners type them in, may request graded hints and
receive immediate feedback. Teachers import WebVTT or SubRip subtitles, mark the
gaps, configure how strictly answers are compared, and follow progress in reports.

> **Status: infrastructure skeleton.** This tree contains the version, build,
> CI, documentation and prompt infrastructure for the 2.0 rewrite. The exercise
> domain — versioned exercise definitions, cues, gaps, attempts, responses,
> grading, player, authoring tool and reports — is **not implemented yet**.
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

- Moodle **5.2** or higher. Moodle **5.3 LTS** is the release target.
- PHP **8.3** or **8.4**.
- PostgreSQL or MariaDB/MySQL as supported by the Moodle release.
- No external plugin dependencies.

## Repository layout

```
mod/elang/
├── version.php                  # component, requires 5.2, supported [502,503]
├── lib.php                      # module features, purpose, instance lifecycle
├── mod_form.php                 # instance settings form (general section only)
├── view.php                     # activity page inside the standard page frame
├── index.php                    # instance list — only relevant on Moodle 4.5
├── classes/event/               # course_module_viewed
├── classes/privacy/provider.php # null provider — replaced in phase 2
├── db/                          # install.xml, access.php, install.php, upgrade.php
├── lang/{en,de}/elang.php       # language strings
├── pix/monologo.{svg,png}       # monochrome activity icon (+ icon.svg fallback)
├── tests/                       # PHPUnit, generator, Behat
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
