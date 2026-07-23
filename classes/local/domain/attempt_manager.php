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

/**
 * Manages the lifecycle of a learner's attempt at an exercise version.
 *
 * Four operations: start an attempt (or resume the in-progress one), submit
 * a response to a gap (evaluated through answer_evaluator, see
 * classes/local/grading/), request the next hint level for a gap, and
 * finish an attempt. elang_attempt's aggregate counters (totalgaps,
 * answeredgaps, exactgaps, correctgaps, hintedgaps, score) and each
 * elang_response's own score are recomputed after every submission or hint
 * request, so they never drift out of sync with each other.
 *
 * Scoring with hints: each elang_gaphint row carries a penalty (0..1, the
 * fraction of that gap's point value given up by having revealed hints up
 * to and including that level — not additive across levels, since a later
 * level's penalty is expected to already account for everything revealed at
 * earlier levels too). An accepted gap contributes (1 - penalty at its
 * response's current hintlevel) points rather than a flat 1; an unaccepted
 * gap always contributes 0, whether or not a hint was used. With no hint
 * used (hintlevel 0, penalty 0), this reduces exactly to the pre-hint
 * scoring of correctgaps/totalgaps.
 *
 * Not yet covered by this class, deliberately: enforcement of a maximum
 * attempt count (elang does not have that field yet — see the blueprint's
 * data model, chapter 6.1), and any Moodle completion or gradebook
 * integration (a later increment reads these aggregates from the outside;
 * this class only maintains them).
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attempt_manager {
    /** @var string An attempt currently being worked on. */
    public const STATE_INPROGRESS = 'inprogress';

    /** @var string An attempt the learner has finished. */
    public const STATE_FINISHED = 'finished';

    /** @var string An attempt abandoned without being finished. */
    public const STATE_ABANDONED = 'abandoned';

    /** @var answer_evaluator */
    private $evaluator;

    /**
     * Construct the manager.
     *
     * @param answer_evaluator $evaluator Evaluates responses against a gap's solution and variants
     */
    public function __construct(answer_evaluator $evaluator) {
        $this->evaluator = $evaluator;
    }

    /**
     * Start a new attempt, or return the learner's existing in-progress one.
     *
     * @param int $elangid The activity id
     * @param int $userid The learner's user id
     * @param int $versionid The published elang_version id being attempted
     * @return \stdClass The elang_attempt record, including its id
     */
    public function start_attempt(int $elangid, int $userid, int $versionid): \stdClass {
        global $DB;

        $existing = $DB->get_record('elang_attempt', [
            'elangid' => $elangid,
            'userid' => $userid,
            'state' => self::STATE_INPROGRESS,
        ]);
        if ($existing) {
            return $existing;
        }

        $nextnumber = (int) $DB->get_field_sql(
            'SELECT COALESCE(MAX(attemptnumber), 0) + 1 FROM {elang_attempt} WHERE elangid = ? AND userid = ?',
            [$elangid, $userid]
        );

        $totalgaps = (int) $DB->count_records_sql(
            'SELECT COUNT(g.id)
               FROM {elang_gap} g
               JOIN {elang_cue} c ON c.id = g.cueid
              WHERE c.versionid = ?',
            [$versionid]
        );

        $attempt = new \stdClass();
        $attempt->elangid = $elangid;
        $attempt->versionid = $versionid;
        $attempt->userid = $userid;
        $attempt->attemptnumber = $nextnumber;
        $attempt->state = self::STATE_INPROGRESS;
        $attempt->totalgaps = $totalgaps;
        $attempt->answeredgaps = 0;
        $attempt->exactgaps = 0;
        $attempt->correctgaps = 0;
        $attempt->hintedgaps = 0;
        $attempt->score = 0;
        $attempt->timestart = time();
        $attempt->timemodified = time();
        $attempt->id = $DB->insert_record('elang_attempt', $attempt);

        return $attempt;
    }

    /**
     * Submit a response to one gap within an in-progress attempt.
     *
     * Resubmitting to the same gap replaces the previous response rather
     * than creating a second row, and increments its try count. Any hint
     * level already revealed for this gap is preserved (submitting an
     * answer never resets or grants hints). The activity's language
     * (elang.language) is looked up from the attempt so callers never need
     * to pass it separately or risk it disagreeing with the activity the
     * attempt belongs to.
     *
     * @param int $attemptid The elang_attempt id
     * @param int $gapid The elang_gap id being answered
     * @param string $responsetext The learner's raw response
     * @return grading_result The evaluation outcome
     */
    public function submit_response(int $attemptid, int $gapid, string $responsetext): grading_result {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);
        if ($attempt->state !== self::STATE_INPROGRESS) {
            throw new \coding_exception('Cannot submit a response to an attempt that is not in progress');
        }

        $language = $DB->get_field('elang', 'language', ['id' => $attempt->elangid], MUST_EXIST);
        $gap = $DB->get_record('elang_gap', ['id' => $gapid], '*', MUST_EXIST);
        $gapanswers = array_values($DB->get_records('elang_gapanswer', ['gapid' => $gapid], 'sortorder ASC'));

        $result = $this->evaluator->evaluate($gap->solution, $gap->gradingalgorithm, $gapanswers, $language, $responsetext);

        $existing = $DB->get_record('elang_response', ['attemptid' => $attemptid, 'gapid' => $gapid]);
        $tries = $existing ? ((int) $existing->tries + 1) : 1;
        $hintlevel = $existing ? (int) $existing->hintlevel : 0;

        $response = $existing ?: new \stdClass();
        $response->attemptid = $attemptid;
        $response->gapid = $gapid;
        $response->responsetext = $responsetext;
        $response->resultstate = $result->resultstate;
        $response->accepted = $result->accepted ? 1 : 0;
        $response->tries = $tries;
        $response->hintlevel = $hintlevel;
        $response->timemodified = time();

        $transaction = $DB->start_delegated_transaction();

        if ($existing) {
            $DB->update_record('elang_response', $response);
        } else {
            $response->score = 0;
            $response->timecreated = time();
            $response->id = $DB->insert_record('elang_response', $response);
        }

        $this->recalculate_attempt_aggregates($attemptid);

        $transaction->allow_commit();

        return $result;
    }

    /**
     * Reveal the next hint level for a gap within an in-progress attempt.
     *
     * Levels are revealed strictly in order: this always returns the level
     * one above whatever was last revealed for this gap (0 if none yet),
     * and fails if no elang_gaphint row exists at that level — a caller
     * cannot skip ahead to a specific level. If nothing has been submitted
     * for this gap yet, an empty response row is created to hold the
     * revealed level, exactly the way submit_response() would have created
     * one; requesting a hint before answering does not implicitly submit
     * anything.
     *
     * @param int $attemptid The elang_attempt id
     * @param int $gapid The elang_gap id to reveal a hint for
     * @return \stdClass The revealed elang_gaphint record
     */
    public function request_hint(int $attemptid, int $gapid): \stdClass {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);
        if ($attempt->state !== self::STATE_INPROGRESS) {
            throw new \coding_exception('Cannot request a hint for an attempt that is not in progress');
        }

        $existing = $DB->get_record('elang_response', ['attemptid' => $attemptid, 'gapid' => $gapid]);
        $currentlevel = $existing ? (int) $existing->hintlevel : 0;
        $nextlevel = $currentlevel + 1;

        $hint = $DB->get_record('elang_gaphint', ['gapid' => $gapid, 'level' => $nextlevel]);
        if (!$hint) {
            throw new \coding_exception("No hint at level $nextlevel is defined for gap $gapid");
        }

        $response = $existing ?: new \stdClass();
        $response->attemptid = $attemptid;
        $response->gapid = $gapid;
        $response->hintlevel = $nextlevel;
        $response->timemodified = time();

        $transaction = $DB->start_delegated_transaction();

        if ($existing) {
            $DB->update_record('elang_response', $response);
        } else {
            // No answer submitted yet: the response row exists solely to
            // hold the hint level. responsetext has no schema-level default
            // (see version_manager's equivalent note for elang.language),
            // so it must be supplied explicitly.
            $response->responsetext = '';
            $response->resultstate = grading_result::RESULTSTATE_EMPTY;
            $response->accepted = 0;
            $response->tries = 0;
            $response->score = 0;
            $response->timecreated = time();
            $response->id = $DB->insert_record('elang_response', $response);
        }

        $this->recalculate_attempt_aggregates($attemptid);

        $transaction->allow_commit();

        return $hint;
    }

    /**
     * Finish an in-progress attempt.
     *
     * @param int $attemptid The elang_attempt id
     * @return \stdClass The updated elang_attempt record
     */
    public function finish_attempt(int $attemptid): \stdClass {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);
        if ($attempt->state !== self::STATE_INPROGRESS) {
            throw new \coding_exception('Cannot finish an attempt that is not in progress');
        }

        $attempt->state = self::STATE_FINISHED;
        $attempt->timefinish = time();
        $attempt->timemodified = time();
        $DB->update_record('elang_attempt', $attempt);

        return $attempt;
    }

    /**
     * Recompute an attempt's aggregate counters, and each response's own
     * score, from its responses and any hint penalties they have incurred.
     *
     * @param int $attemptid The elang_attempt id
     * @return void
     */
    private function recalculate_attempt_aggregates(int $attemptid): void {
        global $DB;

        $responses = $DB->get_records('elang_response', ['attemptid' => $attemptid]);

        $answered = 0;
        $exact = 0;
        $correct = 0;
        $hinted = 0;
        $points = 0.0;

        foreach ($responses as $response) {
            if ($response->resultstate !== grading_result::RESULTSTATE_EMPTY) {
                $answered++;
            }
            if ($response->resultstate === grading_result::RESULTSTATE_EXACT) {
                $exact++;
            }
            if ($response->accepted) {
                $correct++;
            }
            if ($response->hintlevel > 0) {
                $hinted++;
            }

            $penalty = $response->hintlevel > 0
                ? $this->get_hint_penalty((int) $response->gapid, (int) $response->hintlevel)
                : 0.0;
            $responsescore = $response->accepted ? max(0.0, 1.0 - $penalty) : 0.0;
            $points += $responsescore;

            if ((float) $response->score !== $responsescore) {
                $DB->set_field('elang_response', 'score', $responsescore, ['id' => $response->id]);
            }
        }

        $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);
        $attempt->answeredgaps = $answered;
        $attempt->exactgaps = $exact;
        $attempt->correctgaps = $correct;
        $attempt->hintedgaps = $hinted;
        $attempt->score = $attempt->totalgaps > 0 ? round($points / $attempt->totalgaps, 5) : 0;
        $attempt->timemodified = time();
        $DB->update_record('elang_attempt', $attempt);
    }

    /**
     * Look up the penalty fraction for a specific gap and hint level.
     *
     * @param int $gapid The elang_gap id
     * @param int $hintlevel The hint level revealed
     * @return float The penalty (0..1), or 0.0 if no such hint level exists
     */
    private function get_hint_penalty(int $gapid, int $hintlevel): float {
        global $DB;

        $penalty = $DB->get_field('elang_gaphint', 'penalty', ['gapid' => $gapid, 'level' => $hintlevel]);

        return $penalty !== false ? (float) $penalty : 0.0;
    }
}
