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
    }

    /**
     * Gradebook support is declared now that elang_grade_item_update()/
     * elang_update_grades() exist.
     *
     * @return void
     */
    public function test_gradebook_feature_is_declared(): void {
        $this->resetAfterTest();

        $this->assertTrue(elang_supports(FEATURE_GRADE_HAS_GRADE));
    }

    /**
     * Custom completion support is declared now that
     * classes/completion/custom_completion.php exists.
     *
     * @return void
     */
    public function test_completion_feature_is_declared(): void {
        $this->resetAfterTest();

        $this->assertTrue(elang_supports(FEATURE_COMPLETION_HAS_RULES));
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

    /**
     * Deleting an instance removes every dependent record in the versioned
     * schema: version, cue, gap, gapanswer, gaphint, attempt and response.
     *
     * @return void
     */
    public function test_delete_instance_cascades_through_the_versioned_schema(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);

        $version = $generator->create_version(['elangid' => $elang->id]);
        $cue = $generator->create_cue(['versionid' => $version->id]);
        $gap = $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);
        $generator->create_gapanswer(['gapid' => $gap->id, 'answer' => 'chats']);
        $generator->create_gaphint(['gapid' => $gap->id]);

        $attempt = (object) [
            'elangid' => $elang->id,
            'versionid' => $version->id,
            'userid' => $student->id,
            'attemptnumber' => 1,
            'timestart' => time(),
            'timemodified' => time(),
        ];
        $attempt->id = $DB->insert_record('elang_attempt', $attempt);

        $response = (object) [
            'attemptid' => $attempt->id,
            'gapid' => $gap->id,
            'responsetext' => 'chat',
            'timecreated' => time(),
            'timemodified' => time(),
        ];
        $DB->insert_record('elang_response', $response);

        $this->assertTrue(elang_delete_instance($elang->id));

        $this->assertSame(0, $DB->count_records('elang_version', ['elangid' => $elang->id]));
        $this->assertSame(0, $DB->count_records('elang_cue', ['versionid' => $version->id]));
        $this->assertSame(0, $DB->count_records('elang_gap', ['cueid' => $cue->id]));
        $this->assertSame(0, $DB->count_records('elang_gapanswer', ['gapid' => $gap->id]));
        $this->assertSame(0, $DB->count_records('elang_gaphint', ['gapid' => $gap->id]));
        $this->assertSame(0, $DB->count_records('elang_attempt', ['elangid' => $elang->id]));
        $this->assertSame(0, $DB->count_records('elang_response', ['attemptid' => $attempt->id]));
    }

    /**
     * Creating an instance creates its gradebook grade item with the
     * configured maximum grade.
     *
     * @return void
     */
    public function test_creating_an_instance_creates_a_grade_item(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $elang = $this->getDataGenerator()->create_module('elang', ['course' => $course->id, 'grade' => 50]);

        $gradeitem = $DB->get_record('grade_items', [
            'itemtype' => 'mod',
            'itemmodule' => 'elang',
            'iteminstance' => $elang->id,
        ], '*', MUST_EXIST);

        $this->assertEqualsWithDelta(50.0, (float) $gradeitem->grademax, 0.00001);
        $this->assertEqualsWithDelta(0.0, (float) $gradeitem->grademin, 0.00001);
    }

    /**
     * elang_update_grades() pushes the highest finished-attempt score into
     * the gradebook, scaled to the activity's configured maximum grade.
     *
     * @return void
     */
    public function test_update_grades_pushes_the_best_finished_score(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id, 'language' => 'fr', 'grade' => 100]);

        $finalgrade = $this->finalgrade_after_perfect_attempt($elang, $student, $generator);
        $this->assertEqualsWithDelta(100.0, $finalgrade, 0.00001);
    }

    /**
     * A user with no finished attempts gets no positive grade pushed.
     *
     * @return void
     */
    public function test_update_grades_does_not_grade_a_user_with_no_finished_attempts(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id, 'grade' => 100]);

        elang_update_grades($elang, (int) $student->id);

        $gradegrade = $DB->get_record_sql(
            'SELECT gg.finalgrade
               FROM {grade_grades} gg
               JOIN {grade_items} gi ON gi.id = gg.itemid
              WHERE gi.itemtype = ? AND gi.itemmodule = ? AND gi.iteminstance = ? AND gg.userid = ?',
            ['mod', 'elang', $elang->id, $student->id]
        );

        $this->assertTrue($gradegrade === false || $gradegrade->finalgrade === null);
    }

    /**
     * Deleting an instance removes its gradebook grade item along with everything else.
     *
     * @return void
     */
    public function test_delete_instance_removes_the_grade_item(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $elang = $this->getDataGenerator()->create_module('elang', ['course' => $course->id]);

        $this->assertTrue($DB->record_exists('grade_items', [
            'itemtype' => 'mod',
            'itemmodule' => 'elang',
            'iteminstance' => $elang->id,
        ]));

        elang_delete_instance($elang->id);

        $this->assertFalse($DB->record_exists('grade_items', [
            'itemtype' => 'mod',
            'itemmodule' => 'elang',
            'iteminstance' => $elang->id,
        ]));
    }

    /**
     * elang_get_coursemodule_info() populates customdata['customcompletionrules']
     * with the instance's own completionfinishattempt value, but only when
     * completion tracking is automatic — this is what
     * \core_completion\activity_custom_completion::validate_rule() reads to
     * decide whether the rule is "in use" for a given course module, so
     * getting this wrong silently breaks completion state checks regardless
     * of custom_completion::get_state()'s own correctness.
     *
     * @return void
     */
    public function test_get_coursemodule_info_populates_custom_completion_rules(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $elang = $this->getDataGenerator()->create_module('elang', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionfinishattempt' => 1,
        ]);

        $coursemodule = get_coursemodule_from_instance('elang', $elang->id);
        $info = elang_get_coursemodule_info($coursemodule);

        $this->assertNotFalse($info);
        $this->assertSame(1, $info->customdata['customcompletionrules']['completionfinishattempt']);
    }

    /**
     * With completion tracking off, no custom completion rule data is
     * populated at all — matching core's own documented convention (see
     * forum_get_coursemodule_info()) of only doing so when completion is
     * COMPLETION_TRACKING_AUTOMATIC.
     *
     * customdata itself is never initialised to an array by
     * elang_get_coursemodule_info() unless the automatic-completion branch
     * runs — a fresh cached_cm_info's customdata is null until something is
     * assigned to it, so this asserts with empty() rather than
     * assertArrayNotHasKey(), which requires an actual array/ArrayAccess
     * argument and would otherwise throw on a null (confirmed against a
     * real PHPUnit run, not assumed).
     *
     * @return void
     */
    public function test_get_coursemodule_info_omits_rules_without_automatic_completion(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $elang = $this->getDataGenerator()->create_module('elang', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_NONE,
        ]);

        $coursemodule = get_coursemodule_from_instance('elang', $elang->id);
        $info = elang_get_coursemodule_info($coursemodule);

        $this->assertNotFalse($info);
        $this->assertTrue(empty($info->customdata['customcompletionrules']));
    }

    /**
     * A scale-graded activity (elang.grade < 0) maps the attempt score onto
     * a scale item position, never a negative rawgrade. Before this fix,
     * elang_update_grades() multiplied the score fraction by the same
     * negative value used for GRADE_TYPE_SCALE configuration, which always
     * produced a negative rawgrade — scale positions are 1-indexed, never
     * negative.
     *
     * @return void
     */
    public function test_update_grades_maps_a_perfect_score_onto_the_top_scale_item(): void {
        global $CFG;

        $this->resetAfterTest();
        require_once($CFG->libdir . '/gradelib.php');

        $scale = new \grade_scale();
        $scale->courseid = 0;
        $scale->userid = 0;
        $scale->name = 'mod_elang test scale';
        $scale->scale = 'Poor,Average,Good,Excellent';
        $scale->description = '';
        $scale->descriptionformat = FORMAT_MOODLE;
        $scale->id = $scale->insert();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id, 'language' => 'fr', 'grade' => -$scale->id]);

        $finalgrade = $this->finalgrade_after_perfect_attempt($elang, $student, $generator);
        // A perfect (1.0) score maps onto the highest of the 4 scale items
        // (position 4, "Excellent") — never a negative value.
        $this->assertEqualsWithDelta(4.0, $finalgrade, 0.00001);
    }

    /**
     * elang_score_to_rawgrade() maps a fractional score proportionally onto
     * a scale's item positions, clamped to the valid 1..N range.
     *
     * @return void
     */
    public function test_score_to_rawgrade_maps_fractions_onto_scale_positions(): void {
        // A 4-item scale: positions 1..4.
        $this->assertEqualsWithDelta(1.0, elang_score_to_rawgrade(0.0, -7, 4), 0.00001);
        $this->assertEqualsWithDelta(4.0, elang_score_to_rawgrade(1.0, -7, 4), 0.00001);
        $this->assertEqualsWithDelta(3.0, elang_score_to_rawgrade(0.67, -7, 4), 0.00001);

        // A numeric grade is unaffected: still a plain fraction of the maximum.
        $this->assertEqualsWithDelta(50.0, elang_score_to_rawgrade(0.5, 100, 0), 0.00001);
    }

    /**
     * Run one full, correct attempt for $student on $elang and return the
     * final gradebook grade elang_update_grades() pushes. Shared by the
     * point-grade and scale-grade update_grades tests, which differ only in
     * the grade setup and the expected value.
     *
     * @param \stdClass $elang The activity
     * @param \stdClass $student The enrolled learner
     * @param \mod_elang_generator $generator The plugin data generator
     * @return float The learner's final grade for the activity
     */
    private function finalgrade_after_perfect_attempt(
        \stdClass $elang,
        \stdClass $student,
        \mod_elang_generator $generator
    ): float {
        global $DB;

        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draft = $versionmanager->create_draft($elang->id, $student->id);
        $cue = $generator->create_cue(['versionid' => $draft->id]);
        $gap = $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);
        $versionmanager->publish($draft->id, $student->id);

        $attemptmanager = new \mod_elang\local\domain\attempt_manager(
            new \mod_elang\local\grading\answer_evaluator(new \mod_elang\local\grading\script_handler_manager([]))
        );
        $attempt = $attemptmanager->start_attempt($elang->id, $student->id, $draft->id);
        $attemptmanager->submit_response($attempt->id, $gap->id, 'chat');
        $attemptmanager->finish_attempt($attempt->id);

        elang_update_grades($elang, (int) $student->id);

        $gradegrade = $DB->get_record_sql(
            'SELECT gg.finalgrade
               FROM {grade_grades} gg
               JOIN {grade_items} gi ON gi.id = gg.itemid
              WHERE gi.itemtype = ? AND gi.itemmodule = ? AND gi.iteminstance = ? AND gg.userid = ?',
            ['mod', 'elang', $elang->id, $student->id]
        );
        $this->assertNotFalse($gradegrade);

        return (float) $gradegrade->finalgrade;
    }
}
