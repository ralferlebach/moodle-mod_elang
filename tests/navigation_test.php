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

/**
 * Tests for the activity's mode navigation and the transcript access policy.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     ::elang_extend_settings_navigation
 * @covers     ::elang_can_export_worksheet
 * @covers     ::elang_can_export_solution
 * @covers     ::elang_can_export_transcript
 */
final class navigation_test extends \advanced_testcase {
    /**
     * Build a course, an activity and a course module to navigate around.
     *
     * @param array $settings Extra fields for the activity record
     * @return array The course, the activity record and its cm_info
     */
    private function make_activity(array $settings = []): array {
        $course = $this->getDataGenerator()->create_course();
        $elang = $this->getDataGenerator()->create_module(
            'elang',
            array_merge(['course' => $course->id], $settings)
        );
        $cm = get_coursemodule_from_instance('elang', $elang->id, $course->id, false, MUST_EXIST);

        return [$course, $elang, $cm];
    }

    /**
     * Collect the keys of the nodes the activity contributes to the navigation.
     *
     * @param \stdClass $course The course
     * @param \stdClass $cm The course module record
     * @return array The keys of this activity's own navigation nodes
     */
    private function navigation_keys(\stdClass $course, \stdClass $cm): array {
        global $PAGE;

        $PAGE = new \moodle_page();
        $PAGE->set_url('/mod/elang/view.php', ['id' => $cm->id]);
        $PAGE->set_cm($cm, $course);
        $PAGE->set_pagelayout('incourse');

        $modulenode = $PAGE->settingsnav->find('modulesettings', \navigation_node::TYPE_SETTING);
        if (!$modulenode) {
            return [];
        }

        $keys = [];
        foreach ($modulenode->get_children_key_list() as $key) {
            if (strpos((string) $key, 'mod_elang_') === 0) {
                $keys[] = (string) $key;
            }
        }

        return $keys;
    }

    /**
     * An editing teacher gets the two authoring modes, reports and the export.
     *
     * @return void No return value.
     */
    public function test_editing_teacher_sees_every_mode(): void {
        $this->resetAfterTest();

        [$course, , $cm] = $this->make_activity();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $this->setUser($teacher);

        $keys = $this->navigation_keys($course, $cm);

        $this->assertContains('mod_elang_media', $keys);
        $this->assertContains('mod_elang_editcontent', $keys);
        $this->assertContains('mod_elang_reports', $keys);
        $this->assertContains('mod_elang_exporttranscript', $keys);
    }

    /**
     * A learner sees no authoring or reporting mode at all, and with the
     * activity's default settings no transcript export either — so the exercise
     * itself is the only mode they get.
     *
     * @return void No return value.
     */
    public function test_learner_sees_no_extra_modes_by_default(): void {
        $this->resetAfterTest();

        [$course, , $cm] = $this->make_activity();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->setUser($student);

        $this->assertSame([], $this->navigation_keys($course, $cm));
    }

    /**
     * Enabling the worksheet download is what puts the export within a
     * learner's reach, and nothing else appears with it.
     *
     * @return void No return value.
     */
    public function test_learner_sees_the_export_once_the_activity_offers_it(): void {
        $this->resetAfterTest();

        [$course, , $cm] = $this->make_activity(['allowtranscriptdownload' => 1]);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->setUser($student);

        $this->assertSame(['mod_elang_exporttranscript'], $this->navigation_keys($course, $cm));
    }

    /**
     * Reports follow their own capability, not the authoring one: a
     * non-editing teacher reads reports but authors nothing.
     *
     * @return void No return value.
     */
    public function test_non_editing_teacher_sees_reports_but_not_authoring(): void {
        $this->resetAfterTest();

        [$course, , $cm] = $this->make_activity();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $this->setUser($teacher);

        $keys = $this->navigation_keys($course, $cm);

        $this->assertContains('mod_elang_reports', $keys);
        $this->assertNotContains('mod_elang_media', $keys);
        $this->assertNotContains('mod_elang_editcontent', $keys);
    }

    /**
     * Removing the report capability removes the reports mode with it.
     *
     * @return void No return value.
     */
    public function test_prohibiting_viewreports_hides_the_reports_mode(): void {
        $this->resetAfterTest();

        [$course, , $cm] = $this->make_activity();
        $context = \context_module::instance($cm->id);
        $roleid = $this->getDataGenerator()->create_role();
        assign_capability('mod/elang:viewreports', CAP_PROHIBIT, $roleid, $context->id, true);

        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        role_assign($roleid, $teacher->id, $context->id);
        $this->setUser($teacher);

        $keys = $this->navigation_keys($course, $cm);

        $this->assertNotContains('mod_elang_reports', $keys);
        $this->assertContains('mod_elang_media', $keys);
    }

    /**
     * Staff may always take both export products, whatever the activity says.
     *
     * @return void No return value.
     */
    public function test_staff_export_access_ignores_the_activity_settings(): void {
        $this->resetAfterTest();

        [$course, $elang, $cm] = $this->make_activity([
            'allowtranscriptdownload' => 0,
            'solutionavailability' => 'never',
        ]);
        $context = \context_module::instance($cm->id);
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $this->setUser($teacher);

        $this->assertTrue(elang_can_export_worksheet($elang, $context));
        $this->assertTrue(elang_can_export_solution($elang, $context));
        $this->assertTrue(elang_can_export_transcript($elang, $context));
    }

    /**
     * A learner gets nothing from an activity that offers nothing, even though
     * they do hold mod/elang:exporttranscript.
     *
     * @return void No return value.
     */
    public function test_learner_export_access_is_closed_by_default(): void {
        $this->resetAfterTest();

        [$course, $elang, $cm] = $this->make_activity();
        $context = \context_module::instance($cm->id);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->setUser($student);

        $this->assertTrue(has_capability('mod/elang:exporttranscript', $context));
        $this->assertFalse(elang_can_export_worksheet($elang, $context));
        $this->assertFalse(elang_can_export_solution($elang, $context));
        $this->assertFalse(elang_can_export_transcript($elang, $context));
    }

    /**
     * The worksheet switch opens the worksheet only; it says nothing about the
     * solution.
     *
     * @return void No return value.
     */
    public function test_worksheet_switch_does_not_open_the_solution(): void {
        $this->resetAfterTest();

        [$course, $elang, $cm] = $this->make_activity(['allowtranscriptdownload' => 1]);
        $context = \context_module::instance($cm->id);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->setUser($student);

        $this->assertTrue(elang_can_export_worksheet($elang, $context));
        $this->assertFalse(elang_can_export_solution($elang, $context));
    }

    /**
     * With 'always' the solution is open to a learner from the start.
     *
     * @return void No return value.
     */
    public function test_solution_availability_always_opens_the_solution(): void {
        $this->resetAfterTest();

        [$course, $elang, $cm] = $this->make_activity(['solutionavailability' => 'always']);
        $context = \context_module::instance($cm->id);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->setUser($student);

        $this->assertTrue(elang_can_export_solution($elang, $context));
        $this->assertTrue(elang_can_export_transcript($elang, $context));
    }

    /**
     * With 'aftersubmission' the solution stays closed while an attempt is
     * still in progress and opens once it is finished — checked against the
     * learner's own attempts, not anybody else's.
     *
     * @return void No return value.
     */
    public function test_solution_availability_aftersubmission_waits_for_a_finished_attempt(): void {
        global $DB;

        $this->resetAfterTest();

        [$course, $elang, $cm] = $this->make_activity(['solutionavailability' => 'aftersubmission']);
        $context = \context_module::instance($cm->id);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $other = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $version = $generator->create_version(['elangid' => $elang->id, 'status' => 'published']);

        $this->setUser($student);
        $this->assertFalse(elang_can_export_solution($elang, $context));

        // Another learner finishing an attempt must not open anything for this
        // one.
        $DB->insert_record('elang_attempt', (object) [
            'elangid' => $elang->id,
            'versionid' => $version->id,
            'userid' => $other->id,
            'attemptnumber' => 1,
            'state' => 'finished',
            'timestart' => time(),
            'timemodified' => time(),
        ]);
        $this->assertFalse(elang_can_export_solution($elang, $context));

        // An unfinished attempt of their own is not a submission either.
        $attemptid = $DB->insert_record('elang_attempt', (object) [
            'elangid' => $elang->id,
            'versionid' => $version->id,
            'userid' => $student->id,
            'attemptnumber' => 1,
            'state' => 'inprogress',
            'timestart' => time(),
            'timemodified' => time(),
        ]);
        $this->assertFalse(elang_can_export_solution($elang, $context));

        $DB->set_field('elang_attempt', 'state', 'finished', ['id' => $attemptid]);
        $this->assertTrue(elang_can_export_solution($elang, $context));
    }

    /**
     * The activity's own modes are ordered ahead of the generic administrative
     * entries, and each keeps a whole-number position — a fractional one would
     * nest it under another node instead of placing it as a tab.
     *
     * @return void No return value.
     */
    public function test_secondary_navigation_orders_the_modes_first(): void {
        $this->resetAfterTest();

        [$course, , $cm] = $this->make_activity();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $this->setUser($teacher);

        $page = new \moodle_page();
        $page->set_url('/mod/elang/view.php', ['id' => $cm->id]);
        $page->set_cm($cm, $course);

        $view = new \mod_elang\navigation\views\secondary($page);
        $method = new \ReflectionMethod($view, 'get_default_module_mapping');
        $method->setAccessible(true);
        $mapping = $method->invoke($view)[\navigation_node::TYPE_SETTING];

        $positions = [
            'modedit' => 1,
            'mod_elang_media' => 2,
            'mod_elang_editcontent' => 3,
            'mod_elang_reports' => 4,
            'mod_elang_exporttranscript' => 5,
        ];
        foreach ($positions as $key => $position) {
            $this->assertSame($position, $mapping[$key]);
            $this->assertIsInt($mapping[$key]);
        }
    }
}
