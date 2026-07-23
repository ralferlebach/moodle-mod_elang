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
 * Tests for the submit_response external function.
 *
 * Extends \advanced_testcase directly rather than the legacy
 * \externallib_advanced_testcase: this test calls execute()/execute_returns()
 * directly instead of going through the full webservice REST dispatch, so it
 * needs nothing \externallib_advanced_testcase adds over the base class —
 * and \externallib_advanced_testcase itself needs an explicit
 * require_once($CFG->dirroot . '/webservice/tests/helpers.php') to even be
 * defined (confirmed against a real Moodle 4.5 test run: "Class
 * externallib_advanced_testcase not found" without it), is scheduled for
 * deprecation from Moodle 4.6 onwards, and recommends running in an isolated
 * process to avoid its class_alias() calls leaking into other tests. None of
 * that is worth taking on for functionality this test doesn't use.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\submit_response
 */
final class submit_response_test extends \advanced_testcase {
    /** @var \stdClass */
    private $cm;

    /** @var \stdClass */
    private $gap;

    /** @var \stdClass */
    private $student;

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
        $this->cm = $fixture->cm;
        $this->gap = $fixture->gap;
        $this->student = $fixture->student;
        $this->otherstudent = $fixture->otherstudent;
        $this->attemptid = $fixture->attemptid;
    }

    /**
     * An exact response is accepted and the returned aggregates reflect it.
     *
     * @return void
     */
    public function test_exact_response_is_accepted(): void {
        $result = submit_response::execute($this->attemptid, $this->gap->id, 'chat');
        $result = external_api::clean_returnvalue(submit_response::execute_returns(), $result);

        $this->assertSame('exact', $result['resultstate']);
        $this->assertTrue($result['accepted']);
        $this->assertSame(1, $result['answeredgaps']);
        $this->assertSame(1, $result['correctgaps']);
    }

    /**
     * Resubmitting a response updates the same gap rather than duplicating it.
     *
     * @return void
     */
    public function test_resubmission_updates_the_response(): void {
        submit_response::execute($this->attemptid, $this->gap->id, 'chien');
        $result = submit_response::execute($this->attemptid, $this->gap->id, 'chat');

        $this->assertSame('exact', $result['resultstate']);
        $this->assertSame(1, $result['correctgaps']);
    }

    /**
     * A learner cannot submit a response to another learner's attempt.
     *
     * @return void
     */
    public function test_rejects_a_response_to_another_users_attempt(): void {
        $this->setUser($this->otherstudent);

        $this->expectException(\moodle_exception::class);
        submit_response::execute($this->attemptid, $this->gap->id, 'chat');
    }

    /**
     * A gap that does not belong to the attempted version is rejected.
     *
     * @return void
     */
    public function test_rejects_a_gap_from_a_different_version(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $unrelatedversion = $generator->create_version(['elangid' => $this->cm->instance]);
        $unrelatedcue = $generator->create_cue(['versionid' => $unrelatedversion->id]);
        $unrelatedgap = $generator->create_gap(['cueid' => $unrelatedcue->id, 'solution' => 'x']);

        $this->expectException(\moodle_exception::class);
        submit_response::execute($this->attemptid, $unrelatedgap->id, 'x');
    }

    /**
     * A response cannot be submitted once the attempt has been finished.
     *
     * @return void
     */
    public function test_rejects_a_response_to_a_finished_attempt(): void {
        finish_attempt::execute($this->attemptid);

        $this->expectException(\moodle_exception::class);
        submit_response::execute($this->attemptid, $this->gap->id, 'chat');
    }

    /**
     * A response longer than the hard safety cap is rejected before it
     * reaches the grading engine.
     *
     * @return void
     */
    public function test_rejects_an_excessively_long_response(): void {
        $toolong = str_repeat('a', 501);

        $this->expectException(\invalid_parameter_exception::class);
        submit_response::execute($this->attemptid, $this->gap->id, $toolong);
    }
}
