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
 * Tests for v1_decommissioner: it must never drop anything while an
 * activity is unmigrated or unapproved, and once nothing blocks it, it
 * removes exactly the legacy tables and elang.options — nothing else,
 * and in particular never the sign-off audit fields.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\migration\v1_decommissioner
 */
final class v1_decommissioner_test extends \advanced_testcase {
    public static function setUpBeforeClass(): void {
        require_once(__DIR__ . '/../../fixtures/v1_legacy_schema.php');
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
     * Nothing to decommission at all (no V1 tables present): no blockers,
     * safe to call decommission() as a no-op.
     *
     * @return void
     */
    public function test_no_v1_tables_means_no_blockers_and_a_harmless_noop(): void {
        $this->assertSame([], v1_decommissioner::blockers());

        $result = v1_decommissioner::decommission();

        $this->assertSame([], $result->droppedtables);
        $this->assertSame([], $result->droppedfields);
    }

    /**
     * An activity that has not been migrated yet blocks decommissioning.
     *
     * @return void
     */
    public function test_an_unmigrated_activity_blocks_decommissioning(): void {
        v1_legacy_schema::create_tables();
        v1_legacy_schema::insert_sample_activity();

        $blockers = v1_decommissioner::blockers();
        $this->assertNotEmpty($blockers);
        $this->assertStringContainsString('not yet migrated', implode(' ', $blockers));

        $this->expectException(\coding_exception::class);
        v1_decommissioner::decommission();
    }

    /**
     * A migrated but not-yet-approved activity blocks decommissioning too
     * — being migrated is not enough on its own.
     *
     * @return void
     */
    public function test_a_migrated_but_unapproved_activity_blocks_decommissioning(): void {
        v1_legacy_schema::create_tables();
        v1_legacy_schema::insert_sample_activity();
        (new v1_migrator())->migrate_activity(1);

        $blockers = v1_decommissioner::blockers();
        $this->assertNotEmpty($blockers);
        $this->assertStringContainsString('not yet approved', implode(' ', $blockers));

        $this->expectException(\coding_exception::class);
        v1_decommissioner::decommission();
    }

    /**
     * Once every activity is migrated AND approved, decommissioning
     * proceeds: the four legacy tables and elang.options are gone, but the
     * sign-off audit fields survive, and the migrated V2 data (cues, gaps,
     * attempts, responses) is completely untouched.
     *
     * @return void
     */
    public function test_decommissions_once_everything_is_migrated_and_approved(): void {
        global $DB;

        v1_legacy_schema::create_tables();
        v1_legacy_schema::insert_sample_activity();
        (new v1_migrator())->migrate_activity(1);
        v1_signoff::approve(1, 42);

        $this->assertSame([], v1_decommissioner::blockers());

        $result = v1_decommissioner::decommission();

        $this->assertEqualsCanonicalizing(
            ['elang_cues', 'elang_users', 'elang_help', 'elang_check'],
            $result->droppedtables
        );
        $this->assertSame(['elang.options'], $result->droppedfields);

        $dbman = $DB->get_manager();
        foreach (['elang_cues', 'elang_users', 'elang_help', 'elang_check'] as $name) {
            $this->assertFalse($dbman->table_exists(new \xmldb_table($name)), "$name should no longer exist");
        }
        $this->assertFalse(
            $dbman->field_exists(new \xmldb_table('elang'), new \xmldb_field('options')),
            'elang.options should no longer exist'
        );

        // The audit trail and the migrated V2 data both survive.
        $elang = $DB->get_record('elang', ['id' => 1], '*', MUST_EXIST);
        $this->assertSame(42, (int) $elang->migrationapproveduserid);
        $this->assertNotEmpty($elang->migrationapprovedtime);
        $this->assertSame(9, $DB->count_records('elang_cue'));
        $this->assertSame(9, $DB->count_records('elang_gap'));
        $this->assertSame(1, $DB->count_records('elang_attempt'));
        $this->assertSame(3, $DB->count_records('elang_response'));
    }

    /**
     * Calling decommission() a second time, after the legacy tables are
     * already gone, is a harmless no-op rather than an error — there is
     * nothing left to block it, and nothing left to drop.
     *
     * @return void
     */
    public function test_decommissioning_twice_is_a_harmless_noop(): void {
        v1_legacy_schema::create_tables();
        v1_legacy_schema::insert_sample_activity();
        (new v1_migrator())->migrate_activity(1);
        v1_signoff::approve(1, 42);

        v1_decommissioner::decommission();
        $result = v1_decommissioner::decommission();

        $this->assertSame([], $result->droppedtables);
        $this->assertSame([], $result->droppedfields);
    }
}
