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
use mod_elang\fixtures\attempt_test_fixture_builder;

/**
 * Tests for the finish_attempt external function.
 *
 * Extends \advanced_testcase directly rather than the legacy
 * \externallib_advanced_testcase — see submit_response_test.php's class
 * docblock for why.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\finish_attempt
 */
final class finish_attempt_test extends \advanced_testcase {
    /** @var \stdClass */
    private $gap;

    /** @var \stdClass */
    private $otherstudent;

    /** @var int */
    private $attemptid;

    public static function setUpBeforeClass(): void {
        require_once(__DIR__ . '/../fixtures/attempt_test_fixture.php');
        parent::setUpBeforeClass();
    }

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $fixture = attempt_test_fixture_builder::create($this);
        $this->gap = $fixture->gap;
        $this->otherstudent = $fixture->otherstudent;
        $this->attemptid = $fixture->attemptid;
    }

    /**
     * Finishing an in-progress attempt returns its final state.
     *
     * @return void
     */
    public function test_finishes_an_in_progress_attempt(): void {
        submit_response::execute($this->attemptid, $this->gap->id, 'chat');

        $result = finish_attempt::execute($this->attemptid);
        $result = external_api::clean_returnvalue(finish_attempt::execute_returns(), $result);

        $this->assertSame('finished', $result['state']);
        $this->assertSame(1, $result['correctgaps']);
        $this->assertSame(1, $result['totalgaps']);
        $this->assertGreaterThan(0, $result['timefinish']);
    }

    /**
     * Finishing an already-finished attempt is idempotent: it returns the
     * same finished state rather than throwing, so a caller retrying a
     * request it never learned the outcome of succeeds instead of seeing a
     * spurious error.
     *
     * @return void
     */
    public function test_finishing_an_already_finished_attempt_is_idempotent(): void {
        $first = finish_attempt::execute($this->attemptid);
        $second = finish_attempt::execute($this->attemptid);

        $this->assertSame('finished', $second['state']);
        $this->assertSame($first['timefinish'], $second['timefinish']);
    }

    /**
     * A learner cannot finish another learner's attempt.
     *
     * @return void
     */
    public function test_rejects_finishing_another_users_attempt(): void {
        $this->setUser($this->otherstudent);

        $this->expectException(\moodle_exception::class);
        finish_attempt::execute($this->attemptid);
    }

    /**
     * Finishing an attempt marks the activity complete for the learner when
     * automatic completion with the completionfinishattempt rule is on, so the
     * course page reflects it immediately.
     *
     * @return void
     */
    public function test_finishing_updates_activity_completion(): void {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $elang = $this->getDataGenerator()->create_module('elang', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionfinishattempt' => 1,
        ]);
        $cm = get_coursemodule_from_instance('elang', $elang->id);

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draft = $versionmanager->create_draft($elang->id, $student->id);
        $cue = $generator->create_cue(['versionid' => $draft->id]);
        $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);
        $versionmanager->publish($draft->id, $student->id);

        $this->setUser($student);
        $started = start_attempt::execute($cm->id);
        finish_attempt::execute($started['attemptid']);

        $completion = new \completion_info($course);
        $data = $completion->get_data($cm, false, $student->id);
        $this->assertEquals(COMPLETION_COMPLETE, $data->completionstate);
    }
}
