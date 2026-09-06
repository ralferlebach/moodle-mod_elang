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

    /**
     * validate() must batch its reads: the number of database reads it performs
     * stays constant as the version grows, rather than scaling with the number
     * of cues and gaps (which would be an N+1 query on the normal publish path).
     *
     * @return void
     */
    public function test_validation_uses_a_constant_number_of_reads(): void {
        global $DB;

        $build = function (int $cuecount): void {
            $version = $this->generator->create_version(['elangid' => $this->version->elangid]);
            for ($c = 0; $c < $cuecount; $c++) {
                $cue = $this->generator->create_cue([
                    'versionid' => $version->id,
                    'transcript' => 'Le chat dort ici maintenant',
                ]);
                for ($g = 0; $g < 3; $g++) {
                    $gap = $this->generator->create_gap([
                        'cueid' => $cue->id,
                        'solution' => 'chat',
                        'charstart' => $g * 4,
                        'charlength' => 3,
                    ]);
                    $this->generator->create_gaphint(['gapid' => $gap->id, 'level' => 1, 'hinttext' => 'a']);
                }
            }
            $this->version = $version;
        };

        // A small version and a much larger one must cost the same number of
        // reads. Measuring the delta between two sizes cancels out any fixed
        // setup cost and proves the count does not grow with the content.
        $build(2);
        $before = $DB->perf_get_reads();
        $this->validator->validate($this->version->id);
        $smallreads = $DB->perf_get_reads() - $before;

        $build(12);
        $before = $DB->perf_get_reads();
        $this->validator->validate($this->version->id);
        $largereads = $DB->perf_get_reads() - $before;

        $this->assertSame($smallreads, $largereads);
    }

    /**
     * A cue that starts before the recording does is refused.
     *
     * @return void
     */
    public function test_a_negative_start_time_blocks_publishing(): void {
        $this->add_valid_cue_and_gap();
        $this->set_cue_times((int) $this->version->id, -500, 2000);

        $problems = $this->validator->validate((int) $this->version->id);

        $this->assertNotEmpty($problems);
        $this->assertStringContainsString('Cue 1', implode(' ', $problems));
    }

    /**
     * A cue whose end is not after its start is refused. Zero length is the
     * realistic case: it is what a mis-drag in the timeline produces.
     *
     * @dataProvider bad_cue_ranges_provider
     * @param int $starttime The cue start in milliseconds
     * @param int $endtime The cue end in milliseconds
     * @return void
     */
    public function test_an_end_not_after_the_start_blocks_publishing(int $starttime, int $endtime): void {
        $this->add_valid_cue_and_gap();
        $this->set_cue_times((int) $this->version->id, $starttime, $endtime);

        $this->assertNotEmpty($this->validator->validate((int) $this->version->id));
    }

    /**
     * Ranges that are not a range.
     *
     * @return array The start and end in milliseconds
     */
    public static function bad_cue_ranges_provider(): array {
        return [
            'zero length' => [2000, 2000],
            'reversed' => [4000, 2000],
        ];
    }

    /**
     * A cue that ends after the medium does is refused: playback can never
     * reach it, so its gaps can never be answered.
     *
     * @return void
     */
    public function test_a_cue_past_the_end_of_the_medium_blocks_publishing(): void {
        global $DB;
        $this->add_valid_cue_and_gap();
        $DB->set_field('elang_version', 'mediaduration', 10, ['id' => $this->version->id]);
        $this->set_cue_times((int) $this->version->id, 0, 30000);

        $problems = $this->validator->validate((int) $this->version->id);

        $this->assertNotEmpty($problems);
        $this->assertStringContainsString('30000', implode(' ', $problems));
    }

    /**
     * The duration comparison respects the unit difference and the rounding
     * that comes with it.
     *
     * mediaduration is whole seconds, cue times are milliseconds. A 12-second
     * recording really runs somewhere in [12, 13), so a cue ending at 12.4 s is
     * inside it. Comparing the numbers without converting would have rejected
     * essentially every exercise.
     *
     * @return void
     */
    public function test_a_cue_within_the_last_second_is_accepted(): void {
        global $DB;
        $this->add_valid_cue_and_gap();
        $DB->set_field('elang_version', 'mediaduration', 12, ['id' => $this->version->id]);
        $this->set_cue_times((int) $this->version->id, 11000, 12400);

        $this->assertSame([], $this->validator->validate((int) $this->version->id));
    }

    /**
     * A medium of unknown length imposes no upper bound.
     *
     * mediaduration is zero for a provider embed, which reports nothing, and
     * for any file whose length was never determined. Treating zero as "the
     * recording is empty" would refuse to publish all of them.
     *
     * @return void
     */
    public function test_an_unknown_duration_imposes_no_bound(): void {
        global $DB;
        $this->add_valid_cue_and_gap();
        $DB->set_field('elang_version', 'mediaduration', 0, ['id' => $this->version->id]);
        $this->set_cue_times((int) $this->version->id, 0, 9999000);

        $this->assertSame([], $this->validator->validate((int) $this->version->id));
    }

    /**
     * Set the times of every cue of a version.
     *
     * @param int $versionid The version
     * @param int $starttime The start in milliseconds
     * @param int $endtime The end in milliseconds
     * @return void
     */
    private function set_cue_times(int $versionid, int $starttime, int $endtime): void {
        global $DB;

        foreach ($DB->get_records('elang_cue', ['versionid' => $versionid]) as $cue) {
            $DB->set_field('elang_cue', 'starttime', $starttime, ['id' => $cue->id]);
            $DB->set_field('elang_cue', 'endtime', $endtime, ['id' => $cue->id]);
        }
    }
}
