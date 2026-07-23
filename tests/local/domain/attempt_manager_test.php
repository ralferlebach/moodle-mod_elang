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
use mod_elang\local\grading\grading_result;
use mod_elang\local\grading\script_handler_manager;

/**
 * Tests for attempt_manager.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\domain\attempt_manager
 */
final class attempt_manager_test extends \advanced_testcase {
    /** @var attempt_manager */
    private $manager;

    /** @var \stdClass */
    private $elang;

    /** @var \stdClass */
    private $version;

    /** @var \stdClass */
    private $gap;

    /** @var \stdClass */
    private $student;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $this->manager = new attempt_manager(new answer_evaluator(new script_handler_manager([])));

        $course = $this->getDataGenerator()->create_course();
        $this->student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $this->elang = $generator->create_instance(['course' => $course->id, 'language' => 'fr']);
        $this->version = $generator->create_version(['elangid' => $this->elang->id]);
        $cue = $generator->create_cue(['versionid' => $this->version->id]);
        $this->gap = $generator->create_gap([
            'cueid' => $cue->id,
            'solution' => 'chat',
            'gradingalgorithm' => answer_evaluator::ALGORITHM_EXACT,
        ]);
        $generator->create_gaphint([
            'gapid' => $this->gap->id,
            'level' => 1,
            'hinttype' => 'firstletter',
            'hinttext' => 'c',
            'penalty' => 0.1,
        ]);
        $generator->create_gaphint([
            'gapid' => $this->gap->id,
            'level' => 2,
            'hinttype' => 'wordlength',
            'hinttext' => '4',
            'penalty' => 0.3,
        ]);
    }

    /**
     * Starting an attempt records the total number of gaps in the attempted version.
     *
     * @return void
     */
    public function test_start_attempt_records_total_gaps(): void {
        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $this->assertSame(attempt_manager::STATE_INPROGRESS, $attempt->state);
        $this->assertSame(1, (int) $attempt->totalgaps);
        $this->assertSame(1, (int) $attempt->attemptnumber);
    }

    /**
     * Starting an attempt while one is already in progress returns the
     * existing attempt rather than creating a second one.
     *
     * @return void
     */
    public function test_start_attempt_resumes_existing_inprogress_attempt(): void {
        $first = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $second = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $this->assertSame((int) $first->id, (int) $second->id);
    }

    /**
     * An exact response is recorded as exact, accepted, and updates the
     * attempt's aggregate counters.
     *
     * @return void
     */
    public function test_submit_exact_response_updates_aggregates(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $result = $this->manager->submit_response($attempt->id, $this->gap->id, 'chat');

        $this->assertSame(grading_result::RESULTSTATE_EXACT, $result->resultstate);
        $this->assertTrue($result->accepted);

        $updated = $DB->get_record('elang_attempt', ['id' => $attempt->id], '*', MUST_EXIST);
        $this->assertSame(1, (int) $updated->answeredgaps);
        $this->assertSame(1, (int) $updated->exactgaps);
        $this->assertSame(1, (int) $updated->correctgaps);
        $this->assertEqualsWithDelta(1.0, (float) $updated->score, 0.00001);
    }

    /**
     * An incorrect response is recorded as answered but not correct, and
     * contributes zero to the score.
     *
     * @return void
     */
    public function test_submit_incorrect_response_does_not_count_as_correct(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->submit_response($attempt->id, $this->gap->id, 'chien');

        $updated = $DB->get_record('elang_attempt', ['id' => $attempt->id], '*', MUST_EXIST);
        $this->assertSame(1, (int) $updated->answeredgaps);
        $this->assertSame(0, (int) $updated->exactgaps);
        $this->assertSame(0, (int) $updated->correctgaps);
        $this->assertEqualsWithDelta(0.0, (float) $updated->score, 0.00001);
    }

    /**
     * Resubmitting a response to the same gap replaces it rather than
     * creating a second row, and increments the try count.
     *
     * @return void
     */
    public function test_resubmission_replaces_the_response_and_increments_tries(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $this->manager->submit_response($attempt->id, $this->gap->id, 'chien');
        $this->manager->submit_response($attempt->id, $this->gap->id, 'chat');

        $this->assertSame(1, $DB->count_records('elang_response', ['attemptid' => $attempt->id, 'gapid' => $this->gap->id]));

        $response = $DB->get_record('elang_response', ['attemptid' => $attempt->id, 'gapid' => $this->gap->id], '*', MUST_EXIST);
        $this->assertSame(2, (int) $response->tries);
        $this->assertSame(grading_result::RESULTSTATE_EXACT, $response->resultstate);

        $updated = $DB->get_record('elang_attempt', ['id' => $attempt->id], '*', MUST_EXIST);
        $this->assertSame(1, (int) $updated->correctgaps);
    }

    /**
     * A response cannot be submitted to an attempt that is not in progress.
     *
     * @return void
     */
    public function test_submit_response_rejects_a_finished_attempt(): void {
        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->finish_attempt($attempt->id);

        $this->expectException(\coding_exception::class);
        $this->manager->submit_response($attempt->id, $this->gap->id, 'chat');
    }

    /**
     * The first hint request reveals level 1, and creates an empty response
     * row to hold it when nothing has been submitted yet.
     *
     * @return void
     */
    public function test_first_hint_request_reveals_level_one(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $hint = $this->manager->request_hint($attempt->id, $this->gap->id);

        $this->assertSame(1, (int) $hint->level);
        $this->assertSame('firstletter', $hint->hinttype);
        $this->assertSame('c', $hint->hinttext);

        $response = $DB->get_record('elang_response', ['attemptid' => $attempt->id, 'gapid' => $this->gap->id], '*', MUST_EXIST);
        $this->assertSame(1, (int) $response->hintlevel);
        $this->assertSame(grading_result::RESULTSTATE_EMPTY, $response->resultstate);

        $updated = $DB->get_record('elang_attempt', ['id' => $attempt->id], '*', MUST_EXIST);
        $this->assertSame(1, (int) $updated->hintedgaps);
    }

    /**
     * Hint levels are revealed strictly in order: a second request reveals
     * level 2, not level 1 again.
     *
     * @return void
     */
    public function test_second_hint_request_reveals_the_next_level(): void {
        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $this->manager->request_hint($attempt->id, $this->gap->id);
        $second = $this->manager->request_hint($attempt->id, $this->gap->id);

        $this->assertSame(2, (int) $second->level);
        $this->assertSame('wordlength', $second->hinttype);
    }

    /**
     * Requesting a hint beyond the highest defined level fails rather than
     * silently returning nothing.
     *
     * @return void
     */
    public function test_hint_request_beyond_the_last_level_throws(): void {
        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $this->manager->request_hint($attempt->id, $this->gap->id);
        $this->manager->request_hint($attempt->id, $this->gap->id);

        $this->expectException(\coding_exception::class);
        $this->manager->request_hint($attempt->id, $this->gap->id);
    }

    /**
     * A hint cannot be requested for an attempt that is not in progress.
     *
     * @return void
     */
    public function test_hint_request_rejects_a_finished_attempt(): void {
        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->finish_attempt($attempt->id);

        $this->expectException(\coding_exception::class);
        $this->manager->request_hint($attempt->id, $this->gap->id);
    }

    /**
     * An accepted response after a hint was used contributes less than a
     * full point, both on the response itself and the attempt total.
     *
     * @return void
     */
    public function test_accepted_response_after_hint_reflects_the_penalty(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $this->manager->request_hint($attempt->id, $this->gap->id);
        $this->manager->submit_response($attempt->id, $this->gap->id, 'chat');

        $response = $DB->get_record('elang_response', ['attemptid' => $attempt->id, 'gapid' => $this->gap->id], '*', MUST_EXIST);
        $this->assertEqualsWithDelta(0.9, (float) $response->score, 0.00001);

        $updated = $DB->get_record('elang_attempt', ['id' => $attempt->id], '*', MUST_EXIST);
        $this->assertEqualsWithDelta(0.9, (float) $updated->score, 0.00001);
        $this->assertSame(1, (int) $updated->correctgaps);
        $this->assertSame(1, (int) $updated->hintedgaps);
    }

    /**
     * An unaccepted response scores zero regardless of hint use — the
     * penalty only reduces an otherwise-earned point, it never creates one.
     *
     * @return void
     */
    public function test_unaccepted_response_after_hint_still_scores_zero(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $this->manager->request_hint($attempt->id, $this->gap->id);
        $this->manager->submit_response($attempt->id, $this->gap->id, 'chien');

        $updated = $DB->get_record('elang_attempt', ['id' => $attempt->id], '*', MUST_EXIST);
        $this->assertEqualsWithDelta(0.0, (float) $updated->score, 0.00001);
        $this->assertSame(0, (int) $updated->correctgaps);
        $this->assertSame(1, (int) $updated->hintedgaps);
    }

    /**
     * Requesting a second, higher-penalty hint level after already
     * submitting a correct response reduces the previously-earned score
     * accordingly (the response's score is recalculated, not fixed at
     * submission time).
     *
     * @return void
     */
    public function test_requesting_a_hint_after_a_correct_answer_still_applies_the_penalty(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $this->manager->submit_response($attempt->id, $this->gap->id, 'chat');
        $updatedbeforehint = $DB->get_record('elang_attempt', ['id' => $attempt->id], '*', MUST_EXIST);
        $this->assertEqualsWithDelta(1.0, (float) $updatedbeforehint->score, 0.00001);

        $this->manager->request_hint($attempt->id, $this->gap->id);

        $updatedafterhint = $DB->get_record('elang_attempt', ['id' => $attempt->id], '*', MUST_EXIST);
        $this->assertEqualsWithDelta(0.9, (float) $updatedafterhint->score, 0.00001);
    }

    /**
     * Finishing an attempt sets its state and records a finish time.
     *
     * @return void
     */
    public function test_finish_attempt_sets_state_and_timefinish(): void {
        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $finished = $this->manager->finish_attempt($attempt->id);

        $this->assertSame(attempt_manager::STATE_FINISHED, $finished->state);
        $this->assertGreaterThan(0, (int) $finished->timefinish);
    }

    /**
     * An already-finished attempt cannot be finished again.
     *
     * @return void
     */
    public function test_finish_attempt_rejects_an_already_finished_attempt(): void {
        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->finish_attempt($attempt->id);

        $this->expectException(\coding_exception::class);
        $this->manager->finish_attempt($attempt->id);
    }

    /**
     * A user with no attempts at all has no best score.
     *
     * @return void
     */
    public function test_get_best_score_is_null_without_any_attempt(): void {
        $this->assertNull($this->manager->get_best_score($this->elang->id, $this->student->id));
    }

    /**
     * An in-progress attempt does not count towards the best score, even if
     * it currently has a perfect response — only finished attempts count.
     *
     * @return void
     */
    public function test_get_best_score_ignores_in_progress_attempts(): void {
        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->submit_response($attempt->id, $this->gap->id, 'chat');

        $this->assertNull($this->manager->get_best_score($this->elang->id, $this->student->id));
    }

    /**
     * The highest score among several finished attempts is returned, not
     * the latest one.
     *
     * @return void
     */
    public function test_get_best_score_returns_the_highest_finished_attempt(): void {
        $first = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->submit_response($first->id, $this->gap->id, 'chat');
        $this->manager->finish_attempt($first->id);

        $second = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->submit_response($second->id, $this->gap->id, 'chien');
        $this->manager->finish_attempt($second->id);

        $this->assertEqualsWithDelta(1.0, $this->manager->get_best_score($this->elang->id, $this->student->id), 0.00001);
    }
}
