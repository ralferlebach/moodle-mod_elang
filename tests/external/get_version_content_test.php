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

/**
 * Tests for the get_version_content external function.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\get_version_content
 */
final class get_version_content_test extends \advanced_testcase {
    /** @var \stdClass */
    private $teacher;

    /** @var \stdClass */
    private $student;

    /** @var \stdClass */
    private $draft;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        require_once(__DIR__ . '/../fixtures/authoring_test_fixture.php');
        $fixture = authoring_test_fixture_builder::create($this);
        $this->teacher = $fixture->teacher;
        $this->student = $fixture->student;
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
     * A manager reads a version's metadata and full content, solutions
     * included.
     *
     * @return void
     */
    public function test_manager_reads_content_including_solutions(): void {
        $this->setUser($this->teacher);
        save_draft_version::execute((int) $this->draft->id, -1, $this->payload('chat'));

        $result = get_version_content::execute((int) $this->draft->id);
        $result = external_api::clean_returnvalue(get_version_content::execute_returns(), $result);

        $this->assertSame((int) $this->draft->id, $result['versionid']);
        $this->assertSame('draft', $result['status']);
        $this->assertSame(2, $result['revision']);
        $this->assertCount(1, $result['cues']);
        $this->assertSame('cue-1', $result['cues'][0]['cuekey']);
        $this->assertSame('chat', $result['cues'][0]['gaps'][0]['solution']);
        $this->assertSame('chatte', $result['cues'][0]['gaps'][0]['answers'][0]['answer']);
        $this->assertSame(1, $result['cues'][0]['gaps'][0]['hints'][0]['level']);
    }

    /**
     * Content saved through save_draft_version reads back through
     * get_version_content unchanged, so the editor can round-trip it.
     *
     * @return void
     */
    public function test_saved_content_round_trips(): void {
        $this->setUser($this->teacher);
        save_draft_version::execute((int) $this->draft->id, -1, $this->payload('chien'));

        $result = get_version_content::execute((int) $this->draft->id);
        $result = external_api::clean_returnvalue(get_version_content::execute_returns(), $result);

        $gap = $result['cues'][0]['gaps'][0];
        $this->assertSame('gap-1', $gap['gapkey']);
        $this->assertSame('chien', $gap['solution']);
        $this->assertSame(0, $gap['maxlength']);
        $this->assertSame('', $gap['linkurl']);
    }

    /**
     * A user without mod/elang:manage cannot read the authoring content.
     *
     * @return void
     */
    public function test_reading_requires_the_manage_capability(): void {
        $this->setUser($this->student);

        $this->expectException(\required_capability_exception::class);
        get_version_content::execute((int) $this->draft->id);
    }
}
