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

namespace mod_elang\local\grading;

/**
 * Reference-case tests for latin_script_handler.
 *
 * These cases are the fixed reference behaviour for the two grading
 * algorithms (see docs/materials/Lastenheft_Pflichtenheft_Blueprint.md,
 * chapter 10) and must not change without an explicit, documented decision.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\grading\latin_script_handler
 */
final class latin_script_handler_test extends \basic_testcase {
    /** @var latin_script_handler */
    private $handler;

    protected function setUp(): void {
        parent::setUp();
        $this->handler = new latin_script_handler();
    }

    /**
     * Two Unicode encodings of the same visible string must compare equal
     * under the exact algorithm: this is technical canonicalisation, not
     * leniency, and applies regardless of accents, case or apostrophes.
     *
     * @return void
     */
    public function test_exact_treats_composed_and_decomposed_forms_as_equal(): void {
        $composed = "caf\u{00E9}";
        $decomposed = "cafe\u{0301}";

        $this->assertSame(
            $this->handler->normalise_for_exact($composed),
            $this->handler->normalise_for_exact($decomposed)
        );
    }

    /**
     * The exact algorithm is case-sensitive.
     *
     * @return void
     */
    public function test_exact_is_case_sensitive(): void {
        $this->assertNotSame(
            $this->handler->normalise_for_exact('Café'),
            $this->handler->normalise_for_exact('café')
        );
    }

    /**
     * The exact algorithm requires the accent to be present.
     *
     * @return void
     */
    public function test_exact_requires_diacritics(): void {
        $this->assertNotSame(
            $this->handler->normalise_for_exact('cafe'),
            $this->handler->normalise_for_exact('café')
        );
    }

    /**
     * The exact algorithm does not unify different apostrophe glyphs — this
     * is the "mit allen ... Apostrophen etc." requirement.
     *
     * @return void
     */
    public function test_exact_does_not_unify_apostrophe_variants(): void {
        $straight = "l'\u{00E9}cole";
        $curly = "l\u{2019}\u{00E9}cole";

        $this->assertNotSame(
            $this->handler->normalise_for_exact($straight),
            $this->handler->normalise_for_exact($curly)
        );
    }

    /**
     * The exact algorithm trims outer whitespace and collapses interior runs,
     * which is not considered a character-level difference.
     *
     * @return void
     */
    public function test_exact_trims_and_collapses_whitespace(): void {
        $this->assertSame(
            $this->handler->normalise_for_exact('café'),
            $this->handler->normalise_for_exact("  café  ")
        );
        $this->assertSame(
            $this->handler->normalise_for_exact('a b'),
            $this->handler->normalise_for_exact("a\t \tb")
        );
    }

    /**
     * The word-recognised algorithm folds a missing accent to a match.
     *
     * @return void
     */
    public function test_wordrecognized_folds_missing_accent(): void {
        $this->assertSame(
            $this->handler->normalise_for_word_recognised('cafe'),
            $this->handler->normalise_for_word_recognised('café')
        );
    }

    /**
     * The word-recognised algorithm is case-insensitive.
     *
     * @return void
     */
    public function test_wordrecognized_is_case_insensitive(): void {
        $this->assertSame(
            $this->handler->normalise_for_word_recognised('CAFE'),
            $this->handler->normalise_for_word_recognised('café')
        );
    }

    /**
     * The word-recognised algorithm unifies apostrophe variants.
     *
     * @return void
     */
    public function test_wordrecognized_unifies_apostrophe_variants(): void {
        $straight = "l'ecole";
        $curly = "l\u{2019}\u{00E9}cole";

        $this->assertSame(
            $this->handler->normalise_for_word_recognised($straight),
            $this->handler->normalise_for_word_recognised($curly)
        );
    }

    /**
     * The word-recognised algorithm folds German ß to ss via the
     * non-decomposing fallback table.
     *
     * @return void
     */
    public function test_wordrecognized_folds_eszett(): void {
        $this->assertSame(
            $this->handler->normalise_for_word_recognised('strasse'),
            $this->handler->normalise_for_word_recognised('Straße')
        );
    }

    /**
     * The word-recognised algorithm folds French œ to oe.
     *
     * @return void
     */
    public function test_wordrecognized_folds_oe_ligature(): void {
        $this->assertSame(
            $this->handler->normalise_for_word_recognised('oeuf'),
            $this->handler->normalise_for_word_recognised('œuf')
        );
    }

    /**
     * The word-recognised algorithm folds the Turkish dotless i.
     *
     * @return void
     */
    public function test_wordrecognized_folds_turkish_dotless_i(): void {
        $this->assertSame(
            $this->handler->normalise_for_word_recognised('kiz'),
            $this->handler->normalise_for_word_recognised('kız')
        );
    }

    /**
     * Different words must not be folded into a match by either algorithm.
     *
     * @return void
     */
    public function test_unrelated_words_do_not_match(): void {
        $this->assertNotSame(
            $this->handler->normalise_for_exact('chat'),
            $this->handler->normalise_for_exact('chien')
        );
        $this->assertNotSame(
            $this->handler->normalise_for_word_recognised('chat'),
            $this->handler->normalise_for_word_recognised('chien')
        );
    }
}
