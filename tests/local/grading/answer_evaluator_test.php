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

use mod_elang\fixtures\fake_script_handler;

/**
 * Tests for answer_evaluator.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\grading\answer_evaluator
 */
final class answer_evaluator_test extends \advanced_testcase {
    /** @var answer_evaluator */
    private $evaluator;

    public static function setUpBeforeClass(): void {
        require_once(__DIR__ . '/../../fixtures/fake_script_handler.php');
        parent::setUpBeforeClass();
    }

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->evaluator = new answer_evaluator(new script_handler_manager([]));
    }

    /**
     * An empty response is classified as empty and never accepted.
     *
     * @return void
     */
    public function test_empty_response_is_classified_as_empty(): void {
        $result = $this->evaluator->evaluate(
            'café',
            answer_evaluator::ALGORITHM_EXACT,
            [],
            'fr',
            '   '
        );

        $this->assertSame(grading_result::RESULTSTATE_EMPTY, $result->resultstate);
        $this->assertFalse($result->accepted);
        $this->assertNull($result->matchedgapanswerid);
    }

    /**
     * A character-perfect response against the primary solution is exact and
     * accepted, regardless of the configured algorithm.
     *
     * @return void
     */
    public function test_exact_response_against_solution_is_accepted(): void {
        $result = $this->evaluator->evaluate(
            'café',
            answer_evaluator::ALGORITHM_EXACT,
            [],
            'fr',
            'café'
        );

        $this->assertSame(grading_result::RESULTSTATE_EXACT, $result->resultstate);
        $this->assertTrue($result->accepted);
        $this->assertNull($result->matchedgapanswerid);
    }

    /**
     * A response missing the accent is classified as word-recognised, but a
     * gap configured for the exact algorithm does not accept it.
     *
     * @return void
     */
    public function test_wordrecognized_response_is_not_accepted_on_exact_gap(): void {
        $result = $this->evaluator->evaluate(
            'café',
            answer_evaluator::ALGORITHM_EXACT,
            [],
            'fr',
            'cafe'
        );

        $this->assertSame(grading_result::RESULTSTATE_WORDRECOGNIZED, $result->resultstate);
        $this->assertFalse($result->accepted);
    }

    /**
     * The same response is accepted once the gap is configured for the
     * word-recognised algorithm — the classification does not change, only
     * the acceptance decision does.
     *
     * @return void
     */
    public function test_wordrecognized_response_is_accepted_on_wordrecognized_gap(): void {
        $result = $this->evaluator->evaluate(
            'café',
            answer_evaluator::ALGORITHM_WORDRECOGNIZED,
            [],
            'fr',
            'cafe'
        );

        $this->assertSame(grading_result::RESULTSTATE_WORDRECOGNIZED, $result->resultstate);
        $this->assertTrue($result->accepted);
    }

    /**
     * A completely unrelated response is incorrect and not accepted under
     * either algorithm.
     *
     * @return void
     */
    public function test_unrelated_response_is_incorrect(): void {
        $result = $this->evaluator->evaluate(
            'café',
            answer_evaluator::ALGORITHM_WORDRECOGNIZED,
            [],
            'fr',
            'chien'
        );

        $this->assertSame(grading_result::RESULTSTATE_INCORRECT, $result->resultstate);
        $this->assertFalse($result->accepted);
    }

    /**
     * An exact match against an accepted variant (not the primary solution)
     * is reported with that variant's id.
     *
     * @return void
     */
    public function test_exact_match_against_variant_reports_its_id(): void {
        $variant1 = (object) ['id' => 11, 'answer' => 'colour', 'isregex' => 0];
        $variant2 = (object) ['id' => 12, 'answer' => 'color', 'isregex' => 0];

        $result = $this->evaluator->evaluate(
            'colour',
            answer_evaluator::ALGORITHM_EXACT,
            [$variant1, $variant2],
            'en',
            'color'
        );

        $this->assertSame(grading_result::RESULTSTATE_EXACT, $result->resultstate);
        $this->assertTrue($result->accepted);
        $this->assertSame(12, $result->matchedgapanswerid);
    }

    /**
     * A regular-expression variant that matches is treated as exact and is
     * accepted even on a gap configured for the exact algorithm — regex is an
     * alternative matching mode, not a third leniency tier.
     *
     * @return void
     */
    public function test_matching_regex_variant_is_treated_as_exact(): void {
        $variant = (object) ['id' => 21, 'answer' => '\d{4}', 'isregex' => 1];

        $result = $this->evaluator->evaluate(
            'irrelevant',
            answer_evaluator::ALGORITHM_EXACT,
            [$variant],
            'en',
            '1789'
        );

        $this->assertSame(grading_result::RESULTSTATE_EXACT, $result->resultstate);
        $this->assertTrue($result->accepted);
        $this->assertSame(21, $result->matchedgapanswerid);
    }

    /**
     * A non-matching regular expression does not prevent a later exact match
     * against another candidate.
     *
     * @return void
     */
    public function test_non_matching_regex_does_not_block_a_later_exact_match(): void {
        $variant = (object) ['id' => 31, 'answer' => '^only-digits-\d+$', 'isregex' => 1];

        $result = $this->evaluator->evaluate(
            'café',
            answer_evaluator::ALGORITHM_EXACT,
            [$variant],
            'fr',
            'café'
        );

        $this->assertSame(grading_result::RESULTSTATE_EXACT, $result->resultstate);
        $this->assertNull($result->matchedgapanswerid);
    }

    /**
     * The evaluator delegates to whichever script_handler the manager returns
     * for the activity's language, rather than assuming Latin-script rules.
     *
     * @return void
     */
    public function test_delegates_to_the_handler_selected_for_the_language(): void {
        $fake = new fake_script_handler(['xx'], true);
        $evaluator = new answer_evaluator(new script_handler_manager([$fake]));

        $result = $evaluator->evaluate(
            'Anything',
            answer_evaluator::ALGORITHM_WORDRECOGNIZED,
            [],
            'xx',
            'ANYTHING'
        );

        // The fake_script_handler word-recognised reduction (with the
        // delegation marker enabled) lower-cases and prefixes 'fake:', which
        // only happens if the evaluator actually used the handler
        // script_handler_manager returned for language 'xx'.
        $this->assertSame(grading_result::RESULTSTATE_WORDRECOGNIZED, $result->resultstate);
        $this->assertTrue($result->accepted);
    }

    /**
     * The default Jaro threshold (1.0, used when a caller does not pass one
     * explicitly) requires an identical reduced form: a near-miss typo is
     * still incorrect, exactly the pre-Jaro behaviour every existing
     * activity had before elang.jarothreshold existed.
     *
     * @return void
     */
    public function test_default_jaro_threshold_requires_an_identical_reduction(): void {
        $result = $this->evaluator->evaluate(
            'chien',
            answer_evaluator::ALGORITHM_WORDRECOGNIZED,
            [],
            'fr',
            'chien!' // Trailing typo — close, but not the same reduced form.
        );

        $this->assertSame(grading_result::RESULTSTATE_INCORRECT, $result->resultstate);
        $this->assertFalse($result->accepted);
    }

    /**
     * A lower Jaro threshold accepts a near-miss response as word-recognised
     * once its similarity to the reduced solution reaches the threshold.
     *
     * @return void
     */
    public function test_lower_jaro_threshold_accepts_a_near_miss_response(): void {
        // Jaro('chien', 'chien!') is well above 0.9 (one trailing insertion
        // in an otherwise identical five-character string).
        $result = $this->evaluator->evaluate(
            'chien',
            answer_evaluator::ALGORITHM_WORDRECOGNIZED,
            [],
            'fr',
            'chien!',
            0.9
        );

        $this->assertSame(grading_result::RESULTSTATE_WORDRECOGNIZED, $result->resultstate);
        $this->assertTrue($result->accepted);
    }

    /**
     * A response too dissimilar to clear even a lenient Jaro threshold is
     * still incorrect — the threshold makes near-misses tolerable, not
     * unrelated words. Jaro('chien', 'oiseau') is around 0.58 (they share an
     * 'i' and an 'e' in compatible positions), so 0.7 is the threshold that
     * actually exercises "still too dissimilar" here — a lower one would
     * accept this pair too, which is not what this test is meant to show.
     *
     * @return void
     */
    public function test_jaro_threshold_still_rejects_an_unrelated_response(): void {
        $result = $this->evaluator->evaluate(
            'chien',
            answer_evaluator::ALGORITHM_WORDRECOGNIZED,
            [],
            'fr',
            'oiseau',
            0.7
        );

        $this->assertSame(grading_result::RESULTSTATE_INCORRECT, $result->resultstate);
        $this->assertFalse($result->accepted);
    }

    /**
     * jaro_similarity() matches the well-known MARTHA/MARHTA textbook
     * example (two transposed characters), and is 1.0 for identical strings
     * and 0.0 for strings sharing no matching characters within range.
     *
     * @return void
     */
    public function test_jaro_similarity_matches_the_textbook_example(): void {
        $this->assertEqualsWithDelta(1.0, answer_evaluator::jaro_similarity('chat', 'chat'), 0.00001);
        $this->assertEqualsWithDelta(0.944, answer_evaluator::jaro_similarity('MARTHA', 'MARHTA'), 0.001);
        $this->assertEqualsWithDelta(0.0, answer_evaluator::jaro_similarity('abc', 'xyz'), 0.00001);
    }

    /**
     * A stored regex answer variant containing a literal '#' is matched
     * correctly — the previous '#'-delimited implementation would have had
     * the pattern collide with its own delimiter.
     *
     * @return void
     */
    public function test_regex_variant_containing_a_hash_character_matches(): void {
        $variant = (object) ['id' => 41, 'answer' => '^item#\d+$', 'isregex' => 1];

        $result = $this->evaluator->evaluate(
            'irrelevant',
            answer_evaluator::ALGORITHM_EXACT,
            [$variant],
            'en',
            'item#42'
        );

        $this->assertSame(grading_result::RESULTSTATE_EXACT, $result->resultstate);
        $this->assertTrue($result->accepted);
        $this->assertSame(41, $result->matchedgapanswerid);
    }

    /**
     * An invalid regular expression is treated as "no match" for that
     * candidate rather than aborting evaluation of the others, and the
     * debugging() call this triggers is asserted explicitly rather than
     * left unclaimed — an unclaimed debugging() call would otherwise mark
     * this test risky.
     *
     * @return void
     */
    public function test_invalid_regex_variant_does_not_abort_evaluation(): void {
        $invalid = (object) ['id' => 51, 'answer' => '(unclosed', 'isregex' => 1];

        $result = $this->evaluator->evaluate(
            'chat',
            answer_evaluator::ALGORITHM_EXACT,
            [$invalid],
            'fr',
            'chat'
        );

        $this->assertSame(grading_result::RESULTSTATE_EXACT, $result->resultstate);
        $this->assertNull($result->matchedgapanswerid);
        $this->assertDebuggingCalled();
    }
}
