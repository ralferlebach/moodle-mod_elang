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
 * Tests for the get_exercise external function.
 *
 * Extends \advanced_testcase directly — see submit_response_test.php's
 * class docblock for why not \externallib_advanced_testcase.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\get_exercise
 */
final class get_exercise_test extends \advanced_testcase {
    /** @var \stdClass */
    private $cm;

    /** @var \stdClass */
    private $elang;

    /** @var \stdClass */
    private $student;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $this->student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $this->elang = $generator->create_instance(['course' => $course->id, 'language' => 'fr']);
        $this->cm = get_coursemodule_from_instance('elang', $this->elang->id);

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draft = $versionmanager->create_draft($this->elang->id, $this->student->id);
        $cueone = $generator->create_cue(['versionid' => $draft->id]);
        $generator->create_gap(['cueid' => $cueone->id, 'solution' => 'chat']);
        $generator->create_gap(['cueid' => $cueone->id, 'solution' => 'chien']);
        $cuetwo = $generator->create_cue(['versionid' => $draft->id]);
        $generator->create_gap(['cueid' => $cuetwo->id, 'solution' => 'oiseau']);
        $versionmanager->publish($draft->id, $this->student->id);

        $this->setUser($this->student);
    }

    /**
     * The published version's identifiers and counts are returned.
     *
     * @return void
     */
    public function test_returns_published_version_counts(): void {
        $result = get_exercise::execute($this->cm->id);
        $result = external_api::clean_returnvalue(get_exercise::execute_returns(), $result);

        $this->assertSame((int) $this->elang->id, $result['elangid']);
        $this->assertSame('fr', $result['language']);
        $this->assertSame(2, $result['totalcues']);
        $this->assertSame(3, $result['totalgaps']);
        $this->assertNotSame('', $result['contenthash']);
    }

    /**
     * Fails with a clear error when the activity has no published version yet.
     *
     * @return void
     */
    public function test_requires_a_published_version(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $unpublished = $generator->create_instance(['course' => $this->elang->course]);
        $cm = get_coursemodule_from_instance('elang', $unpublished->id);

        $this->expectException(\moodle_exception::class);
        get_exercise::execute($cm->id);
    }

    /**
     * A user without mod/elang:view cannot fetch the exercise shape.
     *
     * @return void
     */
    public function test_requires_capability(): void {
        global $DB;

        $context = \context_module::instance($this->cm->id);
        $studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
        assign_capability('mod/elang:view', CAP_PROHIBIT, $studentrole->id, $context->id, true);
        $context->mark_dirty();

        // The capability mod/elang:view is, by Moodle's own naming
        // convention, the capability its core "uservisible" machinery
        // checks for this module type. Prohibiting it makes require_login() inside
        // self::validate_context() consider the activity hidden and deny
        // access there, before this function's own explicit
        // require_capability('mod/elang:view', ...) call is ever reached —
        // confirmed against a real Moodle 4.5 test run, which threw
        // \core\exception\require_login_exception ("Activity is hidden"),
        // not required_capability_exception. Both correctly deny access;
        // this asserts the one that actually happens.
        $this->expectException(\core\exception\require_login_exception::class);
        get_exercise::execute($this->cm->id);
    }
}
