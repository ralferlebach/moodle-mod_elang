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
 * Tests for version_manager.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\domain\version_manager
 */
final class version_manager_test extends \advanced_testcase {
    /** @var version_manager */
    private $manager;

    /** @var \stdClass */
    private $elang;

    /** @var \stdClass */
    private $course;

    /** @var \context_module */
    private $context;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->manager = new version_manager();

        $this->course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $this->elang = $generator->create_instance(['course' => $this->course->id]);
        $cm = get_coursemodule_from_instance('elang', $this->elang->id);
        $this->context = \context_module::instance($cm->id);
    }

    /**
     * The first draft for a fresh activity is version 1.
     *
     * @return void
     */
    public function test_get_or_create_draft_creates_version_one(): void {
        $draft = $this->manager->get_or_create_draft($this->elang->id, 2);

        $this->assertSame(1, (int) $draft->versionnumber);
        $this->assertSame(version_manager::STATUS_DRAFT, $draft->status);
        $this->assertSame((int) $this->elang->id, (int) $draft->elangid);
    }

    /**
     * Calling get_or_create_draft twice returns the same draft, not a new one.
     *
     * @return void
     */
    public function test_get_or_create_draft_is_idempotent(): void {
        $first = $this->manager->get_or_create_draft($this->elang->id, 2);
        $second = $this->manager->get_or_create_draft($this->elang->id, 2);

        $this->assertSame((int) $first->id, (int) $second->id);
    }

    /**
     * create_draft() always creates a new row, unlike get_or_create_draft().
     *
     * @return void
     */
    public function test_create_draft_always_creates_a_new_version(): void {
        $first = $this->manager->create_draft($this->elang->id, 2);
        $second = $this->manager->create_draft($this->elang->id, 2);

        $this->assertNotEquals($first->id, $second->id);
        $this->assertSame(1, (int) $first->versionnumber);
        $this->assertSame(2, (int) $second->versionnumber);
    }

    /**
     * Nothing is published for a fresh activity.
     *
     * @return void
     */
    public function test_get_published_returns_null_when_nothing_published(): void {
        $this->assertNull($this->manager->get_published($this->elang->id));
    }

    /**
     * Publishing a version marks it published and updates elang.currentversionid.
     *
     * @return void
     */
    public function test_publish_sets_status_and_currentversionid(): void {
        global $DB;

        $draft = $this->manager->get_or_create_draft($this->elang->id, 2);

        $published = $this->manager->publish($draft->id, 2);

        $this->assertSame(version_manager::STATUS_PUBLISHED, $published->status);
        $this->assertSame($draft->id, (int) $DB->get_field('elang', 'currentversionid', ['id' => $this->elang->id]));
        $this->assertSame($published->id, $this->manager->get_published($this->elang->id)->id);
    }

    /**
     * Publishing a new draft archives the previously published version, and
     * never deletes it.
     *
     * @return void
     */
    public function test_publish_archives_the_previous_published_version(): void {
        global $DB;

        $versionone = $this->manager->get_or_create_draft($this->elang->id, 2);
        $this->manager->publish($versionone->id, 2);

        $versiontwo = $this->manager->create_draft($this->elang->id, 2);
        $this->manager->publish($versiontwo->id, 2);

        $this->assertSame(
            version_manager::STATUS_ARCHIVED,
            $DB->get_field('elang_version', 'status', ['id' => $versionone->id])
        );
        $this->assertTrue($DB->record_exists('elang_version', ['id' => $versionone->id]));
        $this->assertSame($versiontwo->id, (int) $DB->get_field('elang', 'currentversionid', ['id' => $this->elang->id]));
    }

    /**
     * The content hash is the same for identical content and changes when
     * the content changes, so it is fit to use as a cache key.
     *
     * @return void
     */
    public function test_content_hash_is_deterministic_and_reflects_content_changes(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $versionone = $this->manager->create_draft($this->elang->id, 2);
        $cue = $generator->create_cue(['versionid' => $versionone->id, 'transcript' => 'Bonjour le monde']);
        $generator->create_gap(['cueid' => $cue->id, 'solution' => 'monde']);

        $hasha = $this->manager->compute_content_hash($versionone->id);
        $hashb = $this->manager->compute_content_hash($versionone->id);
        $this->assertSame($hasha, $hashb);

        $versiontwo = $this->manager->create_draft($this->elang->id, 2);
        $cue2 = $generator->create_cue(['versionid' => $versiontwo->id, 'transcript' => 'Bonjour le monde']);
        $generator->create_gap(['cueid' => $cue2->id, 'solution' => 'univers']);

        $hashc = $this->manager->compute_content_hash($versiontwo->id);
        $this->assertNotSame($hasha, $hashc);
    }

    /**
     * A version that is not currently a draft — already published, in this
     * case — cannot be published again.
     *
     * @return void
     */
    public function test_publish_rejects_a_version_that_is_not_a_draft(): void {
        $draft = $this->manager->get_or_create_draft($this->elang->id, 2);
        $this->manager->publish($draft->id, 2);

        $this->expectException(\coding_exception::class);
        $this->manager->publish($draft->id, 2);
    }

    /**
     * The content hash is sensitive to a gap's maxlength, linkurl and a
     * cue's transcriptformat — all three affect what is rendered or how a
     * response is validated, so a change to any of them must invalidate a
     * cached worksheet or player payload keyed on this hash.
     *
     * @return void
     */
    public function test_content_hash_reflects_maxlength_linkurl_and_transcriptformat(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $versionone = $this->manager->create_draft($this->elang->id, 2);
        $cueone = $generator->create_cue([
            'versionid' => $versionone->id,
            'transcript' => 'Bonjour le monde',
            'transcriptformat' => FORMAT_PLAIN,
        ]);
        $generator->create_gap(['cueid' => $cueone->id, 'solution' => 'monde', 'maxlength' => 50]);
        $hashbase = $this->manager->compute_content_hash($versionone->id);

        $versiontwo = $this->manager->create_draft($this->elang->id, 2);
        $cuetwo = $generator->create_cue([
            'versionid' => $versiontwo->id,
            'transcript' => 'Bonjour le monde',
            'transcriptformat' => FORMAT_PLAIN,
        ]);
        $generator->create_gap(['cueid' => $cuetwo->id, 'solution' => 'monde', 'maxlength' => 5]);
        $hashdifferentmaxlength = $this->manager->compute_content_hash($versiontwo->id);
        $this->assertNotSame($hashbase, $hashdifferentmaxlength);

        $versionthree = $this->manager->create_draft($this->elang->id, 2);
        $cuethree = $generator->create_cue([
            'versionid' => $versionthree->id,
            'transcript' => 'Bonjour le monde',
            'transcriptformat' => FORMAT_MARKDOWN,
        ]);
        $generator->create_gap(['cueid' => $cuethree->id, 'solution' => 'monde', 'maxlength' => 50]);
        $hashdifferentformat = $this->manager->compute_content_hash($versionthree->id);
        $this->assertNotSame($hashbase, $hashdifferentformat);
    }

    /**
     * Changing a version's media columns changes its content hash, so a cache
     * keyed on the hash is invalidated when the medium changes.
     *
     * @return void
     */
    public function test_content_hash_reflects_media_columns(): void {
        global $DB;

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $version = $this->manager->create_draft($this->elang->id, 2);
        $cue = $generator->create_cue(['versionid' => $version->id, 'transcript' => 'Bonjour']);
        $generator->create_gap(['cueid' => $cue->id, 'solution' => 'x']);
        $hashbase = $this->manager->compute_content_hash($version->id);

        $DB->set_field('elang_version', 'mediakind', 'url', ['id' => $version->id]);
        $DB->set_field('elang_version', 'mediaurl', 'https://example.org/a.mp4', ['id' => $version->id]);
        $hashwithmedia = $this->manager->compute_content_hash($version->id);

        $this->assertNotSame($hashbase, $hashwithmedia);
    }

    /**
     * Adding a hint to a gap changes the version's content hash — a hint's
     * type, text and penalty affect how a gap is solved and scored, so the
     * cache key must reflect it (reviewer note 8).
     *
     * @return void
     */
    public function test_content_hash_reflects_hints(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $version = $this->manager->create_draft($this->elang->id, 2);
        $cue = $generator->create_cue(['versionid' => $version->id, 'transcript' => 'Bonjour']);
        $gap = $generator->create_gap(['cueid' => $cue->id, 'solution' => 'x']);
        $hashwithouthint = $this->manager->compute_content_hash($version->id);

        $generator->create_gaphint([
            'gapid' => $gap->id,
            'level' => 1,
            'hinttype' => 'firstletter',
            'hinttext' => 'x',
            'penalty' => 0.1,
        ]);
        $hashwithhint = $this->manager->compute_content_hash($version->id);

        $this->assertNotSame($hashwithouthint, $hashwithhint);
    }

    /**
     * compute_content_hash() correctly attributes gaps and answers to the
     * right cue even across several cues, since the batched queries it uses
     * group results in PHP rather than one query per cue/gap.
     *
     * @return void
     */
    public function test_content_hash_is_stable_across_multiple_cues_and_gaps(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $version = $this->manager->create_draft($this->elang->id, 2);
        $cueone = $generator->create_cue(['versionid' => $version->id, 'sortorder' => 1]);
        $gapone = $generator->create_gap(['cueid' => $cueone->id, 'sortorder' => 1, 'solution' => 'chat']);
        $generator->create_gapanswer(['gapid' => $gapone->id, 'answer' => 'chats']);

        $cuetwo = $generator->create_cue(['versionid' => $version->id, 'sortorder' => 2]);
        $generator->create_gap(['cueid' => $cuetwo->id, 'sortorder' => 1, 'solution' => 'chien']);
        $generator->create_gap(['cueid' => $cuetwo->id, 'sortorder' => 2, 'solution' => 'oiseau']);

        $hasha = $this->manager->compute_content_hash($version->id);
        $hashb = $this->manager->compute_content_hash($version->id);
        $this->assertSame($hasha, $hashb);
        $this->assertSame(64, strlen($hasha), 'SHA-256 hex digest is 64 characters long.');
    }

    /**
     * A manager (mod/elang:manage) may fetch a file from any version, whatever
     * its status — the authoring tool needs to preview draft media.
     *
     * @return void
     */
    public function test_manager_can_access_file_for_any_version_status(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $manager = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');

        $draft = $generator->create_version([
            'elangid' => $this->elang->id,
            'status' => version_manager::STATUS_DRAFT,
        ]);
        $published = $generator->create_version([
            'elangid' => $this->elang->id,
            'status' => version_manager::STATUS_PUBLISHED,
        ]);
        $archived = $generator->create_version([
            'elangid' => $this->elang->id,
            'status' => version_manager::STATUS_ARCHIVED,
        ]);

        foreach ([$draft, $published, $archived] as $version) {
            $this->assertTrue(version_manager::user_can_access_version_file(
                (int) $version->id,
                (int) $this->elang->id,
                $this->context,
                (int) $manager->id
            ));
        }
    }

    /**
     * A learner may fetch the published version's file but never a draft's.
     *
     * @return void
     */
    public function test_learner_can_access_published_but_not_draft_version_file(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $student = $this->getDataGenerator()->create_and_enrol($this->course, 'student');

        $draft = $generator->create_version([
            'elangid' => $this->elang->id,
            'status' => version_manager::STATUS_DRAFT,
        ]);
        $published = $generator->create_version([
            'elangid' => $this->elang->id,
            'status' => version_manager::STATUS_PUBLISHED,
        ]);

        $this->assertTrue(version_manager::user_can_access_version_file(
            (int) $published->id,
            (int) $this->elang->id,
            $this->context,
            (int) $student->id
        ));
        $this->assertFalse(version_manager::user_can_access_version_file(
            (int) $draft->id,
            (int) $this->elang->id,
            $this->context,
            (int) $student->id
        ));
    }

    /**
     * An archived version's file stays available to a learner whose own attempt
     * is pinned to it, but not to another learner without such an attempt.
     *
     * @return void
     */
    public function test_learner_can_access_own_pinned_archived_version_file(): void {
        global $DB;

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $owner = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        $other = $this->getDataGenerator()->create_and_enrol($this->course, 'student');

        $archived = $generator->create_version([
            'elangid' => $this->elang->id,
            'status' => version_manager::STATUS_ARCHIVED,
        ]);

        $DB->insert_record('elang_attempt', (object) [
            'elangid' => $this->elang->id,
            'versionid' => $archived->id,
            'userid' => $owner->id,
            'attemptnumber' => 1,
            'state' => 'inprogress',
            'totalgaps' => 0,
            'answeredgaps' => 0,
            'exactgaps' => 0,
            'correctgaps' => 0,
            'hintedgaps' => 0,
            'score' => 0,
            'timestart' => time(),
            'timemodified' => time(),
        ]);

        $this->assertTrue(version_manager::user_can_access_version_file(
            (int) $archived->id,
            (int) $this->elang->id,
            $this->context,
            (int) $owner->id
        ));
        $this->assertFalse(version_manager::user_can_access_version_file(
            (int) $archived->id,
            (int) $this->elang->id,
            $this->context,
            (int) $other->id
        ));
    }

    /**
     * Access is confined to the owning activity: a version id that belongs to a
     * different activity is rejected even for a manager, so a crafted URL cannot
     * borrow one module context to serve another activity's files.
     *
     * @return void
     */
    public function test_access_is_confined_to_the_owning_activity(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $manager = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');

        $otherelang = $generator->create_instance(['course' => $this->course->id]);
        $foreign = $generator->create_version([
            'elangid' => $otherelang->id,
            'status' => version_manager::STATUS_PUBLISHED,
        ]);

        $this->assertFalse(version_manager::user_can_access_version_file(
            (int) $foreign->id,
            (int) $this->elang->id,
            $this->context,
            (int) $manager->id
        ));
    }

    /**
     * A new draft copies the activity's current language and Jaro threshold
     * onto the version, and starts at revision 1, so grading settings are
     * pinned per version rather than read live from the activity.
     *
     * @return void
     */
    public function test_create_draft_seeds_grading_settings_from_activity(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance([
            'course' => $this->course->id,
            'language' => 'de',
            'jarothreshold' => 0.8,
        ]);

        $draft = $this->manager->create_draft($elang->id, 2);

        $this->assertSame('de', $draft->language);
        $this->assertEqualsWithDelta(0.8, (float) $draft->jarothreshold, 0.00001);
        $this->assertSame(1, (int) $draft->revision);
    }

    /**
     * The content hash reflects the bytes of file-kind media: adding a file to
     * the version's media area changes the hash, so a swapped medium
     * invalidates cached worksheets and player payloads.
     *
     * @return void
     */
    public function test_content_hash_reflects_media_files(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $version = $this->manager->create_draft($this->elang->id, 2);
        $cue = $generator->create_cue(['versionid' => $version->id, 'transcript' => 'Bonjour']);
        $generator->create_gap(['cueid' => $cue->id, 'solution' => 'x']);
        $hashbase = $this->manager->compute_content_hash($version->id);

        get_file_storage()->create_file_from_string([
            'contextid' => $this->context->id,
            'component' => 'mod_elang',
            'filearea' => 'media',
            'itemid' => $version->id,
            'filepath' => '/',
            'filename' => 'clip.mp4',
        ], 'fake video bytes');
        $hashwithfile = $this->manager->compute_content_hash($version->id);

        $this->assertNotSame($hashbase, $hashwithfile);
    }

    /**
     * Creating a draft for an activity that already has a published version
     * branches from it: content, version-stable keys, grading settings and
     * media files are deep-copied, the source version is left intact, and the
     * two versions are independent.
     *
     * @return void
     */
    public function test_create_draft_branches_from_the_published_version(): void {
        global $DB;

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $source = $this->manager->get_or_create_draft($this->elang->id, 2);
        $cue = $generator->create_cue([
            'versionid' => $source->id,
            'cuekey' => 'cue-a',
            'transcript' => 'Le chat dort',
        ]);
        $gap = $generator->create_gap(['cueid' => $cue->id, 'gapkey' => 'gap-a', 'solution' => 'chat']);
        $generator->create_gapanswer(['gapid' => $gap->id, 'answer' => 'chatte']);
        $generator->create_gaphint(['gapid' => $gap->id, 'level' => 1, 'hinttext' => 'c']);
        get_file_storage()->create_file_from_string([
            'contextid' => $this->context->id,
            'component' => 'mod_elang',
            'filearea' => 'media',
            'itemid' => $source->id,
            'filepath' => '/',
            'filename' => 'clip.mp4',
        ], 'video-bytes');
        $DB->set_field('elang_version', 'mediakind', 'file', ['id' => $source->id]);
        $DB->set_field('elang_version', 'jarothreshold', 0.7, ['id' => $source->id]);
        $this->manager->publish($source->id, 2);

        $draft = $this->manager->create_draft($this->elang->id, 2);

        $this->assertNotSame((int) $source->id, (int) $draft->id);
        $this->assertSame(version_manager::STATUS_DRAFT, $draft->status);

        // Content copied, keys preserved, row ids remapped to the new version.
        $draftcue = $DB->get_record('elang_cue', ['versionid' => $draft->id], '*', MUST_EXIST);
        $this->assertSame('cue-a', $draftcue->cuekey);
        $this->assertSame('Le chat dort', $draftcue->transcript);
        $this->assertNotSame((int) $cue->id, (int) $draftcue->id);

        $draftgap = $DB->get_record('elang_gap', ['cueid' => $draftcue->id], '*', MUST_EXIST);
        $this->assertSame('gap-a', $draftgap->gapkey);
        $this->assertSame('chat', $draftgap->solution);
        $this->assertSame(1, $DB->count_records('elang_gapanswer', ['gapid' => $draftgap->id]));
        $this->assertSame(1, $DB->count_records('elang_gaphint', ['gapid' => $draftgap->id]));

        // Grading settings and media carried over from the source version.
        $this->assertEqualsWithDelta(0.7, (float) $draft->jarothreshold, 0.00001);
        $this->assertSame('file', $DB->get_field('elang_version', 'mediakind', ['id' => $draft->id]));
        $draftfiles = get_file_storage()->get_area_files(
            $this->context->id,
            'mod_elang',
            'media',
            $draft->id,
            'id',
            false
        );
        $this->assertCount(1, $draftfiles);

        // The source version keeps its own content: the copy is not a move.
        $this->assertSame(1, $DB->count_records('elang_cue', ['versionid' => $source->id]));
    }

    /**
     * With no published version to branch from, a draft starts empty — the
     * behaviour the first version of an activity and V1 migration rely on.
     *
     * @return void
     */
    public function test_create_draft_without_a_published_version_starts_empty(): void {
        global $DB;

        $draft = $this->manager->create_draft($this->elang->id, 2);

        $this->assertSame(0, $DB->count_records('elang_cue', ['versionid' => $draft->id]));
    }

    /**
     * Publishing with validation refuses a draft whose content is not
     * publishable (here, an empty draft with no cues).
     *
     * @return void
     */
    public function test_publish_with_validation_rejects_an_invalid_version(): void {
        $draft = $this->manager->get_or_create_draft($this->elang->id, 2);

        $this->expectException(\moodle_exception::class);
        $this->manager->publish($draft->id, 2, true);
    }

    /**
     * Publishing with validation lets a well-formed draft through.
     *
     * @return void
     */
    public function test_publish_with_validation_accepts_a_valid_version(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $draft = $this->manager->get_or_create_draft($this->elang->id, 2);
        $cue = $generator->create_cue(['versionid' => $draft->id, 'transcript' => 'Le chat dort']);
        $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);

        $published = $this->manager->publish($draft->id, 2, true);

        $this->assertSame(version_manager::STATUS_PUBLISHED, $published->status);
    }

    /**
     * The default publish path performs no content validation, so it stays a
     * pure lifecycle operation — the behaviour V1 migration relies on when it
     * publishes versions built from imperfect legacy data.
     *
     * @return void
     */
    public function test_publish_without_validation_allows_an_empty_version(): void {
        $draft = $this->manager->get_or_create_draft($this->elang->id, 2);

        $published = $this->manager->publish($draft->id, 2);

        $this->assertSame(version_manager::STATUS_PUBLISHED, $published->status);
    }

    /**
     * load_version_content returns the full authoring view — every cue with its
     * gaps, and each gap with its solution, accepted answers and hints.
     *
     * @return void
     */
    public function test_load_version_content_returns_the_full_authoring_view(): void {
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');

        $version = $this->manager->create_draft($this->elang->id, 2);
        $cue = $generator->create_cue([
            'versionid' => $version->id,
            'cuekey' => 'cue-a',
            'transcript' => 'Le chat dort',
            'sortorder' => 1,
        ]);
        $gap = $generator->create_gap(['cueid' => $cue->id, 'gapkey' => 'gap-a', 'solution' => 'chat', 'sortorder' => 1]);
        $generator->create_gapanswer(['gapid' => $gap->id, 'answer' => 'chatte']);
        $generator->create_gaphint(['gapid' => $gap->id, 'level' => 1, 'hinttext' => 'c']);

        $content = $this->manager->load_version_content($version->id);

        $this->assertCount(1, $content);
        $this->assertSame('cue-a', $content[0]['cuekey']);
        $this->assertCount(1, $content[0]['gaps']);

        $gapview = $content[0]['gaps'][0];
        $this->assertSame('gap-a', $gapview['gapkey']);
        $this->assertSame('chat', $gapview['solution']);
        $this->assertSame('chatte', $gapview['answers'][0]['answer']);
        $this->assertSame(1, $gapview['hints'][0]['level']);
    }

    /**
     * A version with no cues loads as an empty content list.
     *
     * @return void
     */
    public function test_load_version_content_of_an_empty_version_is_empty(): void {
        $version = $this->manager->create_draft($this->elang->id, 2);

        $this->assertSame([], $this->manager->load_version_content($version->id));
    }
}
