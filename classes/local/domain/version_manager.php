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
     * (see Blueprint chapter 12/16). Hashes the JSON encoding of a single
     * canonical structure rather than fields concatenated with chosen
     * delimiters, so content that happens to contain those delimiters can no
     * longer produce an ambiguous pre-hash string (reviewer note 8). Includes
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
        // itself contain (reviewer note 8). Hints are included — their type,
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

        $transaction = $DB->start_delegated_transaction();

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

        $transaction->allow_commit();

        return $draft;
    }

    /**
     * Deep-copy every cue, gap, accepted answer and hint from one version to
     * another, preserving all content fields including the version-stable
     * cuekey/gapkey identities. Only row ids and the parent foreign keys are
     * reassigned; the source version is left untouched.
     *
     * @param int $sourceversionid The version to copy content from
     * @param int $draftversionid The draft version to copy content into
     * @return void
     */
    private function copy_version_content(int $sourceversionid, int $draftversionid): void {
        global $DB;

        $cues = $DB->get_records('elang_cue', ['versionid' => $sourceversionid], 'sortorder ASC, id ASC');
        foreach ($cues as $cue) {
            $sourcecueid = (int) $cue->id;
            unset($cue->id);
            $cue->versionid = $draftversionid;
            $newcueid = $DB->insert_record('elang_cue', $cue);

            $gaps = $DB->get_records('elang_gap', ['cueid' => $sourcecueid], 'sortorder ASC, id ASC');
            foreach ($gaps as $gap) {
                $sourcegapid = (int) $gap->id;
                unset($gap->id);
                $gap->cueid = $newcueid;
                $newgapid = $DB->insert_record('elang_gap', $gap);

                $answers = $DB->get_records('elang_gapanswer', ['gapid' => $sourcegapid], 'sortorder ASC, id ASC');
                foreach ($answers as $answer) {
                    unset($answer->id);
                    $answer->gapid = $newgapid;
                    $DB->insert_record('elang_gapanswer', $answer);
                }

                $hints = $DB->get_records('elang_gaphint', ['gapid' => $sourcegapid], 'level ASC, id ASC');
                foreach ($hints as $hint) {
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
     * @return void
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
