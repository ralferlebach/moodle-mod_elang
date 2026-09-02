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
        $this->version = $generator->create_version(['elangid' => $this->elang->id, 'status' => 'published']);
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
     * Resuming an UNTOUCHED attempt after the exercise was republished follows
     * the new current version — there is no learner data to protect yet, and
     * without this a broken medium that the author fixed would stay broken for
     * everyone who had merely opened the exercise once.
     *
     * @return void
     */
    public function test_resume_of_untouched_attempt_follows_a_new_version(): void {
        global $DB;

        $first = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->assertSame((int) $this->version->id, (int) $first->versionid);

        // The author republishes: a second published version with two gaps.
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $newversion = $generator->create_version(['elangid' => $this->elang->id, 'status' => 'published']);
        $newcue = $generator->create_cue(['versionid' => $newversion->id]);
        $generator->create_gap(['cueid' => $newcue->id, 'solution' => 'chien']);
        $generator->create_gap(['cueid' => $newcue->id, 'solution' => 'dort', 'charstart' => 8, 'charlength' => 4]);
        $DB->set_field('elang', 'currentversionid', $newversion->id, ['id' => $this->elang->id]);

        $resumed = $this->manager->start_attempt($this->elang->id, $this->student->id, (int) $newversion->id);

        $this->assertSame((int) $first->id, (int) $resumed->id, 'The same attempt is resumed, not a new one');
        $this->assertSame((int) $newversion->id, (int) $resumed->versionid, 'The untouched attempt follows the new version');
        $this->assertSame(2, (int) $resumed->totalgaps, 'The gap total matches the new version');
    }

    /**
     * A TOUCHED attempt (a response or hint exists) stays pinned to the version
     * it started on even when a newer version is published.
     *
     * @return void
     */
    public function test_resume_of_touched_attempt_stays_pinned(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->submit_response((int) $attempt->id, (int) $this->gap->id, 'chat');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $newversion = $generator->create_version(['elangid' => $this->elang->id, 'status' => 'published']);
        $DB->set_field('elang', 'currentversionid', $newversion->id, ['id' => $this->elang->id]);

        $resumed = $this->manager->start_attempt($this->elang->id, $this->student->id, (int) $newversion->id);

        $this->assertSame((int) $attempt->id, (int) $resumed->id);
        $this->assertSame((int) $this->version->id, (int) $resumed->versionid, 'The touched attempt keeps its version');
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

        $this->expectException(\moodle_exception::class);
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

        $this->expectException(\moodle_exception::class);
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

        $this->expectException(\moodle_exception::class);
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
     * Finishing an already-finished attempt is idempotent: it returns the
     * same finished record rather than throwing, so a network retry of the
     * same request succeeds instead of surfacing a spurious error.
     *
     * @return void
     */
    public function test_finish_attempt_is_idempotent(): void {
        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $first = $this->manager->finish_attempt($attempt->id);
        $second = $this->manager->finish_attempt($attempt->id);

        $this->assertSame(attempt_manager::STATE_FINISHED, $second->state);
        $this->assertSame((int) $first->timefinish, (int) $second->timefinish);
    }

    /**
     * An attempt in a genuinely different state (abandoned) still cannot be
     * finished — only the already-finished case is tolerated.
     *
     * @return void
     */
    public function test_finish_attempt_rejects_an_abandoned_attempt(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $DB->set_field('elang_attempt', 'state', attempt_manager::STATE_ABANDONED, ['id' => $attempt->id]);

        $this->expectException(\moodle_exception::class);
        $this->manager->finish_attempt($attempt->id);
    }

    /**
     * submit_response() itself refuses a gap that does not belong to the
     * attempt's version, independent of any check an External Function layer
     * might also perform — a task, CLI script or future importer calling
     * this method directly must not be able to bypass that check.
     *
     * @return void
     */
    public function test_submit_response_rejects_a_gap_from_a_different_version(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $otherversion = $generator->create_version(['elangid' => $this->elang->id]);
        $othercue = $generator->create_cue(['versionid' => $otherversion->id]);
        $othergap = $generator->create_gap(['cueid' => $othercue->id, 'solution' => 'chien']);

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $this->expectException(\moodle_exception::class);
        $this->manager->submit_response($attempt->id, $othergap->id, 'chien');
    }

    /**
     * request_hint() itself refuses a gap that does not belong to the
     * attempt's version, for the same reason submit_response() does.
     *
     * @return void
     */
    public function test_request_hint_rejects_a_gap_from_a_different_version(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $otherversion = $generator->create_version(['elangid' => $this->elang->id]);
        $othercue = $generator->create_cue(['versionid' => $otherversion->id]);
        $othergap = $generator->create_gap(['cueid' => $othercue->id, 'solution' => 'chien']);
        $generator->create_gaphint(['gapid' => $othergap->id, 'level' => 1]);

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $this->expectException(\moodle_exception::class);
        $this->manager->request_hint($attempt->id, $othergap->id);
    }

    /**
     * start_attempt() refuses a version that does not belong to the given
     * activity.
     *
     * @return void
     */
    public function test_start_attempt_rejects_a_version_from_a_different_activity(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $course = $this->getDataGenerator()->create_course();
        $otherelang = $generator->create_instance(['course' => $course->id]);
        $otherversion = $generator->create_version(['elangid' => $otherelang->id, 'status' => 'published']);

        $this->expectException(\coding_exception::class);
        $this->manager->start_attempt($this->elang->id, $this->student->id, $otherversion->id);
    }

    /**
     * start_attempt() refuses a version that is not (yet) published, for
     * example a draft still being edited.
     *
     * @return void
     */
    public function test_start_attempt_rejects_an_unpublished_version(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $draft = $generator->create_version(['elangid' => $this->elang->id, 'status' => 'draft']);

        $this->expectException(\coding_exception::class);
        $this->manager->start_attempt($this->elang->id, $this->student->id, $draft->id);
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

    /**
     * A retry that passes the tries count seen before the original submit
     * committed replays the stored outcome without counting a second try. The
     * optimistic-concurrency guard now lives here, under the write lock.
     *
     * @return void
     */
    public function test_submit_response_replays_stored_outcome_on_stale_expected_tries(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->submit_response($attempt->id, $this->gap->id, 'chat');

        $retry = $this->manager->submit_response($attempt->id, $this->gap->id, 'chat', 0);

        $this->assertSame(grading_result::RESULTSTATE_EXACT, $retry->resultstate);
        $this->assertTrue($retry->accepted);

        $response = $DB->get_record('elang_response', ['attemptid' => $attempt->id, 'gapid' => $this->gap->id], '*', MUST_EXIST);
        $this->assertSame(1, (int) $response->tries);
    }

    /**
     * A submit whose expected tries count is ahead of the server is rejected
     * as a stale client rather than silently accepted.
     *
     * @return void
     */
    public function test_submit_response_rejects_expected_tries_ahead_of_the_server(): void {
        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->submit_response($attempt->id, $this->gap->id, 'chat');

        $this->expectException(\moodle_exception::class);
        $this->manager->submit_response($attempt->id, $this->gap->id, 'chat', 5);
    }

    /**
     * A hint retry that passes the level seen before the previous reveal
     * committed replays that level without advancing or re-penalising again.
     *
     * @return void
     */
    public function test_request_hint_replays_current_level_on_previous_expected_level(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->request_hint($attempt->id, $this->gap->id);

        $replay = $this->manager->request_hint($attempt->id, $this->gap->id, 0);

        $this->assertSame(1, (int) $replay->level);

        $response = $DB->get_record('elang_response', ['attemptid' => $attempt->id, 'gapid' => $this->gap->id], '*', MUST_EXIST);
        $this->assertSame(1, (int) $response->hintlevel);
    }

    /**
     * A hint request whose expected level is neither the current level nor
     * exactly one behind is rejected as a stale client.
     *
     * @return void
     */
    public function test_request_hint_rejects_a_stale_expected_level(): void {
        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->request_hint($attempt->id, $this->gap->id);
        $this->manager->request_hint($attempt->id, $this->gap->id);

        $this->expectException(\moodle_exception::class);
        $this->manager->request_hint($attempt->id, $this->gap->id, 0);
    }

    /**
     * Grading reads the Jaro threshold pinned on the attempt's version, not
     * the activity's current value: a version published with a lenient
     * threshold accepts a near-miss even though the activity stays strict.
     *
     * @return void
     */
    public function test_grading_uses_the_versions_jaro_threshold_not_the_activitys(): void {
        global $DB;

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $version = $generator->create_version([
            'elangid' => $this->elang->id,
            'status' => 'published',
            'jarothreshold' => 0.9,
        ]);
        $cue = $generator->create_cue(['versionid' => $version->id, 'transcript' => 'Le chien dort']);
        $gap = $generator->create_gap([
            'cueid' => $cue->id,
            'solution' => 'chien',
            'gradingalgorithm' => answer_evaluator::ALGORITHM_WORDRECOGNIZED,
        ]);

        // The activity keeps the strict default threshold; only the version's
        // 0.9 can accept the trailing-typo near-miss submitted below.
        $this->assertEqualsWithDelta(
            1.0,
            (float) $DB->get_field('elang', 'jarothreshold', ['id' => $this->elang->id]),
            0.00001
        );

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $version->id);
        $result = $this->manager->submit_response($attempt->id, $gap->id, 'chien!');

        $this->assertSame(grading_result::RESULTSTATE_WORDRECOGNIZED, $result->resultstate);
        $this->assertTrue($result->accepted);
    }

    /**
     * delete_attempt() removes the attempt together with all of its responses
     * and returns the deleted record so the caller can regrade its owner.
     *
     * @return void
     */
    public function test_delete_attempt_removes_the_attempt_and_its_responses(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->submit_response($attempt->id, $this->gap->id, 'chat');
        $this->assertSame(1, (int) $DB->count_records('elang_response', ['attemptid' => $attempt->id]));

        $deleted = $this->manager->delete_attempt((int) $attempt->id);

        $this->assertSame((int) $this->student->id, (int) $deleted->userid);
        $this->assertSame(0, (int) $DB->count_records('elang_attempt', ['id' => $attempt->id]));
        $this->assertSame(0, (int) $DB->count_records('elang_response', ['attemptid' => $attempt->id]));
    }

    /**
     * A second finish arriving while the first is still running is a no-op.
     *
     * The two calls are serialised by the write lock, so the second one sees a
     * finished attempt and returns it as it stands. What it must not do is move
     * timefinish: a retried request or a double click would otherwise rewrite
     * when the learner handed their work in.
     *
     * @return void
     */
    public function test_a_repeated_finish_does_not_move_the_finish_time(): void {
        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $first = $this->manager->finish_attempt($attempt->id);
        $second = $this->manager->finish_attempt($attempt->id);

        $this->assertSame(attempt_manager::STATE_FINISHED, $second->state);
        $this->assertSame((int) $first->timefinish, (int) $second->timefinish);
    }

    /**
     * A response arriving after the attempt was finished is refused.
     *
     * The realistic race: a learner presses "finish" while an answer is still
     * in flight. The write lock serialises them, and whichever lands second has
     * to respect what the first decided — an answer accepted into a finished
     * attempt would change a score that has already been reported.
     *
     * @return void
     */
    public function test_a_response_that_loses_the_race_to_finish_is_refused(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->finish_attempt($attempt->id);

        try {
            $this->manager->submit_response($attempt->id, $this->gap->id, 'chat');
            $this->fail('A finished attempt must not accept a response.');
        } catch (\moodle_exception $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        // Nothing was written: no response row, and the aggregates still
        // describe an attempt that answered nothing.
        $this->assertSame(0, $DB->count_records('elang_response', ['attemptid' => $attempt->id]));
        $stored = $DB->get_record('elang_attempt', ['id' => $attempt->id], '*', MUST_EXIST);
        $this->assertSame(0, (int) $stored->answeredgaps);
        $this->assertSame(0.0, (float) $stored->score);
    }

    /**
     * A hint requested after the attempt was finished is refused, and costs
     * nothing.
     *
     * @return void
     */
    public function test_a_hint_that_loses_the_race_to_finish_is_refused(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->submit_response($attempt->id, $this->gap->id, 'chat');
        $this->manager->finish_attempt($attempt->id);

        $before = $DB->get_record('elang_attempt', ['id' => $attempt->id], '*', MUST_EXIST);

        try {
            $this->manager->request_hint($attempt->id, $this->gap->id);
            $this->fail('A finished attempt must not reveal a hint.');
        } catch (\moodle_exception $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        // The refused hint left no penalty behind: a score that dropped after
        // the attempt was handed in would be indefensible.
        $after = $DB->get_record('elang_attempt', ['id' => $attempt->id], '*', MUST_EXIST);
        $this->assertSame((float) $before->score, (float) $after->score);
        $this->assertSame((int) $before->hintedgaps, (int) $after->hintedgaps);
        $this->assertSame(0, (int) $after->hintedgaps);
    }

    /**
     * Two starts for the same learner and activity yield one attempt.
     *
     * Two browser tabs, or a reloaded page, both call start_attempt. Under the
     * start lock the second call must resume the first attempt rather than open
     * a second one — two in-progress attempts for one learner is a state the
     * resume logic has no way to choose between.
     *
     * @return void
     */
    public function test_repeated_starts_yield_a_single_attempt(): void {
        global $DB;

        $first = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $second = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $third = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);

        $this->assertSame((int) $first->id, (int) $second->id);
        $this->assertSame((int) $first->id, (int) $third->id);
        $this->assertSame(1, $DB->count_records('elang_attempt', [
            'elangid' => $this->elang->id,
            'userid' => $this->student->id,
        ]));
    }

    /**
     * Deleting an attempt takes its responses with it.
     *
     * @return void
     */
    public function test_deleting_an_attempt_removes_its_responses(): void {
        global $DB;

        $attempt = $this->manager->start_attempt($this->elang->id, $this->student->id, $this->version->id);
        $this->manager->submit_response($attempt->id, $this->gap->id, 'chat');
        $this->assertSame(1, $DB->count_records('elang_response', ['attemptid' => $attempt->id]));

        $deleted = $this->manager->delete_attempt($attempt->id);

        $this->assertSame((int) $attempt->id, (int) $deleted->id);
        $this->assertSame(0, $DB->count_records('elang_response', ['attemptid' => $attempt->id]));
        $this->assertFalse($DB->record_exists('elang_attempt', ['id' => $attempt->id]));
    }
}
