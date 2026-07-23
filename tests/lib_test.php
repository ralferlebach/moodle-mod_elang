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

namespace mod_elang;

/**
 * Tests for the mod_elang module library functions.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     ::elang_supports
 */
final class lib_test extends \advanced_testcase {
    /**
     * The module declares the assessment purpose so that the activity icon is
     * rendered on the assessment background colour.
     *
     * @return void
     */
    public function test_module_purpose_is_assessment(): void {
        $this->resetAfterTest();

        $this->assertSame(MOD_PURPOSE_ASSESSMENT, elang_supports(FEATURE_MOD_PURPOSE));
    }

    /**
     * The features the skeleton can actually honour are declared.
     *
     * @return void
     */
    public function test_declared_features(): void {
        $this->resetAfterTest();

        $this->assertTrue(elang_supports(FEATURE_MOD_INTRO));
        $this->assertTrue(elang_supports(FEATURE_COMPLETION_TRACKS_VIEWS));
        $this->assertTrue(elang_supports(FEATURE_GROUPS));
        $this->assertTrue(elang_supports(FEATURE_GROUPINGS));
        $this->assertFalse(elang_supports(FEATURE_GRADE_OUTCOMES));
        $this->assertNull(elang_supports('mod_elang_unknown_feature'));
    }

    /**
     * Features whose implementation is still outstanding must not be declared,
     * because Moodle would then call callbacks that do not exist yet.
     *
     * @return void
     */
    public function test_unimplemented_features_are_not_declared(): void {
        $this->resetAfterTest();

        $this->assertFalse(elang_supports(FEATURE_BACKUP_MOODLE2));
        $this->assertFalse(elang_supports(FEATURE_COMPLETION_HAS_RULES));
        $this->assertFalse(elang_supports(FEATURE_GRADE_HAS_GRADE));
    }

    /**
     * A monochrome monologo icon is shipped and is not branded.
     *
     * @return void
     */
    public function test_icon_is_unbranded_monologo(): void {
        $this->resetAfterTest();

        $this->assertFileExists(__DIR__ . '/../pix/monologo.svg');
        $this->assertFalse(elang_is_branded());
    }

    /**
     * An instance can be created, updated and deleted through the module API.
     *
     * @return void
     */
    public function test_instance_lifecycle(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $elang = $this->getDataGenerator()->create_module('elang', ['course' => $course->id]);

        $this->assertTrue($DB->record_exists('elang', ['id' => $elang->id]));

        $record = $DB->get_record('elang', ['id' => $elang->id]);
        $record->instance = $record->id;
        $record->name = 'Renamed exercise';
        $this->assertTrue(elang_update_instance($record));
        $this->assertSame('Renamed exercise', $DB->get_field('elang', 'name', ['id' => $elang->id]));

        $this->assertTrue(elang_delete_instance($elang->id));
        $this->assertFalse($DB->record_exists('elang', ['id' => $elang->id]));
    }
}
