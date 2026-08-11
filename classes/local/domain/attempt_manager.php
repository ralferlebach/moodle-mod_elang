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
 * Concurrency: every state-dependent read that a write depends on (an
 * existing in-progress attempt, an existing response row, the current hint
 * level) happens inside both a per-resource Moodle lock and the delegated
 * transaction that follows it, not before either. Two concurrent calls for
 * the same attempt/gap therefore serialise on the lock rather than racing to
 * read the same "nothing exists yet" state and both trying to create it (see
 * the technical review that prompted this, 2026-07-24, findings P0-04/P0-05).
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

    /** @var int Seconds to wait for a per-resource lock before giving up. */
    private const LOCK_TIMEOUT = 5;

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
     * Also verifies that $versionid actually belongs to $elangid and is
     * currently published — a caller (External Function, CLI script, task)
     * could otherwise start an attempt against an unrelated or draft
     * version purely by supplying a guessed or stale id.
     *
     * @param int $elangid The activity id
     * @param int $userid The learner's user id
     * @param int $versionid The published elang_version id being attempted
     * @return \stdClass The elang_attempt record, including its id
     */
    public function start_attempt(int $elangid, int $userid, int $versionid): \stdClass {
        global $DB;

        return $this->with_lock("attempt_start_{$elangid}_{$userid}", function () use ($DB, $elangid, $userid, $versionid) {
            $transaction = $DB->start_delegated_transaction();

            $existing = $DB->get_record('elang_attempt', [
                'elangid' => $elangid,
                'userid' => $userid,
                'state' => self::STATE_INPROGRESS,
            ]);
            if ($existing) {
                $transaction->allow_commit();
                return $existing;
            }

            $version = $DB->get_record('elang_version', ['id' => $versionid], '*', MUST_EXIST);
            if ((int) $version->elangid !== $elangid) {
                throw new \coding_exception('Version does not belong to this activity');
            }
            if ($version->status !== version_manager::STATUS_PUBLISHED) {
                throw new \coding_exception('Cannot start an attempt against a version that is not published');
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

            $transaction->allow_commit();

            return $attempt;
        });
    }

    /**
     * Submit a response to one gap within an in-progress attempt.
     *
     * Resubmitting to the same gap replaces the previous response rather
     * than creating a second row, and increments its try count. Any hint
     * level already revealed for this gap is preserved (submitting an
     * answer never resets or grants hints). The content language and
     * Jaro-similarity threshold are read from the attempt's pinned version
     * (elang_version.language / .jarothreshold), not from the activity, so
     * grading an in-progress attempt stays stable even if the activity's
     * settings are later edited and a new version is published.
     *
     * Also verifies that $gapid actually belongs to the attempt's version —
     * the External Function layer checks this too (classes/external/
     * submit_response.php), but that must not be the only place this is
     * enforced: any other caller reaching this method directly (a task, a
     * CLI script, a future importer) would otherwise be able to record a
     * response against a gap from an unrelated version.
     *
     * Optimistic-concurrency retry guard: the caller may pass the tries count
     * it last saw for this gap in $expectedtries. If the stored count has
     * already moved past it, a prior request committed and this is a
     * lost-response retry (or stale duplicate), so the stored outcome is
     * returned without counting another try; a count ahead of the server is
     * rejected as a stale client. The check runs here, inside the write lock,
     * so two genuinely concurrent retries cannot both read the same old state
     * and each count a try. $expectedtries of -1 (the default) submits
     * unconditionally.
     *
     * @param int $attemptid The elang_attempt id
     * @param int $gapid The elang_gap id being answered
     * @param string $responsetext The learner's raw response
     * @param int $expectedtries The tries count the caller last saw, or -1 to submit unconditionally
     * @return grading_result The evaluation outcome
     */
    public function submit_response(
        int $attemptid,
        int $gapid,
        string $responsetext,
        int $expectedtries = -1
    ): grading_result {
        global $DB;

        return $this->with_lock(
            "attempt_write_{$attemptid}",
            function () use ($DB, $attemptid, $gapid, $responsetext, $expectedtries) {
                $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);
                if ($attempt->state !== self::STATE_INPROGRESS) {
                    throw new \moodle_exception('error:attemptnotinprogress', 'mod_elang');
                }

                $existing = $DB->get_record('elang_response', ['attemptid' => $attemptid, 'gapid' => $gapid]);
                $currenttries = $existing ? (int) $existing->tries : 0;

                // Optimistic-concurrency retry guard, atomic under the write lock.
                if ($expectedtries >= 0 && $expectedtries < $currenttries) {
                    // A prior submission already committed: replay its stored
                    // outcome without counting another try.
                    return new grading_result($existing->resultstate, (bool) $existing->accepted);
                }
                if ($expectedtries > $currenttries) {
                    // The caller claims more tries than exist: its view is ahead
                    // of the server, which should be impossible. Make it refetch.
                    throw new \moodle_exception('error:staleattemptstate', 'mod_elang');
                }

                $transaction = $DB->start_delegated_transaction();

                $version = $DB->get_record('elang_version', ['id' => $attempt->versionid], '*', MUST_EXIST);
                $gap = $DB->get_record('elang_gap', ['id' => $gapid], '*', MUST_EXIST);
                $cue = $DB->get_record('elang_cue', ['id' => $gap->cueid], '*', MUST_EXIST);
                if ((int) $cue->versionid !== (int) $attempt->versionid) {
                    throw new \moodle_exception('error:gapnotinattemptversion', 'mod_elang');
                }

                $gapanswers = array_values($DB->get_records('elang_gapanswer', ['gapid' => $gapid], 'sortorder ASC'));

                $result = $this->evaluator->evaluate(
                    $gap->solution,
                    $gap->gradingalgorithm,
                    $gapanswers,
                    $version->language,
                    $responsetext,
                    (float) $version->jarothreshold
                );

                $hintlevel = $existing ? (int) $existing->hintlevel : 0;

                $response = $existing ?: new \stdClass();
                $response->attemptid = $attemptid;
                $response->gapid = $gapid;
                $response->responsetext = $responsetext;
                $response->resultstate = $result->resultstate;
                $response->accepted = $result->accepted ? 1 : 0;
                $response->tries = $currenttries + 1;
                $response->hintlevel = $hintlevel;
                $response->timemodified = time();

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
        );
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
     * Also verifies that $gapid belongs to the attempt's version, for the
     * same reason submit_response() does — see its docblock.
     *
     * Optimistic-concurrency retry guard: the caller may pass the hint level
     * it last saw revealed in $expectedlevel. The only benign disagreement is
     * being exactly one level behind — the previous reveal committed but its
     * response was lost — in which case the hint already revealed at the
     * current level is replayed without advancing (and re-penalising) again.
     * Any other disagreement is a stale client and is rejected. The check runs
     * here, inside the write lock, so two genuinely concurrent retries cannot
     * both advance. $expectedlevel of -1 (the default) reveals unconditionally.
     *
     * @param int $attemptid The elang_attempt id
     * @param int $gapid The elang_gap id to reveal a hint for
     * @param int $expectedlevel The hint level the caller last saw, or -1 to reveal unconditionally
     * @return \stdClass The revealed elang_gaphint record
     */
    public function request_hint(int $attemptid, int $gapid, int $expectedlevel = -1): \stdClass {
        global $DB;

        return $this->with_lock(
            "attempt_write_{$attemptid}",
            function () use ($DB, $attemptid, $gapid, $expectedlevel) {
                $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);
                if ($attempt->state !== self::STATE_INPROGRESS) {
                    throw new \moodle_exception('error:attemptnotinprogress', 'mod_elang');
                }

                $gap = $DB->get_record('elang_gap', ['id' => $gapid], '*', MUST_EXIST);
                $cue = $DB->get_record('elang_cue', ['id' => $gap->cueid], '*', MUST_EXIST);
                if ((int) $cue->versionid !== (int) $attempt->versionid) {
                    throw new \moodle_exception('error:gapnotinattemptversion', 'mod_elang');
                }

                $existing = $DB->get_record('elang_response', ['attemptid' => $attemptid, 'gapid' => $gapid]);
                $currentlevel = $existing ? (int) $existing->hintlevel : 0;

                // Optimistic-concurrency retry guard, atomic under the write lock.
                if ($expectedlevel >= 0 && $expectedlevel !== $currentlevel) {
                    if ($expectedlevel === $currentlevel - 1 && $currentlevel >= 1) {
                        // Lost-response replay: return the hint already revealed
                        // at the current level without advancing or re-penalising.
                        return $DB->get_record(
                            'elang_gaphint',
                            ['gapid' => $gapid, 'level' => $currentlevel],
                            '*',
                            MUST_EXIST
                        );
                    }
                    throw new \moodle_exception('error:staleattemptstate', 'mod_elang');
                }

                $nextlevel = $currentlevel + 1;

                $hint = $DB->get_record('elang_gaphint', ['gapid' => $gapid, 'level' => $nextlevel]);
                if (!$hint) {
                    throw new \moodle_exception('error:nomorehints', 'mod_elang');
                }

                $transaction = $DB->start_delegated_transaction();

                $response = $existing ?: new \stdClass();
                $response->attemptid = $attemptid;
                $response->gapid = $gapid;
                $response->hintlevel = $nextlevel;
                $response->timemodified = time();

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
        );
    }

    /**
     * Finish an in-progress attempt.
     *
     * Idempotent: finishing an attempt that is already finished returns the
     * existing finished record unchanged rather than throwing, so that a
     * network retry of the same request (the caller never learned whether
     * the first call actually committed) succeeds instead of surfacing a
     * spurious error. Only a genuinely different state (for example
     * "abandoned") is rejected.
     *
     * @param int $attemptid The elang_attempt id
     * @return \stdClass The updated (or already-finished) elang_attempt record
     */
    public function finish_attempt(int $attemptid): \stdClass {
        global $DB;

        return $this->with_lock("attempt_write_{$attemptid}", function () use ($DB, $attemptid) {
            $transaction = $DB->start_delegated_transaction();

            $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);

            if ($attempt->state === self::STATE_FINISHED) {
                $transaction->allow_commit();
                return $attempt;
            }
            if ($attempt->state !== self::STATE_INPROGRESS) {
                throw new \moodle_exception('error:attemptnotinprogress', 'mod_elang');
            }

            $attempt->state = self::STATE_FINISHED;
            $attempt->timefinish = time();
            $attempt->timemodified = time();
            $DB->update_record('elang_attempt', $attempt);

            $transaction->allow_commit();

            return $attempt;
        });
    }

    /**
     * Return the highest score among an activity's finished attempts for one user.
     *
     * Used by lib.php's elang_update_grades() to compute the gradebook grade;
     * kept here rather than in lib.php because it is a pure query over
     * elang_attempt, the same kind of responsibility as every other method
     * on this class, and because it is independently testable without a
     * Moodle gradebook bootstrap.
     *
     * @param int $elangid The activity id
     * @param int $userid The user id
     * @return float|null The highest score (0..1), or null if the user has no finished attempts
     */
    public function get_best_score(int $elangid, int $userid): ?float {
        global $DB;

        $best = $DB->get_field_sql(
            'SELECT MAX(score) FROM {elang_attempt} WHERE elangid = ? AND userid = ? AND state = ?',
            [$elangid, $userid, self::STATE_FINISHED]
        );

        return $best !== null ? (float) $best : null;
    }

    /**
     * Permanently delete one attempt and all of its responses.
     *
     * Wrapped in a transaction so a half-deleted attempt (responses gone but
     * the attempt row left behind, or vice versa) can never result from a
     * mid-operation failure. The caller is responsible for the capability check
     * (mod/elang:deleteattempts) and for pushing the recomputed gradebook grade
     * afterwards, since this class deliberately performs no gradebook bootstrap.
     *
     * @param int $attemptid The elang_attempt id
     * @return \stdClass The deleted attempt record, so the caller can regrade its owner
     */
    public function delete_attempt(int $attemptid): \stdClass {
        global $DB;

        return $this->with_lock('attempt:' . $attemptid, function () use ($DB, $attemptid): \stdClass {
            $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);

            $transaction = $DB->start_delegated_transaction();
            $DB->delete_records('elang_response', ['attemptid' => $attemptid]);
            $DB->delete_records('elang_attempt', ['id' => $attemptid]);
            $transaction->allow_commit();

            return $attempt;
        });
    }

    /**
     * Recompute an attempt's aggregate counters, and each response's own
     * score, from its responses and any hint penalties they have incurred.
     *
     * Loads all hint penalties potentially needed in a single query instead
     * of one query per hinted response, since a response's hintlevel can
     * only ever be one of a handful of small integers shared across all of
     * an attempt's responses.
     *
     * @param int $attemptid The elang_attempt id
     * @return void
     */
    private function recalculate_attempt_aggregates(int $attemptid): void {
        global $DB;

        $responses = $DB->get_records('elang_response', ['attemptid' => $attemptid]);
        $penalties = $this->load_hint_penalties($responses);

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
                ? ($penalties[$response->gapid . ':' . $response->hintlevel] ?? 0.0)
                : 0.0;
            // Penalties are validated to [0, 1] when a draft is saved, so
            // 1 - penalty already lands in [0, 1]; clamp anyway so no single
            // response can ever contribute more than one point even if a stray
            // legacy value slipped through.
            $responsescore = $response->accepted ? min(1.0, max(0.0, 1.0 - $penalty)) : 0.0;
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
        $attempt->score = $attempt->totalgaps > 0 ? min(1.0, max(0.0, round($points / $attempt->totalgaps, 5))) : 0;
        $attempt->timemodified = time();
        $DB->update_record('elang_attempt', $attempt);
    }

    /**
     * Load every hint penalty potentially needed for a set of responses in
     * a single query, keyed by "gapid:level".
     *
     * @param \stdClass[] $responses elang_response rows (as returned by get_records())
     * @return array<string, float> Penalty fraction keyed by "gapid:level"
     */
    private function load_hint_penalties(array $responses): array {
        global $DB;

        $gapids = [];
        foreach ($responses as $response) {
            if ($response->hintlevel > 0) {
                $gapids[(int) $response->gapid] = true;
            }
        }
        if (empty($gapids)) {
            return [];
        }

        [$insql, $inparams] = $DB->get_in_or_equal(array_keys($gapids));
        $hints = $DB->get_records_select('elang_gaphint', "gapid $insql", $inparams, '', 'id, gapid, level, penalty');

        $penalties = [];
        foreach ($hints as $hint) {
            $penalties[$hint->gapid . ':' . $hint->level] = (float) $hint->penalty;
        }

        return $penalties;
    }

    /**
     * Run a callback while holding a Moodle lock on a named resource,
     * releasing it afterwards regardless of outcome.
     *
     * @param string $resource Lock resource key, unique per activity/user or per attempt as appropriate
     * @param callable $callback The critical section to run while the lock is held
     * @return mixed Whatever $callback returns
     */
    private function with_lock(string $resource, callable $callback) {
        $lockfactory = \core\lock\lock_config::get_lock_factory('mod_elang');
        $lock = $lockfactory->get_lock($resource, self::LOCK_TIMEOUT);
        if (!$lock) {
            throw new \moodle_exception('error:couldnotobtainlock', 'mod_elang');
        }

        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }
}
