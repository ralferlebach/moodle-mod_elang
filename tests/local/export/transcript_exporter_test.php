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

namespace mod_elang\local\export;

/**
 * Tests for transcript_exporter.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\export\transcript_exporter
 */
final class transcript_exporter_test extends \advanced_testcase {
    /**
     * The transcript joins every cue's text in sort order, one paragraph each.
     *
     * @return void
     */
    public function test_plain_text_joins_cue_transcripts_in_order(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);
        $version = $generator->create_version(['elangid' => $elang->id, 'status' => 'published']);
        $generator->create_cue(['versionid' => $version->id, 'sortorder' => 2, 'transcript' => 'Second line.']);
        $generator->create_cue(['versionid' => $version->id, 'sortorder' => 1, 'transcript' => 'First line.']);

        $text = (new transcript_exporter())->plain_text((int) $version->id);

        $this->assertSame("First line.\n\nSecond line.", $text);
    }

    /**
     * A version with no cues exports as an empty string.
     *
     * @return void
     */
    public function test_plain_text_of_an_empty_version_is_empty(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);
        $version = $generator->create_version(['elangid' => $elang->id]);

        $this->assertSame('', (new transcript_exporter())->plain_text((int) $version->id));
    }

    /**
     * Create a published version whose single cue carries one gap whose solution
     * word is literally present in the transcript, mirroring how a real gap is
     * stored (charstart/charlength index into the full text).
     *
     * @return \stdClass The created version record
     */
    private function version_with_a_gap(): \stdClass {
        $course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);
        $version = $generator->create_version(['elangid' => $elang->id, 'status' => 'published']);
        $cue = $generator->create_cue([
            'versionid' => $version->id,
            'sortorder' => 1,
            'transcript' => 'Le chat dort',
        ]);
        $generator->create_gap([
            'cueid' => $cue->id,
            'gapkey' => 'gap-1',
            'charstart' => 3,
            'charlength' => 4,
            'solution' => 'chat',
        ]);

        return $version;
    }

    /**
     * The learner worksheet (the default, masked export) must never contain a
     * gap's solution text: this is the P0 information-flow guarantee, checked
     * across every export path that shares transcript_exporter::paragraphs().
     *
     * @return void
     */
    public function test_worksheet_export_never_contains_the_solution(): void {
        $this->resetAfterTest();

        $version = $this->version_with_a_gap();
        $exporter = new transcript_exporter();

        $worksheet = $exporter->plain_text((int) $version->id, true);

        $this->assertStringNotContainsString('chat', $worksheet);
        $this->assertStringContainsString('Le', $worksheet);
        $this->assertStringContainsString('dort', $worksheet);
    }

    /**
     * The default parameterless export is the masked worksheet, so a caller that
     * forgets the flag cannot accidentally leak the solution.
     *
     * @return void
     */
    public function test_default_export_is_the_masked_worksheet(): void {
        $this->resetAfterTest();

        $version = $this->version_with_a_gap();

        $this->assertStringNotContainsString('chat', (new transcript_exporter())->plain_text((int) $version->id));
    }

    /**
     * The solution export (only reachable after a mod/elang:exportsolution check)
     * keeps the full text, including the gap solution.
     *
     * @return void
     */
    public function test_solution_export_contains_the_solution(): void {
        $this->resetAfterTest();

        $version = $this->version_with_a_gap();

        $this->assertStringContainsString('chat', (new transcript_exporter())->plain_text((int) $version->id, false));
    }
}
