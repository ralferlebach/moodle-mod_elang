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

namespace mod_elang\backup;

use backup;
use backup_controller;
use restore_controller;
use restore_dbops;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

/**
 * Backup and restore tests for mod_elang.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \backup_elang_activity_structure_step
 * @covers     \restore_elang_activity_structure_step
 */
final class restore_test extends \advanced_testcase {
    /**
     * Seed a course with a student and a published elang activity (one cue, one
     * gap with an answer variant and a hint), with the current-version pointer
     * set.
     *
     * @return \stdClass The created course, student, elang, version, cue and gap.
     */
    private function seed_activity(): \stdClass {
        global $DB;

        $seed = new \stdClass();
        $seed->course = $this->getDataGenerator()->create_course();
        $seed->student = $this->getDataGenerator()->create_and_enrol($seed->course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $seed->elang = $generator->create_instance(['course' => $seed->course->id, 'language' => 'fr']);
        $seed->version = $generator->create_version(['elangid' => $seed->elang->id, 'status' => 'published']);
        $seed->cue = $generator->create_cue(['versionid' => $seed->version->id, 'transcript' => 'Le chat dort']);
        $seed->gap = $generator->create_gap(
            ['cueid' => $seed->cue->id, 'solution' => 'chat', 'charstart' => 3, 'charlength' => 4]
        );
        $generator->create_gapanswer(['gapid' => $seed->gap->id, 'answer' => 'chats']);
        $generator->create_gaphint(['gapid' => $seed->gap->id, 'level' => 1, 'hinttext' => 'animal']);
        $DB->set_field('elang', 'currentversionid', $seed->version->id, ['id' => $seed->elang->id]);

        return $seed;
    }

    /**
     * Duplicate a course containing a fully populated elang activity, with user
     * data, and assert the whole content tree, the learner attempt and every
     * internal reference (current version, attempt version, response gap) is
     * reproduced in the copy.
     *
     * @return void
     */
    public function test_backup_restore_preserves_content_and_user_data(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();

        $seed = $this->seed_activity();
        $course = $seed->course;
        $student = $seed->student;
        $elang = $seed->elang;
        $version = $seed->version;
        $gap = $seed->gap;

        // A finished attempt with one response, so user data is exercised.
        $evaluator = new \mod_elang\local\grading\answer_evaluator(
            new \mod_elang\local\grading\script_handler_manager([])
        );
        $attempts = new \mod_elang\local\domain\attempt_manager($evaluator);
        $attempt = $attempts->start_attempt((int) $elang->id, (int) $student->id, (int) $version->id);
        $attempts->submit_response((int) $attempt->id, (int) $gap->id, 'chat');
        $attempts->finish_attempt((int) $attempt->id);

        $newcourseid = $this->backup_and_restore($course, (int) $USER->id);

        // Exactly one elang activity in the copied course.
        $cms = get_coursemodules_in_course('elang', $newcourseid);
        $this->assertCount(1, $cms);
        $newelang = $DB->get_record('elang', ['id' => reset($cms)->instance], '*', MUST_EXIST);
        $this->assertNotEquals($elang->id, $newelang->id);

        // The content tree came across intact.
        $newversion = $DB->get_record('elang_version', ['elangid' => $newelang->id], '*', MUST_EXIST);
        $newcue = $DB->get_record('elang_cue', ['versionid' => $newversion->id], '*', MUST_EXIST);
        $newgap = $DB->get_record('elang_gap', ['cueid' => $newcue->id], '*', MUST_EXIST);
        $this->assertSame('Le chat dort', $newcue->transcript);
        $this->assertSame('chat', $newgap->solution);
        $this->assertSame(3, (int) $newgap->charstart);
        $this->assertSame('chats', $DB->get_field('elang_gapanswer', 'answer', ['gapid' => $newgap->id]));
        $this->assertSame('animal', $DB->get_field('elang_gaphint', 'hinttext', ['gapid' => $newgap->id]));

        // The forward reference to the current version was remapped, not left
        // pointing at the source activity's version id.
        $this->assertSame((int) $newversion->id, (int) $newelang->currentversionid);

        // The learner attempt and its response came across, with their internal
        // references remapped to the copied version and gap.
        $newattempt = $DB->get_record('elang_attempt', ['elangid' => $newelang->id], '*', MUST_EXIST);
        $this->assertSame((int) $student->id, (int) $newattempt->userid);
        $this->assertSame((int) $newversion->id, (int) $newattempt->versionid);
        $this->assertSame('finished', $newattempt->state);

        $newresponse = $DB->get_record('elang_response', ['attemptid' => $newattempt->id], '*', MUST_EXIST);
        $this->assertSame((int) $newgap->id, (int) $newresponse->gapid);
        $this->assertSame('chat', $newresponse->responsetext);
    }

    /**
     * An author id that has no user mapping in the restore must not be carried
     * over as a raw number: on another site that id belongs to somebody else.
     * It becomes unknown (0) instead, while the content survives.
     *
     * @return void
     */
    public function test_restore_does_not_reassign_an_unmapped_user_id(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();

        $seed = $this->seed_activity();

        // Stamp an author and a migration sign-off that the restore cannot map,
        // because no such user is part of the backup.
        $ghostid = 999999;
        $DB->set_field('elang_version', 'usermodified', $ghostid, ['id' => $seed->version->id]);
        $DB->set_field('elang', 'migrationapproveduserid', $ghostid, ['id' => $seed->elang->id]);

        $newcourseid = $this->backup_and_restore($seed->course, (int) $USER->id, false);

        $cms = get_coursemodules_in_course('elang', $newcourseid);
        $newelang = $DB->get_record('elang', ['id' => reset($cms)->instance], '*', MUST_EXIST);
        $newversion = $DB->get_record('elang_version', ['elangid' => $newelang->id], '*', MUST_EXIST);

        // The unmapped ids are cleared, never reused.
        $this->assertNotEquals($ghostid, (int) $newversion->usermodified);
        $this->assertSame(0, (int) $newversion->usermodified);
        $this->assertNotEquals($ghostid, (int) $newelang->migrationapproveduserid);
        $this->assertSame(0, (int) $newelang->migrationapproveduserid);

        // The content itself came across regardless.
        $this->assertSame(
            'Le chat dort',
            $DB->get_field('elang_cue', 'transcript', ['versionid' => $newversion->id])
        );
    }

    /**
     * When the backup carries user information, a mapped author is restored as
     * the corresponding user on the destination site.
     *
     * @return void
     */
    public function test_restore_maps_a_known_author(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();

        $seed = $this->seed_activity();
        $author = $this->getDataGenerator()->create_and_enrol($seed->course, 'editingteacher');
        $DB->set_field('elang_version', 'usermodified', $author->id, ['id' => $seed->version->id]);

        $newcourseid = $this->backup_and_restore($seed->course, (int) $USER->id);

        $cms = get_coursemodules_in_course('elang', $newcourseid);
        $newelang = $DB->get_record('elang', ['id' => reset($cms)->instance], '*', MUST_EXIST);
        $newversion = $DB->get_record('elang_version', ['elangid' => $newelang->id], '*', MUST_EXIST);

        // Restoring into the same site maps the author onto themselves.
        $this->assertSame((int) $author->id, (int) $newversion->usermodified);
    }

    /**
     * Assert that a backup taken without user information restores the content
     * but none of the learner attempts.
     *
     * @return void
     */
    public function test_backup_restore_without_userinfo_drops_attempts(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();

        $seed = $this->seed_activity();
        $course = $seed->course;
        $student = $seed->student;
        $elang = $seed->elang;
        $version = $seed->version;

        $evaluator = new \mod_elang\local\grading\answer_evaluator(
            new \mod_elang\local\grading\script_handler_manager([])
        );
        $attempts = new \mod_elang\local\domain\attempt_manager($evaluator);
        $attempt = $attempts->start_attempt((int) $elang->id, (int) $student->id, (int) $version->id);
        $attempts->finish_attempt((int) $attempt->id);

        $newcourseid = $this->backup_and_restore($course, (int) $USER->id, false);

        $cms = get_coursemodules_in_course('elang', $newcourseid);
        $newelang = $DB->get_record('elang', ['id' => reset($cms)->instance], '*', MUST_EXIST);
        $newversion = $DB->get_record('elang_version', ['elangid' => $newelang->id], '*', MUST_EXIST);

        // Content is present, current version remapped, but no attempts.
        $this->assertSame('chat', $DB->get_field_sql(
            'SELECT g.solution FROM {elang_gap} g JOIN {elang_cue} c ON c.id = g.cueid WHERE c.versionid = ?',
            [$newversion->id]
        ));
        $this->assertSame((int) $newversion->id, (int) $newelang->currentversionid);
        $this->assertSame(0, $DB->count_records('elang_attempt', ['elangid' => $newelang->id]));
    }

    /**
     * Back up a course and restore it into a fresh course as a copy.
     *
     * @param \stdClass $course The course to duplicate
     * @param int $userid The user performing the backup and restore
     * @param bool $userinfo Whether to include user data in the backup
     * @return int The id of the newly restored course
     */
    private function backup_and_restore(\stdClass $course, int $userid, bool $userinfo = true): int {
        global $CFG;

        // Keep the backup as an unzipped directory the restore can read in the
        // same run, and avoid file logging that can hold the file open.
        $CFG->backup_file_logger_level = backup::LOG_NONE;

        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $userid
        );
        $bc->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
        $bc->get_plan()->get_setting('users')->set_value($userinfo);
        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        $bc->destroy();

        $newcourseid = restore_dbops::create_new_course('Restored', 'REST' . $course->id, $course->category);
        $rc = new restore_controller(
            $backupid,
            $newcourseid,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $userid,
            backup::TARGET_NEW_COURSE
        );
        $rc->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
        $rc->get_plan()->get_setting('users')->set_value($userinfo);
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        return $newcourseid;
    }
}
