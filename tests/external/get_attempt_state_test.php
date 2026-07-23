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
 * Tests for the get_attempt_state external function.
 *
 * Extends \advanced_testcase directly — see submit_response_test.php's
 * class docblock for why not \externallib_advanced_testcase. Uses
 * attempt_test_fixture_builder the same way submit_response_test and
 * finish_attempt_test do, via require_once() in setUpBeforeClass() — see
 * that fixture's class docblock for why it is a plain class rather than a
 * trait.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\get_attempt_state
 */
final class get_attempt_state_test extends \advanced_testcase {
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
     * Aggregates and the submitted response are reflected correctly, and
     * the learner's own typed text is returned for resuming the attempt.
     *
     * @return void
     */
    public function test_returns_aggregates_and_the_submitted_response(): void {
        submit_response::execute($this->attemptid, $this->gap->id, 'chat');

        $result = get_attempt_state::execute($this->attemptid);
        $result = external_api::clean_returnvalue(get_attempt_state::execute_returns(), $result);

        $this->assertSame('inprogress', $result['state']);
        $this->assertSame(1, $result['answeredgaps']);
        $this->assertSame(1, $result['correctgaps']);
        $this->assertCount(1, $result['responses']);
        $this->assertSame((int) $this->gap->id, $result['responses'][0]['gapid']);
        $this->assertSame('chat', $result['responses'][0]['responsetext']);
        $this->assertSame('exact', $result['responses'][0]['resultstate']);
    }

    /**
     * An attempt with no responses yet still returns a valid, empty-responses state.
     *
     * @return void
     */
    public function test_returns_empty_responses_before_anything_is_submitted(): void {
        $result = get_attempt_state::execute($this->attemptid);
        $result = external_api::clean_returnvalue(get_attempt_state::execute_returns(), $result);

        $this->assertSame(0, $result['answeredgaps']);
        $this->assertCount(0, $result['responses']);
    }

    /**
     * A learner cannot fetch another learner's attempt state.
     *
     * @return void
     */
    public function test_rejects_another_users_attempt(): void {
        $this->setUser($this->otherstudent);

        $this->expectException(\moodle_exception::class);
        get_attempt_state::execute($this->attemptid);
    }
}
