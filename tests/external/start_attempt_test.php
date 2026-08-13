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

/**
 * Tests for the start_attempt external function.
 *
 * Extends \advanced_testcase directly rather than the legacy
 * \externallib_advanced_testcase — see submit_response_test.php's class
 * docblock for why.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\start_attempt
 */
final class start_attempt_test extends \advanced_testcase {
    /** @var \stdClass */
    private $course;

    /** @var \stdClass */
    private $cm;

    /** @var \stdClass */
    private $elang;

    /** @var \stdClass */
    private $student;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $this->course = $this->getDataGenerator()->create_course();
        $this->student = $this->getDataGenerator()->create_and_enrol($this->course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $this->elang = $generator->create_instance(['course' => $this->course->id, 'language' => 'fr']);
        $this->cm = get_coursemodule_from_instance('elang', $this->elang->id);

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draft = $versionmanager->create_draft($this->elang->id, $this->student->id);
        $cue = $generator->create_cue(['versionid' => $draft->id]);
        $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);
        $versionmanager->publish($draft->id, $this->student->id);
    }

    /**
     * A capable, enrolled student can start an attempt.
     *
     * @return void
     */
    public function test_starts_a_new_attempt(): void {
        $this->setUser($this->student);

        $result = start_attempt::execute($this->cm->id);
        $result = external_api::clean_returnvalue(start_attempt::execute_returns(), $result);

        $this->assertSame('inprogress', $result['state']);
        $this->assertSame(1, $result['attemptnumber']);
        $this->assertSame(1, $result['totalgaps']);
        $this->assertSame(0, $result['answeredgaps']);
    }

    /**
     * Calling it twice resumes the same attempt rather than starting a new one.
     *
     * @return void
     */
    public function test_resumes_an_existing_attempt(): void {
        $this->setUser($this->student);

        $first = start_attempt::execute($this->cm->id);
        $second = start_attempt::execute($this->cm->id);

        $this->assertSame($first['attemptid'], $second['attemptid']);
    }

    /**
     * A user without mod/elang:attempt cannot start an attempt.
     *
     * @return void
     */
    public function test_requires_capability(): void {
        global $DB;

        $this->setUser($this->student);

        $context = \context_module::instance($this->cm->id);
        $studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
        assign_capability('mod/elang:attempt', CAP_PROHIBIT, $studentrole->id, $context->id, true);
        $context->mark_dirty();

        $this->expectException(\required_capability_exception::class);
        start_attempt::execute($this->cm->id);
    }

    /**
     * Starting an attempt fails with a clear error when the activity has no
     * published version yet.
     *
     * @return void
     */
    public function test_requires_a_published_version(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $unpublished = $generator->create_instance(['course' => $this->course->id]);
        $cm = get_coursemodule_from_instance('elang', $unpublished->id);

        $this->setUser($this->student);

        $this->expectException(\moodle_exception::class);
        start_attempt::execute($cm->id);
    }
}
