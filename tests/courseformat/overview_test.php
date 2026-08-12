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

namespace mod_elang\courseformat;

/**
 * Tests for the activity overview (Moodle 5.0+).
 *
 * Skips on Moodle versions without the overview feature (4.5), so the file is
 * inert there and only exercises the class on 5.0 and later.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\courseformat\overview
 */
final class overview_test extends \advanced_testcase {
    /**
     * A teacher sees the report action and the attempt count; a learner sees
     * neither.
     *
     * @return void
     */
    public function test_overview_items_depend_on_capability(): void {
        global $DB;

        if (!class_exists('\core_courseformat\activityoverviewbase')) {
            $this->markTestSkipped('The activity overview requires Moodle 5.0 or later.');
        }

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id, 'language' => 'fr']);
        $version = $generator->create_version(['elangid' => $elang->id, 'status' => 'published']);
        $cue = $generator->create_cue(['versionid' => $version->id, 'transcript' => 'Le chat dort']);
        $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);
        $DB->insert_record('elang_attempt', (object) [
            'elangid' => $elang->id,
            'versionid' => $version->id,
            'userid' => $student->id,
            'attemptnumber' => 1,
            'state' => 'finished',
            'totalgaps' => 1,
            'answeredgaps' => 0,
            'correctgaps' => 0,
            'exactgaps' => 0,
            'hintedgaps' => 0,
            'score' => 0,
            'timestart' => time(),
            'timefinish' => time(),
            'timemodified' => time(),
        ]);

        $cm = get_fast_modinfo($course)->get_cm($elang->cmid);

        // Teacher: the report action and a non-empty attempts item.
        $this->setUser($teacher);
        $overview = new overview($cm);
        $this->assertInstanceOf(\core_courseformat\local\overview\overviewitem::class, $overview->get_actions_overview());
        $extras = $overview->get_extra_overview_items();
        $this->assertArrayHasKey('attempts', $extras);
        $this->assertInstanceOf(\core_courseformat\local\overview\overviewitem::class, $extras['attempts']);

        // Learner: neither the action nor the attempts item.
        $this->setUser($student);
        $overview = new overview($cm);
        $this->assertNull($overview->get_actions_overview());
        $this->assertSame([], $overview->get_extra_overview_items());
    }
}
