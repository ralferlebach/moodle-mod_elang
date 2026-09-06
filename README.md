moodle-mod_elang
================

<img src="pix/logo.svg" alt="eLang logo" width="72" align="right">

[![Moodle Plugin CI](https://github.com/ralferlebach/moodle-mod_elang/actions/workflows/moodle-ci.yml/badge.svg?branch=development)](https://github.com/ralferlebach/moodle-mod_elang/actions/workflows/moodle-ci.yml)

Video- and audio-based gap-fill language exercises with time-coded subtitles for Moodle.

Learners watch or listen to a medium while working through its transcript. Selected
words or phrases are hidden; learners type them in, may request graded hints and
receive immediate, tolerant feedback. Teachers import WebVTT or SubRip subtitles,
mark the gaps in a subtitle studio, configure how strictly answers are compared,
and follow progress in reports.


Requirements
------------

This plugin requires Moodle 4.5+ (2024100700) and is declared as supported on
Moodle 4.5 LTS through 5.2 (`$plugin->supported = [405, 502]`). Moodle main is
exercised in non-blocking CI jobs as an early warning; that is not a support
claim.

It is developed and supported on Moodle 4.5 LTS through Moodle 5.2, on PHP 8.1 to
8.4, with PostgreSQL and MariaDB/MySQL. The upcoming Moodle 5.3 is additionally
exercised in continuous integration.


Motivation for this plugin
--------------------------

The original *elang* activity brought subtitle-based gap-fill exercises to Moodle
but stopped at Moodle 3.x and an ageing front-end stack. This plugin is a ground-up
2.0 rewrite for current Moodle: a versioned exercise model, a tolerant grading
engine, a React-based subtitle studio for authoring, full backup/restore, and an
automated one-way migration from the 1.x data. It keeps the `mod_elang` component
name so existing courses can be upgraded rather than rebuilt.


Installation
------------

Install the plugin into the folder

    mod/elang

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins.


Usage & Settings
----------------

After installing the plugin, it is ready to use. Add an *eLang* activity to a
course, then work through the tabs in the order they appear:

1. **Media** — upload the video or audio, or give a source address. Subtitles are
   timed against the medium, so this comes first; the subtitle editor refuses to
   open before it is set.
2. **Subtitles & gaps** — import a WebVTT or SubRip file, or write the cues by
   hand, and mark the gaps.
3. **Publish** — from the editor toolbar. Only a published version is what
   learners see; later edits open a new draft, so an exercise somebody is working
   on never changes underneath them.

Per activity, a teacher sets:

* the exercise language and the answer-comparison tolerance (a Jaro similarity
  threshold), alongside the usual grade and completion options;
* **Subtitle display** — below the medium, or as a caption over the picture (top
  or bottom). An overlay needs a picture and a playback clock, so audio and
  provider embeds fall back to the display below the medium;
* **Playback at subtitle boundaries** — stop at every subtitle that still has an
  empty gap, never stop, or stop only at the subtitle currently being worked on.
  A subtitle whose gaps are all filled in never stops playback in any mode, so a
  second run through an exercise stops only where something is still missing;
* **Transcript download for learners** (`allowtranscriptdownload`) — whether
  learners may export the worksheet, that is the transcript with every gap
  blanked out. The capability `mod/elang:exporttranscript` allows it; this
  setting decides whether a given exercise offers it;
* **Solution availability** (`solutionavailability`) — when learners may export
  the full text: never, after they have submitted their own attempt, or always.
  Staff with `mod/elang:exportsolution` are not affected by it.

There are two site-wide settings, at
Site administration -> Plugins -> Activity modules -> eLang:

* **Allowed languages** (`mod_elang/allowedlanguages`) — restricts the languages
  offered in the activity settings. When empty, all installed languages are offered.
* **Ask before embedding YouTube or Vimeo** (`mod_elang/providerconsent`, on by
  default) — an exercise built on a provider video shows a notice instead of the
  video and embeds it only after the learner agrees. Without it, the provider
  receives the learner's IP address and browser details as soon as the page
  opens. Turn it off only if consent is obtained elsewhere on your site.

If you want to learn more about using activity plugins in Moodle, please see
https://docs.moodle.org/en/Activities.


Capabilities
------------

This plugin introduces these additional capabilities:

* **mod/elang:addinstance** - Add a new eLang activity to a course. Assigned to editing teachers and managers by default.
* **mod/elang:view** - View an eLang activity. Assigned to all enrolled roles by default.
* **mod/elang:attempt** - Attempt an exercise as a learner. Assigned to students by default.
* **mod/elang:manage** - Create and edit exercise content in the subtitle studio. Assigned to editing teachers and managers by default.
* **mod/elang:useregex** - Use regular expressions as accepted answers when authoring. Assigned to **managers only** by default: a regular expression is evaluated against learner input, and an unlucky one can occupy a request for a very long time.
* **mod/elang:viewreports** - View learner reports. Assigned to teachers and managers by default.
* **mod/elang:exportreports** - Export the attempt report. Assigned to teachers and managers by default.
* **mod/elang:exporttranscript** - Export a transcript (PDF, plain text, DOCX or ODT). Assigned to **students**, teachers, editing teachers and managers by default. Whether a given activity offers it to learners is the `allowtranscriptdownload` setting, not a capability.
* **mod/elang:exportsolution** - Export the full solution transcript, including the hidden answers. Assigned to teachers and managers by default.
* **mod/elang:deleteattempts** - Delete learner attempts. Assigned to **editing teachers** and managers by default; non-editing teachers may view and export reports but not destroy them.


Scheduled Tasks
---------------

This plugin does not add any additional scheduled tasks.

The one-way migration of 1.x activities to the 2.0 model runs as an ad-hoc task
that is queued on demand when an administrator starts a migration; it is not a
recurring scheduled task.


How this plugin works
---------------------

An exercise is a sequence of time-coded cues (from the subtitles). Within a cue's
transcript, selected words become gaps. Learners type answers, which are graded by
a two-stage engine: an exact, normalised comparison first, then a configurable
Jaro-similarity tolerance so that near-misses (a missed accent, a small typo) can be
accepted. Graded hints reveal progressively more at a configurable penalty.

Exercise content is versioned. Editing produces a draft; publishing pins a version.
Each learner attempt is tied to the version it started on, so republishing never
changes a running attempt. Grades flow to the gradebook and completion can be tied
to finishing an attempt.

Authoring happens in a subtitle studio (a React front-end): import WebVTT/SubRip,
adjust cue timings on a waveform timeline, mark gaps by selection or by rule
(a word list, or every nth word), preview the masked exercise as a learner sees it,
and autosave throughout.

Activities are fully covered by Moodle course backup and restore, and by the
privacy (GDPR) API.


Theme support
-------------

This plugin is developed and tested on Moodle Core's Boost theme.
It should also work with Boost child themes, including Moodle Core's Classic theme.
However, we can't support any other theme than Boost.


Plugin repositories
-------------------

The latest development version can be found on Github:
https://github.com/ralferlebach/moodle-mod_elang


Bug and problem reports / Support requests
------------------------------------------

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear.

Please report bugs and problems on Github:
https://github.com/ralferlebach/moodle-mod_elang/issues

We will do our best to solve your problems, but please note that due to limited resources we can't always provide per-case support.


Feature proposals
-----------------

Due to limited resources, the functionality of this plugin is primarily implemented for our own local needs and published as-is to the community. We are aware that members of the community will have other needs and would love to see them solved by this plugin.

Please issue feature proposals on Github:
https://github.com/ralferlebach/moodle-mod_elang/issues

Please create pull requests on Github:
https://github.com/ralferlebach/moodle-mod_elang/pulls

We are always interested to read about your feature proposals or even get a pull request from you, but please accept that we can handle your issues only as feature _proposals_ and not as feature _requests_.


Moodle release support
----------------------

Due to limited resources, this plugin is only maintained for the most recent major release of Moodle as well as the most recent LTS release of Moodle. Bugfixes are backported to the LTS release. However, new features and improvements are not necessarily backported to the LTS release.

Apart from these maintained releases, previous versions of this plugin which work in legacy major releases of Moodle are still available as-is without any further updates in the Moodle Plugins repository.

There may be several weeks after a new major release of Moodle has been published until we can do a compatibility check and fix problems if necessary. If you encounter problems with a new major release of Moodle - or can confirm that this plugin still works with a new major release - please let us know on Github.


Translating this plugin
-----------------------

This Moodle plugin is provided with English and German language packs only. Translations into other languages must be managed through AMOS (https://lang.moodle.org), where they will become part of Moodle's official language pack.

As the plugin creator, we continue to maintain the German translation. For all other languages, we kindly ask you to contribute your translations directly in AMOS. These contributions will be reviewed by Moodle's official language pack maintainers before being included in the official repository.

Thank you for supporting the global Moodle community!


Right-to-left support
---------------------

The plugin's own styles use logical CSS properties, so the interface follows the
text direction, and an automated test asserts that the marked edge of a selected
cue moves to the other side when the document direction flips. One thing stays
physical on purpose: the authoring timeline draws *time*, not text — the waveform
runs left to right and cue positions are percentages of elapsed time — so its
handles are not mirrored in RTL.

This is a structural check, not a review by a reader of an RTL language. If you
use the plugin in Arabic, Hebrew or Persian and something reads wrongly, a bug
report or a pull request is welcome.


Provenance and licensing
------------------------

This plugin keeps the `mod_elang` component name of the original *elang*
activity, so existing courses can be upgraded rather than rebuilt. Its history
spans two independent code bases and two licences:

| | Version 1 | Version 2 |
| --- | --- | --- |
| Author | Université de La Rochelle and others | Ralf Erlebach |
| 1.x maintainer | Christophe Demko | — |
| Licence | CeCILL-B | GNU GPL v3 or later |
| Period | 2013–2018 | from 2026 |

Moodle plugins must be licensed GPL-v3-compatibly, so version 2.0 is licensed
GNU GPL v3 or later. It is a ground-up re-implementation from a behavioural
specification with reference test cases, and **does not carry over any version 1
source code**; the language files and icons are new as well. CeCILL-B is a
permissive licence with a strong attribution obligation, and the original work is
acknowledged here and in the release notes accordingly.

The detailed provenance record — including the component-name handover and the
log of any adopted passages (currently empty) — is kept in the repository under
`docs/materials/`. This is not legal advice; institutions publishing the plugin
should have a legally responsible body confirm the licensing and naming.


Maintainers
-----------

The plugin is maintained by\
Ralf Erlebach


Copyright
---------

The copyright of this plugin is held by\
Ralf Erlebach

This plugin continues the *elang* activity originally created at the Université de
La Rochelle (1.x maintainer: Christophe Demko), published under CeCILL-B; see the
Provenance and licensing section above.

Individual copyrights of individual developers are tracked in PHPDoc comments and Git commits.
