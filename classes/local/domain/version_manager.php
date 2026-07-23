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

    /**
     * Return the current draft for an activity, creating one if none exists.
     *
     * @param int $elangid The activity id
     * @param int|null $userid Id to record as usermodified when a draft is created; defaults to the current user
     * @return \stdClass The draft elang_version record
     */
    public function get_or_create_draft(int $elangid, ?int $userid = null): \stdClass {
        global $DB;

        $draft = $DB->get_record('elang_version', ['elangid' => $elangid, 'status' => self::STATUS_DRAFT]);
        if ($draft) {
            return $draft;
        }

        return $this->create_draft($elangid, $userid);
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
        global $DB, $USER;

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

        return $draft;
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
     * @param int $versionid The elang_version id to publish
     * @param int|null $userid Id to record as usermodified; defaults to the current user
     * @return \stdClass The published elang_version record
     */
    public function publish(int $versionid, ?int $userid = null): \stdClass {
        global $DB, $USER;

        $version = $DB->get_record('elang_version', ['id' => $versionid], '*', MUST_EXIST);

        $transaction = $DB->start_delegated_transaction();

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
    }

    /**
     * Compute a deterministic content hash over a version's cues, gaps,
     * accepted answers and grading algorithms.
     *
     * Used as the cache key for rendered worksheets and player payloads
     * (see Blueprint chapter 12/16); intentionally excludes hints and
     * timestamps, since neither affects what a learner is shown to solve.
     *
     * @param int $versionid The elang_version id
     * @return string A SHA-1 hash of the normalised content
     */
    public function compute_content_hash(int $versionid): string {
        global $DB;

        $cues = $DB->get_records('elang_cue', ['versionid' => $versionid], 'sortorder ASC');

        $cueparts = [];
        foreach ($cues as $cue) {
            $gaps = $DB->get_records('elang_gap', ['cueid' => $cue->id], 'sortorder ASC');

            $gapparts = [];
            foreach ($gaps as $gap) {
                $answers = $DB->get_records('elang_gapanswer', ['gapid' => $gap->id], 'sortorder ASC');

                $answerparts = [];
                foreach ($answers as $answer) {
                    $answerparts[] = $answer->answer . '|' . $answer->isregex;
                }

                $gapparts[] = implode(',', [
                    $gap->gapkey,
                    $gap->charstart,
                    $gap->charlength,
                    $gap->solution,
                    $gap->gradingalgorithm,
                    implode(';', $answerparts),
                ]);
            }

            $cueparts[] = implode(',', [
                $cue->cuekey,
                $cue->starttime,
                $cue->endtime,
                $cue->transcript,
                implode('|', $gapparts),
            ]);
        }

        return sha1(implode("\n", $cueparts));
    }
}
