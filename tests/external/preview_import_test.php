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
use mod_elang\fixtures\authoring_test_fixture_builder;

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

        require_once(__DIR__ . '/../fixtures/authoring_test_fixture.php');
        $fixture = authoring_test_fixture_builder::create($this);
        $this->teacher = $fixture->teacher;
        $this->student = $fixture->student;
        $this->draft = $fixture->draft;
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
