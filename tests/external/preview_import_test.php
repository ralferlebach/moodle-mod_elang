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
 * Tests for the preview_import external function.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\preview_import
 */
final class preview_import_test extends \advanced_testcase {
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
     * A manager previews the cues parsed from a WebVTT file.
     *
     * @return void
     */
    public function test_manager_previews_parsed_cues(): void {
        $this->setUser($this->teacher);
        $vtt = "WEBVTT\n\n00:00:01.000 --> 00:00:04.000\nBonjour le monde\n";

        $result = preview_import::execute((int) $this->draft->id, $vtt);
        $result = external_api::clean_returnvalue(preview_import::execute_returns(), $result);

        $this->assertSame(1, $result['cuecount']);
        $this->assertCount(1, $result['cues']);
        $this->assertSame('Bonjour le monde', $result['cues'][0]['transcript']);
        $this->assertSame(1000, $result['cues'][0]['starttime']);
        $this->assertSame(4000, $result['cues'][0]['endtime']);
        $this->assertSame([], $result['warnings']);
    }

    /**
     * Without the parsegaps option, V1 gap markers stay literal text and no
     * gaps are reported — the previous behaviour is unchanged.
     *
     * @return void
     */
    public function test_markers_stay_literal_without_parsegaps(): void {
        $this->setUser($this->teacher);
        $vtt = "WEBVTT\n\n00:00:01.000 --> 00:00:04.000\nDer [Hund] läuft\n";

        $result = preview_import::execute((int) $this->draft->id, $vtt);
        $result = external_api::clean_returnvalue(preview_import::execute_returns(), $result);

        $this->assertSame('Der [Hund] läuft', $result['cues'][0]['transcript']);
        $this->assertSame([], $result['cues'][0]['gaps']);
    }

    /**
     * With the parsegaps option, V1 markers are stripped and returned as
     * gaps, distinguishing the help-allowed bracket form from the brace form.
     *
     * @return void
     */
    public function test_parsegaps_recognises_v1_markers(): void {
        $this->setUser($this->teacher);
        $vtt = "WEBVTT\n\n00:00:01.000 --> 00:00:04.000\nDer [Hund] läuft\n\n"
            . "00:00:05.000 --> 00:00:08.000\nDie {Katze} schläft\n";

        $result = preview_import::execute((int) $this->draft->id, $vtt, true);
        $result = external_api::clean_returnvalue(preview_import::execute_returns(), $result);

        $this->assertSame('Der Hund läuft', $result['cues'][0]['transcript']);
        $this->assertCount(1, $result['cues'][0]['gaps']);
        $this->assertSame(4, $result['cues'][0]['gaps'][0]['charstart']);
        $this->assertSame(4, $result['cues'][0]['gaps'][0]['charlength']);
        $this->assertSame('Hund', $result['cues'][0]['gaps'][0]['solution']);
        $this->assertTrue($result['cues'][0]['gaps'][0]['hintsallowed']);

        $this->assertSame('Die Katze schläft', $result['cues'][1]['transcript']);
        $this->assertCount(1, $result['cues'][1]['gaps']);
        $this->assertFalse($result['cues'][1]['gaps'][0]['hintsallowed']);
    }

    /**
     * A user without mod/elang:manage cannot use the importer.
     *
     * @return void
     */
    public function test_preview_requires_the_manage_capability(): void {
        $this->setUser($this->student);

        $this->expectException(\required_capability_exception::class);
        preview_import::execute((int) $this->draft->id, "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nX\n");
    }
}
