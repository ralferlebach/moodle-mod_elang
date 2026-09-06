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

namespace mod_elang\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Tests for the mod_elang privacy provider.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\privacy\provider
 */
final class provider_test extends \core_privacy\tests\provider_testcase {
    /** @var \stdClass */
    private $cm;

    /** @var \stdClass */
    private $context;

    /** @var \stdClass */
    private $student;

    /** @var \stdClass */
    private $otherstudent;

    /** @var int */
    private $attemptid;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $this->student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->otherstudent = $this->getDataGenerator()->create_and_enrol($course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id, 'language' => 'fr']);
        $this->cm = get_coursemodule_from_instance('elang', $elang->id);
        $this->context = \context_module::instance($this->cm->id);

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draft = $versionmanager->create_draft($elang->id, $this->student->id);
        $cue = $generator->create_cue(['versionid' => $draft->id]);
        $gap = $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);
        $versionmanager->publish($draft->id, $this->student->id);

        $attemptmanager = new \mod_elang\local\domain\attempt_manager(
            new \mod_elang\local\grading\answer_evaluator(new \mod_elang\local\grading\script_handler_manager([]))
        );
        $attempt = $attemptmanager->start_attempt($elang->id, $this->student->id, $draft->id);
        $attemptmanager->submit_response($attempt->id, $gap->id, 'chat');
        $this->attemptid = $attempt->id;
    }

    /**
     * A user who only authored a content version — never attempting the
     * exercise — still has personal data in the activity: their id is stamped on
     * the version, so discovery, the userlist and export must all find them, and
     * erasure must detach them while keeping the content.
     *
     * @return void
     */
    public function test_an_author_without_attempts_is_covered(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $author = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);
        $cm = get_coursemodule_from_instance('elang', $elang->id);
        $context = \context_module::instance($cm->id);

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draft = $versionmanager->create_draft($elang->id, $author->id);
        $versionmanager->publish($draft->id, $author->id);
        $this->assertSame((int) $author->id, (int) $DB->get_field('elang_version', 'usermodified', ['id' => $draft->id]));

        // Discovery finds the activity even though the author never attempted it.
        $contextlist = provider::get_contexts_for_userid((int) $author->id);
        // Cast both sides: contextlist ids can come back as strings (see the
        // note in test_get_contexts_for_userid_finds_the_activity_context).
        $this->assertContains((int) $context->id, array_map('intval', $contextlist->get_contextids()));

        // The userlist includes the author.
        $userlist = new userlist($context, 'mod_elang');
        provider::get_users_in_context($userlist);
        $this->assertContains((int) $author->id, array_map('intval', $userlist->get_userids()));

        // Export writes the authoring data.
        $this->export_context_data_for_user((int) $author->id, $context, 'mod_elang');
        $data = writer::with_context($context)->get_data([get_string('pluginname', 'mod_elang')]);
        $this->assertNotEmpty($data->authoredversions);

        // Erasure detaches the author but keeps the version itself.
        $approved = new approved_contextlist(
            \core_user::get_user((int) $author->id),
            'mod_elang',
            [$context->id]
        );
        provider::delete_data_for_user($approved);
        $this->assertTrue($DB->record_exists('elang_version', ['id' => $draft->id]));
        $this->assertSame(0, (int) $DB->get_field('elang_version', 'usermodified', ['id' => $draft->id]));
    }

    /**
     * The user who signed off a 1.x migration is personal data too, and is
     * discovered, listed and detached on erasure.
     *
     * @return void
     */
    public function test_the_migration_signoff_user_is_covered(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $admin = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);
        $cm = get_coursemodule_from_instance('elang', $elang->id);
        $context = \context_module::instance($cm->id);

        $DB->set_field('elang', 'migrationapproveduserid', $admin->id, ['id' => $elang->id]);
        $DB->set_field('elang', 'migrationapprovedtime', time(), ['id' => $elang->id]);

        $contextlist = provider::get_contexts_for_userid((int) $admin->id);
        // Cast both sides: contextlist ids can come back as strings (see the
        // note in test_get_contexts_for_userid_finds_the_activity_context).
        $this->assertContains((int) $context->id, array_map('intval', $contextlist->get_contextids()));

        $userlist = new userlist($context, 'mod_elang');
        provider::get_users_in_context($userlist);
        $this->assertContains((int) $admin->id, array_map('intval', $userlist->get_userids()));

        $approved = new approved_contextlist(
            \core_user::get_user((int) $admin->id),
            'mod_elang',
            [$context->id]
        );
        provider::delete_data_for_user($approved);
        $this->assertSame(0, (int) $DB->get_field('elang', 'migrationapproveduserid', ['id' => $elang->id]));
    }

    /**
     * A user with an attempt has the activity context in their contextlist.
     *
     * @return void
     */
    public function test_get_contexts_for_userid_finds_the_activity_context(): void {
        $contextlist = provider::get_contexts_for_userid($this->student->id);

        // PHPUnit's assertContains() has used strict (===) comparison since
        // PHPUnit 9 (see sebastianbergmann/phpunit#3426);
        // contextlist::get_contextids() is backed by a raw SQL query whose
        // results can come back as strings on MariaDB/PDO even for an
        // integer column, so both sides are cast to int here rather than
        // relying on type coercion.
        $this->assertContains((int) $this->context->id, array_map('intval', $contextlist->get_contextids()));
    }

    /**
     * A user without any attempt has no contexts.
     *
     * @return void
     */
    public function test_get_contexts_for_userid_is_empty_for_a_user_without_attempts(): void {
        $contextlist = provider::get_contexts_for_userid($this->otherstudent->id);

        $this->assertEmpty($contextlist->get_contextids());
    }

    /**
     * Exporting data for the student includes their attempt and response.
     *
     * @return void
     */
    public function test_export_user_data_includes_the_attempt_and_response(): void {
        $approvedlist = new approved_contextlist($this->student, 'mod_elang', [$this->context->id]);
        provider::export_user_data($approvedlist);

        $writer = writer::with_context($this->context);
        $this->assertTrue($writer->has_any_data());

        $data = $writer->get_data([get_string('pluginname', 'mod_elang')]);
        $this->assertCount(1, $data->attempts);
        $this->assertCount(1, $data->attempts[0]->responses);
        $this->assertSame('chat', $data->attempts[0]->responses[0]->responsetext);
    }

    /**
     * Exported attempt and response data includes the aggregate counters
     * and per-response hint/score fields, not only the fields the very
     * first version of this provider covered.
     *
     * @return void
     */
    public function test_export_user_data_includes_aggregate_and_hint_fields(): void {
        $approvedlist = new approved_contextlist($this->student, 'mod_elang', [$this->context->id]);
        provider::export_user_data($approvedlist);

        $data = writer::with_context($this->context)->get_data([get_string('pluginname', 'mod_elang')]);
        $attempt = $data->attempts[0];

        // Using property_exists() rather than assertObjectHasAttribute()/
        // assertObjectHasProperty(): the former was removed in PHPUnit 10,
        // the latter does not exist before PHPUnit 10, and this plugin's CI
        // matrix spans both (PHPUnit 9.6 on Moodle 4.5, PHPUnit 11 from
        // Moodle 5.0 onwards).
        foreach (['versionid', 'totalgaps', 'answeredgaps', 'exactgaps', 'correctgaps', 'hintedgaps', 'timemodified'] as $field) {
            $this->assertTrue(property_exists($attempt, $field), "exported attempt is missing '$field'");
        }

        $response = $attempt->responses[0];
        foreach (['hintlevel', 'score', 'timemodified'] as $field) {
            $this->assertTrue(property_exists($response, $field), "exported response is missing '$field'");
        }
    }

    /**
     * The metadata declaration describes every personal field actually
     * stored on elang_attempt and elang_response, not only a subset.
     *
     * @return void
     */
    public function test_metadata_describes_every_personal_field(): void {
        $collection = new \core_privacy\local\metadata\collection('mod_elang');
        $collection = provider::get_metadata($collection);

        $fieldsbytable = [];
        foreach ($collection->get_collection() as $item) {
            if ($item instanceof \core_privacy\local\metadata\types\database_table) {
                $fieldsbytable[$item->get_name()] = array_keys($item->get_privacy_fields());
            }
        }

        $expectedattemptfields = [
            'versionid', 'userid', 'attemptnumber', 'state', 'totalgaps', 'answeredgaps',
            'exactgaps', 'correctgaps', 'hintedgaps', 'score', 'timestart', 'timefinish', 'timemodified',
        ];
        foreach ($expectedattemptfields as $field) {
            $this->assertContains($field, $fieldsbytable['elang_attempt'], "elang_attempt.$field is not described in metadata");
        }

        $expectedresponsefields = [
            'responsetext', 'resultstate', 'accepted', 'tries', 'hintlevel', 'score',
            'timecreated', 'timemodified',
        ];
        foreach ($expectedresponsefields as $field) {
            $this->assertContains($field, $fieldsbytable['elang_response'], "elang_response.$field is not described in metadata");
        }
    }

    /**
     * The activity context lists exactly the users who have attempted it.
     *
     * @return void
     */
    public function test_get_users_in_context_finds_the_attempting_user(): void {
        $userlist = new userlist($this->context, 'mod_elang');
        provider::get_users_in_context($userlist);

        $this->assertEqualsCanonicalizing([(int) $this->student->id], $userlist->get_userids());
    }

    /**
     * Deleting all data in a context removes every attempt and response.
     *
     * @return void
     */
    public function test_delete_data_for_all_users_in_context_removes_everything(): void {
        global $DB;

        provider::delete_data_for_all_users_in_context($this->context);

        $this->assertSame(0, $DB->count_records('elang_attempt', ['id' => $this->attemptid]));
        $this->assertSame(0, $DB->count_records('elang_response', ['attemptid' => $this->attemptid]));
    }

    /**
     * Deleting data for one user does not touch another user's data in the
     * same context.
     *
     * @return void
     */
    public function test_delete_data_for_user_only_removes_that_users_data(): void {
        global $DB;

        $attemptmanager = new \mod_elang\local\domain\attempt_manager(
            new \mod_elang\local\grading\answer_evaluator(new \mod_elang\local\grading\script_handler_manager([]))
        );
        $currentversionid = $DB->get_field('elang', 'currentversionid', ['id' => $this->cm->instance], MUST_EXIST);
        $otherattempt = $attemptmanager->start_attempt($this->cm->instance, $this->otherstudent->id, $currentversionid);

        $approvedlist = new approved_contextlist($this->student, 'mod_elang', [$this->context->id]);
        provider::delete_data_for_user($approvedlist);

        $this->assertSame(0, $DB->count_records('elang_attempt', ['id' => $this->attemptid]));
        $this->assertSame(1, $DB->count_records('elang_attempt', ['id' => $otherattempt->id]));
    }

    /**
     * Deleting data for an approved userlist removes only the listed users.
     *
     * @return void
     */
    public function test_delete_data_for_users_removes_only_listed_users(): void {
        global $DB;

        $attemptmanager = new \mod_elang\local\domain\attempt_manager(
            new \mod_elang\local\grading\answer_evaluator(new \mod_elang\local\grading\script_handler_manager([]))
        );
        $otherattempt = $attemptmanager->start_attempt(
            $this->cm->instance,
            $this->otherstudent->id,
            $DB->get_field('elang', 'currentversionid', ['id' => $this->cm->instance])
        );

        $approvedlist = new approved_userlist($this->context, 'mod_elang', [$this->student->id]);
        provider::delete_data_for_users($approvedlist);

        $this->assertSame(0, $DB->count_records('elang_attempt', ['id' => $this->attemptid]));
        $this->assertSame(1, $DB->count_records('elang_attempt', ['id' => $otherattempt->id]));
    }

    /**
     * Erasing one person's data detaches them from the authoring trail without
     * taking the exercise with them.
     *
     * The versions belong to the course, not to the author; what has to go is
     * the identifying reference. Deleting the content instead would erase other
     * people's work — including the learners still attempting it — which is not
     * what a right-to-erasure request asks for.
     *
     * @return void
     */
    public function test_erasure_detaches_the_author_but_keeps_the_content(): void {
        global $DB;

        $versionid = (int) $DB->get_field('elang', 'currentversionid', ['id' => $this->cm->instance], MUST_EXIST);
        $this->assertSame(
            (int) $this->student->id,
            (int) $DB->get_field('elang_version', 'usermodified', ['id' => $versionid], MUST_EXIST)
        );
        $cuecount = $DB->count_records('elang_cue', ['versionid' => $versionid]);
        $this->assertGreaterThan(0, $cuecount);

        provider::delete_data_for_user(
            new approved_contextlist($this->student, 'mod_elang', [$this->context->id])
        );

        // The stamp is gone...
        $this->assertSame(
            0,
            (int) $DB->get_field('elang_version', 'usermodified', ['id' => $versionid], MUST_EXIST)
        );
        // ...and the exercise is still there.
        $this->assertTrue($DB->record_exists('elang_version', ['id' => $versionid]));
        $this->assertSame($cuecount, $DB->count_records('elang_cue', ['versionid' => $versionid]));
    }

    /**
     * The migration sign-off is detached too.
     *
     * It names the person who approved a migrated activity, so it is personal
     * data even though nothing about it looks like a learner record.
     *
     * @return void
     */
    public function test_erasure_detaches_the_migration_signoff(): void {
        global $DB;

        $DB->set_field('elang', 'migrationapproveduserid', $this->student->id, ['id' => $this->cm->instance]);

        provider::delete_data_for_user(
            new approved_contextlist($this->student, 'mod_elang', [$this->context->id])
        );

        $this->assertNull(
            $DB->get_field('elang', 'migrationapproveduserid', ['id' => $this->cm->instance], MUST_EXIST) ?: null
        );
    }

    /**
     * Erasing one person's authoring trail leaves another person's alone.
     *
     * @return void
     */
    public function test_erasure_leaves_another_authors_trail_alone(): void {
        global $DB;

        $versionid = (int) $DB->get_field('elang', 'currentversionid', ['id' => $this->cm->instance], MUST_EXIST);
        $DB->set_field('elang_version', 'usermodified', $this->otherstudent->id, ['id' => $versionid]);

        provider::delete_data_for_user(
            new approved_contextlist($this->student, 'mod_elang', [$this->context->id])
        );

        $this->assertSame(
            (int) $this->otherstudent->id,
            (int) $DB->get_field('elang_version', 'usermodified', ['id' => $versionid], MUST_EXIST)
        );
    }

    /**
     * A context that is not this activity is left alone.
     *
     * approved_contextlist and the userlist API can carry a course or system
     * context, and a provider that acted on one would delete across a whole
     * site rather than across one activity.
     *
     * @return void
     */
    public function test_a_non_module_context_is_ignored(): void {
        global $DB;

        provider::delete_data_for_all_users_in_context(\context_course::instance($this->cm->course));
        provider::delete_data_for_all_users_in_context(\context_system::instance());

        $this->assertSame(1, $DB->count_records('elang_attempt', ['id' => $this->attemptid]));
        $this->assertSame(1, $DB->count_records('elang_response', ['attemptid' => $this->attemptid]));
    }

    /**
     * Wiping one activity does not reach into another.
     *
     * @return void
     */
    public function test_wiping_one_activity_leaves_another_alone(): void {
        global $DB;

        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $second = $generator->create_instance(['course' => $this->cm->course, 'language' => 'fr']);
        $secondversion = $generator->create_version(['elangid' => $second->id, 'status' => 'published']);
        $secondcue = $generator->create_cue(['versionid' => $secondversion->id]);
        $generator->create_gap(['cueid' => $secondcue->id, 'solution' => 'chat']);

        $attemptmanager = new \mod_elang\local\domain\attempt_manager(
            new \mod_elang\local\grading\answer_evaluator(new \mod_elang\local\grading\script_handler_manager([]))
        );
        $secondattempt = $attemptmanager->start_attempt(
            (int) $second->id,
            (int) $this->student->id,
            (int) $secondversion->id
        );

        provider::delete_data_for_all_users_in_context($this->context);

        $this->assertSame(0, $DB->count_records('elang_attempt', ['id' => $this->attemptid]));
        $this->assertSame(1, $DB->count_records('elang_attempt', ['id' => $secondattempt->id]));
    }

    /**
     * Removing the activity takes its personal data with it.
     *
     * This is the lifecycle question the privacy API does not answer: a course
     * cleanup deletes activities directly, without going through any of the
     * provider methods. If the module's own deletion left attempts and
     * responses behind, a site would keep learner answers for an exercise that
     * no longer exists, and nothing would ever surface them again.
     *
     * @return void
     */
    public function test_deleting_the_activity_removes_its_personal_data(): void {
        global $DB;

        $this->assertSame(1, $DB->count_records('elang_attempt', ['id' => $this->attemptid]));
        $this->assertGreaterThan(0, $DB->count_records('elang_response', ['attemptid' => $this->attemptid]));

        course_delete_module((int) $this->cm->id);

        $this->assertSame(0, $DB->count_records('elang_attempt', ['id' => $this->attemptid]));
        $this->assertSame(0, $DB->count_records('elang_response', ['attemptid' => $this->attemptid]));
        $this->assertSame(0, $DB->count_records('elang', ['id' => $this->cm->instance]));
    }

    /**
     * Every table with a column naming a person is declared in the metadata.
     *
     * Derived from db/install.xml rather than from a list written here: a table
     * added later with a userid column would otherwise be personal data the
     * privacy API never mentions, and nobody would notice until an export came
     * back incomplete.
     *
     * @return void
     */
    public function test_every_table_naming_a_person_is_declared(): void {
        global $CFG;

        $schema = file_get_contents($CFG->dirroot . '/mod/elang/db/install.xml');
        $this->assertNotFalse($schema);

        $carrying = [];
        preg_match_all('~<TABLE NAME="([a-z_]+)"(.*?)</TABLE>~s', $schema, $tables, PREG_SET_ORDER);
        foreach ($tables as $table) {
            if (preg_match('~<FIELD NAME="[a-z]*user[a-z]*"~', $table[2])) {
                $carrying[] = $table[1];
            }
        }
        $this->assertNotEmpty($carrying, 'The schema should still carry user references.');

        $declared = [];
        foreach (provider::get_metadata(new collection('mod_elang'))->get_collection() as $item) {
            $declared[] = $item->get_name();
        }
        foreach ($carrying as $table) {
            $this->assertContains(
                $table,
                $declared,
                "$table has a column naming a person but is not described in get_metadata()."
            );
        }
    }
}
