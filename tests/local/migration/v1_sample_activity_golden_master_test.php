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
 * End-to-end golden-master test: builds the real V1 sample dataset (see
 * tests/fixtures/v1_legacy_schema.php) through actual database tables —
 * not hand-copied JSON strings, unlike v1_cue_parser_test.php — reads it
 * back exactly the way a real migrator eventually will, and checks the
 * result against the same independently-computed expectations.
 *
 * What this test deliberately does NOT cover, because the source data
 * cannot currently answer it (see Migration_V1_V2.md chapter 3, "offene
 * Punkte"): elang_attempt/elang_response reconstruction (tries cannot be
 * recovered per user from elang_check), elang_gaphint level/penalty
 * (V1's help flag is a single bool, not staged), and
 * elang_gap.gradingalgorithm (an activity-wide decision from
 * elang.options.usecasesensitive/usetransliteration, not decided here).
 * This test is the mechanical, fully-determined slice only — reconstructing
 * every cue's transcript and gaps correctly from the raw legacy tables.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\migration\v1_cue_parser
 */
final class v1_sample_activity_golden_master_test extends \advanced_testcase {
    public static function setUpBeforeClass(): void {
        require_once(__DIR__ . '/../../fixtures/v1_legacy_schema.php');
        parent::setUpBeforeClass();
    }

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        v1_legacy_schema::create_tables();
        v1_legacy_schema::insert_sample_activity();
    }

    protected function tearDown(): void {
        // Defensive: these tables are not part of install.xml, so nothing
        // guarantees resetAfterTest() alone knows to remove them between
        // runs. Drop them explicitly regardless of how the test ended.
        v1_legacy_schema::drop_tables();

        parent::tearDown();
    }

    /**
     * The fixture's five tables round-trip through real insert/select, with
     * the explicit ids preserved (see v1_legacy_schema::insert_sample_activity()
     * docblock for why insert_record_raw() with customsequence is required
     * for that) and the cross-references between them intact.
     *
     * @return void
     */
    public function test_fixture_data_round_trips_with_ids_and_cross_references_intact(): void {
        global $DB;

        $this->assertSame(1, $DB->count_records('elang'));
        $this->assertSame(9, $DB->count_records('elang_cues', ['id_elang' => 1]));
        $this->assertSame(3, $DB->count_records('elang_users', ['id_elang' => 1]));
        $this->assertSame(2, $DB->count_records('elang_help', ['id_elang' => 1]));
        $this->assertSame(4, $DB->count_records('elang_check', ['id_elang' => 1]));

        // Column id_cue must point at a real elang_cues.id (10..18),
        // not at the unrelated `number`/`order` counters (see
        // Migration_V1_V2.md chapter 3.1 on the three different
        // referencing schemes).
        $idcues = $DB->get_fieldset_select('elang_users', 'id_cue', 'id_elang = ?', [1]);
        sort($idcues);
        $this->assertSame([10, 11, 12], $idcues);
    }

    /**
     * Every one of the nine real cues parses to the expected transcript and
     * gap set — the same nine cases as v1_cue_parser_test.php, but read
     * back from actual database rows via the fixture instead of literal
     * JSON strings in the test, so the fixture itself is exercised too.
     *
     * @return void
     */
    public function test_every_cue_in_the_sample_activity_parses_correctly(): void {
        global $DB;

        $expected = [
            10 => ['transcript' => 'Welcome to the Example Subtitle File!', 'gapcount' => 1],
            11 => ['transcript' => 'This is a demonstration of SRT subtitles.', 'gapcount' => 1],
            12 => ['transcript' => 'You can use SRT files to add subtitles to your videos.', 'gapcount' => 1],
            13 => [
                'transcript' => "Each subtitle entry consists of a number, a timecode,\nand the subtitle text.",
                'gapcount' => 1,
            ],
            14 => ['transcript' => 'The timecode format is hours:minutes:seconds,milliseconds.', 'gapcount' => 2],
            15 => ['transcript' => 'You can adjust the timing to match your video.', 'gapcount' => 1],
            16 => ['transcript' => 'Make sure the subtitle text is clear and readable.', 'gapcount' => 1],
            17 => ['transcript' => "And that's how you create an SRT subtitle file!", 'gapcount' => 0],
            18 => ['transcript' => 'Enjoy adding subtitles to your videos!', 'gapcount' => 1],
        ];

        $cuerecords = $DB->get_records('elang_cues', ['id_elang' => 1], 'number ASC');
        $this->assertCount(9, $cuerecords);

        foreach ($cuerecords as $cuerecord) {
            $this->assertArrayHasKey(
                (int) $cuerecord->id,
                $expected,
                "Unexpected cue id {$cuerecord->id} in fixture data"
            );

            $result = v1_cue_parser::parse($cuerecord->json);
            $case = $expected[(int) $cuerecord->id];

            $this->assertSame(
                $case['transcript'],
                $result->transcript,
                "Transcript mismatch for cue id {$cuerecord->id} (number {$cuerecord->number})"
            );
            $this->assertCount(
                $case['gapcount'],
                $result->gaps,
                "Gap count mismatch for cue id {$cuerecord->id} (number {$cuerecord->number})"
            );
        }
    }

    /**
     * elang_users.json holds the learner's per-gap state for a gap, keyed
     * 1-indexed WITHIN that cue (always "1" here, since every migrated cue
     * in this activity has at most one gap) — not the global, buggy `order`
     * counter elang_cues.json/elang_check/elang_help use. Confirms that
     * distinction (Migration_V1_V2.md chapter 3.1) against real rows: the
     * learner's answer to cue 11 ("demonstration") is correctly readable
     * under key "1", matching the cue's own single gap.
     *
     * @return void
     */
    public function test_learner_response_is_keyed_per_cue_not_by_the_global_order_counter(): void {
        global $DB;

        $userrow = $DB->get_record('elang_users', ['id_elang' => 1, 'id_cue' => 11], '*', MUST_EXIST);
        $state = json_decode($userrow->json, true);

        $this->assertArrayHasKey('1', $state);
        $this->assertSame('demonstration', $state['1']['content']);
        $this->assertFalse($state['1']['help']);
    }
}
