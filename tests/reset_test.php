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

namespace mod_elang;

use mod_elang\local\domain\attempt_manager;
use mod_elang\local\grading\answer_evaluator;
use mod_elang\local\grading\script_handler_manager;

/**
 * What a course reset does, and what it deliberately leaves alone.
 *
 * Reusing a course for the next cohort is the ordinary case, and until this
 * existed it left every attempt in place: the new group opened an exercise that
 * already held the previous group's answers, visible in the report and counted
 * in the gradebook, belonging to people no longer in the course.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     ::elang_reset_userdata
 * @covers     ::elang_reset_course_form_defaults
 */
final class reset_test extends \advanced_testcase {
    /**
     * Build a course with one exercise and one learner who has answered.
     *
     * @return array The course, the activity and the attempt
     */
    private function make_worked_exercise(): array {
        global $CFG;

        require_once($CFG->dirroot . '/mod/elang/lib.php');

        $course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);
        $version = $generator->create_version(['elangid' => $elang->id, 'status' => 'published']);
        $cue = $generator->create_cue(['versionid' => $version->id, 'transcript' => 'Le chat dort']);
        $gap = $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);

        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $manager = new attempt_manager(new answer_evaluator(new script_handler_manager([])));
        $attempt = $manager->start_attempt((int) $elang->id, (int) $student->id, (int) $version->id);
        $manager->submit_response((int) $attempt->id, (int) $gap->id, 'chat');

        return [$course, $elang, $attempt, $version];
    }

    /**
     * Ticking the box removes attempts and their responses.
     *
     * @return void
     */
    public function test_resetting_removes_attempts_and_responses(): void {
        global $DB;
        $this->resetAfterTest();

        [$course, $elang, $attempt] = $this->make_worked_exercise();

        $this->assertSame(1, $DB->count_records('elang_attempt', ['elangid' => $elang->id]));
        $this->assertGreaterThan(0, $DB->count_records('elang_response', ['attemptid' => $attempt->id]));

        elang_reset_userdata((object) ['courseid' => $course->id, 'reset_elang_attempts' => 1]);

        $this->assertSame(0, $DB->count_records('elang_attempt', ['elangid' => $elang->id]));
        $this->assertSame(0, $DB->count_records('elang_response', ['attemptid' => $attempt->id]));
    }

    /**
     * The exercise itself survives.
     *
     * A reset prepares a course for the next cohort; it does not empty it. The
     * versions, cues and gaps are the teaching material, and deleting them would
     * turn "start again with a new group" into "lose the exercise".
     *
     * @return void
     */
    public function test_resetting_keeps_the_exercise(): void {
        global $DB;
        $this->resetAfterTest();

        [$course, $elang, , $version] = $this->make_worked_exercise();
        $cuecount = $DB->count_records('elang_cue', ['versionid' => $version->id]);

        elang_reset_userdata((object) ['courseid' => $course->id, 'reset_elang_attempts' => 1]);

        $this->assertTrue($DB->record_exists('elang', ['id' => $elang->id]));
        $this->assertTrue($DB->record_exists('elang_version', ['id' => $version->id]));
        $this->assertSame($cuecount, $DB->count_records('elang_cue', ['versionid' => $version->id]));
    }

    /**
     * Without the box ticked, nothing is deleted.
     *
     * @return void
     */
    public function test_not_ticking_the_box_deletes_nothing(): void {
        global $DB;
        $this->resetAfterTest();

        [$course, $elang] = $this->make_worked_exercise();

        elang_reset_userdata((object) ['courseid' => $course->id, 'reset_elang_attempts' => 0]);

        $this->assertSame(1, $DB->count_records('elang_attempt', ['elangid' => $elang->id]));
    }

    /**
     * The option is off by default.
     *
     * Deleting learner work is not something a reset should do because a box
     * happened to arrive pre-ticked.
     *
     * @return void
     */
    public function test_the_option_is_off_by_default(): void {
        global $CFG;
        $this->resetAfterTest();

        require_once($CFG->dirroot . '/mod/elang/lib.php');
        $course = $this->getDataGenerator()->create_course();

        $defaults = elang_reset_course_form_defaults($course);

        $this->assertSame(0, $defaults['reset_elang_attempts']);
    }

    /**
     * A reset in one course leaves another course's work alone.
     *
     * @return void
     */
    public function test_resetting_one_course_leaves_another_alone(): void {
        global $DB;
        $this->resetAfterTest();

        [$course, $elang] = $this->make_worked_exercise();
        [, $otherelang] = $this->make_worked_exercise();

        elang_reset_userdata((object) ['courseid' => $course->id, 'reset_elang_attempts' => 1]);

        $this->assertSame(0, $DB->count_records('elang_attempt', ['elangid' => $elang->id]));
        $this->assertSame(1, $DB->count_records('elang_attempt', ['elangid' => $otherelang->id]));
    }
}
