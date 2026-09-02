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
 * The one-draft-per-activity invariant is maintained here rather than by a
 * database constraint: a partial unique index (unique elangid only where
 * status = draft) is not portable across PostgreSQL and MariaDB through XMLDB,
 * and a full unique (elangid, status) would wrongly forbid the several archived
 * versions an activity accumulates. Instead, get_or_create_draft() is the entry
 * point authoring uses: under the per-activity lock it reuses the existing draft
 * and only creates one when none exists. create_draft() is a lower-level
 * primitive that always inserts a new draft row and is used where no draft can
 * exist yet (V1 migration seeding the first draft, and tests); authoring flows
 * must not call it directly. As a safety net, get_or_create_draft() tolerates a
 * stray second draft (from a hand-crafted call) by returning the most recent one
 * rather than failing.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class version_manager {
    use transaction_trait;

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
            // Return the most recent draft if one exists. get_records(..., 1) is
            // used rather than get_record() so a stray second draft (only
            // reachable by calling create_draft() directly, against this class's
            // contract) yields the newest draft instead of throwing.
            $drafts = $DB->get_records(
                'elang_version',
                ['elangid' => $elangid, 'status' => self::STATUS_DRAFT],
                'id DESC',
                '*',
                0,
                1
            );
            if (!empty($drafts)) {
                return reset($drafts);
            }

            return $this->create_draft_locked($elangid, $userid);
        });
    }

    /**
     * Create a new draft version for an activity.
     *
     * Low-level entry point. It always inserts a new row and does NOT enforce
     * this class's "at most one draft per activity" invariant, so ordinary
     * application code must use get_or_create_draft() instead. It exists for the
     * two callers that legitimately need an unconditional draft: the one-way V1
     * migration, which creates the very first draft of a freshly migrated
     * activity, and test fixtures.
     *
     * Not part of the stable API; ordinary callers use get_or_create_draft().
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
     * Decide whether a user may receive a file from a version's media or poster area.
     *
     * Managers (mod/elang:manage) may fetch any version of this activity — draft,
     * published or archived — so the authoring tool can preview unpublished media.
     * A learner may fetch the published version's files, and an archived version's
     * files only while one of their own attempts is pinned to that exact version,
     * so an in-progress or finished attempt keeps rendering its original medium
     * after a newer version is published. Draft media is never served to a
     * non-manager, mirroring the version protection the attempt-bound read API
     * (get_attempt_exercise) enforces, so an unpublished upload cannot leak
     * through a guessed pluginfile URL. A version that does not belong to the
     * given activity is rejected outright.
     *
     * @param int $versionid The elang_version id taken from the file path
     * @param int $elangid The activity the file must belong to
     * @param \context $context The module context, for the capability check
     * @param int $userid The user requesting the file
     * @return bool True if the user is entitled to the file, false otherwise
     */
    public static function user_can_access_version_file(
        int $versionid,
        int $elangid,
        \context $context,
        int $userid
    ): bool {
        global $DB;

        $status = $DB->get_field('elang_version', 'status', ['id' => $versionid, 'elangid' => $elangid]);
        if ($status === false) {
            return false;
        }
        if (has_capability('mod/elang:manage', $context, $userid)) {
            return true;
        }
        if ($status === self::STATUS_PUBLISHED) {
            return true;
        }
        if ($status === self::STATUS_ARCHIVED) {
            return $DB->record_exists('elang_attempt', [
                'elangid' => $elangid,
                'userid' => $userid,
                'versionid' => $versionid,
            ]);
        }

        return false;
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
     * When $validate is true the draft's content is checked first (see
     * version_validator) and publishing is refused with the collected problems
     * if it is not coherent. The authoring layer passes true; V1 migration
     * leaves it false, migrating imperfect legacy data as-is and reporting
     * issues through v1_verifier instead.
     *
     * @param int $versionid The elang_version id to publish
     * @param int|null $userid Id to record as usermodified; defaults to the current user
     * @param bool $validate Whether to reject the draft if its content is not publishable
     * @return \stdClass The published elang_version record
     */
    public function publish(int $versionid, ?int $userid = null, bool $validate = false): \stdClass {
        global $DB, $USER;

        $version = $DB->get_record('elang_version', ['id' => $versionid], '*', MUST_EXIST);

        return $this->with_activity_lock((int) $version->elangid, function () use ($DB, $USER, $versionid, $userid, $validate) {
            // Publishing archives the previous version, promotes this one and
            // repoints the activity. Half of that is a broken activity: an
            // archived previous version with nothing published in its place
            // leaves learners with no exercise at all.
            return $this->in_transaction(function () use ($DB, $USER, $versionid, $userid, $validate) {
                $version = $DB->get_record('elang_version', ['id' => $versionid], '*', MUST_EXIST);
                if ($version->status !== self::STATUS_DRAFT) {
                    throw new \coding_exception('Only a draft version can be published');
                }

                if ($validate) {
                    $problems = (new version_validator())->validate($versionid);
                    if (!empty($problems)) {
                        throw new \moodle_exception(
                            'error:versionnotpublishable',
                            'mod_elang',
                            '',
                            implode(' ', $problems)
                        );
                    }
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

                return $version;
            });
        });
    }

    /**
     * Replace a draft version's content wholesale with the supplied cues,
     * gaps, accepted answers and hints, and bump its revision counter.
     *
     * The authoring editor sends the draft's full current state, so the
     * existing content is deleted and re-inserted inside one transaction under
     * the activity lock. Only a draft can be edited; a published or archived
     * version is immutable. The caller may pass the revision it last saw in
     * $expectedrevision (a per-draft optimistic-concurrency token): if the
     * stored revision has moved on, another save committed in the meantime and
     * this one is refused so the editor reloads rather than silently clobbering
     * the other change. $expectedrevision of -1 (the default) saves
     * unconditionally. Version-stable cuekey/gapkey identities are taken as
     * given from the payload; the editor generates them for new content and
     * echoes them back for existing content. No content validation happens
     * here — a half-finished draft is a legitimate save; validity is only
     * required at publish time (see publish() / version_validator).
     *
     * @param int $versionid The draft elang_version id to overwrite
     * @param array $cues The full cue list, each with nested gaps/answers/hints
     * @param int $expectedrevision The revision the caller last saw, or -1 to save unconditionally
     * @return \stdClass The updated elang_version record, with its new revision
     */
    public function save_draft_content(int $versionid, array $cues, int $expectedrevision = -1): \stdClass {
        global $DB, $USER;

        $version = $DB->get_record('elang_version', ['id' => $versionid], '*', MUST_EXIST);

        return $this->with_activity_lock(
            (int) $version->elangid,
            function () use ($DB, $USER, $versionid, $cues, $expectedrevision) {
                return $this->in_transaction(function () use ($DB, $USER, $versionid, $cues, $expectedrevision) {
                    $version = $DB->get_record('elang_version', ['id' => $versionid], '*', MUST_EXIST);
                    if ($version->status !== self::STATUS_DRAFT) {
                        throw new \moodle_exception('error:versionnotadraft', 'mod_elang');
                    }
                    if ($expectedrevision >= 0 && (int) $version->revision !== $expectedrevision) {
                        throw new \moodle_exception('error:draftrevisionmismatch', 'mod_elang');
                    }

                    // Validated before anything is removed. The delete wipes the
                    // draft's whole content, so a payload rejected afterwards
                    // would leave the author with neither their old work nor
                    // their new — and a rejected payload is the ordinary case
                    // here, not an exceptional one. The transaction remains the
                    // backstop for a genuine database failure during the insert.
                    self::validate_content_shape($cues);

                    $this->delete_version_content($versionid);
                    $this->insert_version_content($versionid, $cues);

                    $version->revision = (int) $version->revision + 1;
                    $version->usermodified = (int) $USER->id;
                    $DB->update_record('elang_version', $version);

                    return $version;
                });
            }
        );
    }

    /**
     * Delete every cue, gap, accepted answer and hint belonging to a version,
     * children first so no foreign key is ever left dangling. Used when a draft
     * is overwritten; a draft has no attempts, so no learner response can
     * reference the rows being removed.
     *
     * @param int $versionid The version whose content is removed
     * @return void No return value.
     */
    private function delete_version_content(int $versionid): void {
        global $DB;

        $cueids = $DB->get_fieldset_select('elang_cue', 'id', 'versionid = ?', [$versionid]);
        if (empty($cueids)) {
            return;
        }

        [$cuein, $cueparams] = $DB->get_in_or_equal($cueids);
        $gapids = $DB->get_fieldset_select('elang_gap', 'id', "cueid $cuein", $cueparams);
        if (!empty($gapids)) {
            [$gapin, $gapparams] = $DB->get_in_or_equal($gapids);
            $DB->delete_records_select('elang_gapanswer', "gapid $gapin", $gapparams);
            $DB->delete_records_select('elang_gaphint', "gapid $gapin", $gapparams);
        }
        $DB->delete_records_select('elang_gap', "cueid $cuein", $cueparams);
        $DB->delete_records('elang_cue', ['versionid' => $versionid]);
    }

    /**
     * Insert a full cue list (with nested gaps, accepted answers and hints)
     * into a version from the shaped array the external layer provides. A gap's
     * maxlength of 0 and empty linkurl are stored as NULL, matching how the
     * rest of the plugin represents "unset" for those optional columns.
     *
     * @param int $versionid The version the content is attached to
     * @param array $cues The cue list, each with nested gaps/answers/hints
     * @return void No return value.
     */
    private function insert_version_content(int $versionid, array $cues): void {
        global $DB;

        self::validate_content_shape($cues);

        foreach ($cues as $cue) {
            $cueid = $DB->insert_record('elang_cue', (object) [
                'versionid' => $versionid,
                'cuekey' => $cue['cuekey'],
                'sortorder' => $cue['sortorder'],
                'starttime' => $cue['starttime'],
                'endtime' => $cue['endtime'],
                'transcript' => $cue['transcript'],
                'transcriptformat' => $cue['transcriptformat'],
            ]);

            foreach ($cue['gaps'] as $gap) {
                $gapid = $DB->insert_record('elang_gap', (object) [
                    'cueid' => $cueid,
                    'gapkey' => $gap['gapkey'],
                    'sortorder' => $gap['sortorder'],
                    'charstart' => $gap['charstart'],
                    'charlength' => $gap['charlength'],
                    'solution' => $gap['solution'],
                    'gradingalgorithm' => $gap['gradingalgorithm'],
                    'maxlength' => (int) $gap['maxlength'] > 0 ? (int) $gap['maxlength'] : null,
                    'linkurl' => $gap['linkurl'] !== '' ? $gap['linkurl'] : null,
                ]);

                foreach ($gap['answers'] as $answer) {
                    $DB->insert_record('elang_gapanswer', (object) [
                        'gapid' => $gapid,
                        'sortorder' => $answer['sortorder'],
                        'answer' => $answer['answer'],
                        'isregex' => $answer['isregex'],
                    ]);
                }

                foreach ($gap['hints'] as $hint) {
                    $DB->insert_record('elang_gaphint', (object) [
                        'gapid' => $gapid,
                        'level' => $hint['level'],
                        'hinttype' => $hint['hinttype'],
                        'hinttext' => $hint['hinttext'],
                        'penalty' => $hint['penalty'],
                        'timecreated' => time(),
                    ]);
                }
            }
        }
    }

    /**
     * Enforce the domain invariants of an incoming cue list before any of it is
     * written. The external layer's PARAM_* filters only guarantee scalar
     * types, so a hand-crafted request could otherwise store a hint penalty
     * outside [0, 1] (which would push a response score above 1), an isregex
     * flag other than 0 or 1, an uncompilable regex variant that never matches,
     * or an unknown hint type or grading algorithm. Rejecting them here keeps
     * the stored content within the range the grading engine is defined for.
     *
     * @param array $cues The cue list, each with nested gaps/answers/hints
     * @return void No return value.
     * @throws \moodle_exception When any value falls outside its allowed domain
     */
    private static function validate_content_shape(array $cues): void {
        $knownalgorithms = [
            \mod_elang\local\grading\answer_evaluator::ALGORITHM_EXACT,
            \mod_elang\local\grading\answer_evaluator::ALGORITHM_WORDRECOGNIZED,
        ];
        $knownhinttypes = ['text', 'firstletter', 'wordlength', 'partial', 'solution', 'translation'];

        // The identity keys and hint levels are backed by UNIQUE database
        // indexes (versionid-cuekey, cueid-gapkey, gapid-level). Without these
        // guards a payload with a repeated key or level would surface as a raw
        // dml_write_exception mid-insert; catching it here turns it into a clear
        // message and keeps the save transaction from aborting on a DB error.
        $seencuekeys = [];
        foreach ($cues as $cue) {
            $cuekey = (string) $cue['cuekey'];
            if (isset($seencuekeys[$cuekey])) {
                throw new \moodle_exception('error:duplicatecuekey', 'mod_elang', '', $cuekey);
            }
            $seencuekeys[$cuekey] = true;

            $seengapkeys = [];
            foreach ($cue['gaps'] ?? [] as $gap) {
                $gapkey = (string) $gap['gapkey'];
                if (isset($seengapkeys[$gapkey])) {
                    throw new \moodle_exception('error:duplicategapkey', 'mod_elang', '', $gapkey);
                }
                $seengapkeys[$gapkey] = true;

                if ((int) $gap['charstart'] < 0 || (int) $gap['charlength'] < 0) {
                    throw new \moodle_exception('error:negativegapoffset', 'mod_elang');
                }

                if (!in_array($gap['gradingalgorithm'], $knownalgorithms, true)) {
                    throw new \moodle_exception(
                        'error:invalidgradingalgorithm',
                        'mod_elang',
                        '',
                        $gap['gradingalgorithm']
                    );
                }

                foreach ($gap['answers'] ?? [] as $answer) {
                    $isregex = (int) $answer['isregex'];
                    if ($isregex !== 0 && $isregex !== 1) {
                        throw new \moodle_exception('error:invalidisregex', 'mod_elang');
                    }
                    if (
                        $isregex === 1
                        && !\mod_elang\local\grading\answer_evaluator::is_valid_regex((string) $answer['answer'])
                    ) {
                        throw new \moodle_exception('error:invalidregexpattern', 'mod_elang', '', $answer['answer']);
                    }
                }

                $seenlevels = [];
                foreach ($gap['hints'] ?? [] as $hint) {
                    $level = (int) $hint['level'];
                    if (isset($seenlevels[$level])) {
                        throw new \moodle_exception('error:duplicatehintlevel', 'mod_elang', '', $level);
                    }
                    $seenlevels[$level] = true;

                    $penalty = (float) $hint['penalty'];
                    if (!is_finite($penalty) || $penalty < 0.0 || $penalty > 1.0) {
                        throw new \moodle_exception('error:invalidpenalty', 'mod_elang');
                    }
                    if (!in_array($hint['hinttype'], $knownhinttypes, true)) {
                        throw new \moodle_exception('error:invalidhinttype', 'mod_elang', '', $hint['hinttype']);
                    }
                }
            }
        }
    }

    /**
     * Set a draft version's medium: file (uploaded video/audio plus optional
     * poster), a direct url, an embeddable provider reference, or none.
     *
     * Only a draft can be changed. The medium is versioned like all other
     * content, so this writes to the version's own file areas (itemid = the
     * version id) and media columns, leaving other versions and their
     * in-progress attempts untouched. Whichever columns and file areas do not
     * belong to the chosen kind are cleared, so switching from a file to a url
     * (or to no medium) never leaves a stale upload behind. For file media the
     * caller supplies the ids of prepared draft file areas, which are saved
     * into the version's 'media' and 'poster' areas; the version is only marked
     * file-kind if a media file actually landed.
     *
     * @param int $versionid The draft elang_version id
     * @param array $media The medium: kind, url, provider, providerref, mime,
     *        duration, and the mediadraftitemid/posterdraftitemid draft areas
     * @return \stdClass The updated elang_version record
     */
    public function set_draft_media(int $versionid, array $media): \stdClass {
        global $DB;

        $kind = (string) $media['kind'];
        if (!in_array($kind, ['file', 'url', 'provider', ''], true)) {
            throw new \moodle_exception('error:invalidmediakind', 'mod_elang');
        }

        $version = $DB->get_record('elang_version', ['id' => $versionid], '*', MUST_EXIST);

        return $this->with_activity_lock(
            (int) $version->elangid,
            function () use ($DB, $versionid, $media, $kind) {
                // The stored columns and the file areas describe one medium
                // between them. A run that saved the files and then failed to
                // update the columns would leave a version claiming one kind of
                // medium while holding another.
                return $this->in_transaction(function () use ($DB, $versionid, $media, $kind) {
                    $version = $DB->get_record('elang_version', ['id' => $versionid], '*', MUST_EXIST);
                    if ($version->status !== self::STATUS_DRAFT) {
                        throw new \moodle_exception('error:versionnotadraft', 'mod_elang');
                    }

                    $cm = get_coursemodule_from_instance('elang', $version->elangid, 0, false, MUST_EXIST);
                    $contextid = (int) \context_module::instance($cm->id)->id;

                    // Start from a clean slate, then fill in only what the chosen
                    // kind needs.
                    $version->mediakind = null;
                    $version->mediaurl = null;
                    $version->mediaprovider = null;
                    $version->mediaproviderref = null;
                    $version->mediamime = ((string) ($media['mime'] ?? '')) !== '' ? $media['mime'] : null;
                    $version->mediaduration = (int) ($media['duration'] ?? 0) > 0 ? (int) $media['duration'] : null;

                    if ($kind === 'file') {
                        file_save_draft_area_files(
                            (int) ($media['mediadraftitemid'] ?? 0),
                            $contextid,
                            'mod_elang',
                            'media',
                            $versionid
                        );
                        file_save_draft_area_files(
                            (int) ($media['posterdraftitemid'] ?? 0),
                            $contextid,
                            'mod_elang',
                            'poster',
                            $versionid
                        );

                        $mediafiles = get_file_storage()->get_area_files($contextid, 'mod_elang', 'media', $versionid, 'id', false);
                        $version->mediakind = !empty($mediafiles) ? 'file' : null;
                    } else if ($kind === 'url') {
                        $this->clear_version_files($contextid, $versionid);
                        $version->mediakind = 'url';
                        $version->mediaurl = ((string) ($media['url'] ?? '')) !== '' ? $media['url'] : null;
                    } else if ($kind === 'provider') {
                        $this->clear_version_files($contextid, $versionid);
                        $version->mediakind = 'provider';
                        $version->mediaprovider = ((string) ($media['provider'] ?? '')) !== '' ? $media['provider'] : null;
                        $version->mediaproviderref = ((string) ($media['providerref'] ?? '')) !== '' ? $media['providerref'] : null;
                    } else {
                        // No medium at all: clear the files and every media column.
                        $this->clear_version_files($contextid, $versionid);
                        $version->mediamime = null;
                        $version->mediaduration = null;
                    }

                    $DB->update_record('elang_version', $version);

                    return $version;
                });
            }
        );
    }

    /**
     * Remove every media and poster file from a version's file areas. Used when
     * a version switches away from file-kind media so no stale upload remains.
     *
     * @param int $contextid The activity context id
     * @param int $versionid The version whose files (itemid) are removed
     * @return void No return value.
     */
    private function clear_version_files(int $contextid, int $versionid): void {
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'mod_elang', 'media', $versionid);
        $fs->delete_area_files($contextid, 'mod_elang', 'poster', $versionid);
    }

    /**
     * Assemble a version's full authoring content — every cue with its gaps,
     * and each gap with its accepted answers and hints, INCLUDING solutions.
     * This is the manager-facing editor view and must never be sent to a
     * learner (get_attempt_cues is the masked, attempt-bound counterpart). The
     * shape mirrors save_draft_content()'s input exactly, so the editor can
     * load, edit and save the same structure round-trip. Rows are loaded in a
     * bounded number of queries (one per table) rather than one per cue or gap.
     *
     * @param int $versionid The elang_version id to read
     * @return array A list of cues, each with nested gaps, answers and hints
     */
    public function load_version_content(int $versionid): array {
        global $DB;

        $cues = $DB->get_records('elang_cue', ['versionid' => $versionid], 'sortorder ASC, id ASC');
        if (empty($cues)) {
            return [];
        }

        [$cuein, $cueparams] = $DB->get_in_or_equal(array_keys($cues));
        $gaps = $DB->get_records_select('elang_gap', "cueid $cuein", $cueparams, 'cueid ASC, sortorder ASC, id ASC');

        $answersbygap = [];
        $hintsbygap = [];
        if (!empty($gaps)) {
            [$gapin, $gapparams] = $DB->get_in_or_equal(array_keys($gaps));

            $answers = $DB->get_records_select('elang_gapanswer', "gapid $gapin", $gapparams, 'gapid ASC, sortorder ASC, id ASC');
            foreach ($answers as $answer) {
                $answersbygap[$answer->gapid][] = [
                    'sortorder' => (int) $answer->sortorder,
                    'answer' => (string) $answer->answer,
                    'isregex' => (int) $answer->isregex,
                ];
            }

            $hints = $DB->get_records_select('elang_gaphint', "gapid $gapin", $gapparams, 'gapid ASC, level ASC, id ASC');
            foreach ($hints as $hint) {
                $hintsbygap[$hint->gapid][] = [
                    'level' => (int) $hint->level,
                    'hinttype' => (string) $hint->hinttype,
                    'hinttext' => (string) ($hint->hinttext ?? ''),
                    'penalty' => (float) $hint->penalty,
                ];
            }
        }

        $gapsbycue = [];
        foreach ($gaps as $gap) {
            $gapsbycue[$gap->cueid][] = [
                'gapkey' => (string) $gap->gapkey,
                'sortorder' => (int) $gap->sortorder,
                'charstart' => (int) $gap->charstart,
                'charlength' => (int) $gap->charlength,
                'solution' => (string) $gap->solution,
                'gradingalgorithm' => (string) $gap->gradingalgorithm,
                'maxlength' => $gap->maxlength !== null ? (int) $gap->maxlength : 0,
                'linkurl' => (string) ($gap->linkurl ?? ''),
                'answers' => $answersbygap[$gap->id] ?? [],
                'hints' => $hintsbygap[$gap->id] ?? [],
            ];
        }

        $result = [];
        foreach ($cues as $cue) {
            $result[] = [
                'cuekey' => (string) $cue->cuekey,
                'sortorder' => (int) $cue->sortorder,
                'starttime' => (int) $cue->starttime,
                'endtime' => (int) $cue->endtime,
                'transcript' => (string) $cue->transcript,
                'transcriptformat' => (int) $cue->transcriptformat,
                'gaps' => $gapsbycue[$cue->id] ?? [],
            ];
        }

        return $result;
    }

    /**
     * Compute a deterministic content hash over a version's cues, gaps,
     * accepted answers and grading algorithms.
     *
     * Used as the cache key for rendered worksheets and player payloads.
     * Hashes the JSON encoding of a single canonical structure rather than
     * fields concatenated with chosen delimiters, so content that happens to
     * contain those delimiters cannot produce an ambiguous pre-hash string
     * (two different exercises hashing alike). Includes
     * the media columns, cues, gaps, accepted answers, grading algorithms,
     * maxlength, linkurl, transcriptformat AND the gaps' hints (level, type,
     * text, penalty) — a hint change affects how a gap is solved and scored,
     * so it must invalidate the cache. The bytes of file-kind media and poster
     * images are folded in via their stored content hashes, so swapping a
     * medium changes the hash. Timestamps and row ids are deliberately
     * excluded.
     *
     * Loads cues, gaps, answers and hints in a bounded number of queries (one
     * per table, scoped by IN-lists derived from the previous query's ids)
     * rather than one query per cue or gap, so the cost stays closer to
     * constant in the number of queries rather than growing with the cue count.
     *
     * @param int $versionid The elang_version id
     * @return string A SHA-256 hash of the normalised content
     */
    public function compute_content_hash(int $versionid): string {
        global $DB;

        $media = $DB->get_record(
            'elang_version',
            ['id' => $versionid],
            'elangid, mediakind, mediaurl, mediaprovider, mediaproviderref, mediamime, mediaduration',
            MUST_EXIST
        );

        // The content hash folds in the bytes of file-kind media and poster
        // images via their stored content hashes, so a swapped video or poster
        // invalidates cached worksheets and player payloads. Resolve the
        // activity context that owns those files. The activity may have no
        // course module yet (for example a migration that inserts an elang row
        // directly, as v1_media_migrator also tolerates); with no module there
        // is no file context and hence no files to fold in.
        $cm = get_coursemodule_from_instance('elang', (int) $media->elangid, 0, false, IGNORE_MISSING);
        $contextid = $cm ? (int) \context_module::instance($cm->id)->id : 0;

        $cues = $DB->get_records('elang_cue', ['versionid' => $versionid], 'sortorder ASC, id ASC');

        $gapsbycue = [];
        $answersbygap = [];
        $hintsbygap = [];
        if (!empty($cues)) {
            [$cueinsql, $cueinparams] = $DB->get_in_or_equal(array_keys($cues));
            $gaps = $DB->get_records_select(
                'elang_gap',
                "cueid $cueinsql",
                $cueinparams,
                'cueid ASC, sortorder ASC, id ASC'
            );
            foreach ($gaps as $gap) {
                $gapsbycue[$gap->cueid][] = $gap;
            }

            if (!empty($gaps)) {
                [$gapinsql, $gapinparams] = $DB->get_in_or_equal(array_keys($gaps));
                $answers = $DB->get_records_select(
                    'elang_gapanswer',
                    "gapid $gapinsql",
                    $gapinparams,
                    'gapid ASC, sortorder ASC, id ASC'
                );
                foreach ($answers as $answer) {
                    $answersbygap[$answer->gapid][] = $answer;
                }

                $hints = $DB->get_records_select(
                    'elang_gaphint',
                    "gapid $gapinsql",
                    $gapinparams,
                    'gapid ASC, level ASC, id ASC'
                );
                foreach ($hints as $hint) {
                    $hintsbygap[$hint->gapid][] = $hint;
                }
            }
        }

        // Build a single canonical structure and hash its JSON encoding, rather
        // than concatenating fields with chosen delimiters that content could
        // itself contain and so collide. Hints are included — their type,
        // text and penalty all affect how a gap is solved and scored, so a
        // change to any of them must invalidate a cached worksheet or player
        // payload. Media columns and the media/poster files (by stored content
        // hash) are folded in for the same reason. Timestamps and row ids are
        // deliberately excluded.
        $content = [
            'media' => [
                'kind' => (string) $media->mediakind,
                'url' => (string) $media->mediaurl,
                'provider' => (string) $media->mediaprovider,
                'providerref' => (string) $media->mediaproviderref,
                'mime' => (string) $media->mediamime,
                'duration' => (string) $media->mediaduration,
                'files' => $contextid ? $this->hash_area_files($contextid, 'media', $versionid) : [],
                'poster' => $contextid ? $this->hash_area_files($contextid, 'poster', $versionid) : [],
            ],
            'cues' => [],
        ];

        foreach ($cues as $cue) {
            $cueentry = [
                'cuekey' => (string) $cue->cuekey,
                'starttime' => (int) $cue->starttime,
                'endtime' => (int) $cue->endtime,
                'transcript' => (string) $cue->transcript,
                'transcriptformat' => (int) $cue->transcriptformat,
                'gaps' => [],
            ];

            foreach ($gapsbycue[$cue->id] ?? [] as $gap) {
                $gapentry = [
                    'gapkey' => (string) $gap->gapkey,
                    'charstart' => (int) $gap->charstart,
                    'charlength' => (int) $gap->charlength,
                    'solution' => (string) $gap->solution,
                    'gradingalgorithm' => (string) $gap->gradingalgorithm,
                    'maxlength' => $gap->maxlength === null ? null : (int) $gap->maxlength,
                    'linkurl' => (string) $gap->linkurl,
                    'answers' => [],
                    'hints' => [],
                ];

                foreach ($answersbygap[$gap->id] ?? [] as $answer) {
                    $gapentry['answers'][] = [
                        'answer' => (string) $answer->answer,
                        'isregex' => (int) $answer->isregex,
                    ];
                }

                foreach ($hintsbygap[$gap->id] ?? [] as $hint) {
                    $gapentry['hints'][] = [
                        'level' => (int) $hint->level,
                        'hinttype' => (string) $hint->hinttype,
                        'hinttext' => (string) $hint->hinttext,
                        'penalty' => (float) $hint->penalty,
                    ];
                }

                $cueentry['gaps'][] = $gapentry;
            }

            $content['cues'][] = $cueentry;
        }

        return hash('sha256', json_encode($content));
    }

    /**
     * Normalise one file area of a version into a deterministic list of file
     * descriptors for the content hash. Each entry carries the file's path,
     * name, size and stored content hash — the bytes themselves are never
     * re-read, and directory placeholders are skipped. Ordering by path then
     * name keeps the result stable across calls and databases.
     *
     * @param int $contextid The activity context id owning the files
     * @param string $filearea The file area to read (media or poster)
     * @param int $itemid The version id used as the file itemid
     * @return array A list of [path, name, size, contenthash] associative arrays
     */
    private function hash_area_files(int $contextid, string $filearea, int $itemid): array {
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'mod_elang', $filearea, $itemid, 'filepath, filename', false);

        $list = [];
        foreach ($files as $file) {
            $list[] = [
                'path' => $file->get_filepath(),
                'name' => $file->get_filename(),
                'size' => (int) $file->get_filesize(),
                'contenthash' => $file->get_contenthash(),
            ];
        }

        return $list;
    }

    /**
     * Create a new draft version for an activity, assuming the caller
     * already holds the activity's lock.
     *
     * Copy-on-write: when the activity already has a published version, the
     * draft branches from it — its grading settings, media columns, cues,
     * gaps, accepted answers, hints and media/poster files are deep-copied so
     * that editing produces a new version that starts as a faithful copy of
     * what learners currently see, while their in-progress attempts stay on
     * the version they began. Version-stable keys (cuekey/gapkey) are
     * preserved, so the same logical cue or gap keeps its identity across
     * versions. When there is no published version yet (a brand-new activity,
     * or the first version built during V1 migration) the draft starts empty
     * and its grading settings come from the activity defaults instead.
     *
     * @param int $elangid The activity id
     * @param int|null $userid Id to record as usermodified; defaults to the current user
     * @return \stdClass The created elang_version record, including its new id
     */
    private function create_draft_locked(int $elangid, ?int $userid = null): \stdClass {
        global $DB, $USER;

        // The row, its copied content and its copied files are one draft.
        // A version created without the content it was branched from is a
        // draft an author would silently start from nothing.
        return $this->in_transaction(function () use ($DB, $USER, $elangid, $userid) {
            $nextnumber = (int) $DB->get_field_sql(
                'SELECT COALESCE(MAX(versionnumber), 0) + 1 FROM {elang_version} WHERE elangid = ?',
                [$elangid]
            );

            $source = $this->get_published($elangid);

            $draft = new \stdClass();
            $draft->elangid = $elangid;
            $draft->versionnumber = $nextnumber;
            $draft->status = self::STATUS_DRAFT;
            $draft->contenthash = '';
            $draft->revision = 1;
            if ($source) {
                // Branch from the published version: carry over its grading
                // settings and media description. The content and files are copied
                // below, after the row exists to own them.
                $draft->language = $source->language;
                $draft->jarothreshold = $source->jarothreshold;
                $draft->mediakind = $source->mediakind;
                $draft->mediaurl = $source->mediaurl;
                $draft->mediaprovider = $source->mediaprovider;
                $draft->mediaproviderref = $source->mediaproviderref;
                $draft->mediamime = $source->mediamime;
                $draft->mediaduration = $source->mediaduration;
            } else {
                // No version to branch from: seed the grading settings from the
                // activity's current values (see elang_add_instance) and leave the
                // draft empty for the caller to fill.
                $elang = $DB->get_record('elang', ['id' => $elangid], 'language, jarothreshold', MUST_EXIST);
                $draft->language = $elang->language;
                $draft->jarothreshold = $elang->jarothreshold;
            }
            $draft->usermodified = $userid ?? (int) $USER->id;
            $draft->timecreated = time();
            $draft->id = $DB->insert_record('elang_version', $draft);

            if ($source) {
                $this->copy_version_content((int) $source->id, (int) $draft->id);
                $this->copy_version_files($elangid, (int) $source->id, (int) $draft->id);
            }

            return $draft;
        });
    }

    /**
     * Deep-copy every cue, gap, accepted answer and hint from one version to
     * another, preserving all content fields including the version-stable
     * cuekey/gapkey identities. Only row ids and the parent foreign keys are
     * reassigned; the source version is left untouched.
     *
     * @param int $sourceversionid The version to copy content from
     * @param int $draftversionid The draft version to copy content into
     * @return void No return value.
     */
    private function copy_version_content(int $sourceversionid, int $draftversionid): void {
        global $DB;

        $cues = $DB->get_records('elang_cue', ['versionid' => $sourceversionid], 'sortorder ASC, id ASC');
        if (empty($cues)) {
            return;
        }

        // Read the whole source subtree up front — all gaps, all answers, all
        // hints — in one query each, grouped by parent id. Copying a published
        // version into a new draft then costs four reads regardless of size,
        // rather than one read per cue and two per gap. The inserts stay
        // per-row because each child needs its freshly inserted parent's id.
        [$cuein, $cueparams] = $DB->get_in_or_equal(array_keys($cues));
        $gapsbycue = [];
        $gaps = $DB->get_records_select('elang_gap', "cueid $cuein", $cueparams, 'cueid ASC, sortorder ASC, id ASC');
        foreach ($gaps as $gap) {
            $gapsbycue[(int) $gap->cueid][] = $gap;
        }

        $answersbygap = [];
        $hintsbygap = [];
        if (!empty($gaps)) {
            [$gapin, $gapparams] = $DB->get_in_or_equal(array_keys($gaps));
            $answers = $DB->get_records_select(
                'elang_gapanswer',
                "gapid $gapin",
                $gapparams,
                'gapid ASC, sortorder ASC, id ASC'
            );
            foreach ($answers as $answer) {
                $answersbygap[(int) $answer->gapid][] = $answer;
            }

            $hints = $DB->get_records_select('elang_gaphint', "gapid $gapin", $gapparams, 'gapid ASC, level ASC, id ASC');
            foreach ($hints as $hint) {
                $hintsbygap[(int) $hint->gapid][] = $hint;
            }
        }

        foreach ($cues as $cue) {
            $sourcecueid = (int) $cue->id;
            unset($cue->id);
            $cue->versionid = $draftversionid;
            $newcueid = $DB->insert_record('elang_cue', $cue);

            foreach ($gapsbycue[$sourcecueid] ?? [] as $gap) {
                $sourcegapid = (int) $gap->id;
                unset($gap->id);
                $gap->cueid = $newcueid;
                $newgapid = $DB->insert_record('elang_gap', $gap);

                foreach ($answersbygap[$sourcegapid] ?? [] as $answer) {
                    unset($answer->id);
                    $answer->gapid = $newgapid;
                    $DB->insert_record('elang_gapanswer', $answer);
                }

                foreach ($hintsbygap[$sourcegapid] ?? [] as $hint) {
                    unset($hint->id);
                    $hint->gapid = $newgapid;
                    $DB->insert_record('elang_gaphint', $hint);
                }
            }
        }
    }

    /**
     * Copy a version's media and poster files into another version's file
     * areas at itemid = the target version id, in the same activity context.
     * Mirrors v1_media_migrator: with no course module there is no file
     * context, so there is nothing to copy.
     *
     * @param int $elangid The activity that owns the file context
     * @param int $sourceversionid The version whose files are copied (source itemid)
     * @param int $draftversionid The draft version the files are copied to (target itemid)
     * @return void No return value.
     */
    private function copy_version_files(int $elangid, int $sourceversionid, int $draftversionid): void {
        $cm = get_coursemodule_from_instance('elang', $elangid, 0, false, IGNORE_MISSING);
        if (!$cm) {
            return;
        }

        $contextid = (int) \context_module::instance($cm->id)->id;
        $fs = get_file_storage();

        foreach (['media', 'poster'] as $area) {
            $files = $fs->get_area_files($contextid, 'mod_elang', $area, $sourceversionid, 'id', false);
            foreach ($files as $file) {
                $fs->create_file_from_storedfile([
                    'contextid' => $contextid,
                    'component' => 'mod_elang',
                    'filearea' => $area,
                    'itemid' => $draftversionid,
                    'filepath' => $file->get_filepath(),
                    'filename' => $file->get_filename(),
                ], $file);
            }
        }
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
