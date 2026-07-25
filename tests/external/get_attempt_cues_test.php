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
 * Tests for the get_attempt_cues external function.
 *
 * Extends \advanced_testcase directly — see submit_response_test.php's
 * class docblock for why not \externallib_advanced_testcase.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\get_attempt_cues
 */
final class get_attempt_cues_test extends \advanced_testcase {
    /** @var \stdClass */
    private $cm;

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
        $result = start_attempt::execute($this->cm->id);
        $this->attemptid = $result['attemptid'];
    }

    /**
     * The solution word never appears in the returned transcript, and the
     * gap position is instead marked with a {{gap:<gapkey>}} token.
     *
     * @return void
     */
    public function test_transcript_is_masked_and_never_contains_the_solution(): void {
        $result = get_attempt_cues::execute($this->attemptid, 0, 1);
        $result = external_api::clean_returnvalue(get_attempt_cues::execute_returns(), $result);

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
        $result = get_attempt_cues::execute($this->attemptid, 0, 1);
        $result = external_api::clean_returnvalue(get_attempt_cues::execute_returns(), $result);

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
        $firstpage = get_attempt_cues::execute($this->attemptid, 0, 2);
        $this->assertCount(2, $firstpage['cues']);
        $this->assertSame(3, $firstpage['totalcues']);

        $secondpage = get_attempt_cues::execute($this->attemptid, 2, 2);
        $this->assertCount(1, $secondpage['cues']);
        $this->assertSame(3, $secondpage['totalcues']);

        $this->assertNotSame($firstpage['cues'][0]['id'], $secondpage['cues'][0]['id']);
    }

    /**
     * Each cue's gaps are attributed to the correct cue even when several
     * cues on the same page each have their own gap — the gaps for a page
     * are loaded in one batched query and grouped by cueid in PHP rather
     * than one query per cue, and grouping is exactly the kind of step that
     * could silently mix gaps up across cues if done wrong.
     *
     * @return void
     */
    public function test_gaps_are_attributed_to_the_correct_cue_across_a_page(): void {
        $result = get_attempt_cues::execute($this->attemptid, 0, 3);
        $result = external_api::clean_returnvalue(get_attempt_cues::execute_returns(), $result);

        $this->assertCount(3, $result['cues']);

        $gapkeysbycue = [];
        foreach ($result['cues'] as $cue) {
            $this->assertCount(1, $cue['gaps'], "cue {$cue['id']} should have exactly one gap");
            $gapkeysbycue[$cue['id']] = $cue['gaps'][0]['gapkey'];
        }

        // Three cues, three gaps, three distinct gapkeys — nothing merged or
        // duplicated across cues.
        $this->assertCount(3, array_unique($gapkeysbycue));
    }

    /**
     * The cues returned stay pinned to the attempt's version even after a
     * newer, structurally different version has been published — the read
     * side must never drift onto get_published() while an attempt is open.
     *
     * @return void
     */
    public function test_returns_the_pinned_version_after_a_newer_one_is_published(): void {
        // Publish a new version B with a single, different cue, versus
        // version A's three cues.
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draftb = $versionmanager->create_draft((int) $this->cm->instance, $this->student->id);
        $cueb = $generator->create_cue([
            'versionid' => $draftb->id,
            'sortorder' => 1,
            'transcript' => 'Un oiseau vole.',
        ]);
        $generator->create_gap([
            'cueid' => $cueb->id,
            'sortorder' => 1,
            'charstart' => 3,
            'charlength' => 6,
            'solution' => 'oiseau',
        ]);
        $versionmanager->publish($draftb->id, $this->student->id);

        $result = get_attempt_cues::execute($this->attemptid, 0, 10);
        $result = external_api::clean_returnvalue(get_attempt_cues::execute_returns(), $result);

        // Still version A: three cues, and the first cue is version A's
        // masked "Le chat dort.", not version B's single cue.
        $this->assertSame(3, $result['totalcues']);
        $this->assertCount(3, $result['cues']);
        $this->assertStringNotContainsString('oiseau', $result['cues'][0]['transcript']);
        $this->assertSame(
            'Le {{gap:' . $result['cues'][0]['gaps'][0]['gapkey'] . '}} dort.',
            $result['cues'][0]['transcript']
        );
    }

    /**
     * A learner cannot read another learner's attempt cues.
     *
     * @return void
     */
    public function test_rejects_another_users_attempt(): void {
        $this->setUser($this->otherstudent);

        $this->expectException(\moodle_exception::class);
        get_attempt_cues::execute($this->attemptid, 0, 10);
    }

    /**
     * A negative offset is rejected.
     *
     * @return void
     */
    public function test_rejects_a_negative_offset(): void {
        $this->expectException(\invalid_parameter_exception::class);
        get_attempt_cues::execute($this->attemptid, -1, 10);
    }

    /**
     * A limit of zero, or above the hard cap, is rejected.
     *
     * @return void
     */
    public function test_rejects_an_out_of_range_limit(): void {
        try {
            get_attempt_cues::execute($this->attemptid, 0, 0);
            $this->fail('Expected invalid_parameter_exception for limit=0');
        } catch (\invalid_parameter_exception $e) {
            $this->assertTrue(true);
        }

        $this->expectException(\invalid_parameter_exception::class);
        get_attempt_cues::execute($this->attemptid, 0, 500);
    }

    /**
     * A user without mod/elang:attempt cannot fetch cues.
     *
     * @return void
     */
    public function test_requires_capability(): void {
        global $DB;

        // The mod/elang:attempt capability is not the module's "uservisible"
        // capability (mod/elang:view is), so prohibiting it lets
        // validate_context()'s require_login() pass and the function's own
        // require_capability('mod/elang:attempt', ...) is what denies access.
        $context = \context_module::instance($this->cm->id);
        $studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
        assign_capability('mod/elang:attempt', CAP_PROHIBIT, $studentrole->id, $context->id, true);
        $context->mark_dirty();

        $this->expectException(\core\exception\required_capability_exception::class);
        get_attempt_cues::execute($this->attemptid, 0, 10);
    }
}
