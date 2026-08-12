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

use mod_elang\fixtures\authoring_test_fixture_builder;

/**
 * Trust-boundary tests for save_draft_version: the capability gate on regex
 * answer variants and the domain-invariant validation of an incoming cue list.
 *
 * The React editor normally sends well-formed content, but an external function
 * must defend itself against a hand-crafted request, so these tests drive the
 * function directly with payloads the UI would never produce.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\save_draft_version
 * @covers     \mod_elang\external\authoring_helper
 * @covers     \mod_elang\local\domain\version_manager
 */
final class save_draft_version_security_test extends \advanced_testcase {
    /** @var \stdClass */
    private $teacher;

    /** @var \stdClass */
    private $elang;

    /** @var \stdClass */
    private $draft;

    /** @var \stdClass */
    private $course;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        require_once(__DIR__ . '/../fixtures/authoring_test_fixture.php');
        $fixture = authoring_test_fixture_builder::create($this);
        $this->teacher = $fixture->teacher;
        $this->elang = $fixture->elang;
        $this->draft = $fixture->draft;
        $this->course = get_course($this->elang->course);
    }

    /**
     * Start from the shared one-cue, one-gap payload and apply a mutation to it,
     * so each test states only what it changes from a valid baseline.
     *
     * @param callable $mutate Receives the payload by reference to alter it
     * @return array The mutated cue list
     */
    private function mutated_payload(callable $mutate): array {
        $payload = authoring_test_fixture_builder::payload('chat');
        $mutate($payload);
        return $payload;
    }

    /**
     * An editing teacher holds mod/elang:manage but not mod/elang:useregex, so a
     * save that stores a regex answer variant must be refused.
     *
     * @return void
     */
    public function test_editingteacher_cannot_save_a_regex_variant(): void {
        $this->setUser($this->teacher);

        $payload = $this->mutated_payload(function (array &$payload): void {
            $payload[0]['gaps'][0]['answers'][] = ['sortorder' => 2, 'answer' => 'ch.t', 'isregex' => 1];
        });

        $this->expectException(\required_capability_exception::class);
        save_draft_version::execute((int) $this->draft->id, -1, $payload);
    }

    /**
     * A manager holds both mod/elang:manage and mod/elang:useregex, so the same
     * regex variant is stored.
     *
     * @return void
     */
    public function test_manager_can_save_a_regex_variant(): void {
        global $DB;

        $manager = $this->getDataGenerator()->create_and_enrol($this->course, 'manager');
        $this->setUser($manager);

        $payload = $this->mutated_payload(function (array &$payload): void {
            $payload[0]['gaps'][0]['answers'][] = ['sortorder' => 2, 'answer' => 'ch.t', 'isregex' => 1];
        });

        save_draft_version::execute((int) $this->draft->id, -1, $payload);

        $cue = $DB->get_record('elang_cue', ['versionid' => $this->draft->id], '*', MUST_EXIST);
        $gap = $DB->get_record('elang_gap', ['cueid' => $cue->id], '*', MUST_EXIST);
        $this->assertSame(1, (int) $DB->count_records('elang_gapanswer', ['gapid' => $gap->id, 'isregex' => 1]));
    }

    /**
     * A hint penalty below 0 (which would push a response score above 1) is
     * rejected before anything is written.
     *
     * @return void
     */
    public function test_a_negative_penalty_is_rejected(): void {
        $this->setUser($this->teacher);

        $payload = $this->mutated_payload(function (array &$payload): void {
            $payload[0]['gaps'][0]['hints'][0]['penalty'] = -0.5;
        });

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessageMatches('/between 0 and 1/');
        save_draft_version::execute((int) $this->draft->id, -1, $payload);
    }

    /**
     * A hint penalty above 1 is rejected too.
     *
     * @return void
     */
    public function test_a_penalty_above_one_is_rejected(): void {
        $this->setUser($this->teacher);

        $payload = $this->mutated_payload(function (array &$payload): void {
            $payload[0]['gaps'][0]['hints'][0]['penalty'] = 1.5;
        });

        $this->expectException(\moodle_exception::class);
        save_draft_version::execute((int) $this->draft->id, -1, $payload);
    }

    /**
     * An isregex flag other than 0 or 1 is rejected.
     *
     * @return void
     */
    public function test_an_out_of_range_isregex_flag_is_rejected(): void {
        $this->setUser($this->teacher);

        $payload = $this->mutated_payload(function (array &$payload): void {
            $payload[0]['gaps'][0]['answers'][0]['isregex'] = 2;
        });

        $this->expectException(\moodle_exception::class);
        save_draft_version::execute((int) $this->draft->id, -1, $payload);
    }

    /**
     * A regex variant whose pattern does not compile is rejected, rather than
     * being stored and then silently never matching at grade time.
     *
     * @return void
     */
    public function test_an_uncompilable_regex_variant_is_rejected(): void {
        $manager = $this->getDataGenerator()->create_and_enrol($this->course, 'manager');
        $this->setUser($manager);

        $payload = $this->mutated_payload(function (array &$payload): void {
            $payload[0]['gaps'][0]['answers'][] = ['sortorder' => 2, 'answer' => '(unclosed', 'isregex' => 1];
        });

        $this->expectException(\moodle_exception::class);
        save_draft_version::execute((int) $this->draft->id, -1, $payload);
    }

    /**
     * A grading algorithm outside the two named algorithms is rejected.
     *
     * @return void
     */
    public function test_an_unknown_grading_algorithm_is_rejected(): void {
        $this->setUser($this->teacher);

        $payload = $this->mutated_payload(function (array &$payload): void {
            $payload[0]['gaps'][0]['gradingalgorithm'] = 'fuzzy';
        });

        $this->expectException(\moodle_exception::class);
        save_draft_version::execute((int) $this->draft->id, -1, $payload);
    }

    /**
     * A hint type outside the allowed set is rejected.
     *
     * @return void
     */
    public function test_an_unknown_hint_type_is_rejected(): void {
        $this->setUser($this->teacher);

        $payload = $this->mutated_payload(function (array &$payload): void {
            $payload[0]['gaps'][0]['hints'][0]['hinttype'] = 'blink';
        });

        $this->expectException(\moodle_exception::class);
        save_draft_version::execute((int) $this->draft->id, -1, $payload);
    }

    /**
     * Two cues sharing a key would violate the versionid-cuekey unique index; it
     * is rejected with a clear message rather than a raw database write error.
     *
     * @return void
     */
    public function test_a_duplicate_cue_key_is_rejected(): void {
        $this->setUser($this->teacher);

        $payload = $this->mutated_payload(function (array &$payload): void {
            // A second cue reusing the first cue's key.
            $payload[] = $payload[0];
        });

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessageMatches('/cues share the key/');
        save_draft_version::execute((int) $this->draft->id, -1, $payload);
    }

    /**
     * Two gaps in one cue sharing a key would violate the cueid-gapkey unique
     * index; it is rejected with a clear message.
     *
     * @return void
     */
    public function test_a_duplicate_gap_key_is_rejected(): void {
        $this->setUser($this->teacher);

        $payload = $this->mutated_payload(function (array &$payload): void {
            $payload[0]['gaps'][] = $payload[0]['gaps'][0];
        });

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessageMatches('/gaps in one cue share/');
        save_draft_version::execute((int) $this->draft->id, -1, $payload);
    }

    /**
     * Two hints of one gap sharing a level would violate the gapid-level unique
     * index; it is rejected with a clear message.
     *
     * @return void
     */
    public function test_a_duplicate_hint_level_is_rejected(): void {
        $this->setUser($this->teacher);

        $payload = $this->mutated_payload(function (array &$payload): void {
            $level = $payload[0]['gaps'][0]['hints'][0]['level'];
            $payload[0]['gaps'][0]['hints'][] = [
                'level' => $level,
                'hinttype' => 'text',
                'hinttext' => 'duplicate',
                'penalty' => 0,
            ];
        });

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessageMatches('/hints at level/');
        save_draft_version::execute((int) $this->draft->id, -1, $payload);
    }

    /**
     * A negative gap offset or length is structurally impossible and rejected
     * before anything is written.
     *
     * @return void
     */
    public function test_a_negative_gap_offset_is_rejected(): void {
        $this->setUser($this->teacher);

        $payload = $this->mutated_payload(function (array &$payload): void {
            $payload[0]['gaps'][0]['charstart'] = -1;
        });

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessageMatches('/must not be negative/');
        save_draft_version::execute((int) $this->draft->id, -1, $payload);
    }
}
