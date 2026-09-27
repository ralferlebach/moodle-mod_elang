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

use mod_elang\fixtures\authoring_test_fixture_builder;

/**
 * The partial save endpoint.
 *
 * What is checked here is the property the endpoint exists for — that leaving a
 * cue out is not a way of deleting it — and that being partial does not make it
 * a way around anything the wholesale endpoint enforces.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\save_draft_cues
 */
final class save_draft_cues_test extends \advanced_testcase {
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
     * One shaped cue.
     *
     * @param string $key The cue key
     * @param int $sortorder Position in the draft
     * @param string $transcript The cue text
     * @return array A single cue with one gap
     */
    private function cue(string $key, int $sortorder, string $transcript): array {
        return [
            'cuekey' => $key,
            'sortorder' => $sortorder,
            'starttime' => 0,
            'endtime' => 5000,
            'transcript' => $transcript,
            'transcriptformat' => FORMAT_PLAIN,
            'gaps' => [[
                'gapkey' => $key . '-gap',
                'sortorder' => 1,
                'charstart' => 0,
                'charlength' => 2,
                'solution' => 'Le',
                'gradingalgorithm' => 'exact',
                'maxlength' => 0,
                'linkurl' => '',
                'answers' => [],
                'hints' => [],
            ]],
        ];
    }

    /**
     * Put three cues into the draft and return the revision afterwards.
     *
     * @return int The draft revision
     */
    private function seed_three(): int {
        global $DB;

        save_draft_version::execute((int) $this->draft->id, -1, [
            $this->cue('a', 1, 'Le chat dort'),
            $this->cue('b', 2, 'Le chien court'),
            $this->cue('c', 3, 'La souris mange'),
        ]);

        return (int) $DB->get_field('elang_version', 'revision', ['id' => $this->draft->id]);
    }

    /**
     * An unmentioned cue survives; the mentioned ones are written.
     *
     * The case from the issue: cue B has an end time before its start time, so
     * the editor holds it back and sends A and C. B must still be there, with
     * the text the server last accepted.
     *
     * @return void
     */
    public function test_cues_left_out_of_the_payload_are_not_deleted(): void {
        global $DB;

        $this->setUser($this->teacher);
        $revision = $this->seed_three();

        save_draft_cues::execute((int) $this->draft->id, $revision, [
            $this->cue('a', 1, 'Le chat rêve'),
            $this->cue('c', 3, 'La souris dort'),
        ]);

        $cues = $DB->get_records(
            'elang_cue',
            ['versionid' => $this->draft->id],
            'sortorder ASC',
            'cuekey, transcript'
        );

        $this->assertSame(['a', 'b', 'c'], array_keys($cues));
        $this->assertSame('Le chat rêve', $cues['a']->transcript);
        $this->assertSame('Le chien court', $cues['b']->transcript, 'The held-back cue kept its saved text.');
        $this->assertSame('La souris dort', $cues['c']->transcript);
    }

    /**
     * Deletion is explicit and takes the cue's gaps with it.
     *
     * @return void
     */
    public function test_named_cue_keys_are_removed(): void {
        global $DB;

        $this->setUser($this->teacher);
        $revision = $this->seed_three();

        save_draft_cues::execute((int) $this->draft->id, $revision, [], ['b']);

        $this->assertSame(
            ['a', 'c'],
            array_values($DB->get_fieldset_select(
                'elang_cue',
                'cuekey',
                'versionid = ? ORDER BY sortorder ASC',
                [$this->draft->id]
            ))
        );
        $this->assertSame(2, $DB->count_records_sql(
            'SELECT COUNT(g.id) FROM {elang_gap} g JOIN {elang_cue} c ON c.id = g.cueid WHERE c.versionid = ?',
            [$this->draft->id]
        ));
    }

    /**
     * The revision moves once per call, and a stale one is refused.
     *
     * @return void
     */
    public function test_a_stale_expected_revision_is_rejected(): void {
        global $DB;

        $this->setUser($this->teacher);
        $revision = $this->seed_three();

        $result = save_draft_cues::execute((int) $this->draft->id, $revision, [$this->cue('a', 1, 'Le chat rêve')]);
        $this->assertSame($revision + 1, $result['revision']);

        $this->expectException(\moodle_exception::class);
        save_draft_cues::execute((int) $this->draft->id, $revision, [$this->cue('a', 1, 'Le chat chasse')]);
    }

    /**
     * A published version is not editable through this endpoint either.
     *
     * @return void
     */
    public function test_a_published_version_cannot_be_edited(): void {
        global $DB;

        $this->setUser($this->teacher);
        $this->seed_three();
        $DB->set_field('elang_version', 'status', 'published', ['id' => $this->draft->id]);

        $this->expectException(\moodle_exception::class);
        save_draft_cues::execute((int) $this->draft->id, -1, [$this->cue('a', 1, 'Le chat rêve')]);
    }

    /**
     * Saving part of a draft still needs the capability to manage it.
     *
     * @return void
     */
    public function test_saving_requires_the_manage_capability(): void {
        $this->setUser($this->teacher);
        $this->seed_three();

        $this->setUser($this->student);
        $this->expectException(\moodle_exception::class);
        save_draft_cues::execute((int) $this->draft->id, -1, [$this->cue('a', 1, 'Le chat rêve')]);
    }

    /**
     * A regular expression in an accepted answer still needs its own capability.
     *
     * A teacher may manage the activity without being trusted to run regular
     * expressions against learner input. The wholesale endpoint checks this;
     * a partial save that did not would be a way around it.
     *
     * @return void
     */
    public function test_a_regex_answer_still_needs_the_useregex_capability(): void {
        global $DB;

        $this->setUser($this->teacher);
        $revision = $this->seed_three();

        $context = \context_module::instance(
            get_coursemodule_from_instance(
                'elang',
                (int) $DB->get_field('elang_version', 'elangid', ['id' => $this->draft->id])
            )->id
        );
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher']);
        assign_capability('mod/elang:useregex', CAP_PROHIBIT, $roleid, $context->id, true);

        $cue = $this->cue('a', 1, 'Le chat dort');
        $cue['gaps'][0]['answers'] = [['sortorder' => 1, 'answer' => 'ch.t', 'isregex' => 1]];

        $this->expectException(\moodle_exception::class);
        save_draft_cues::execute((int) $this->draft->id, $revision, [$cue]);
    }
}
