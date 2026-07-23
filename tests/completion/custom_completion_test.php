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

namespace mod_elang\completion;

/**
 * Tests for mod_elang's custom completion rule.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\completion\custom_completion
 */
final class custom_completion_test extends \advanced_testcase {
    /** @var \stdClass */
    private $elang;

    /** @var \stdClass */
    private $student;

    /** @var \cm_info */
    private $cminfo;

    /** @var int */
    private $versionid;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        // The core method validate_rule() (core_completion\activity_custom_completion)
        // checks two things: that the rule is defined by the plugin at all
        // (get_defined_custom_rules()), and separately that it is actually
        // enabled/in use for THIS specific course module instance. The
        // second check needs completion tracking on, and the rule's own
        // field set, at creation time — matching how Moodle core's own
        // completion tests configure a module (see completion/tests/
        // bulk_update_test.php: 'completion' => COMPLETION_TRACKING_
        // AUTOMATIC alongside the unsuffixed rule field name; the suffix
        // used in mod_form.php's add_completion_rules() is a form-rendering
        // concern only, stripped before instance data is processed).
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $this->student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->elang = $this->getDataGenerator()->create_module('elang', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionfinishattempt' => 1,
        ]);

        $modinfo = get_fast_modinfo($course->id);
        $this->cminfo = $modinfo->get_cm($this->elang->cmid);

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $version = $versionmanager->create_draft($this->elang->id, $this->student->id);
        $this->versionid = (int) $version->id;
    }

    /**
     * The module defines exactly the one completionfinishattempt rule.
     *
     * @return void
     */
    public function test_defines_completionfinishattempt(): void {
        $this->assertSame(['completionfinishattempt'], custom_completion::get_defined_custom_rules());
    }

    /**
     * A user with no attempt at all has not met the rule.
     *
     * @return void
     */
    public function test_state_is_incomplete_without_any_attempt(): void {
        $completion = new custom_completion($this->cminfo, (int) $this->student->id);

        $this->assertSame(COMPLETION_INCOMPLETE, $completion->get_state('completionfinishattempt'));
    }

    /**
     * An in-progress attempt does not satisfy the rule — it must be finished.
     *
     * @return void
     */
    public function test_state_is_incomplete_with_only_an_in_progress_attempt(): void {
        global $DB;

        $attempt = (object) [
            'elangid' => $this->elang->id,
            'versionid' => $this->versionid,
            'userid' => $this->student->id,
            'attemptnumber' => 1,
            'state' => \mod_elang\local\domain\attempt_manager::STATE_INPROGRESS,
            'totalgaps' => 1,
            'timestart' => time(),
            'timemodified' => time(),
        ];
        $DB->insert_record('elang_attempt', $attempt);

        $completion = new custom_completion($this->cminfo, (int) $this->student->id);

        $this->assertSame(COMPLETION_INCOMPLETE, $completion->get_state('completionfinishattempt'));
    }

    /**
     * A finished attempt satisfies the rule.
     *
     * @return void
     */
    public function test_state_is_complete_with_a_finished_attempt(): void {
        global $DB;

        $attempt = (object) [
            'elangid' => $this->elang->id,
            'versionid' => $this->versionid,
            'userid' => $this->student->id,
            'attemptnumber' => 1,
            'state' => \mod_elang\local\domain\attempt_manager::STATE_FINISHED,
            'totalgaps' => 1,
            'timestart' => time(),
            'timefinish' => time(),
            'timemodified' => time(),
        ];
        $DB->insert_record('elang_attempt', $attempt);

        $completion = new custom_completion($this->cminfo, (int) $this->student->id);

        $this->assertSame(COMPLETION_COMPLETE, $completion->get_state('completionfinishattempt'));
    }

    /**
     * An undefined rule is rejected rather than silently treated as incomplete.
     *
     * validate_rule() throws coding_exception specifically for a rule this
     * class never defined at all (confirmed against Moodle core source —
     * a different branch, moodle_exception, covers a rule that is defined
     * but not enabled for this particular activity instance).
     *
     * @return void
     */
    public function test_undefined_rule_is_rejected(): void {
        $completion = new custom_completion($this->cminfo, (int) $this->student->id);

        $this->expectException(\coding_exception::class);
        $completion->get_state('completionsomethingelse');
    }

    /**
     * A human-readable description is provided for the rule.
     *
     * @return void
     */
    public function test_provides_a_rule_description(): void {
        $completion = new custom_completion($this->cminfo, (int) $this->student->id);

        $descriptions = $completion->get_custom_rule_descriptions();

        $this->assertArrayHasKey('completionfinishattempt', $descriptions);
        $this->assertNotSame('', $descriptions['completionfinishattempt']);
    }

    /**
     * The custom rule appears in the display sort order alongside the core rules.
     *
     * @return void
     */
    public function test_sort_order_includes_the_custom_rule(): void {
        $completion = new custom_completion($this->cminfo, (int) $this->student->id);

        $this->assertContains('completionfinishattempt', $completion->get_sort_order());
    }
}
