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

namespace mod_elang\local\domain;

use mod_elang\local\grading\answer_evaluator;

/**
 * Tests for version_validator.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\domain\version_validator
 */
final class version_validator_test extends \advanced_testcase {
    /** @var version_validator */
    private $validator;

    /** @var \stdClass */
    private $version;

    /** @var \mod_elang_generator */
    private $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $this->validator = new version_validator();

        $course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $this->generator = $generator;
        $elang = $generator->create_instance(['course' => $course->id]);
        $this->version = $generator->create_version(['elangid' => $elang->id]);
    }

    /**
     * Add one valid cue with one valid gap to the version under test.
     *
     * @return \stdClass The created gap
     */
    private function add_valid_cue_and_gap(): \stdClass {
        $cue = $this->generator->create_cue(['versionid' => $this->version->id, 'transcript' => 'Le chat dort']);

        return $this->generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);
    }

    /**
     * A version with a cue, a well-formed gap and a valid algorithm is
     * publishable.
     *
     * @return void
     */
    public function test_a_valid_version_has_no_problems(): void {
        $this->add_valid_cue_and_gap();

        $this->assertSame([], $this->validator->validate($this->version->id));
    }

    /**
     * A version with no cues at all cannot be published.
     *
     * @return void
     */
    public function test_a_version_with_no_cues_is_rejected(): void {
        $this->assertNotEmpty($this->validator->validate($this->version->id));
    }

    /**
     * A version whose cues carry no gaps has nothing to answer and is rejected.
     *
     * @return void
     */
    public function test_a_version_without_gaps_is_rejected(): void {
        $this->generator->create_cue(['versionid' => $this->version->id, 'transcript' => 'Le chat dort']);

        $problems = $this->validator->validate($this->version->id);

        $this->assertStringContainsString('no gaps', implode(' ', $problems));
    }

    /**
     * An empty (or whitespace-only) solution cannot be graded and is rejected.
     *
     * @return void
     */
    public function test_an_empty_solution_is_rejected(): void {
        $cue = $this->generator->create_cue(['versionid' => $this->version->id, 'transcript' => 'Le chat']);
        $this->generator->create_gap([
            'cueid' => $cue->id,
            'charstart' => 0,
            'charlength' => 2,
            'solution' => '  ',
        ]);

        $this->assertNotEmpty($this->validator->validate($this->version->id));
    }

    /**
     * A gap whose character range runs past the end of its transcript is
     * rejected.
     *
     * @return void
     */
    public function test_a_gap_outside_the_transcript_is_rejected(): void {
        $cue = $this->generator->create_cue(['versionid' => $this->version->id, 'transcript' => 'Le chat']);
        $this->generator->create_gap([
            'cueid' => $cue->id,
            'charstart' => 5,
            'charlength' => 10,
            'solution' => 'x',
        ]);

        $this->assertNotEmpty($this->validator->validate($this->version->id));
    }

    /**
     * Two gaps in the same cue whose character ranges overlap are rejected.
     *
     * @return void
     */
    public function test_overlapping_gaps_are_rejected(): void {
        $cue = $this->generator->create_cue(['versionid' => $this->version->id, 'transcript' => 'Le chat dort']);
        $this->generator->create_gap([
            'cueid' => $cue->id,
            'charstart' => 0,
            'charlength' => 5,
            'solution' => 'first',
            'sortorder' => 1,
        ]);
        $this->generator->create_gap([
            'cueid' => $cue->id,
            'charstart' => 3,
            'charlength' => 5,
            'solution' => 'second',
            'sortorder' => 2,
        ]);

        $this->assertNotEmpty($this->validator->validate($this->version->id));
    }

    /**
     * An unknown grading algorithm is rejected.
     *
     * @return void
     */
    public function test_an_unknown_grading_algorithm_is_rejected(): void {
        $cue = $this->generator->create_cue(['versionid' => $this->version->id, 'transcript' => 'Le chat dort']);
        $this->generator->create_gap([
            'cueid' => $cue->id,
            'solution' => 'chat',
            'gradingalgorithm' => 'nonsense',
        ]);

        $this->assertNotEmpty($this->validator->validate($this->version->id));
    }

    /**
     * Hint levels that skip a step (1, 3) cannot be revealed in order and are
     * rejected.
     *
     * @return void
     */
    public function test_non_contiguous_hint_levels_are_rejected(): void {
        $gap = $this->add_valid_cue_and_gap();
        $this->generator->create_gaphint(['gapid' => $gap->id, 'level' => 1, 'hinttext' => 'a']);
        $this->generator->create_gaphint(['gapid' => $gap->id, 'level' => 3, 'hinttext' => 'c']);

        $this->assertNotEmpty($this->validator->validate($this->version->id));
    }

    /**
     * Hint levels that run 1, 2 without a gap are accepted.
     *
     * @return void
     */
    public function test_contiguous_hint_levels_are_accepted(): void {
        $gap = $this->add_valid_cue_and_gap();
        $this->generator->create_gaphint(['gapid' => $gap->id, 'level' => 1, 'hinttext' => 'a']);
        $this->generator->create_gaphint(['gapid' => $gap->id, 'level' => 2, 'hinttext' => 'b']);

        $this->assertSame([], $this->validator->validate($this->version->id));
    }
}
