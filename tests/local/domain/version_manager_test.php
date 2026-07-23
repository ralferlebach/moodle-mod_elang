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

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->manager = new version_manager();

        $course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $this->elang = $generator->create_instance(['course' => $course->id]);
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
}
