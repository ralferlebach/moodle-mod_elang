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

namespace mod_elang\local\import;

/**
 * Tests for the V1 inline gap syntax parser.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\import\gap_syntax_parser
 */
final class gap_syntax_parser_test extends \basic_testcase {
    /**
     * A bracket marker becomes a help-allowed gap and the brackets vanish.
     *
     * @return void
     */
    public function test_bracket_marker_becomes_gap_with_help(): void {
        $result = gap_syntax_parser::parse('Der [Hund] läuft');

        $this->assertSame('Der Hund läuft', $result->transcript);
        $this->assertCount(1, $result->gaps);
        $this->assertSame(4, $result->gaps[0]->charstart);
        $this->assertSame(4, $result->gaps[0]->charlength);
        $this->assertSame('Hund', $result->gaps[0]->solution);
        $this->assertTrue($result->gaps[0]->hintsallowed);
    }

    /**
     * A brace marker becomes a gap without help.
     *
     * @return void
     */
    public function test_brace_marker_becomes_gap_without_help(): void {
        $result = gap_syntax_parser::parse('Die {Katze} schläft');

        $this->assertSame('Die Katze schläft', $result->transcript);
        $this->assertCount(1, $result->gaps);
        $this->assertFalse($result->gaps[0]->hintsallowed);
    }

    /**
     * Several markers in one transcript keep their offsets consistent with
     * the cleaned text, in codepoints even with multibyte characters before
     * a gap.
     *
     * @return void
     */
    public function test_multiple_markers_use_codepoint_offsets_into_cleaned_text(): void {
        $result = gap_syntax_parser::parse('Où est [le] petit {déjeuner} ?');

        $this->assertSame('Où est le petit déjeuner ?', $result->transcript);
        $this->assertCount(2, $result->gaps);

        $this->assertSame(7, $result->gaps[0]->charstart);
        $this->assertSame(2, $result->gaps[0]->charlength);
        $this->assertSame('le', $result->gaps[0]->solution);
        $this->assertTrue($result->gaps[0]->hintsallowed);

        $this->assertSame(16, $result->gaps[1]->charstart);
        $this->assertSame(8, $result->gaps[1]->charlength);
        $this->assertSame('déjeuner', $result->gaps[1]->solution);
        $this->assertFalse($result->gaps[1]->hintsallowed);

        // The solution really sits at the reported range of the cleaned text.
        $this->assertSame(
            'déjeuner',
            \core_text::substr($result->transcript, $result->gaps[1]->charstart, $result->gaps[1]->charlength)
        );
    }

    /**
     * Unmatched, empty and line-spanning markers stay literal text.
     *
     * @return void
     */
    public function test_unusable_markers_stay_literal(): void {
        $unmatched = gap_syntax_parser::parse('An [unclosed marker');
        $this->assertSame('An [unclosed marker', $unmatched->transcript);
        $this->assertCount(0, $unmatched->gaps);

        $empty = gap_syntax_parser::parse('Empty [] pair');
        $this->assertSame('Empty [] pair', $empty->transcript);
        $this->assertCount(0, $empty->gaps);

        $spanning = gap_syntax_parser::parse("A [line\nbreak] pair");
        $this->assertSame("A [line\nbreak] pair", $spanning->transcript);
        $this->assertCount(0, $spanning->gaps);
    }

    /**
     * A transcript without any markers passes through unchanged.
     *
     * @return void
     */
    public function test_plain_transcript_passes_through(): void {
        $result = gap_syntax_parser::parse('Nothing to see here');

        $this->assertSame('Nothing to see here', $result->transcript);
        $this->assertSame([], $result->gaps);
    }
}
