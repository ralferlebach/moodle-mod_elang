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

namespace mod_elang\local\report;

use mod_elang\local\domain\attempt_manager;
use mod_elang\local\grading\answer_evaluator;
use mod_elang\local\grading\script_handler_manager;

/**
 * Tests for attempt_report.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\report\attempt_report
 */
final class attempt_report_test extends \advanced_testcase {
    /**
     * A finished attempt appears in the activity listing and its detail pairs
     * every gap with the learner's response in order.
     *
     * @return void
     */
    public function test_report_lists_and_details_an_attempt(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id, 'language' => 'fr']);
        $version = $generator->create_version(['elangid' => $elang->id, 'status' => 'published']);
        $cue = $generator->create_cue(['versionid' => $version->id, 'transcript' => 'Le chat et le chien']);
        $gapone = $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat', 'sortorder' => 1]);
        $gaptwo = $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chien', 'sortorder' => 2]);

        $manager = new attempt_manager(new answer_evaluator(new script_handler_manager([])));
        $attempt = $manager->start_attempt((int) $elang->id, (int) $student->id, (int) $version->id);
        $manager->submit_response($attempt->id, (int) $gapone->id, 'chat');
        $manager->submit_response($attempt->id, (int) $gaptwo->id, 'wrong');
        $manager->finish_attempt($attempt->id);

        $report = new attempt_report();

        $list = $report->list_for_activity((int) $elang->id);
        $this->assertCount(1, $list);
        $this->assertSame((int) $attempt->id, $list[0]['attemptid']);
        $this->assertSame((int) $student->id, $list[0]['userid']);
        $this->assertSame('finished', $list[0]['state']);
        $this->assertSame(2, $list[0]['answeredgaps']);

        $detail = $report->detail((int) $attempt->id);
        $this->assertSame((int) $elang->id, $detail['attempt']['elangid']);
        $this->assertSame('finished', $detail['attempt']['state']);
        $this->assertCount(2, $detail['gaps']);

        $this->assertSame('chat', $detail['gaps'][0]['solution']);
        $this->assertSame('chat', $detail['gaps'][0]['responsetext']);
        $this->assertSame('exact', $detail['gaps'][0]['resultstate']);

        $this->assertSame('chien', $detail['gaps'][1]['solution']);
        $this->assertSame('wrong', $detail['gaps'][1]['responsetext']);
        $this->assertSame('incorrect', $detail['gaps'][1]['resultstate']);
    }

    /**
     * An activity with no attempts lists nothing.
     *
     * @return void
     */
    public function test_activity_with_no_attempts_lists_nothing(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);

        $this->assertSame([], (new attempt_report())->list_for_activity((int) $elang->id));
    }

    /**
     * Passing a group id restricts the listing to that group's members.
     *
     * @return void
     */
    public function test_list_filters_by_group(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $insider = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $outsider = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $group = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $group->id, 'userid' => $insider->id]);

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);
        $version = $generator->create_version(['elangid' => $elang->id, 'status' => 'published']);

        $manager = new attempt_manager(new answer_evaluator(new script_handler_manager([])));
        $manager->start_attempt((int) $elang->id, (int) $insider->id, (int) $version->id);
        $manager->start_attempt((int) $elang->id, (int) $outsider->id, (int) $version->id);

        $report = new attempt_report();
        $this->assertCount(2, $report->list_for_activity((int) $elang->id));

        $grouponly = $report->list_for_activity((int) $elang->id, (int) $group->id);
        $this->assertCount(1, $grouponly);
        $this->assertSame((int) $insider->id, $grouponly[0]['userid']);
    }

    /**
     * export_rows() flattens each attempt into one row keyed by the stable
     * column names of export_columns(), ready for the Dataformat API.
     *
     * @return void
     */
    public function test_export_rows_match_the_export_columns(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id, 'language' => 'fr']);
        $version = $generator->create_version(['elangid' => $elang->id, 'status' => 'published']);
        $cue = $generator->create_cue(['versionid' => $version->id, 'transcript' => 'Le chat dort']);
        $gap = $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);

        $manager = new attempt_manager(new answer_evaluator(new script_handler_manager([])));
        $attempt = $manager->start_attempt((int) $elang->id, (int) $student->id, (int) $version->id);
        $manager->submit_response($attempt->id, (int) $gap->id, 'chat');
        $manager->finish_attempt($attempt->id);

        $report = new attempt_report();
        $columns = $report->export_columns();
        $this->assertArrayHasKey('user', $columns);
        $this->assertArrayHasKey('score', $columns);

        $rows = $report->export_rows((int) $elang->id);
        $this->assertCount(1, $rows);
        $row = reset($rows);
        $this->assertSame(array_keys($columns), array_keys($row));
        $this->assertSame(fullname($student), $row['user']);
    }
}
