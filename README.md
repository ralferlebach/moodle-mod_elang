# moodle-mod_elang

[![Moodle Plugin CI](https://github.com/ralferlebach/moodle-mod_elang/actions/workflows/moodle-ci.yml/badge.svg?branch=development)](https://github.com/ralferlebach/moodle-mod_elang/actions/workflows/moodle-ci.yml)

Video-based gap-fill language exercises with time-coded subtitles for Moodle.

Learners watch or listen to a medium while working through its transcript. Selected
words or phrases are hidden; learners type them in, may request graded hints and
receive immediate feedback. Teachers import WebVTT or SubRip subtitles, mark the
gaps, configure how strictly answers are compared, and follow progress in reports.

> **Status:** active alpha development. The versioned exercise schema, the
> two-algorithm-plus-Jaro answer evaluator, the attempt/version domain layer, all
> seven External Functions, the learner **player** (transcript, answering, graded
> hints, media/cue sync, resume), the privacy provider, gradebook integration, the
> `completionfinishattempt` completion rule and the one-way **version&nbsp;1 → 2.0
> migration** (including legacy-backup restore) are implemented and tested. The
> authoring studio (visual editor and subtitle import), reporting and data exports
> are the current work in progress.

## Requirements

This plugin requires Moodle 4.5 (LTS) or later.

- Moodle **4.5 LTS** up to **5.3 LTS** (release target: 5.3).
- PHP **8.1** to **8.4**, within the bounds of the respective Moodle release
  (4.5 → 8.1–8.3, 5.0 → 8.2–8.3, 5.2/5.3 → 8.3–8.4). The code is written against
  the PHP 8.1 language level throughout.
- PostgreSQL or MariaDB/MySQL, as supported by the Moodle release.
- No external plugin dependencies. Optional integrations (AI subsystem, OAuth 2,
  file converters, ffmpeg) are detected at runtime and degrade cleanly.

## Motivation for this plugin

The activity lets teachers turn any captioned video or audio into a self-marking
listening-and-writing exercise: learners fill the gaps in the running transcript
while the medium plays, and answers are graded server-side against configurable
comparison rules.

Version 1 (`mod_elang` 1.x, Université de La Rochelle, up to Moodle 3.4) is the
functional ancestor of this plugin but shares no implementation with it. Version
2.0 is a compatible re-implementation under the same component name: the same
activity concept and subtitle formats and comparable answer-comparison rules, but
a new data model, user interface, API layer and test architecture, plus a one-way
migration of version 1 activities and learner data. Version 1 is published under
CeCILL-B, version 2.0 under GNU GPL v3 or later; provenance is documented in
`docs/materials/Lizenz_und_Herkunft.md`.

## Installation

Install the plugin like any other plugin to folder `mod/elang`.

See <http://docs.moodle.org/en/Installing_plugins> for details on installing
Moodle plugins.

## Usage & Settings

After installing the plugin, it is ready to use without any global configuration.

Per activity, a teacher creates an *Language exercise* activity, provides the
medium (a Moodle file, a direct URL or an embeddable provider reference), imports
its subtitles, marks the gaps and chooses how answers are compared. Each content
change publishes a new immutable version; attempts already in progress stay pinned
to the version they were started on, so a publish never disturbs a running attempt.

Answers are compared per gap by exactly one of two named algorithms — `exact` and
`wordrecognized` — with an optional Jaro-similarity threshold for the latter.
Script-system-dependent normalisation is delegated to `elangscript` subplugins
(with a Latin default); non-Latin scripts are not bundled in core.

If you want to learn more about using activity modules in Moodle, please see
<https://docs.moodle.org/en/Activities>.

## Capabilities

This plugin introduces these additional capabilities:

### mod/elang:addinstance

Add a new Language exercise to a course. Allowed for editing teachers and
managers by default.

### mod/elang:view

View the activity. Allowed for students, teachers, editing teachers and managers
by default.

### mod/elang:attempt

Start and work on an attempt. Allowed for students by default.

### mod/elang:manage

Author and publish exercise content (create drafts, edit cues and gaps, publish
versions, access unpublished draft media). Allowed for editing teachers and
managers by default.

### mod/elang:useregex

Use regular-expression answer variants when authoring gaps. Allowed for managers
by default, because a malformed or malicious pattern is a higher-trust operation.

### mod/elang:deleteattempts

Delete learners' attempts (a data-loss operation). Allowed for editing teachers
and managers by default.

### mod/elang:viewreports

View the teacher reports for the activity. Allowed for teachers, editing teachers
and managers by default.

### mod/elang:exportreports

Export report data (which contains personal data). Allowed for editing teachers
and managers by default.

### mod/elang:exporttranscript

Export the transcript / worksheet. Allowed for students, teachers, editing
teachers and managers by default.

## Scheduled Tasks

This plugin does not add any additional scheduled tasks. The one-way migration of
version 1 activities runs as an ad-hoc task, queued on demand from the migration
admin page, not on a fixed schedule.

## How this plugin works / Pitfalls

An activity owns a chain of immutable **versions**. Editing accumulates in a single
draft; publishing marks the draft published, archives the previously published
version and repoints the activity at the new one. Every attempt records the version
id it runs against (`elang_attempt.versionid`) and reads its content strictly from
that version, so publishing new content never changes what a learner in mid-attempt
sees or how their already-stored responses are interpreted.

Solutions and accepted answer variants never leave the server; grading is
server-side only and is never placed in events. Media files and posters are stored
per version (the file area itemid is the version id) and are served through
`elang_pluginfile()`, which applies the same version protection as the attempt-bound
read API: a learner may fetch the published version's media, or an archived
version's media only while one of their own attempts is pinned to it, while draft
media stays reserved for users who may manage the activity.

### Development

```bash
make check     # phpcs + phpdoc + js + mustache + cpd + phpunit (hard gate)
make fix       # auto-fix code style and PHPDoc, rebuild AMD
make phpunit   # run the plugin test suite
```

CI runs `moodle-plugin-ci` across a sample of the supported range — Moodle 4.5 /
5.0 / 5.2 and the 5.3 development branch, PHP 8.1 to 8.4, MariaDB and PostgreSQL
(PHPUnit + Behat). The 5.3 jobs are allowed to fail until the LTS release.

### Documentation

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

## Theme support

This plugin is developed and tested on Moodle Core's Boost theme. It should also
work with Boost child themes, including Moodle Core's Classic theme. However, we
can't support any other theme than Boost.

## Plugin repositories

This plugin is not (yet) published in the Moodle plugins repository.

The latest development version can be found on Github:
<https://github.com/ralferlebach/moodle-mod_elang>

## Bug and problem reports / Support requests

This plugin is carefully developed and thoroughly tested, but bugs and problems
can always appear.

Please report bugs and problems on Github:
<https://github.com/ralferlebach/moodle-mod_elang/issues>

We will do our best to solve your problems, but please note that due to limited
resources we can't always provide per-case support.

## Feature proposals

Due to limited resources, the functionality of this plugin is primarily
implemented for our own local needs and published as-is to the community. We are
aware that members of the community will have other needs and would love to see
them solved by this plugin.

Please issue feature proposals on Github:
<https://github.com/ralferlebach/moodle-mod_elang/issues>

We are always interested to read about your feature proposals, but please accept
that we can handle your issues only as feature *proposals* and not as feature
*requests*.

## Moodle release support

Due to limited resources, this plugin is maintained for the most recent major
release of Moodle as well as the most recent LTS release of Moodle. There may be
several weeks after a new major release of Moodle has been published until we can
do a compatibility check. If you encounter problems with a new major release of
Moodle — or can confirm that this plugin still works with it — please let us know
on Github.

## Translating this plugin

This Moodle plugin is shipped with English and German language packs. Additional
translations are welcome as pull requests on Github until the plugin is published
on AMOS (<https://lang.moodle.org>).

## Right-to-left support

This plugin has not been tested with Moodle's support for right-to-left (RTL)
languages. If you want to use this plugin with a RTL language and it doesn't work
as-is, you are welcome to send a pull request on Github.

## Maintainers

The plugin is maintained by Ralf Erlebach.

The original version 1 plugin was created and maintained by Christophe Demko and
collaborators at the Université de La Rochelle.

## Copyright

The copyright of this plugin is held by Ralf Erlebach.

Individual copyrights of individual developers are tracked in PHPDoc comments and
Git commits. Version 2.0 is licensed under GNU GPL v3 or later; version 1 material,
where carried over, remains under CeCILL-B and is attributed individually (see
`docs/materials/Lizenz_und_Herkunft.md`).
