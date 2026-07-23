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
 * Tests for the get_cues external function.
 *
 * Extends \advanced_testcase directly — see submit_response_test.php's
 * class docblock for why not \externallib_advanced_testcase.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\get_cues
 */
final class get_cues_test extends \advanced_testcase {
    /** @var \stdClass */
    private $cm;

    /** @var \stdClass */
    private $student;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $this->student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id, 'language' => 'fr']);
        $this->cm = get_coursemodule_from_instance('elang', $elang->id);

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draft = $versionmanager->create_draft($elang->id, $this->student->id);

        // Transcript "Le chat dort." — chat at codepoint 3, length 4 (verified with mb_strpos()).
        $cueone = $generator->create_cue([
            'versionid' => $draft->id,
            'sortorder' => 1,
            'transcript' => 'Le chat dort.',
        ]);
        $generator->create_gap([
            'cueid' => $cueone->id,
            'sortorder' => 1,
            'charstart' => 3,
            'charlength' => 4,
            'solution' => 'chat',
            'linkurl' => 'https://example.org/chat',
        ]);

        $cuetwo = $generator->create_cue(['versionid' => $draft->id, 'sortorder' => 2]);
        $generator->create_gap(['cueid' => $cuetwo->id, 'solution' => 'chien']);

        $cuethree = $generator->create_cue(['versionid' => $draft->id, 'sortorder' => 3]);
        $generator->create_gap(['cueid' => $cuethree->id, 'solution' => 'oiseau']);

        $versionmanager->publish($draft->id, $this->student->id);

        $this->setUser($this->student);
    }

    /**
     * The solution word never appears in the returned transcript, and the
     * gap position is instead marked with a {{gap:<gapkey>}} token.
     *
     * @return void
     */
    public function test_transcript_is_masked_and_never_contains_the_solution(): void {
        $result = get_cues::execute($this->cm->id, 0, 1);
        $result = external_api::clean_returnvalue(get_cues::execute_returns(), $result);

        $cue = $result['cues'][0];
        $this->assertStringNotContainsString('chat', $cue['transcript']);
        $this->assertStringContainsString('{{gap:', $cue['transcript']);
        $this->assertSame('Le {{gap:' . $cue['gaps'][0]['gapkey'] . '}} dort.', $cue['transcript']);
    }

    /**
     * Gaps never expose charstart/charlength, which would leak the
     * solution's character length as an unrequested hint.
     *
     * @return void
     */
    public function test_gaps_do_not_expose_character_positions(): void {
        $result = get_cues::execute($this->cm->id, 0, 1);
        $result = external_api::clean_returnvalue(get_cues::execute_returns(), $result);

        $gap = $result['cues'][0]['gaps'][0];
        $this->assertArrayNotHasKey('charstart', $gap);
        $this->assertArrayNotHasKey('charlength', $gap);
        $this->assertSame('https://example.org/chat', $gap['linkurl']);
    }

    /**
     * offset/limit correctly page through the cues, and totalcues always
     * reflects the full count regardless of the page requested.
     *
     * @return void
     */
    public function test_pagination_returns_the_correct_page(): void {
        $firstpage = get_cues::execute($this->cm->id, 0, 2);
        $this->assertCount(2, $firstpage['cues']);
        $this->assertSame(3, $firstpage['totalcues']);

        $secondpage = get_cues::execute($this->cm->id, 2, 2);
        $this->assertCount(1, $secondpage['cues']);
        $this->assertSame(3, $secondpage['totalcues']);

        $this->assertNotSame($firstpage['cues'][0]['id'], $secondpage['cues'][0]['id']);
    }

    /**
     * A negative offset is rejected.
     *
     * @return void
     */
    public function test_rejects_a_negative_offset(): void {
        $this->expectException(\invalid_parameter_exception::class);
        get_cues::execute($this->cm->id, -1, 10);
    }

    /**
     * A limit of zero, or above the hard cap, is rejected.
     *
     * @return void
     */
    public function test_rejects_an_out_of_range_limit(): void {
        try {
            get_cues::execute($this->cm->id, 0, 0);
            $this->fail('Expected invalid_parameter_exception for limit=0');
        } catch (\invalid_parameter_exception $e) {
            $this->assertTrue(true);
        }

        $this->expectException(\invalid_parameter_exception::class);
        get_cues::execute($this->cm->id, 0, 500);
    }

    /**
     * A user without mod/elang:view cannot fetch cues.
     *
     * @return void
     */
    public function test_requires_capability(): void {
        global $DB;

        $context = \context_module::instance($this->cm->id);
        $studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
        assign_capability('mod/elang:view', CAP_PROHIBIT, $studentrole->id, $context->id, true);
        $context->mark_dirty();

        // See get_exercise_test::test_requires_capability() for why this is
        // require_login_exception, not required_capability_exception:
        // mod/elang:view gates require_login()'s own "uservisible" check
        // inside validate_context(), which denies access before this
        // function's explicit require_capability() call is ever reached.
        $this->expectException(\core\exception\require_login_exception::class);
        get_cues::execute($this->cm->id);
    }
}
