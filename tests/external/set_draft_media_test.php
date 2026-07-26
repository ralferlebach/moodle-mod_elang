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

namespace mod_elang\external;

use core_external\external_api;
use mod_elang\local\domain\version_manager;

/**
 * Tests for the set_draft_media external function.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\set_draft_media
 */
final class set_draft_media_test extends \advanced_testcase {
    /** @var \stdClass */
    private $teacher;

    /** @var \stdClass */
    private $student;

    /** @var \stdClass */
    private $draft;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $this->teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $this->student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);
        $this->draft = (new version_manager())->create_draft((int) $elang->id, (int) $this->teacher->id);
    }

    /**
     * A manager can set url media and gets the descriptor back.
     *
     * @return void
     */
    public function test_manager_sets_url_media(): void {
        $this->setUser($this->teacher);

        $result = set_draft_media::execute(
            (int) $this->draft->id,
            'url',
            'https://example.org/video.mp4',
            '',
            '',
            'video/mp4',
            120
        );
        $result = external_api::clean_returnvalue(set_draft_media::execute_returns(), $result);

        $this->assertSame('url', $result['mediakind']);
        $this->assertSame('https://example.org/video.mp4', $result['mediaurl']);
        $this->assertSame('video/mp4', $result['mediamime']);
        $this->assertSame(120, $result['mediaduration']);
    }

    /**
     * A file uploaded to a draft area is saved onto the version, and
     * get_version_content then hands back its name and URL for the editor.
     *
     * @return void
     */
    public function test_file_media_is_saved_and_exposed_by_get_version_content(): void {
        $this->setUser($this->teacher);

        $draftitemid = file_get_unused_draft_itemid();
        get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance((int) $this->teacher->id)->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $draftitemid,
            'filepath' => '/',
            'filename' => 'clip.mp4',
        ], 'video-bytes');

        $saved = set_draft_media::execute((int) $this->draft->id, 'file', '', '', '', 'video/mp4', 0, $draftitemid, 0);
        $saved = external_api::clean_returnvalue(set_draft_media::execute_returns(), $saved);
        $this->assertSame('file', $saved['mediakind']);

        $content = get_version_content::execute((int) $this->draft->id);
        $content = external_api::clean_returnvalue(get_version_content::execute_returns(), $content);

        $this->assertSame('file', $content['mediakind']);
        $this->assertSame('clip.mp4', $content['mediafilename']);
        $this->assertNotSame('', $content['mediafileurl']);
    }

    /**
     * A user without mod/elang:manage cannot change the medium.
     *
     * @return void
     */
    public function test_setting_media_requires_the_manage_capability(): void {
        $this->setUser($this->student);

        $this->expectException(\required_capability_exception::class);
        set_draft_media::execute((int) $this->draft->id, 'url', 'https://example.org/video.mp4');
    }
}
