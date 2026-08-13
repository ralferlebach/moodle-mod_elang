<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_elang\local\migration;

use mod_elang\local\domain\version_manager;
use mod_elang\local\grading\answer_evaluator;
use mod_elang\local\grading\grading_result;
use mod_elang\local\grading\script_handler_manager;

/**
 * Migrates one V1 (mod_elang 1.x) activity's legacy content and learner data
 * into the V2 schema, transactionally.
 *
 * This is step 3 ("Migration") of
 * of the read-only step 1/2 v1_detector already provides. It is the
 * per-activity unit of work a resumable ad-hoc task would call in a loop —
 * this class itself is not the task: it has no knowledge of blocks spanning
 * multiple activities, progress markers, or scheduling, only of migrating
 * exactly one already-identified, not-yet-migrated activity correctly and
 * atomically. See the class docblock in v1_detector for how "not yet
 * migrated" is decided.
 *
 * What this migrates, and the decisions each mapping rests on (all
 * cross-referenced to.2/3, not invented here):
 *
 * - elang_cues.json -> elang_cue + elang_gap, via v1_cue_parser. cuekey/
 *   gapkey are derived from the stable V1 ids ("v1-cue-<id>",
 *   "v1-gap-<cueid>-<position>"), not from V1's own buggy `order` counter.
 * - A gap's `[bracket]` vs `{brace}` origin (v1_cue_parser's ->hintsallowed)
 *   becomes exactly one elang_gaphint (level 1, type solution, penalty 0)
 *   when true, none when false — independent of whether any learner
 *   actually used it, because it is an authoring-time property of the gap,
 *   not a per-response fact.
 * - elang.options -> gradingalgorithm/jarothreshold for EVERY gap of the
 *   activity uniformly, via v1_options_mapper (shared with v1_detector).
 * - elang_users (per cue, per learner, latest state only) -> one
 *   elang_attempt per learner (attemptnumber 1, state finished — V1 has no
 *   "in progress" concept to preserve) + one elang_response per gap that
 *   row's json actually has an entry for. Responses are re-evaluated
 *   through the real answer_evaluator rather than the migration inventing
 *   its own resultstate/accepted logic: V1 already normalises a successful
 *   match's stored text to the canonical solution before saving it
 *   (server.php:335), so replaying it through evaluate() reliably reproduces
 *   at least an exact match; an unsuccessful guess's own raw text replays as
 *   incorrect just as reliably.
 * - tries is always migrated as 1 — confirmed to have no V1 source at all,
 *   not even in elang_check.
 * - elang_help/elang_check are read by nothing here — confirmed to carry no
 *   user reference, so neither can contribute to a specific learner's
 *   migrated data (same chapter).
 *
 * What this deliberately does NOT do: decide "not yet migrated" for itself
 * (the caller must have already established that via v1_detector), drop the
 * legacy tables or elang.options (a separate, later release per. mbz restore (a
 * different code path that reuses these same rules, per chapter 5).
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class v1_migrator {
    /** @var version_manager */
    private $versionmanager;

    /** @var answer_evaluator */
    private $evaluator;

    /**
     * Construct the migrator.
     *
     * @param version_manager|null $versionmanager Defaults to a real one; overridable for tests
     * @param answer_evaluator|null $evaluator Defaults to a real one with no elangscript subplugins; overridable for tests
     */
    public function __construct(?version_manager $versionmanager = null, ?answer_evaluator $evaluator = null) {
        $this->versionmanager = $versionmanager ?? new version_manager();
        $this->evaluator = $evaluator ?? new answer_evaluator(new script_handler_manager([]));
    }

    /**
     * Migrate one activity. Refuses to run twice: an activity that already
     * has a currentversionid is assumed already migrated and rejected
     * outright, not silently re-migrated or merged with what is there.
     *
     * The whole activity migrates inside a single delegated transaction —
     * matching
     * the coarsest possible block granularity (one activity). A caller
     * migrating many activities in one resumable task is expected to call
     * this once per activity, each call its own transaction, so a failure
     * partway through a large site's migration only ever loses the one
     * activity being processed when it failed, not everything migrated
     * before it.
     *
     * @param int $elangid The V1 activity to migrate
     * @return object The migration report — see the class docblock for what
     *         each field means; ->parseerrors, ->invalidlinks and
     *         ->orphanedresponses are non-fatal findings, not exceptions:
     *         the activity still migrates as completely as the data allows,
     *         with every skip recorded for a human to review afterwards
     * @throws \coding_exception if the activity does not exist, has no V1
     *         options to migrate from, or already has a currentversionid
     */
    public function migrate_activity(int $elangid): object {
        global $DB;

        $elang = $DB->get_record('elang', ['id' => $elangid], '*', MUST_EXIST);
        if (!empty($elang->currentversionid)) {
            throw new \coding_exception("elang $elangid already has a published version; refusing to re-migrate");
        }
        if ($elang->options === null) {
            throw new \coding_exception("elang $elangid has no V1 options to migrate from");
        }

        $options = json_decode((string) $elang->options, true) ?? [];
        [$gradingalgorithm, $jarothreshold] = v1_options_mapper::map_grading_algorithm($options);

        $report = (object) [
            'elangid' => $elangid,
            'cuecount' => 0,
            'gapcount' => 0,
            'hintcount' => 0,
            'attemptcount' => 0,
            'responsecount' => 0,
            'mediafilecount' => 0,
            'posterfilecount' => 0,
            'parseerrors' => [],
            'invalidlinks' => [],
            'orphanedresponses' => [],
        ];

        $transaction = $DB->start_delegated_transaction();

        // Persist the grading threshold mapped from this activity's V1 options
        // onto the activity row before creating the draft. It becomes the
        // activity's default for future drafts and, because create_draft seeds
        // a new version's grading settings from the activity, the published
        // version's pinned threshold too — so grading a new attempt scores
        // answers exactly as V1 did. (language needs no such fix here: it is
        // migrated onto the activity separately and create_draft copies it.)
        $DB->set_field('elang', 'jarothreshold', $jarothreshold, ['id' => $elangid]);

        $draft = $this->versionmanager->create_draft($elangid);
        $cuemap = $this->migrate_cues($draft->id, $elangid, $gradingalgorithm, $report);

        // Copy the V1 video/poster files into the new version's media areas
        // before publishing, so the published version's content hash already
        // reflects that it is file-kind media (see v1_media_migrator and
        // version_manager::compute_content_hash()).
        $mediaresult = (new v1_media_migrator())->migrate($elangid, $draft->id);
        $report->mediafilecount = $mediaresult->mediafiles;
        $report->posterfilecount = $mediaresult->posterfiles;

        $version = $this->versionmanager->publish($draft->id);
        $this->migrate_learners($elang, $version->id, $cuemap, $gradingalgorithm, $jarothreshold, $report);

        $transaction->allow_commit();

        return $report;
    }

    /**
     * Migrate every elang_cues row into elang_cue/elang_gap/elang_gaphint,
     * accumulating counts and non-fatal findings into $report.
     *
     * @param int $versionid The draft elang_version id to attach cues to
     * @param int $elangid The V1 activity id
     * @param string $gradingalgorithm This activity's mapped algorithm (same for every gap)
     * @param object $report Mutated in place
     * @return array<int, array<int, array{id: int, solution: string}>> Map
     *         of V1 elang_cues.id to [1-indexed gap position => migrated gap
     *         info], for migrate_learners() to resolve elang_users.json
     *         entries against
     */
    private function migrate_cues(int $versionid, int $elangid, string $gradingalgorithm, object $report): array {
        global $DB;

        $cuemap = [];
        $sortorder = 0;

        $v1cues = $DB->get_records('elang_cues', ['id_elang' => $elangid], 'id ASC');
        foreach ($v1cues as $v1cue) {
            try {
                $parsed = v1_cue_parser::parse($v1cue->json);
            } catch (\Throwable $e) {
                $report->parseerrors[] = "cue id {$v1cue->id} (number {$v1cue->number}): " . $e->getMessage();
                continue;
            }

            $cue = (object) [
                'versionid' => $versionid,
                'cuekey' => 'v1-cue-' . $v1cue->id,
                'sortorder' => $sortorder++,
                'starttime' => (int) $v1cue->begin,
                'endtime' => (int) $v1cue->end,
                'transcript' => $parsed->transcript,
                'transcriptformat' => FORMAT_PLAIN,
            ];
            $cue->id = $DB->insert_record('elang_cue', $cue);
            $report->cuecount++;

            $gapmap = [];
            $gapsortorder = 0;

            foreach ($parsed->gaps as $gapindex => $gap) {
                $position = $gapindex + 1; // Position is 1-indexed per cue, matching elang_users.json's keys.

                $linkurl = $this->validate_link($gap->linkurl, $v1cue->id, $position, $report);

                $gaprecord = (object) [
                    'cueid' => $cue->id,
                    'gapkey' => 'v1-gap-' . $v1cue->id . '-' . $position,
                    'sortorder' => $gapsortorder++,
                    'charstart' => $gap->charstart,
                    'charlength' => $gap->charlength,
                    'solution' => $gap->solution,
                    'gradingalgorithm' => $gradingalgorithm,
                    'maxlength' => null,
                    'linkurl' => $linkurl,
                ];
                $gaprecord->id = $DB->insert_record('elang_gap', $gaprecord);
                $report->gapcount++;

                if ($gap->hintsallowed) {
                    $DB->insert_record('elang_gaphint', (object) [
                        'gapid' => $gaprecord->id,
                        'level' => 1,
                        'hinttype' => 'solution',
                        'hinttext' => $gap->solution,
                        'penalty' => 0,
                        'timecreated' => time(),
                    ]);
                    $report->hintcount++;
                }

                $gapmap[$position] = ['id' => (int) $gaprecord->id, 'solution' => $gap->solution];
            }

            $cuemap[(int) $v1cue->id] = $gapmap;
        }

        return $cuemap;
    }

    /**
     * Validate a parsed gap's link URL against the same rule. 1 specifies: http(s) only, nothing taken
     * verbatim from subtitle markup. An invalid link is dropped (the gap
     * itself is still migrated) and recorded in $report, never silently.
     *
     * @param string $linkurl Empty string when the gap had no link at all
     * @param int $v1cueid For the report message
     * @param int $position For the report message (1-indexed within the cue)
     * @param object $report Mutated in place
     * @return string The link to store — either $linkurl unchanged, or ''
     */
    private function validate_link(string $linkurl, int $v1cueid, int $position, object $report): string {
        if ($linkurl === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $linkurl) === 1) {
            return $linkurl;
        }

        $report->invalidlinks[] = "cue id {$v1cueid} gap {$position}: {$linkurl}";

        return '';
    }

    /**
     * Migrate every learner's elang_users rows for this activity into one
     * elang_attempt (per learner) and one elang_response per gap their data
     * covers, accumulating counts and non-fatal findings into $report.
     *
     * @param \stdClass $elang The V1/V2 shared elang row (for timestamps/language)
     * @param int $versionid The now-published elang_version id
     * @param array $cuemap From migrate_cues()
     * @param string $gradingalgorithm This activity's mapped algorithm
     * @param float $jarothreshold This activity's mapped threshold
     * @param object $report Mutated in place
     * @return void No return value.
     */
    private function migrate_learners(
        \stdClass $elang,
        int $versionid,
        array $cuemap,
        string $gradingalgorithm,
        float $jarothreshold,
        object $report
    ): void {
        global $DB;

        $totalgaps = $report->gapcount;

        $byuser = [];
        $v1users = $DB->get_records('elang_users', ['id_elang' => $elang->id], 'id_user ASC, id_cue ASC');
        foreach ($v1users as $v1user) {
            $byuser[(int) $v1user->id_user][] = $v1user;
        }

        foreach ($byuser as $userid => $rows) {
            $attempt = (object) [
                'elangid' => $elang->id,
                'versionid' => $versionid,
                'userid' => $userid,
                'attemptnumber' => 1,
                'state' => 'finished',
                'totalgaps' => $totalgaps,
                'answeredgaps' => 0,
                'exactgaps' => 0,
                'correctgaps' => 0,
                'hintedgaps' => 0,
                'score' => 0,
                // V1 never recorded a per-attempt start/finish time; the
                // activity's own timecreated/timemodified are the closest
                // available approximation, not a precise record.
                'timestart' => (int) $elang->timecreated,
                'timefinish' => (int) $elang->timemodified,
                'timemodified' => time(),
            ];
            $attempt->id = $DB->insert_record('elang_attempt', $attempt);
            $report->attemptcount++;

            $answered = 0;
            $exact = 0;
            $correct = 0;
            $hinted = 0;
            $points = 0.0;

            foreach ($rows as $v1user) {
                $v1cueid = (int) $v1user->id_cue;
                if (!isset($cuemap[$v1cueid])) {
                    $report->orphanedresponses[] = "id_user={$userid} id_cue={$v1cueid} (no matching migrated cue)";
                    continue;
                }

                $state = json_decode((string) $v1user->json, true) ?? [];
                foreach ($state as $position => $entry) {
                    $position = (int) $position;
                    if (!isset($cuemap[$v1cueid][$position])) {
                        $report->orphanedresponses[] =
                            "id_user={$userid} id_cue={$v1cueid} position={$position} (no matching migrated gap)";
                        continue;
                    }

                    $gapinfo = $cuemap[$v1cueid][$position];
                    $help = !empty($entry['help']);
                    $content = (string) ($entry['content'] ?? '');

                    if ($help || $content === '') {
                        $result = new grading_result(grading_result::RESULTSTATE_EMPTY, false, null);
                        $hintlevel = $help ? 1 : 0;
                    } else {
                        $result = $this->evaluator->evaluate(
                            $gapinfo['solution'],
                            $gradingalgorithm,
                            [],
                            $elang->language,
                            $content,
                            $jarothreshold
                        );
                        $hintlevel = 0;
                    }

                    if ($result->resultstate !== grading_result::RESULTSTATE_EMPTY) {
                        $answered++;
                    }
                    if ($result->resultstate === grading_result::RESULTSTATE_EXACT) {
                        $exact++;
                    }
                    if ($result->accepted) {
                        $correct++;
                    }
                    if ($hintlevel > 0) {
                        $hinted++;
                    }

                    // Hint penalty is always 0 here — every migrated hint is
                    // the single, unpenalised level created in
                    // migrate_cues() (.1, "keine
                    // Bestrafung" — V1 had no gradebook to penalise against).
                    $responsescore = $result->accepted ? 1.0 : 0.0;
                    $points += $responsescore;

                    $DB->insert_record('elang_response', (object) [
                        'attemptid' => $attempt->id,
                        'gapid' => $gapinfo['id'],
                        'responsetext' => $content,
                        'resultstate' => $result->resultstate,
                        'accepted' => $result->accepted ? 1 : 0,
                        'tries' => 1,
                        'hintlevel' => $hintlevel,
                        'score' => $responsescore,
                        'timecreated' => (int) $elang->timemodified,
                        'timemodified' => (int) $elang->timemodified,
                    ]);
                    $report->responsecount++;
                }
            }

            $attempt->answeredgaps = $answered;
            $attempt->exactgaps = $exact;
            $attempt->correctgaps = $correct;
            $attempt->hintedgaps = $hinted;
            $attempt->score = $totalgaps > 0 ? round($points / $totalgaps, 5) : 0;
            $DB->update_record('elang_attempt', $attempt);
        }
    }
}
