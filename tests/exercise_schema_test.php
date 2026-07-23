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

namespace mod_elang;

/**
 * Integration test for the versioned exercise data model introduced in
 * db/install.xml: elang_version, elang_cue, elang_gap, elang_gapanswer and
 * elang_gaphint, built through the test generator.
 *
 * This does not cover a single class, so it deliberately exercises the
 * schema and generator together rather than one unit.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversNothing
 */
final class exercise_schema_test extends \advanced_testcase {
    /**
     * A full mini exercise (one version, one cue, one gap, two answer
     * variants, one hint) can be built and read back through the schema.
     *
     * @return void
     */
    public function test_full_mini_exercise_round_trips_through_the_schema(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id, 'language' => 'fr']);

        $version = $generator->create_version(['elangid' => $elang->id]);
        $DB->set_field('elang', 'currentversionid', $version->id, ['id' => $elang->id]);

        $cue = $generator->create_cue([
            'versionid' => $version->id,
            'transcript' => 'Bonjour, comment allez-vous ?',
        ]);
        $gap = $generator->create_gap([
            'cueid' => $cue->id,
            'solution' => 'allez',
            'gradingalgorithm' => \mod_elang\local\grading\answer_evaluator::ALGORITHM_WORDRECOGNIZED,
        ]);
        $variant = $generator->create_gapanswer(['gapid' => $gap->id, 'answer' => 'alez']);
        $hint = $generator->create_gaphint(['gapid' => $gap->id, 'hinttype' => 'firstletter', 'hinttext' => 'a']);

        $this->assertSame(1, $DB->count_records('elang_version', ['elangid' => $elang->id]));
        $this->assertSame($version->id, $DB->get_field('elang', 'currentversionid', ['id' => $elang->id]));
        $this->assertSame(1, $DB->count_records('elang_cue', ['versionid' => $version->id]));
        $this->assertSame(1, $DB->count_records('elang_gap', ['cueid' => $cue->id]));
        $this->assertSame(1, $DB->count_records('elang_gapanswer', ['gapid' => $gap->id]));
        $this->assertSame(1, $DB->count_records('elang_gaphint', ['gapid' => $gap->id]));

        $storedgap = $DB->get_record('elang_gap', ['id' => $gap->id], '*', MUST_EXIST);
        $this->assertSame('allez', $storedgap->solution);
        $this->assertSame(\mod_elang\local\grading\answer_evaluator::ALGORITHM_WORDRECOGNIZED, $storedgap->gradingalgorithm);
        $this->assertNotSame('', $storedgap->gapkey);

        $storedcue = $DB->get_record('elang_cue', ['id' => $cue->id], '*', MUST_EXIST);
        $this->assertNotSame('', $storedcue->cuekey);
    }

    /**
     * cuekey and gapkey stay stable when a second version is created for the
     * same activity — this is the prerequisite for 2.1-2 (retroactive
     * acceptance of answer variants with re-grading against later versions).
     *
     * @return void
     */
    public function test_cue_and_gap_keys_can_be_reused_across_versions(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);

        $versionone = $generator->create_version(['elangid' => $elang->id]);
        $cueone = $generator->create_cue(['versionid' => $versionone->id]);
        $gapone = $generator->create_gap(['cueid' => $cueone->id, 'solution' => 'chat']);

        $versiontwo = $generator->create_version(['elangid' => $elang->id]);
        $cuetwo = $generator->create_cue(['versionid' => $versiontwo->id, 'cuekey' => $cueone->cuekey]);
        $gaptwo = $generator->create_gap([
            'cueid' => $cuetwo->id,
            'gapkey' => $gapone->gapkey,
            'solution' => 'chat',
        ]);

        $this->assertSame($cueone->cuekey, $cuetwo->cuekey);
        $this->assertSame($gapone->gapkey, $gaptwo->gapkey);
        $this->assertNotEquals($versionone->id, $versiontwo->id);

        $this->assertSame(2, $DB->count_records('elang_version', ['elangid' => $elang->id]));
    }
}
