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

namespace mod_elang\local\authoring;

/**
 * Tests for the rule-based gap generator.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\authoring\gap_rule_generator
 */
final class gap_rule_generator_test extends \advanced_testcase {
    /**
     * The word-list rule blanks each whole-word occurrence, case-insensitively
     * by default, with codepoint-correct offsets.
     *
     * @return void
     */
    public function test_word_list_rule(): void {
        $generator = new gap_rule_generator();

        $gaps = $generator->generate('Le chat dort. Le Chat court.', [
            'type' => gap_rule_generator::RULE_WORDS,
            'words' => ['chat'],
        ]);

        $this->assertCount(2, $gaps);
        $this->assertSame(3, $gaps[0]->charstart);
        $this->assertSame(4, $gaps[0]->charlength);
        $this->assertSame('chat', $gaps[0]->solution);
        // The second "Chat" keeps its original casing in the solution.
        $this->assertSame('Chat', $gaps[1]->solution);
    }

    /**
     * A case-sensitive word-list rule only matches the exact casing.
     *
     * @return void
     */
    public function test_case_sensitive_word_list_rule(): void {
        $generator = new gap_rule_generator();

        $gaps = $generator->generate('Le chat dort. Le Chat court.', [
            'type' => gap_rule_generator::RULE_WORDS,
            'words' => ['chat'],
            'casesensitive' => true,
        ]);

        $this->assertCount(1, $gaps);
        $this->assertSame('chat', $gaps[0]->solution);
    }

    /**
     * Offsets are codepoints, so an astral character before a word does not
     * shift its gap by an extra unit.
     *
     * @return void
     */
    public function test_offsets_are_codepoints(): void {
        $generator = new gap_rule_generator();

        // A musical-note emoji (one codepoint) precedes the words.
        $gaps = $generator->generate("\u{1F3B5} chat", [
            'type' => gap_rule_generator::RULE_WORDS,
            'words' => ['chat'],
        ]);

        $this->assertCount(1, $gaps);
        $this->assertSame(2, $gaps[0]->charstart);
        $this->assertSame(4, $gaps[0]->charlength);
    }

    /**
     * The every-nth rule blanks every nth word from the given offset.
     *
     * @return void
     */
    public function test_every_nth_rule(): void {
        $generator = new gap_rule_generator();

        $gaps = $generator->generate('one two three four five six', [
            'type' => gap_rule_generator::RULE_EVERY_NTH,
            'n' => 2,
            'offset' => 1,
        ]);

        // Words at indices 1, 3, 5: two, four, six.
        $this->assertSame(['two', 'four', 'six'], array_map(fn($g) => $g->solution, $gaps));
    }

    /**
     * Hyphenated and apostrophe words stay whole.
     *
     * @return void
     */
    public function test_words_with_hyphen_and_apostrophe_stay_whole(): void {
        $generator = new gap_rule_generator();

        $gaps = $generator->generate("l'enfant bien-aime", [
            'type' => gap_rule_generator::RULE_EVERY_NTH,
            'n' => 1,
        ]);

        $this->assertSame(["l'enfant", 'bien-aime'], array_map(fn($g) => $g->solution, $gaps));
    }

    /**
     * An unknown rule type is rejected.
     *
     * @return void
     */
    public function test_unknown_rule_type_is_rejected(): void {
        $generator = new gap_rule_generator();

        $this->expectException(\coding_exception::class);
        $generator->generate('one two', ['type' => 'nonsense']);
    }
}
