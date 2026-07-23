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
 * Tests for answer_evaluator.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\grading\answer_evaluator
 */
final class answer_evaluator_test extends \basic_testcase {
    /** @var answer_evaluator */
    private $evaluator;

    protected function setUp(): void {
        parent::setUp();
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
        $fake = new fake_script_handler(['xx']);
        $evaluator = new answer_evaluator(new script_handler_manager([$fake]));

        $result = $evaluator->evaluate(
            'Anything',
            answer_evaluator::ALGORITHM_WORDRECOGNIZED,
            [],
            'xx',
            'ANYTHING'
        );

        // The fake_script_handler word-recognised reduction lower-cases and
        // prefixes 'fake:', which only happens if the evaluator actually used
        // the handler script_handler_manager returned for language 'xx'.
        $this->assertSame(grading_result::RESULTSTATE_WORDRECOGNIZED, $result->resultstate);
        $this->assertTrue($result->accepted);
    }
}
