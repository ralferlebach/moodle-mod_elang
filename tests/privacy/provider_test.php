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

namespace mod_elang\privacy;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Tests for the mod_elang privacy provider.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\privacy\provider
 */
final class provider_test extends \core_privacy\tests\provider_testcase {
    /** @var \stdClass */
    private $cm;

    /** @var \stdClass */
    private $context;

    /** @var \stdClass */
    private $student;

    /** @var \stdClass */
    private $otherstudent;

    /** @var int */
    private $attemptid;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $this->student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->otherstudent = $this->getDataGenerator()->create_and_enrol($course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id, 'language' => 'fr']);
        $this->cm = get_coursemodule_from_instance('elang', $elang->id);
        $this->context = \context_module::instance($this->cm->id);

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draft = $versionmanager->create_draft($elang->id, $this->student->id);
        $cue = $generator->create_cue(['versionid' => $draft->id]);
        $gap = $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);
        $versionmanager->publish($draft->id, $this->student->id);

        $attemptmanager = new \mod_elang\local\domain\attempt_manager(
            new \mod_elang\local\grading\answer_evaluator(new \mod_elang\local\grading\script_handler_manager([]))
        );
        $attempt = $attemptmanager->start_attempt($elang->id, $this->student->id, $draft->id);
        $attemptmanager->submit_response($attempt->id, $gap->id, 'chat');
        $this->attemptid = $attempt->id;
    }

    /**
     * A user with an attempt has the activity context in their contextlist.
     *
     * @return void
     */
    public function test_get_contexts_for_userid_finds_the_activity_context(): void {
        $contextlist = provider::get_contexts_for_userid($this->student->id);

        // PHPUnit's assertContains() has used strict (===) comparison since
        // PHPUnit 9 (see sebastianbergmann/phpunit#3426);
        // contextlist::get_contextids() is backed by a raw SQL query whose
        // results can come back as strings on MariaDB/PDO even for an
        // integer column, so both sides are cast to int here rather than
        // relying on type coercion.
        $this->assertContains((int) $this->context->id, array_map('intval', $contextlist->get_contextids()));
    }

    /**
     * A user without any attempt has no contexts.
     *
     * @return void
     */
    public function test_get_contexts_for_userid_is_empty_for_a_user_without_attempts(): void {
        $contextlist = provider::get_contexts_for_userid($this->otherstudent->id);

        $this->assertEmpty($contextlist->get_contextids());
    }

    /**
     * Exporting data for the student includes their attempt and response.
     *
     * @return void
     */
    public function test_export_user_data_includes_the_attempt_and_response(): void {
        $approvedlist = new approved_contextlist($this->student, 'mod_elang', [$this->context->id]);
        provider::export_user_data($approvedlist);

        $writer = writer::with_context($this->context);
        $this->assertTrue($writer->has_any_data());

        $data = $writer->get_data([get_string('pluginname', 'mod_elang')]);
        $this->assertCount(1, $data->attempts);
        $this->assertCount(1, $data->attempts[0]->responses);
        $this->assertSame('chat', $data->attempts[0]->responses[0]->responsetext);
    }

    /**
     * The activity context lists exactly the users who have attempted it.
     *
     * @return void
     */
    public function test_get_users_in_context_finds_the_attempting_user(): void {
        $userlist = new userlist($this->context, 'mod_elang');
        provider::get_users_in_context($userlist);

        $this->assertEqualsCanonicalizing([(int) $this->student->id], $userlist->get_userids());
    }

    /**
     * Deleting all data in a context removes every attempt and response.
     *
     * @return void
     */
    public function test_delete_data_for_all_users_in_context_removes_everything(): void {
        global $DB;

        provider::delete_data_for_all_users_in_context($this->context);

        $this->assertSame(0, $DB->count_records('elang_attempt', ['id' => $this->attemptid]));
        $this->assertSame(0, $DB->count_records('elang_response', ['attemptid' => $this->attemptid]));
    }

    /**
     * Deleting data for one user does not touch another user's data in the
     * same context.
     *
     * @return void
     */
    public function test_delete_data_for_user_only_removes_that_users_data(): void {
        global $DB;

        $attemptmanager = new \mod_elang\local\domain\attempt_manager(
            new \mod_elang\local\grading\answer_evaluator(new \mod_elang\local\grading\script_handler_manager([]))
        );
        $currentversionid = $DB->get_field('elang', 'currentversionid', ['id' => $this->cm->instance], MUST_EXIST);
        $otherattempt = $attemptmanager->start_attempt($this->cm->instance, $this->otherstudent->id, $currentversionid);

        $approvedlist = new approved_contextlist($this->student, 'mod_elang', [$this->context->id]);
        provider::delete_data_for_user($approvedlist);

        $this->assertSame(0, $DB->count_records('elang_attempt', ['id' => $this->attemptid]));
        $this->assertSame(1, $DB->count_records('elang_attempt', ['id' => $otherattempt->id]));
    }

    /**
     * Deleting data for an approved userlist removes only the listed users.
     *
     * @return void
     */
    public function test_delete_data_for_users_removes_only_listed_users(): void {
        global $DB;

        $attemptmanager = new \mod_elang\local\domain\attempt_manager(
            new \mod_elang\local\grading\answer_evaluator(new \mod_elang\local\grading\script_handler_manager([]))
        );
        $otherattempt = $attemptmanager->start_attempt(
            $this->cm->instance,
            $this->otherstudent->id,
            $DB->get_field('elang', 'currentversionid', ['id' => $this->cm->instance])
        );

        $approvedlist = new approved_userlist($this->context, 'mod_elang', [$this->student->id]);
        provider::delete_data_for_users($approvedlist);

        $this->assertSame(0, $DB->count_records('elang_attempt', ['id' => $this->attemptid]));
        $this->assertSame(1, $DB->count_records('elang_attempt', ['id' => $otherattempt->id]));
    }
}
