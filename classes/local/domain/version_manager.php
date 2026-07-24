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
 * Manages the draft/publish lifecycle of exercise versions.
 *
 * An activity has at most one draft version at a time, where content edits
 * accumulate, and at most one published version, referenced by
 * elang.currentversionid, which is what learners attempt. Publishing never
 * modifies or removes an existing version: it creates a new published state
 * for the version being published and archives whichever version was
 * previously published. Existing attempts stay linked to the version they
 * were started on (see elang_attempt.versionid) and are therefore never
 * affected by a later publish.
 *
 * Concurrency: creating a draft and publishing are both serialised per
 * activity through a Moodle lock (see with_activity_lock()), in addition to
 * the delegated transaction each already used or now uses — a plain
 * transaction alone does not stop two concurrent requests under the default
 * READ COMMITTED isolation both reading "no draft exists yet" and both
 * creating one, or both reading the same "currently published" version and
 * both ending up marked published.
 *
 * This class does not yet provide authoring operations (adding or editing
 * cues and gaps within a draft); those belong to the authoring tool (phase 4)
 * and operate directly on elang_cue/elang_gap via their own repositories.
 * This class only manages the version records themselves.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class version_manager {
    /** @var string A version being edited, not yet visible to learners. */
    public const STATUS_DRAFT = 'draft';

    /** @var string The version currently attempted by learners. */
    public const STATUS_PUBLISHED = 'published';

    /** @var string A formerly published version, superseded by a later publish. */
    public const STATUS_ARCHIVED = 'archived';

    /** @var int Seconds to wait for a per-activity lock before giving up. */
    private const LOCK_TIMEOUT = 5;

    /**
     * Return the current draft for an activity, creating one if none exists.
     *
     * @param int $elangid The activity id
     * @param int|null $userid Id to record as usermodified when a draft is created; defaults to the current user
     * @return \stdClass The draft elang_version record
     */
    public function get_or_create_draft(int $elangid, ?int $userid = null): \stdClass {
        global $DB;

        return $this->with_activity_lock($elangid, function () use ($DB, $elangid, $userid) {
            $draft = $DB->get_record('elang_version', ['elangid' => $elangid, 'status' => self::STATUS_DRAFT]);
            if ($draft) {
                return $draft;
            }

            return $this->create_draft_locked($elangid, $userid);
        });
    }

    /**
     * Create a new draft version for an activity.
     *
     * Always creates a new row; callers that want to reuse an existing draft
     * should use get_or_create_draft() instead.
     *
     * @param int $elangid The activity id
     * @param int|null $userid Id to record as usermodified; defaults to the current user
     * @return \stdClass The created elang_version record, including its new id
     */
    public function create_draft(int $elangid, ?int $userid = null): \stdClass {
        return $this->with_activity_lock($elangid, function () use ($elangid, $userid) {
            return $this->create_draft_locked($elangid, $userid);
        });
    }

    /**
     * Return the version currently published for an activity, if any.
     *
     * @param int $elangid The activity id
     * @return \stdClass|null The published elang_version record, or null if nothing is published yet
     */
    public function get_published(int $elangid): ?\stdClass {
        global $DB;

        $currentversionid = $DB->get_field('elang', 'currentversionid', ['id' => $elangid], MUST_EXIST);
        if (empty($currentversionid)) {
            return null;
        }

        $version = $DB->get_record('elang_version', ['id' => $currentversionid]);

        return $version ?: null;
    }

    /**
     * Publish a version: mark it published, archive whichever version was
     * previously published for the same activity, and point elang.currentversionid
     * at it.
     *
     * Refuses to publish a version that is not currently a draft (already
     * published, already archived, or otherwise) — publishing is a one-way
     * transition per version, not an operation that can be repeated or run
     * against an unexpected state.
     *
     * @param int $versionid The elang_version id to publish
     * @param int|null $userid Id to record as usermodified; defaults to the current user
     * @return \stdClass The published elang_version record
     */
    public function publish(int $versionid, ?int $userid = null): \stdClass {
        global $DB, $USER;

        $version = $DB->get_record('elang_version', ['id' => $versionid], '*', MUST_EXIST);

        return $this->with_activity_lock((int) $version->elangid, function () use ($DB, $USER, $versionid, $userid) {
            $transaction = $DB->start_delegated_transaction();

            $version = $DB->get_record('elang_version', ['id' => $versionid], '*', MUST_EXIST);
            if ($version->status !== self::STATUS_DRAFT) {
                throw new \coding_exception('Only a draft version can be published');
            }

            $previous = $this->get_published($version->elangid);
            if ($previous && (int) $previous->id !== (int) $version->id) {
                $DB->set_field('elang_version', 'status', self::STATUS_ARCHIVED, ['id' => $previous->id]);
            }

            $version->status = self::STATUS_PUBLISHED;
            $version->contenthash = $this->compute_content_hash($versionid);
            $version->usermodified = $userid ?? (int) $USER->id;
            $DB->update_record('elang_version', $version);

            $DB->set_field('elang', 'currentversionid', $version->id, ['id' => $version->elangid]);

            $transaction->allow_commit();

            return $version;
        });
    }

    /**
     * Compute a deterministic content hash over a version's cues, gaps,
     * accepted answers and grading algorithms.
     *
     * Used as the cache key for rendered worksheets and player payloads
     * (see Blueprint chapter 12/16); intentionally excludes elang_gaphint
     * rows and timestamps, since neither affects what a learner is shown to
     * solve or how a response is scored against the gap itself. Does
     * include maxlength, linkurl and transcriptformat — all three affect
     * either what is rendered or how a response is validated, and their
     * earlier absence from the hash meant a change to any of them would not
     * have invalidated a cached worksheet or player payload.
     *
     * Loads cues, gaps and answers in three queries total (one per table,
     * scoped by IN-lists derived from the previous query's ids) rather than
     * one query per cue and one per gap, which used to make this method's
     * cost grow with the number of cues in the version rather than being
     * closer to constant in the number of queries.
     *
     * @param int $versionid The elang_version id
     * @return string A SHA-256 hash of the normalised content
     */
    public function compute_content_hash(int $versionid): string {
        global $DB;

        $cues = $DB->get_records('elang_cue', ['versionid' => $versionid], 'sortorder ASC');
        if (empty($cues)) {
            return hash('sha256', '');
        }

        $cueids = array_keys($cues);
        [$cueinsql, $cueinparams] = $DB->get_in_or_equal($cueids);
        $gaps = $DB->get_records_select('elang_gap', "cueid $cueinsql", $cueinparams, 'cueid ASC, sortorder ASC');

        $gapsbycue = [];
        foreach ($gaps as $gap) {
            $gapsbycue[$gap->cueid][] = $gap;
        }

        $answersbygap = [];
        if (!empty($gaps)) {
            $gapids = array_keys($gaps);
            [$gapinsql, $gapinparams] = $DB->get_in_or_equal($gapids);
            $answers = $DB->get_records_select(
                'elang_gapanswer',
                "gapid $gapinsql",
                $gapinparams,
                'gapid ASC, sortorder ASC'
            );
            foreach ($answers as $answer) {
                $answersbygap[$answer->gapid][] = $answer;
            }
        }

        $cueparts = [];
        foreach ($cues as $cue) {
            $gapparts = [];
            foreach ($gapsbycue[$cue->id] ?? [] as $gap) {
                $answerparts = [];
                foreach ($answersbygap[$gap->id] ?? [] as $answer) {
                    $answerparts[] = $answer->answer . '|' . $answer->isregex;
                }

                $gapparts[] = implode(',', [
                    $gap->gapkey,
                    $gap->charstart,
                    $gap->charlength,
                    $gap->solution,
                    $gap->gradingalgorithm,
                    (string) $gap->maxlength,
                    (string) $gap->linkurl,
                    implode(';', $answerparts),
                ]);
            }

            $cueparts[] = implode(',', [
                $cue->cuekey,
                $cue->starttime,
                $cue->endtime,
                $cue->transcript,
                (string) $cue->transcriptformat,
                implode('|', $gapparts),
            ]);
        }

        return hash('sha256', implode("\n", $cueparts));
    }

    /**
     * Create a new draft version for an activity, assuming the caller
     * already holds the activity's lock.
     *
     * @param int $elangid The activity id
     * @param int|null $userid Id to record as usermodified; defaults to the current user
     * @return \stdClass The created elang_version record, including its new id
     */
    private function create_draft_locked(int $elangid, ?int $userid = null): \stdClass {
        global $DB, $USER;

        $transaction = $DB->start_delegated_transaction();

        $nextnumber = (int) $DB->get_field_sql(
            'SELECT COALESCE(MAX(versionnumber), 0) + 1 FROM {elang_version} WHERE elangid = ?',
            [$elangid]
        );

        $draft = new \stdClass();
        $draft->elangid = $elangid;
        $draft->versionnumber = $nextnumber;
        $draft->status = self::STATUS_DRAFT;
        $draft->contenthash = '';
        $draft->usermodified = $userid ?? (int) $USER->id;
        $draft->timecreated = time();
        $draft->id = $DB->insert_record('elang_version', $draft);

        $transaction->allow_commit();

        return $draft;
    }

    /**
     * Run a callback while holding a Moodle lock scoped to one activity's
     * version lifecycle (draft creation and publishing), releasing it
     * afterwards regardless of outcome.
     *
     * @param int $elangid The activity id
     * @param callable $callback The critical section to run while the lock is held
     * @return mixed Whatever $callback returns
     */
    private function with_activity_lock(int $elangid, callable $callback) {
        $lockfactory = \core\lock\lock_config::get_lock_factory('mod_elang');
        $lock = $lockfactory->get_lock("version_lifecycle_{$elangid}", self::LOCK_TIMEOUT);
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
