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

namespace mod_elang\local\migration;

/**
 * Tests for v1_media_migrator.
 *
 * Extends \advanced_testcase directly — see submit_response_test.php's class
 * docblock for why not \externallib_advanced_testcase.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\migration\v1_media_migrator
 */
final class v1_media_migrator_test extends \advanced_testcase {
    /**
     * Version 1 videos (several encodings) and the poster are copied into the
     * versioned media/poster areas, the version is marked file-kind, and the
     * version 1 originals are left untouched.
     *
     * @return void
     */
    public function test_copies_v1_video_and_poster_into_the_versioned_areas(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);
        $cm = get_coursemodule_from_instance('elang', $elang->id);
        $context = \context_module::instance($cm->id);

        // Simulate version 1 media: two video encodings and one poster, all in
        // the version 1 file areas at itemid 0.
        $fs = get_file_storage();
        foreach (['clip.mp4', 'clip.webm'] as $name) {
            $fs->create_file_from_string([
                'contextid' => $context->id,
                'component' => 'mod_elang',
                'filearea' => 'videos',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $name,
            ], "bytes-of-{$name}");
        }
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'mod_elang',
            'filearea' => 'poster',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'poster.jpg',
        ], 'poster-bytes');

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draft = $versionmanager->create_draft($elang->id);

        $result = (new v1_media_migrator())->migrate($elang->id, (int) $draft->id);

        $this->assertSame(2, $result->mediafiles);
        $this->assertSame(1, $result->posterfiles);
        $this->assertSame('file', $DB->get_field('elang_version', 'mediakind', ['id' => $draft->id], MUST_EXIST));

        $this->assertCount(2, $fs->get_area_files($context->id, 'mod_elang', 'media', $draft->id, 'filename', false));
        $this->assertCount(1, $fs->get_area_files($context->id, 'mod_elang', 'poster', $draft->id, 'filename', false));

        // The copy is non-destructive: the version 1 originals remain.
        $this->assertCount(2, $fs->get_area_files($context->id, 'mod_elang', 'videos', 0, 'filename', false));
        $this->assertCount(1, $fs->get_area_files($context->id, 'mod_elang', 'poster', 0, 'filename', false));
    }

    /**
     * An activity with a poster but no video leaves mediakind unset (there is
     * no playable medium) while still copying the poster.
     *
     * @return void
     */
    public function test_poster_without_video_does_not_set_file_kind(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);
        $cm = get_coursemodule_from_instance('elang', $elang->id);
        $context = \context_module::instance($cm->id);

        $fs = get_file_storage();
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'mod_elang',
            'filearea' => 'poster',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'poster.jpg',
        ], 'poster-bytes');

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draft = $versionmanager->create_draft($elang->id);

        $result = (new v1_media_migrator())->migrate($elang->id, (int) $draft->id);

        $this->assertSame(0, $result->mediafiles);
        $this->assertSame(1, $result->posterfiles);
        $this->assertNull($DB->get_field('elang_version', 'mediakind', ['id' => $draft->id], MUST_EXIST));
    }

    /**
     * An activity with no real course module (a DB-only simulated activity) is
     * a harmless no-op.
     *
     * @return void
     */
    public function test_missing_course_module_is_a_harmless_noop(): void {
        $this->resetAfterTest();

        $result = (new v1_media_migrator())->migrate(999999, 123);

        $this->assertSame(0, $result->mediafiles);
        $this->assertSame(0, $result->posterfiles);
    }
}
