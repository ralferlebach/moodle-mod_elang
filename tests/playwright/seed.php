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
 * CLI seeder for the Playwright browser/accessibility tests.
 *
 * Creates a throwaway course, an elang activity with a small published version,
 * and a dedicated editing-teacher login, then prints the `export` lines the
 * Playwright config consumes (ELANG_BASE_URL/USER/PASS/CMID/VERSIONID).
 *
 * Run on a disposable dev/staging site only.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/user/lib.php');

global $CFG, $DB;

$admin = get_admin();
\core\session\manager::set_user($admin);

$unique = time();
$course = create_course((object) [
    'fullname' => 'Elang PW ' . $unique,
    'shortname' => 'elangpw' . $unique,
    'category' => 1,
    'summary' => '',
    'summaryformat' => FORMAT_HTML,
    'format' => 'topics',
]);

$moduleinfo = create_module((object) [
    'modulename' => 'elang',
    'course' => $course->id,
    'section' => 0,
    'visible' => 1,
    'name' => 'Studio fixture ' . $unique,
    'introeditor' => ['text' => 'Playwright fixture', 'format' => FORMAT_HTML, 'itemid' => 0],
    'language' => 'en',
    'jarothreshold' => 90,
    'grade' => 100,
    'completion' => 0,
    'cmidnumber' => '',
    'groupmode' => 0,
    'groupingid' => 0,
    'visibleoncoursepage' => 1,
]);
$cmid = (int) $moduleinfo->coursemodule;
$elangid = (int) $moduleinfo->instance;

// A small published version so the view/player and report have content.
$cues = [[
    'cuekey' => 'c1',
    'sortorder' => 1,
    'starttime' => 0,
    'endtime' => 2000,
    'transcript' => 'Le chat dort ici',
    'transcriptformat' => FORMAT_PLAIN,
    'gaps' => [[
        'gapkey' => 'g1',
        'sortorder' => 1,
        'charstart' => 3,
        'charlength' => 4,
        'solution' => 'chat',
        'gradingalgorithm' => 'exact',
        'maxlength' => 0,
        'linkurl' => '',
        'answers' => [],
        'hints' => [['level' => 1, 'hinttype' => 'text', 'hinttext' => 'animal', 'penalty' => 0.1]],
    ]],
]];

$manager = new \mod_elang\local\domain\version_manager();
$draft = $manager->get_or_create_draft($elangid, (int) $admin->id);
$manager->save_draft_content((int) $draft->id, $cues);

// A medium, before publishing. Subtitles are timed against one, so edit.php
// refuses to open without it and the editor tests would land on the "add a
// medium first" notice instead of the studio. A URL medium needs no file to be
// uploaded, and the browser tests never play it — they check that the editor
// mounts and is operable.
$manager->set_draft_media((int) $draft->id, [
    'kind' => 'url',
    'url' => 'https://example.org/elang-playwright-fixture.mp4',
    'mime' => 'video/mp4',
]);

$manager->publish((int) $draft->id, (int) $admin->id);
$version = $manager->get_published($elangid);
if ($version === null) {
    cli_error('Publishing the seeded version failed.');
}

// A dedicated editing teacher with a known password to drive the editor.
$username = 'elang_pw_' . $unique;
$password = 'Elang-pw-' . $unique . '!';
$user = $DB->get_record('user', ['username' => $username]);
if (!$user) {
    $newuser = (object) [
        'username' => $username,
        'auth' => 'manual',
        'confirmed' => 1,
        'mnethostid' => $CFG->mnet_localhost_id,
        'email' => $username . '@example.invalid',
        'firstname' => 'Elang',
        'lastname' => 'Author',
        'password' => $password,
    ];
    $userid = user_create_user($newuser, true, false);
    $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
}

$editingteacher = $DB->get_record('role', ['shortname' => 'editingteacher'], '*', MUST_EXIST);
$context = \context_course::instance($course->id);
role_assign($editingteacher->id, $user->id, $context->id);
enrol_try_internal_enrol($course->id, $user->id, $editingteacher->id);

echo "export ELANG_BASE_URL='" . $CFG->wwwroot . "'\n";
echo "export ELANG_USER='" . $username . "'\n";
echo "export ELANG_PASS='" . $password . "'\n";
echo "export ELANG_CMID='" . $cmid . "'\n";
echo "export ELANG_VERSIONID='" . $version->id . "'\n";
