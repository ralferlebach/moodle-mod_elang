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

/**
 * Tests for transcript_masker.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\domain\transcript_masker
 */
final class transcript_masker_test extends \basic_testcase {
    /**
     * With no gaps, the transcript passes through unchanged.
     *
     * @return void
     */
    public function test_no_gaps_leaves_transcript_unchanged(): void {
        $result = transcript_masker::mask('Le chat dort.', []);

        $this->assertSame('Le chat dort.', $result);
    }

    /**
     * A single gap is replaced by its token, and the surrounding text and
     * the original solution word are both preserved/removed correctly.
     *
     * @return void
     */
    public function test_single_gap_is_replaced_and_solution_is_not_present(): void {
        // Transcript "Le chat dort." — "chat" starts at codepoint 3, length 4.
        $result = transcript_masker::mask('Le chat dort.', [
            (object) ['charstart' => 3, 'charlength' => 4, 'gapkey' => 'g1'],
        ]);

        $this->assertSame('Le {{gap:g1}} dort.', $result);
        $this->assertStringNotContainsString('chat', $result);
    }

    /**
     * Multiple gaps are all replaced, regardless of the order they were
     * passed in (the masker sorts by position itself).
     *
     * @return void
     */
    public function test_multiple_gaps_are_all_replaced_in_position_order(): void {
        // Transcript "Le chat dort sur le tapis." — "chat" at 3/4, "tapis" at 20/5
        // (verified with mb_strpos(), not counted by hand).
        $gaps = [
            (object) ['charstart' => 20, 'charlength' => 5, 'gapkey' => 'g2'],
            (object) ['charstart' => 3, 'charlength' => 4, 'gapkey' => 'g1'],
        ];

        $result = transcript_masker::mask('Le chat dort sur le tapis.', $gaps);

        $this->assertSame('Le {{gap:g1}} dort sur le {{gap:g2}}.', $result);
    }

    /**
     * A gap at the very start of the transcript is handled correctly (empty
     * text before it).
     *
     * @return void
     */
    public function test_gap_at_the_start_of_the_transcript(): void {
        $result = transcript_masker::mask('Chat noir.', [
            (object) ['charstart' => 0, 'charlength' => 4, 'gapkey' => 'g1'],
        ]);

        $this->assertSame('{{gap:g1}} noir.', $result);
    }

    /**
     * A gap at the very end of the transcript is handled correctly (empty
     * text after it).
     *
     * @return void
     */
    public function test_gap_at_the_end_of_the_transcript(): void {
        $result = transcript_masker::mask('Un chat', [
            (object) ['charstart' => 3, 'charlength' => 4, 'gapkey' => 'g1'],
        ]);

        $this->assertSame('Un {{gap:g1}}', $result);
    }

    /**
     * Character offsets are Unicode codepoint offsets, not byte offsets: a
     * multi-byte character before the gap must not shift its position.
     *
     * @return void
     */
    public function test_offsets_are_codepoints_not_bytes(): void {
        // Transcript "café dort" — "café" is 4 codepoints but 5 bytes (é is 2
        // bytes in UTF-8). "dort" starts at codepoint 5, not byte 5.
        $result = transcript_masker::mask('café dort', [
            (object) ['charstart' => 5, 'charlength' => 4, 'gapkey' => 'g1'],
        ]);

        $this->assertSame('café {{gap:g1}}', $result);
    }

    /**
     * The masker also accepts plain arrays, not just objects, for gap
     * representations (useful when data has already been decoded to arrays).
     *
     * @return void
     */
    public function test_accepts_array_gap_representations(): void {
        $result = transcript_masker::mask('Le chat dort.', [
            ['charstart' => 3, 'charlength' => 4, 'gapkey' => 'g1'],
        ]);

        $this->assertSame('Le {{gap:g1}} dort.', $result);
    }

    /**
     * Overlapping gaps are rejected loudly rather than silently producing a
     * transcript that might still expose solution text.
     *
     * @return void
     */
    public function test_overlapping_gaps_throw(): void {
        $gaps = [
            (object) ['charstart' => 0, 'charlength' => 5, 'gapkey' => 'g1'],
            (object) ['charstart' => 3, 'charlength' => 4, 'gapkey' => 'g2'],
        ];

        $this->expectException(\coding_exception::class);
        transcript_masker::mask('Le chat dort.', $gaps);
    }
}
