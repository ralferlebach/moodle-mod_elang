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
 * Tests for v1_verifier: a clean migration of the real sample activity
 * verifies with no discrepancies, and each class of tampering the verifier
 * claims to catch is actually caught, not just plausible-sounding.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\migration\v1_verifier
 */
final class v1_verifier_test extends \advanced_testcase {
    public static function setUpBeforeClass(): void {
        require_once(__DIR__ . '/../../fixtures/v1_legacy_schema.php');
        parent::setUpBeforeClass();
    }

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        v1_legacy_schema::create_tables();
        v1_legacy_schema::insert_sample_activity();
        (new v1_migrator())->migrate_activity(1);
    }

    protected function tearDown(): void {
        v1_legacy_schema::drop_tables();

        parent::tearDown();
    }

    /**
     * A migration nothing has tampered with verifies clean.
     *
     * @return void
     */
    public function test_verifies_a_clean_migration_with_no_discrepancies(): void {
        $result = (new v1_verifier())->verify_activity(1);

        $this->assertTrue($result->ok);
        $this->assertSame([], $result->discrepancies);
        $this->assertSame(1, $result->elangid);
    }

    /**
     * A gap's solution changed after migration (by anything — a bug, a
     * manual edit, DB corruption) is caught: the verifier compares against
     * the V1 source freshly re-parsed, not against what the migration
     * itself believed it wrote.
     *
     * @return void
     */
    public function test_detects_a_solution_changed_after_migration(): void {
        global $DB;

        $DB->set_field('elang_gap', 'solution', 'tampered', ['gapkey' => 'v1-gap-11-1']);

        $result = (new v1_verifier())->verify_activity(1);

        $this->assertFalse($result->ok);
        $this->assertNotEmpty($result->discrepancies);
        $this->assertStringContainsString('v1-gap-11-1', implode(' ', $result->discrepancies));
    }

    /**
     * A migrated cue row deleted after the fact is caught as missing.
     *
     * @return void
     */
    public function test_detects_a_missing_cue(): void {
        global $DB;

        $cue = $DB->get_record('elang_cue', ['cuekey' => 'v1-cue-10'], '*', MUST_EXIST);
        $DB->delete_records('elang_gap', ['cueid' => $cue->id]);
        $DB->delete_records('elang_cue', ['id' => $cue->id]);

        $result = (new v1_verifier())->verify_activity(1);

        $this->assertFalse($result->ok);
        $found = false;
        foreach ($result->discrepancies as $line) {
            if (str_contains($line, 'v1-cue-10') && str_contains($line, 'missing')) {
                $found = true;
            }
        }
        $this->assertTrue($found, 'expected a "missing elang_cue" discrepancy for v1-cue-10');
    }

    /**
     * A gap's gradingalgorithm changed to something other than what
     * v1_options_mapper would derive from this activity's options is caught.
     *
     * @return void
     */
    public function test_detects_a_wrong_gradingalgorithm(): void {
        global $DB;

        // The sample activity maps to `exact` (Migration_V1_V2.md chapter
        // 1.2); tamper one gap to the other value.
        $DB->set_field('elang_gap', 'gradingalgorithm', 'wordrecognized', ['gapkey' => 'v1-gap-10-1']);

        $result = (new v1_verifier())->verify_activity(1);

        $this->assertFalse($result->ok);
        $this->assertStringContainsString('v1-gap-10-1', implode(' ', $result->discrepancies));
    }

    /**
     * A learner's response silently dropped after migration (one row
     * deleted from elang_response) is caught as a count mismatch —
     * verify_learners() counts independently from the V1 side, not by
     * trusting the attempt's own aggregate counters.
     *
     * @return void
     */
    public function test_detects_a_dropped_response(): void {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['elangid' => 1, 'userid' => 2], '*', MUST_EXIST);
        $anyresponse = $DB->get_records('elang_response', ['attemptid' => $attempt->id], '', '*', 0, 1);
        $DB->delete_records('elang_response', ['id' => reset($anyresponse)->id]);

        $result = (new v1_verifier())->verify_activity(1);

        $this->assertFalse($result->ok);
        $this->assertStringContainsString(
            get_string('verify:responsecount', 'mod_elang', (object) ['userid' => 2, 'actual' => 2, 'expected' => 3]),
            implode(' ', $result->discrepancies)
        );
    }

    /**
     * Verifying an activity that was never migrated is refused, not
     * silently reported as "nothing wrong found".
     *
     * @return void
     */
    public function test_refuses_to_verify_an_unmigrated_activity(): void {
        global $DB;

        $DB->insert_record_raw('elang', (object) [
            'id' => 3,
            'course' => 2,
            'name' => 'Never migrated',
            'intro' => '',
            'introformat' => 1,
            'language' => 'en-GB',
            'options' => '{}',
            'timecreated' => time(),
            'timemodified' => time(),
        ], true, false, true);

        $this->expectException(\coding_exception::class);
        (new v1_verifier())->verify_activity(3);
    }

    /**
     * Verifying an activity whose V1 legacy rows are already gone (a later
     * "Abbau" release already ran, or they were removed some other way) is
     * refused rather than silently verifying against nothing.
     *
     * @return void
     */
    public function test_refuses_to_verify_when_v1_source_is_gone(): void {
        global $DB;

        $DB->delete_records('elang_cues', ['id_elang' => 1]);

        $this->expectException(\coding_exception::class);
        (new v1_verifier())->verify_activity(1);
    }
}
