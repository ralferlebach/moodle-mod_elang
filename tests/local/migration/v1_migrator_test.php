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

use mod_elang\fixtures\v1_legacy_schema;

/**
 * Golden-master tests for v1_migrator against the real sample activity
 * (v1_legacy_schema::insert_sample_activity()) — every expected count and
 * value below was computed independently from the same source data, not
 * copied from the migrator's own output.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\migration\v1_migrator
 */
final class v1_migrator_test extends \advanced_testcase {
    public static function setUpBeforeClass(): void {
        require_once(__DIR__ . '/../../fixtures/v1_legacy_schema.php');
        parent::setUpBeforeClass();
    }

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        v1_legacy_schema::create_tables();
    }

    protected function tearDown(): void {
        v1_legacy_schema::drop_tables();

        parent::tearDown();
    }

    /**
     * Migrating the real sample activity produces exactly the counts an
     * independent read of its nine cues and one learner's three
     * elang_users rows predicts: 9 cues, 9 gaps, 5 gaphints (every `[bracket]`
     * gap — cue 10/12/14's "timecode"/15/16 — regardless of whether that
     * particular learner ever requested one), one attempt, three responses
     * (one per elang_users row this learner has).
     *
     * @return void
     */
    public function test_migrates_the_real_sample_activity_with_the_expected_counts(): void {
        global $DB;

        v1_legacy_schema::insert_sample_activity();

        $migrator = new v1_migrator();
        $report = $migrator->migrate_activity(1);

        $this->assertSame(1, $report->elangid);
        $this->assertSame(9, $report->cuecount);
        $this->assertSame(9, $report->gapcount);
        $this->assertSame(5, $report->hintcount);
        $this->assertSame(1, $report->attemptcount);
        $this->assertSame(3, $report->responsecount);
        $this->assertSame([], $report->parseerrors);
        $this->assertSame([], $report->invalidlinks);
        $this->assertSame([], $report->orphanedresponses);

        $this->assertSame(9, $DB->count_records('elang_cue'));
        $this->assertSame(9, $DB->count_records('elang_gap'));
        $this->assertSame(5, $DB->count_records('elang_gaphint'));
        $this->assertSame(1, $DB->count_records('elang_attempt'));
        $this->assertSame(3, $DB->count_records('elang_response'));

        $elang = $DB->get_record('elang', ['id' => 1], '*', MUST_EXIST);
        $this->assertNotEmpty($elang->currentversionid);
    }

    /**
     * Every migrated gap gets the activity-wide `exact` algorithm — the
     * sample activity's options (usecasesensitive=true, usetransliteration=
     * false, jaroDistance="1") map to `exact` per v1_options_mapper, not
     * `wordrecognized` — and cuekey/gapkey are derived from the stable V1
     * ids, not the buggy `order` counter.
     *
     * @return void
     */
    public function test_migrated_gaps_use_the_activitys_mapped_algorithm_and_stable_keys(): void {
        global $DB;

        v1_legacy_schema::insert_sample_activity();
        (new v1_migrator())->migrate_activity(1);

        $algorithms = $DB->get_fieldset_sql('SELECT DISTINCT gradingalgorithm FROM {elang_gap}');
        $this->assertSame(['exact'], $algorithms);

        $cue = $DB->get_record('elang_cue', ['cuekey' => 'v1-cue-10'], '*', MUST_EXIST);
        $gap = $DB->get_record('elang_gap', ['gapkey' => 'v1-gap-10-1', 'cueid' => $cue->id], '*', MUST_EXIST);
        $this->assertSame('Example', $gap->solution);
    }

    /**
     * The one learner's three responses land with the expected content and
     * classification: cue 11 ("demonstration", answered correctly, no help)
     * comes back exact/accepted; cues 10 and 12 (help used, never actually
     * answered) come back empty/hinted. Aggregate counters on the attempt
     * reflect exactly that split, and the score is 1 accepted gap out of 9
     * total.
     *
     * @return void
     */
    public function test_migrated_responses_and_attempt_aggregates_match_the_learners_real_data(): void {
        global $DB;

        v1_legacy_schema::insert_sample_activity();
        (new v1_migrator())->migrate_activity(1);

        $attempt = $DB->get_record('elang_attempt', ['userid' => 2], '*', MUST_EXIST);
        $this->assertSame(1, (int) $attempt->attemptnumber);
        $this->assertSame('finished', $attempt->state);
        $this->assertSame(9, (int) $attempt->totalgaps);
        $this->assertSame(1, (int) $attempt->answeredgaps);
        $this->assertSame(1, (int) $attempt->exactgaps);
        $this->assertSame(1, (int) $attempt->correctgaps);
        $this->assertSame(2, (int) $attempt->hintedgaps);
        $this->assertEqualsWithDelta(1 / 9, (float) $attempt->score, 0.00001);

        $demonstrationgap = $DB->get_record('elang_gap', ['gapkey' => 'v1-gap-11-1'], '*', MUST_EXIST);
        $response = $DB->get_record('elang_response', ['attemptid' => $attempt->id, 'gapid' => $demonstrationgap->id], '*', MUST_EXIST);
        $this->assertSame('demonstration', $response->responsetext);
        $this->assertSame('exact', $response->resultstate);
        $this->assertSame(1, (int) $response->accepted);
        $this->assertSame(1, (int) $response->tries);
        $this->assertSame(0, (int) $response->hintlevel);

        $examplegap = $DB->get_record('elang_gap', ['gapkey' => 'v1-gap-10-1'], '*', MUST_EXIST);
        $helpedresponse = $DB->get_record('elang_response', ['attemptid' => $attempt->id, 'gapid' => $examplegap->id], '*', MUST_EXIST);
        $this->assertSame('empty', $helpedresponse->resultstate);
        $this->assertSame(0, (int) $helpedresponse->accepted);
        $this->assertSame(1, (int) $helpedresponse->hintlevel);
    }

    /**
     * A gap's link (cue 12, "files", pointing at a real https URL) survives
     * migration unchanged.
     *
     * @return void
     */
    public function test_valid_link_url_is_preserved(): void {
        global $DB;

        v1_legacy_schema::insert_sample_activity();
        (new v1_migrator())->migrate_activity(1);

        $gap = $DB->get_record('elang_gap', ['gapkey' => 'v1-gap-12-1'], '*', MUST_EXIST);
        $this->assertSame('https://de.wikipedia.org/wiki/File', $gap->linkurl);
    }

    /**
     * An invalid link (not http/https) is dropped — the gap itself still
     * migrates — and recorded in the report rather than silently lost.
     *
     * @return void
     */
    public function test_invalid_link_url_is_dropped_and_reported(): void {
        global $DB;

        v1_legacy_schema::insert_sample_activity();

        $DB->set_field('elang_cues', 'json', json_encode([
            ['type' => 'text', 'content' => 'You can use SRT '],
            ['type' => 'input', 'content' => 'files', 'order' => 2, 'help' => true, 'link' => 'javascript:alert(1)'],
            ['type' => 'text', 'content' => ' to add subtitles to your videos.'],
        ]), ['id' => 12]);

        $report = (new v1_migrator())->migrate_activity(1);

        $this->assertCount(1, $report->invalidlinks);
        $this->assertStringContainsString('javascript:alert(1)', $report->invalidlinks[0]);

        $gap = $DB->get_record('elang_gap', ['gapkey' => 'v1-gap-12-1'], '*', MUST_EXIST);
        $this->assertSame('', $gap->linkurl);
    }

    /**
     * An orphaned elang_users row (id_cue pointing at nothing that exists,
     * or a position inside its json with no matching migrated gap) is
     * recorded in the report and does not abort the migration — every
     * other response for that same learner still migrates.
     *
     * @return void
     */
    public function test_orphaned_response_is_reported_not_fatal(): void {
        global $DB;

        v1_legacy_schema::insert_sample_activity();

        $DB->insert_record_raw('elang_users', (object) [
            'id' => 999,
            'id_elang' => 1,
            'id_cue' => 888888,
            'id_user' => 2,
            'json' => json_encode(['1' => ['help' => false, 'content' => 'orphaned']]),
        ], true, false, true);

        $report = (new v1_migrator())->migrate_activity(1);

        $this->assertCount(1, $report->orphanedresponses);
        $this->assertStringContainsString('id_cue=888888', $report->orphanedresponses[0]);
        // The other three, valid elang_users rows for this learner still migrated.
        $this->assertSame(3, $report->responsecount);
    }

    /**
     * Migrating an activity that already has a currentversionid is refused
     * outright — never silently re-migrated or merged.
     *
     * @return void
     */
    public function test_refuses_to_migrate_an_activity_that_already_has_a_version(): void {
        v1_legacy_schema::insert_sample_activity();

        $migrator = new v1_migrator();
        $migrator->migrate_activity(1);

        $this->expectException(\coding_exception::class);
        $migrator->migrate_activity(1);
    }

    /**
     * Migrating an activity with no V1 options blob (a genuinely V2-native
     * activity, or one already past the point options would have been
     * cleared) is refused rather than guessed at.
     *
     * @return void
     */
    public function test_refuses_to_migrate_an_activity_with_no_options_blob(): void {
        global $DB;

        $DB->insert_record_raw('elang', (object) [
            'id' => 5,
            'course' => 2,
            'name' => 'Native V2 activity',
            'intro' => '',
            'introformat' => 1,
            'language' => 'en-GB',
            'timecreated' => time(),
            'timemodified' => time(),
        ], true, false, true);

        $this->expectException(\coding_exception::class);
        (new v1_migrator())->migrate_activity(5);
    }
}
