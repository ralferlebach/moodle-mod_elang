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

/**
 * Shared fixture builder for external function tests that operate on an
 * existing attempt (submit_response_test, finish_attempt_test).
 *
 * A plain class with a static method, not a trait: a trait consumed via
 * "use TraitName;" inside a class body is resolved when that class is
 * compiled — i.e. as soon as the file containing it is loaded, before any
 * method (including a static setUpBeforeClass()) can run. Loading such a
 * trait via require_once() inside setUpBeforeClass(), as
 * tests/fixtures/fake_script_handler.php does for a plain class, is
 * therefore too late and fails with "Trait ... not found" (confirmed
 * against a real Moodle 4.5 test run). A require_once() directly at file
 * scope, before the class declaration, does load it in time, but Moodle's
 * coding standard flags a file-scope require_once as an unwanted global
 * state change outside of MOODLE_INTERNAL-guarded files (also confirmed
 * against phpcs --standard=moodle).
 *
 * A plain class with a static factory method sidesteps both problems: like
 * fake_script_handler, it is only referenced at runtime, from inside a
 * setUp() method body, so require_once() inside setUpBeforeClass() is early
 * enough, and no file-scope statement is needed.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elang\fixtures;

/**
 * Builds a single-gap exercise with a published version and an
 * already-started attempt for the enrolled student.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class attempt_test_fixture_builder {
    /**
     * Create a course with two enrolled students, a published one-gap
     * exercise, and an in-progress attempt started by the first student.
     *
     * @param \advanced_testcase $testcase The calling test case, already
     *        past resetAfterTest()
     * @return object{cm: \stdClass, gap: \stdClass, student: \stdClass, otherstudent: \stdClass, attemptid: int}
     */
    public static function create(\advanced_testcase $testcase): object {
        $course = $testcase->getDataGenerator()->create_course();
        $student = $testcase->getDataGenerator()->create_and_enrol($course, 'student');
        $otherstudent = $testcase->getDataGenerator()->create_and_enrol($course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $testcase->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id, 'language' => 'fr']);
        $cm = get_coursemodule_from_instance('elang', $elang->id);

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draft = $versionmanager->create_draft($elang->id, $student->id);
        $cue = $generator->create_cue(['versionid' => $draft->id]);
        $gap = $generator->create_gap([
            'cueid' => $cue->id,
            'solution' => 'chat',
            'gradingalgorithm' => \mod_elang\local\grading\answer_evaluator::ALGORITHM_EXACT,
        ]);
        $versionmanager->publish($draft->id, $student->id);

        $testcase->setUser($student);
        $result = \mod_elang\external\start_attempt::execute($cm->id);

        return (object) [
            'cm' => $cm,
            'gap' => $gap,
            'student' => $student,
            'otherstudent' => $otherstudent,
            'attemptid' => $result['attemptid'],
        ];
    }
}
