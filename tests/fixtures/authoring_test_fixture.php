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

/**
 * Shared fixture builder for the authoring external-function tests
 * (get_version_content, save_draft_version, set_draft_media, preview_import,
 * publish_version). They all begin from the same starting point: a course with
 * an editing teacher and a student, plus a fresh draft version owned by the
 * teacher. Centralising that here removes the near-identical setUp() blocks the
 * copy/paste detector flagged across those five files.
 *
 * A plain class with a static factory method, not a trait: a trait consumed via
 * "use TraitName;" inside a class body is resolved when that class is compiled,
 * before setUpBeforeClass() can require the file — which fails with "Trait not
 * found" on a real Moodle 4.5 test run. The same reasoning is documented on
 * attempt_test_fixture_builder; this follows that established pattern.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elang\fixtures;

use mod_elang\local\domain\version_manager;

/**
 * Builds a course, an editing teacher, a student and a fresh draft version for
 * the authoring external-function tests.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class authoring_test_fixture_builder {
    /**
     * Create the shared authoring starting point.
     *
     * @param \advanced_testcase $testcase The calling test case, already past
     *        resetAfterTest()
     * @return object{elang: \stdClass, teacher: \stdClass, student: \stdClass, draft: \stdClass}
     */
    public static function create(\advanced_testcase $testcase): object {
        $course = $testcase->getDataGenerator()->create_course();
        $teacher = $testcase->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $student = $testcase->getDataGenerator()->create_and_enrol($course, 'student');

        /** @var \mod_elang_generator $generator */
        $generator = $testcase->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);
        $draft = (new version_manager())->create_draft((int) $elang->id, (int) $teacher->id);

        return (object) [
            'elang' => $elang,
            'teacher' => $teacher,
            'student' => $student,
            'draft' => $draft,
        ];
    }

    /**
     * Build a one-cue, one-gap payload with the given gap solution, suitable
     * for save_draft_version::execute(). Shared by the authoring tests that
     * need a minimal but complete cue list.
     *
     * @param string $solution The gap solution to embed
     * @return array A cue list suitable for save_draft_version::execute()
     */
    public static function payload(string $solution): array {
        return [
            [
                'cuekey' => 'cue-1',
                'sortorder' => 1,
                'starttime' => 0,
                'endtime' => 5000,
                'transcript' => 'Le chat dort',
                'transcriptformat' => FORMAT_PLAIN,
                'gaps' => [
                    [
                        'gapkey' => 'gap-1',
                        'sortorder' => 1,
                        'charstart' => 3,
                        'charlength' => 4,
                        'solution' => $solution,
                        'gradingalgorithm' => 'exact',
                        'maxlength' => 0,
                        'linkurl' => '',
                        'answers' => [
                            ['sortorder' => 1, 'answer' => 'chatte', 'isregex' => 0],
                        ],
                        'hints' => [
                            ['level' => 1, 'hinttype' => 'text', 'hinttext' => 'animal', 'penalty' => 0.1],
                        ],
                    ],
                ],
            ],
        ];
    }
}
