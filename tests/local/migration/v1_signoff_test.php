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
 * Tests for v1_signoff: approval is a separate, persisted decision from
 * migration itself, never assumed from a clean verification report.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\migration\v1_signoff
 */
final class v1_signoff_test extends \advanced_testcase {
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
     * A freshly migrated activity is not approved yet, and is listed as
     * pending approval.
     *
     * @return void
     */
    public function test_a_migrated_activity_starts_unapproved_and_pending(): void {
        $this->assertFalse(v1_signoff::is_approved(1));
        $this->assertSame([1], v1_signoff::pending_approval_ids());

        $status = v1_signoff::get_status(1);
        $this->assertTrue($status->migrated);
        $this->assertFalse($status->approved);
        $this->assertNull($status->approveduserid);
        $this->assertNull($status->approvedtime);
    }

    /**
     * Approving records the userid and a timestamp, and removes the
     * activity from the pending-approval list.
     *
     * @return void
     */
    public function test_approve_records_userid_and_time_and_clears_pending(): void {
        $before = time();
        v1_signoff::approve(1, 42);
        $after = time();

        $this->assertTrue(v1_signoff::is_approved(1));
        $this->assertSame([], v1_signoff::pending_approval_ids());

        $status = v1_signoff::get_status(1);
        $this->assertTrue($status->approved);
        $this->assertSame(42, $status->approveduserid);
        $this->assertGreaterThanOrEqual($before, $status->approvedtime);
        $this->assertLessThanOrEqual($after, $status->approvedtime);
    }

    /**
     * Approval does not require a clean v1_verifier report — the decision
     * to approve despite a known discrepancy belongs to the administrator,
     * not to this class (see the class docblock).
     *
     * @return void
     */
    public function test_approve_does_not_require_a_clean_verification(): void {
        global $DB;

        $DB->set_field('elang_gap', 'solution', 'tampered', ['gapkey' => 'v1-gap-11-1']);
        $result = (new v1_verifier())->verify_activity(1);
        $this->assertFalse($result->ok, 'sanity check: the tampering should actually be detected');

        v1_signoff::approve(1, 42);

        $this->assertTrue(v1_signoff::is_approved(1));
    }

    /**
     * Approving an activity that was never migrated is refused.
     *
     * @return void
     */
    public function test_refuses_to_approve_an_unmigrated_activity(): void {
        global $DB;

        $DB->insert_record_raw('elang', (object) [
            'id' => 3,
            'course' => 2,
            'name' => 'Never migrated',
            'intro' => '',
            'introformat' => 1,
            'language' => 'en-GB',
            'timecreated' => time(),
            'timemodified' => time(),
        ], true, false, true);

        $this->expectException(\coding_exception::class);
        v1_signoff::approve(3, 42);
    }
}
