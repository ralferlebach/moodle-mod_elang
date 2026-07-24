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

use mod_elang\fixtures\v1_data_simulator;
use mod_elang\fixtures\v1_legacy_schema;

/**
 * Tests for v1_data_simulator itself — not a golden master (there is no
 * single correct output for randomised data), but a check that what it
 * produces actually has the shape Migration_V1_V2.md chapter 1.1 asks for:
 * respects the real V1 schema's constraints, reproduces the gap-order bug
 * mechanism rather than just asserting it exists, and reliably includes the
 * requested edge cases.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\fixtures\v1_data_simulator
 */
final class v1_data_simulator_test extends \advanced_testcase {
    public static function setUpBeforeClass(): void {
        require_once(__DIR__ . '/../../fixtures/v1_legacy_schema.php');
        require_once(__DIR__ . '/../../fixtures/v1_data_simulator.php');
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
     * Two runs with the same seed produce byte-identical data — required for
     * a migration test suite to be able to pin exact expected values against
     * a generated (not hand-written) fixture.
     *
     * @return void
     */
    public function test_same_seed_produces_identical_output(): void {
        global $DB;

        $simulator = new v1_data_simulator(['seed' => 42, 'activitycount' => 1, 'injectedgecases' => false]);
        $summary = $simulator->generate();
        $firstjson = $DB->get_fieldset_select('elang_cues', 'json', 'id_elang = ?', [$summary->elangids[0]]);

        v1_legacy_schema::drop_tables();
        v1_legacy_schema::create_tables();
        // Calling drop_tables()/create_tables() only ever touches the four
        // legacy-only tables (by design — they must never go near
        // install.xml's own elang table). The simulator writes straight
        // into the real elang table now (Migration_V1_V2.md chapter 1.3),
        // so this test has to clear that itself before generating a second
        // time with the same
        // ids, or the second run's INSERT collides with the first run's row.
        $DB->delete_records('elang');

        $simulator2 = new v1_data_simulator(['seed' => 42, 'activitycount' => 1, 'injectedgecases' => false]);
        $summary2 = $simulator2->generate();
        $secondjson = $DB->get_fieldset_select('elang_cues', 'json', 'id_elang = ?', [$summary2->elangids[0]]);

        $this->assertSame($firstjson, $secondjson);
    }

    /**
     * The requested number of activities, each with cue counts inside the
     * configured range, are actually created.
     *
     * @return void
     */
    public function test_generates_the_requested_number_of_activities_and_cue_range(): void {
        global $DB;

        $simulator = new v1_data_simulator([
            'seed' => 1,
            'activitycount' => 4,
            'mincuesperactivity' => 6,
            'maxcuesperactivity' => 6,
            'learnersperactivity' => 2,
            'injectedgecases' => false,
        ]);
        $summary = $simulator->generate();

        $this->assertCount(4, $summary->elangids);
        $this->assertSame(4, $DB->count_records('elang'));

        foreach ($summary->elangids as $elangid) {
            $this->assertSame(6, $DB->count_records('elang_cues', ['id_elang' => $elangid]));
        }
    }

    /**
     * icueuser is UNIQUE in real V1 (db/install.xml) — the simulator's
     * overwrite-in-place learner simulation must never violate it, however
     * many (activity, learner, cue) combinations a large run produces.
     *
     * @return void
     */
    public function test_never_produces_duplicate_elang_users_rows_for_the_same_cue_and_learner(): void {
        global $DB;

        $simulator = new v1_data_simulator([
            'seed' => 7,
            'activitycount' => 2,
            'mincuesperactivity' => 8,
            'maxcuesperactivity' => 8,
            'learnersperactivity' => 6,
            'injectedgecases' => false,
        ]);
        $simulator->generate();

        $rows = $DB->get_records_sql(
            'SELECT id_cue, id_user, COUNT(*) AS c FROM {elang_users} GROUP BY id_cue, id_user HAVING COUNT(*) > 1'
        );

        $this->assertEmpty($rows, 'icueuser must stay unique, as it is in real V1');
    }

    /**
     * The gap-order bug (Migration_V1_V2.md chapter 3.1) is reproduced by
     * running the real buggy mechanism, not injected after the fact — so a
     * cue that is the ONLY cue with more than one gap in a short activity
     * must show no collision (the bug only manifests once an earlier cue's
     * extra gaps overlap a later cue's own index), while a longer run with
     * multiple multi-gap cues reliably produces at least one collision or
     * gap in the `order` sequence, exactly like the real sample activity did.
     *
     * @return void
     */
    public function test_reproduces_the_real_gap_order_counter_bug_at_scale(): void {
        global $DB;

        $simulator = new v1_data_simulator([
            'seed' => 3,
            'activitycount' => 1,
            'mincuesperactivity' => 10,
            'maxcuesperactivity' => 10,
            'learnersperactivity' => 0,
            'injectedgecases' => false,
        ]);
        $summary = $simulator->generate();

        $jsonvalues = $DB->get_fieldset_select('elang_cues', 'json', 'id_elang = ?', [$summary->elangids[0]]);
        $orders = [];
        foreach ($jsonvalues as $json) {
            foreach (json_decode($json, true) as $segment) {
                if ($segment['type'] === 'input') {
                    $orders[] = $segment['order'];
                }
            }
        }

        $this->assertNotEmpty($orders);
        $this->assertNotSame(
            count($orders),
            count(array_unique($orders)),
            'a 10-cue run using multi-gap templates should reproduce at least one order collision, '
                . 'the same way the real sample activity did (Migration_V1_V2.md chapter 3.1)'
        );

        // Column `number`, unlike `order`, must stay strictly sequential and
        // unique — verified never to be affected by the bug
        // (Migration_V1_V2.md chapter 1.2).
        $numbers = array_map('intval', $DB->get_fieldset_select('elang_cues', 'number', 'id_elang = ?', [$summary->elangids[0]]));
        sort($numbers);
        $this->assertSame(range(1, 10), $numbers);
    }

    /**
     * With injectedgecases enabled, every edge case Migration_V1_V2.md
     * chapter 3.1 lists is actually present in the generated data, not just
     * named in the returned summary.
     *
     * @return void
     */
    public function test_injects_every_documented_edge_case(): void {
        global $DB;

        $simulator = new v1_data_simulator([
            'seed' => 5,
            'activitycount' => 1,
            'mincuesperactivity' => 4,
            'maxcuesperactivity' => 4,
            'learnersperactivity' => 1,
            'injectedgecases' => true,
        ]);
        $summary = $simulator->generate();

        $this->assertCount(4, $summary->edgecases);

        $longest = $DB->get_field_sql(
            'SELECT MAX(' . $DB->sql_length('json') . ') FROM {elang_users} WHERE id_user = ?',
            [900001]
        );
        $this->assertGreaterThan(1000, $longest, 'overlong answer text edge case not found');

        $orphan = $DB->get_record('elang_users', ['id_user' => 900002]);
        $this->assertNotFalse($orphan);
        $this->assertFalse(
            $DB->record_exists('elang_cues', ['id' => $orphan->id_cue]),
            'orphaned response edge case should reference a non-existent cue'
        );

        $linkcue = $DB->get_record('elang_cues', ['id_elang' => $summary->elangids[0], 'number' => 9999]);
        $this->assertNotFalse($linkcue);
        $this->assertStringContainsString('javascript:', $linkcue->json, 'invalid link URL edge case not found');

        $empty = $DB->get_record('elang_users', ['id_user' => 900003]);
        $this->assertNotFalse($empty);
        $state = json_decode($empty->json, true);
        $this->assertSame('', reset($state)['content']);
    }

    /**
     * With injectedgecases disabled, none of the edge-case marker user ids
     * appear — a test that specifically wants "clean" generated data is not
     * forced to filter them out itself.
     *
     * @return void
     */
    public function test_edge_cases_are_opt_out(): void {
        global $DB;

        $simulator = new v1_data_simulator([
            'seed' => 5,
            'activitycount' => 1,
            'mincuesperactivity' => 4,
            'maxcuesperactivity' => 4,
            'learnersperactivity' => 1,
            'injectedgecases' => false,
        ]);
        $summary = $simulator->generate();

        $this->assertSame([], $summary->edgecases);
        $this->assertFalse($DB->record_exists('elang_users', ['id_user' => 900001]));
        $this->assertFalse($DB->record_exists('elang_cues', ['id_elang' => $summary->elangids[0], 'number' => 9999]));
    }
}
