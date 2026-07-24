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
 * Tests for v1_detector, against the real sample activity
 * (v1_legacy_schema::insert_sample_activity()) — a golden-master style
 * check on the dry-run report's counts, not just "did it not crash".
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\migration\v1_detector
 */
final class v1_detector_test extends \advanced_testcase {
    public static function setUpBeforeClass(): void {
        require_once(__DIR__ . '/../../fixtures/v1_legacy_schema.php');
        parent::setUpBeforeClass();
    }

    protected function tearDown(): void {
        v1_legacy_schema::drop_tables();

        parent::tearDown();
    }

    /**
     * Before the legacy tables exist at all, nothing is detected and
     * nothing is reported — never an error, never a false positive.
     *
     * @return void
     */
    public function test_reports_nothing_when_no_v1_tables_exist(): void {
        $this->resetAfterTest();

        $this->assertFalse(v1_detector::v1_tables_present());
        $this->assertSame([], v1_detector::pending_activity_ids());
        $this->assertSame([], v1_detector::dry_run_report());
    }

    /**
     * The real sample activity is correctly detected as present and
     * pending, and the dry-run report's counts match what is independently
     * known about it (Migration_V1_V2.md chapter 1.1/1.2): 9 cues, 9 gaps
     * total, 1 learner, and — usecasesensitive=true, usetransliteration=
     * false, jaroDistance="1" in its options — mapped to `exact`.
     *
     * @return void
     */
    public function test_detects_and_reports_the_real_sample_activity(): void {
        $this->resetAfterTest();

        v1_legacy_schema::create_tables();
        v1_legacy_schema::insert_sample_activity();

        $this->assertTrue(v1_detector::v1_tables_present());
        $this->assertSame([1], v1_detector::pending_activity_ids());

        $report = v1_detector::dry_run_report();
        $this->assertCount(1, $report);

        $entry = $report[0];
        $this->assertSame(1, $entry->elangid);
        $this->assertSame('Test', $entry->name);
        $this->assertSame(9, $entry->cuecount);
        $this->assertSame(9, $entry->gapcount);
        $this->assertSame(1, $entry->learnercount);
        $this->assertSame('exact', $entry->gradingalgorithm);
        $this->assertEqualsWithDelta(1.0, $entry->jarothreshold, 0.00001);
        $this->assertSame([], $entry->parseerrors);
    }

    /**
     * Once an elang row already has a currentversionid (i.e. a V2 version
     * has already been published for it), the activity is no longer
     * reported as pending — the dry-run heuristic documented in the class
     * docblock.
     *
     * @return void
     */
    public function test_activity_with_a_currentversionid_is_no_longer_pending(): void {
        global $DB;

        $this->resetAfterTest();

        v1_legacy_schema::create_tables();
        v1_legacy_schema::insert_sample_activity();

        $this->assertSame([1], v1_detector::pending_activity_ids(), 'still pending before currentversionid is set');

        // Model the moment data migration finishes: the same elang row the
        // V1 activity always had (insert_sample_activity() already created
        // it, complete with the real schema's grade/completionfinishattempt/
        // jarothreshold defaults) now gets a currentversionid.
        $DB->set_field('elang', 'currentversionid', 999, ['id' => 1]);

        $this->assertSame([], v1_detector::pending_activity_ids());
        $this->assertSame([], v1_detector::dry_run_report());
    }

    /**
     * A lenient activity (any of usecasesensitive=false, usetransliteration=
     * true, jaroDistance<1) maps to wordrecognized with the activity's own
     * jaroDistance carried over, per the rule in Migration_V1_V2.md chapter
     * 1.2 — verified here against a second, independently-inserted
     * activity rather than by re-deriving the same sample activity's
     * (already strict) options.
     *
     * @return void
     */
    public function test_lenient_activity_maps_to_wordrecognized_with_its_jarothreshold(): void {
        global $DB;

        $this->resetAfterTest();

        v1_legacy_schema::create_tables();

        $DB->insert_record_raw('elang', (object) [
            'id' => 2,
            'course' => 2,
            'name' => 'Lenient activity',
            'intro' => '',
            'introformat' => 1,
            'timecreated' => time(),
            'timemodified' => time(),
            'language' => 'fr-FR',
            'options' => json_encode([
                'usecasesensitive' => false,
                'usetransliteration' => false,
                'jaroDistance' => '0.9',
            ]),
        ], true, false, true);
        v1_legacy_schema::insert_row('elang_cues', (object) [
            'id' => 900,
            'id_elang' => 2,
            'number' => 1,
            'begin' => 0,
            'end' => 1000,
            'title' => 'Le ... est bleu.',
            'json' => json_encode([
                ['type' => 'text', 'content' => 'Le '],
                ['type' => 'input', 'content' => 'ciel', 'order' => 0, 'help' => false],
                ['type' => 'text', 'content' => ' est bleu.'],
            ]),
        ]);

        $report = v1_detector::dry_run_report();
        $this->assertCount(1, $report);
        $this->assertSame('wordrecognized', $report[0]->gradingalgorithm);
        $this->assertEqualsWithDelta(0.9, $report[0]->jarothreshold, 0.00001);
    }

    /**
     * A cue with json that fails to parse is captured in ->parseerrors with
     * enough detail to find it (cue id and number), and does not prevent
     * the rest of that activity's cues from being counted correctly.
     *
     * @return void
     */
    public function test_unparseable_cue_is_reported_not_fatal(): void {
        global $DB;

        $this->resetAfterTest();

        v1_legacy_schema::create_tables();
        v1_legacy_schema::insert_sample_activity();

        v1_legacy_schema::insert_row('elang_cues', (object) [
            'id' => 999,
            'id_elang' => 1,
            'number' => 10,
            'begin' => 41000,
            'end' => 42000,
            'title' => 'broken',
            'json' => '{"not":"an array"}',
        ]);

        $report = v1_detector::dry_run_report();
        $entry = $report[0];

        $this->assertSame(10, $entry->cuecount);
        $this->assertCount(1, $entry->parseerrors);
        $this->assertStringContainsString('cue id 999', $entry->parseerrors[0]);
        // The other 9 cues' 9 gaps are still counted despite the 10th
        // failing to parse.
        $this->assertSame(9, $entry->gapcount);
    }
}
