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
use mod_elang\local\domain\version_manager;

/**
 * Tests for the publish_version external function.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\publish_version
 */
final class publish_version_test extends \advanced_testcase {
    /** @var \stdClass */
    private $teacher;

    /** @var \stdClass */
    private $student;

    /** @var \stdClass */
    private $elang;

    /** @var \mod_elang_generator */
    private $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $this->teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $this->student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $this->generator = $generator;
        $this->elang = $generator->create_instance(['course' => $course->id]);
    }

    /**
     * Build a valid draft (one cue, one gap) and return it.
     *
     * @return \stdClass The draft version
     */
    private function create_valid_draft(): \stdClass {
        $manager = new version_manager();
        $draft = $manager->create_draft((int) $this->elang->id, (int) $this->teacher->id);
        $cue = $this->generator->create_cue(['versionid' => $draft->id, 'transcript' => 'Le chat dort']);
        $this->generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);

        return $draft;
    }

    /**
     * A manager can publish a well-formed draft, and it becomes the activity's
     * current version.
     *
     * @return void
     */
    public function test_manager_publishes_a_valid_draft(): void {
        $draft = $this->create_valid_draft();
        $this->setUser($this->teacher);

        $result = publish_version::execute((int) $draft->id);
        $result = external_api::clean_returnvalue(publish_version::execute_returns(), $result);

        $this->assertSame((int) $draft->id, $result['versionid']);
        $this->assertSame('published', $result['status']);
        $this->assertSame(
            (int) $draft->id,
            (int) (new version_manager())->get_published((int) $this->elang->id)->id
        );
    }

    /**
     * Publishing an empty (invalid) draft is refused.
     *
     * @return void
     */
    public function test_publishing_an_invalid_draft_is_rejected(): void {
        $manager = new version_manager();
        $draft = $manager->create_draft((int) $this->elang->id, (int) $this->teacher->id);
        $this->setUser($this->teacher);

        $this->expectException(\moodle_exception::class);
        publish_version::execute((int) $draft->id);
    }

    /**
     * A user without mod/elang:manage cannot publish.
     *
     * @return void
     */
    public function test_publishing_requires_the_manage_capability(): void {
        $draft = $this->create_valid_draft();
        $this->setUser($this->student);

        $this->expectException(\required_capability_exception::class);
        publish_version::execute((int) $draft->id);
    }
}
