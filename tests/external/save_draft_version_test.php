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

namespace mod_elang\external;

use core_external\external_api;
use mod_elang\fixtures\authoring_test_fixture_builder;
use mod_elang\local\domain\version_manager;

/**
 * Tests for the save_draft_version external function.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\save_draft_version
 */
final class save_draft_version_test extends \advanced_testcase {
    /** @var \stdClass */
    private $teacher;

    /** @var \stdClass */
    private $student;

    /** @var \stdClass */
    private $elang;

    /** @var \stdClass */
    private $draft;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        require_once(__DIR__ . '/../fixtures/authoring_test_fixture.php');
        $fixture = authoring_test_fixture_builder::create($this);
        $this->teacher = $fixture->teacher;
        $this->student = $fixture->student;
        $this->elang = $fixture->elang;
        $this->draft = $fixture->draft;
    }

    /**
     * Build a one-cue, one-gap payload with the given gap solution.
     *
     * @param string $solution The gap solution to embed
     * @return array A cue list suitable for save_draft_version::execute()
     */
    private function payload(string $solution): array {
        return authoring_test_fixture_builder::payload($solution);
    }

    /**
     * Saving persists the content, its nested answers and hints, and advances
     * the draft revision from 1 to 2.
     *
     * @return void
     */
    public function test_saving_persists_content_and_bumps_revision(): void {
        global $DB;

        $this->setUser($this->teacher);

        $result = save_draft_version::execute((int) $this->draft->id, -1, $this->payload('chat'));
        $result = external_api::clean_returnvalue(save_draft_version::execute_returns(), $result);

        $this->assertSame(2, $result['revision']);

        $cue = $DB->get_record('elang_cue', ['versionid' => $this->draft->id], '*', MUST_EXIST);
        $this->assertSame('cue-1', $cue->cuekey);
        $gap = $DB->get_record('elang_gap', ['cueid' => $cue->id], '*', MUST_EXIST);
        $this->assertSame('gap-1', $gap->gapkey);
        $this->assertSame('chat', $gap->solution);
        $this->assertSame(1, $DB->count_records('elang_gapanswer', ['gapid' => $gap->id]));
        $this->assertSame(1, $DB->count_records('elang_gaphint', ['gapid' => $gap->id]));
    }

    /**
     * A second save replaces the first draft's content rather than adding to it.
     *
     * @return void
     */
    public function test_saving_replaces_previous_content(): void {
        global $DB;

        $this->setUser($this->teacher);

        save_draft_version::execute((int) $this->draft->id, -1, $this->payload('chat'));
        save_draft_version::execute((int) $this->draft->id, -1, $this->payload('chien'));

        $this->assertSame(1, $DB->count_records('elang_cue', ['versionid' => $this->draft->id]));
        $cue = $DB->get_record('elang_cue', ['versionid' => $this->draft->id], '*', MUST_EXIST);
        $gap = $DB->get_record('elang_gap', ['cueid' => $cue->id], '*', MUST_EXIST);
        $this->assertSame('chien', $gap->solution);
    }

    /**
     * A save whose expected revision no longer matches the stored one is
     * refused, so a concurrent edit is not silently clobbered.
     *
     * @return void
     */
    public function test_a_stale_expected_revision_is_rejected(): void {
        $this->setUser($this->teacher);

        // First save moves the draft from revision 1 to 2.
        save_draft_version::execute((int) $this->draft->id, 1, $this->payload('chat'));

        // A second save that still believes it is on revision 1 is stale.
        $this->expectException(\moodle_exception::class);
        save_draft_version::execute((int) $this->draft->id, 1, $this->payload('chien'));
    }

    /**
     * A published version is immutable: it cannot be edited through this
     * function.
     *
     * @return void
     */
    public function test_a_published_version_cannot_be_edited(): void {
        $this->setUser($this->teacher);

        save_draft_version::execute((int) $this->draft->id, -1, $this->payload('chat'));
        (new version_manager())->publish((int) $this->draft->id, (int) $this->teacher->id);

        $this->expectException(\moodle_exception::class);
        save_draft_version::execute((int) $this->draft->id, -1, $this->payload('chien'));
    }

    /**
     * A user without mod/elang:manage cannot save.
     *
     * @return void
     */
    public function test_saving_requires_the_manage_capability(): void {
        $this->setUser($this->student);

        $this->expectException(\required_capability_exception::class);
        save_draft_version::execute((int) $this->draft->id, -1, $this->payload('chat'));
    }
}
