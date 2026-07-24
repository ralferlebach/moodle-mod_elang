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

namespace mod_elang\task;

use mod_elang\fixtures\v1_data_simulator;
use mod_elang\fixtures\v1_legacy_schema;

/**
 * Tests for migrate_v1_activities_task: block-wise processing and
 * re-queueing behaviour, exercised by calling execute() directly (the
 * standard way Moodle core itself tests adhoc tasks — this bypasses cron's
 * own dispatch loop, not the task logic under test).
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\task\migrate_v1_activities_task
 */
final class migrate_v1_activities_task_test extends \advanced_testcase {
    public static function setUpBeforeClass(): void {
        require_once(__DIR__ . '/../fixtures/v1_legacy_schema.php');
        require_once(__DIR__ . '/../fixtures/v1_data_simulator.php');
        parent::setUpBeforeClass();
    }

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    protected function tearDown(): void {
        v1_legacy_schema::drop_tables();

        parent::tearDown();
    }

    /**
     * No V1 tables at all: execute() does nothing, does not error.
     *
     * @return void
     */
    public function test_does_nothing_when_no_v1_tables_present(): void {
        $task = new migrate_v1_activities_task();
        $task->set_custom_data((object) ['blocksize' => 20]);
        // The mtrace() output is expected (that is the task's whole point
        // of communicating progress) but must not trip PHPUnit's "printed
        // output" risky-test detection — capture and discard it rather than
        // let it hit stdout unclaimed during the test run.
        ob_start();
        $task->execute();
        ob_end_clean();

        $this->assertTrue(true); // Reaching here without an exception is the assertion.
    }

    /**
     * The one real sample activity fits inside a single block (default
     * block size 20): one execute() call migrates it completely, and
     * nothing remains pending afterwards — so no follow-up task is queued.
     *
     * @return void
     */
    public function test_migrates_the_only_pending_activity_in_one_block(): void {
        global $DB;

        v1_legacy_schema::create_tables();
        v1_legacy_schema::insert_sample_activity();

        $task = new migrate_v1_activities_task();
        $task->set_custom_data((object) ['blocksize' => 20]);
        // The mtrace() output is expected (that is the task's whole point
        // of communicating progress) but must not trip PHPUnit's "printed
        // output" risky-test detection — capture and discard it rather than
        // let it hit stdout unclaimed during the test run.
        ob_start();
        $task->execute();
        ob_end_clean();

        $elang = $DB->get_record('elang', ['id' => 1], '*', MUST_EXIST);
        $this->assertNotEmpty($elang->currentversionid);

        $queued = \core\task\manager::get_adhoc_tasks(migrate_v1_activities_task::class);
        $this->assertCount(0, $queued, 'nothing pending, no follow-up task should be queued');
    }

    /**
     * With more pending activities than fit in one block, exactly
     * blocksize of them migrate per execute() call, and a follow-up task
     * instance is queued to continue with the rest.
     *
     * @return void
     */
    public function test_processes_one_block_and_requeues_when_more_pending(): void {
        global $DB;

        v1_legacy_schema::create_tables();
        $simulator = new v1_data_simulator([
            'seed' => 11,
            'activitycount' => 3,
            'mincuesperactivity' => 2,
            'maxcuesperactivity' => 2,
            'learnersperactivity' => 0,
            'injectedgecases' => false,
        ]);
        $summary = $simulator->generate();
        $this->assertCount(3, $summary->elangids);

        $task = new migrate_v1_activities_task();
        $task->set_custom_data((object) ['blocksize' => 1]);
        // The mtrace() output is expected (that is the task's whole point
        // of communicating progress) but must not trip PHPUnit's "printed
        // output" risky-test detection — capture and discard it rather than
        // let it hit stdout unclaimed during the test run.
        ob_start();
        $task->execute();
        ob_end_clean();

        $migratedcount = $DB->count_records_select('elang', 'currentversionid IS NOT NULL');
        $this->assertSame(1, $migratedcount, 'only one activity should migrate with blocksize 1');

        $queued = \core\task\manager::get_adhoc_tasks(migrate_v1_activities_task::class);
        $this->assertCount(1, $queued, 'a follow-up task should be queued while activities remain pending');
    }

    /**
     * An activity v1_detector considers pending (it has elang_cues rows and
     * no currentversionid) but that v1_migrator itself refuses — here, one
     * with no options blob to migrate from, an inconsistency v1_detector's
     * pending_activity_ids() does not itself filter for — is logged as a
     * failure and does not stop the rest of the block from migrating.
     *
     * @return void
     */
    public function test_a_failing_activity_does_not_stop_the_rest_of_the_block(): void {
        global $DB;

        v1_legacy_schema::create_tables();
        v1_legacy_schema::insert_sample_activity();

        // A second elang row with elang_cues content but no options blob:
        // pending_activity_ids() will still return it (it only checks
        // currentversionid), but migrate_activity() refuses it outright.
        $DB->insert_record_raw('elang', (object) [
            'id' => 2,
            'course' => 2,
            'name' => 'Missing options',
            'intro' => '',
            'introformat' => 1,
            'language' => 'en-GB',
            'timecreated' => time(),
            'timemodified' => time(),
        ], true, false, true);
        $DB->insert_record_raw('elang_cues', (object) [
            'id' => 900,
            'id_elang' => 2,
            'number' => 1,
            'begin' => 0,
            'end' => 1000,
            'title' => 'Broken.',
            'json' => json_encode([['type' => 'text', 'content' => 'Broken.']]),
        ], true, false, true);

        $task = new migrate_v1_activities_task();
        $task->set_custom_data((object) ['blocksize' => 20]);
        // The mtrace() output is expected (that is the task's whole point
        // of communicating progress) but must not trip PHPUnit's "printed
        // output" risky-test detection — capture and discard it rather than
        // let it hit stdout unclaimed during the test run.
        ob_start();
        $task->execute();
        ob_end_clean();

        $migrated = $DB->get_record('elang', ['id' => 1], '*', MUST_EXIST);
        $this->assertNotEmpty($migrated->currentversionid, 'the valid activity should still have migrated');

        $stillbroken = $DB->get_record('elang', ['id' => 2], '*', MUST_EXIST);
        $this->assertEmpty($stillbroken->currentversionid, 'the broken activity should not have migrated');
    }
}
