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
 * Tests for the request_hint external function.
 *
 * Extends \advanced_testcase directly — see submit_response_test.php's
 * class docblock for why not \externallib_advanced_testcase.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\request_hint
 */
final class request_hint_test extends \advanced_testcase {
    /** @var \stdClass */
    private $gap;

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
        $cm = get_coursemodule_from_instance('elang', $elang->id);

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draft = $versionmanager->create_draft($elang->id, $this->student->id);
        $cue = $generator->create_cue(['versionid' => $draft->id]);
        $this->gap = $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);
        $generator->create_gaphint([
            'gapid' => $this->gap->id,
            'level' => 1,
            'hinttype' => 'firstletter',
            'hinttext' => 'c',
            'penalty' => 0.1,
        ]);
        $versionmanager->publish($draft->id, $this->student->id);

        $this->setUser($this->student);
        $result = start_attempt::execute($cm->id);
        $this->attemptid = $result['attemptid'];
    }

    /**
     * Requesting a hint returns its content and the updated attempt state.
     *
     * @return void
     */
    public function test_reveals_the_first_hint_level(): void {
        $result = request_hint::execute($this->attemptid, $this->gap->id);
        $result = external_api::clean_returnvalue(request_hint::execute_returns(), $result);

        $this->assertSame(1, $result['level']);
        $this->assertSame('firstletter', $result['hinttype']);
        $this->assertSame('c', $result['hinttext']);
        $this->assertEqualsWithDelta(0.1, $result['penalty'], 0.00001);
        $this->assertSame(1, $result['hintedgaps']);
    }

    /**
     * A correct answer submitted after a hint reflects the penalty in the
     * returned score.
     *
     * @return void
     */
    public function test_score_reflects_the_hint_penalty_after_a_correct_answer(): void {
        request_hint::execute($this->attemptid, $this->gap->id);

        $result = submit_response::execute($this->attemptid, $this->gap->id, 'chat');

        $this->assertEqualsWithDelta(0.9, $result['score'], 0.00001);
    }

    /**
     * Requesting a hint beyond the last defined level is rejected with a
     * clear error rather than a raw domain-layer exception.
     *
     * @return void
     */
    public function test_rejects_a_request_beyond_the_last_level(): void {
        request_hint::execute($this->attemptid, $this->gap->id);

        $this->expectException(\moodle_exception::class);
        request_hint::execute($this->attemptid, $this->gap->id);
    }

    /**
     * A learner cannot request a hint for another learner's attempt.
     *
     * @return void
     */
    public function test_rejects_another_users_attempt(): void {
        $this->setUser($this->otherstudent);

        $this->expectException(\moodle_exception::class);
        request_hint::execute($this->attemptid, $this->gap->id);
    }

    /**
     * A gap that does not belong to the attempted version is rejected.
     *
     * @return void
     */
    public function test_rejects_a_gap_from_a_different_version(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $attemptrecord = $this->get_attempt_record();
        $unrelatedversion = $generator->create_version(['elangid' => $attemptrecord->elangid]);
        $unrelatedcue = $generator->create_cue(['versionid' => $unrelatedversion->id]);
        $unrelatedgap = $generator->create_gap(['cueid' => $unrelatedcue->id, 'solution' => 'x']);

        $this->expectException(\moodle_exception::class);
        request_hint::execute($this->attemptid, $unrelatedgap->id);
    }

    /**
     * A hint cannot be requested once the attempt has been finished.
     *
     * @return void
     */
    public function test_rejects_a_finished_attempt(): void {
        finish_attempt::execute($this->attemptid);

        $this->expectException(\moodle_exception::class);
        request_hint::execute($this->attemptid, $this->gap->id);
    }

    /**
     * Fetch the raw elang_attempt record backing $this->attemptid.
     *
     * @return \stdClass
     */
    private function get_attempt_record(): \stdClass {
        global $DB;

        return $DB->get_record('elang_attempt', ['id' => $this->attemptid], '*', MUST_EXIST);
    }
}
