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

// One activity per subtitle position, plus an audio one. The player renders
// these three cases quite differently — a bounded transcript below the medium,
// or a caption moved over the picture with no transcript underneath — and none
// of that is reachable from a single fixture.
//
// Every one of them needs its own medium and its own published version: the
// player refuses to start an attempt without one, and a version is pinned per
// activity.
$variants = [
    'below' => ['position' => 'below', 'mime' => 'video/mp4', 'url' => 'https://example.org/pw.mp4'],
    'overlaybottom' => ['position' => 'overlaybottom', 'mime' => 'video/mp4', 'url' => 'https://example.org/pw.mp4'],
    'overlaytop' => ['position' => 'overlaytop', 'mime' => 'video/mp4', 'url' => 'https://example.org/pw.mp4'],
    // Stored as an overlay, but audio has no picture to draw one on, so the
    // player must fall back to the display below the medium.
    'audio' => ['position' => 'overlaytop', 'mime' => 'audio/mpeg', 'url' => 'https://example.org/pw.mp3'],
];

$variantcmids = [];
foreach ($variants as $key => $variant) {
    $info = create_module((object) [
        'modulename' => 'elang',
        'course' => $course->id,
        'section' => 0,
        'visible' => 1,
        'name' => 'Player ' . $key . ' ' . $unique,
        'introeditor' => ['text' => 'Playwright fixture', 'format' => FORMAT_HTML, 'itemid' => 0],
        'language' => 'en',
        'jarothreshold' => 90,
        'grade' => 100,
        'completion' => 0,
        'cmidnumber' => '',
        'groupmode' => 0,
        'groupingid' => 0,
        'visibleoncoursepage' => 1,
        'subtitleposition' => $variant['position'],
        'cuepausemode' => 'auto',
    ]);

    $variantdraft = $manager->get_or_create_draft((int) $info->instance, (int) $admin->id);
    $manager->save_draft_content((int) $variantdraft->id, $cues);
    $manager->set_draft_media((int) $variantdraft->id, [
        'kind' => 'url',
        'url' => $variant['url'],
        'mime' => $variant['mime'],
    ]);
    $manager->publish((int) $variantdraft->id, (int) $admin->id);

    $variantcmids[$key] = (int) $info->coursemodule;
}

// A long transcript, for the cue list. Forty cues is what the workspace was
// built for: before it, every one of them rendered its whole form at once.
$longinfo = create_module((object) [
    'modulename' => 'elang',
    'course' => $course->id,
    'section' => 0,
    'visible' => 1,
    'name' => 'Long transcript ' . $unique,
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

$longcues = [];
for ($i = 1; $i <= 40; $i++) {
    $longcues[] = [
        'cuekey' => 'lc' . $i,
        'sortorder' => $i,
        'starttime' => ($i - 1) * 2000,
        'endtime' => $i * 2000,
        'transcript' => 'Sentence number ' . $i . ' of the long transcript',
        'transcriptformat' => FORMAT_PLAIN,
        'gaps' => [[
            'gapkey' => 'lg' . $i,
            'sortorder' => 1,
            'charstart' => 0,
            'charlength' => 8,
            'solution' => 'Sentence',
            'gradingalgorithm' => 'exact',
            'maxlength' => 0,
            'linkurl' => '',
            'answers' => [],
            'hints' => [],
        ]],
    ];
}

$longdraft = $manager->get_or_create_draft((int) $longinfo->instance, (int) $admin->id);
$manager->save_draft_content((int) $longdraft->id, $longcues);
$manager->set_draft_media((int) $longdraft->id, [
    'kind' => 'url',
    'url' => 'https://example.org/pw-long.mp4',
    'mime' => 'video/mp4',
]);

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

// A learner as well. Only the student archetype holds mod/elang:attempt, so the
// teacher above cannot start an attempt and the player refuses to load for
// them — correctly. Anything about what a learner sees has to be driven by a
// learner.
$studentname = 'elang_pw_student_' . $unique;
$studentpassword = 'Elang-st-' . $unique . '!';
$student = $DB->get_record('user', ['username' => $studentname]);
if (!$student) {
    $newstudent = (object) [
        'username' => $studentname,
        'auth' => 'manual',
        'confirmed' => 1,
        'mnethostid' => $CFG->mnet_localhost_id,
        'email' => $studentname . '@example.invalid',
        'firstname' => 'Elang',
        'lastname' => 'Learner',
        'password' => $studentpassword,
    ];
    $student = $DB->get_record(
        'user',
        ['id' => user_create_user($newstudent, true, false)],
        '*',
        MUST_EXIST
    );
}

$studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
role_assign($studentrole->id, $student->id, $context->id);
enrol_try_internal_enrol($course->id, $student->id, $studentrole->id);

echo "export ELANG_BASE_URL='" . $CFG->wwwroot . "'\n";
echo "export ELANG_USER='" . $username . "'\n";
echo "export ELANG_PASS='" . $password . "'\n";
echo "export ELANG_CMID='" . $cmid . "'\n";
echo "export ELANG_VERSIONID='" . $version->id . "'\n";
echo "export ELANG_CMID_BELOW='" . $variantcmids['below'] . "'\n";
echo "export ELANG_CMID_OVERLAYBOTTOM='" . $variantcmids['overlaybottom'] . "'\n";
echo "export ELANG_CMID_OVERLAYTOP='" . $variantcmids['overlaytop'] . "'\n";
echo "export ELANG_CMID_AUDIO='" . $variantcmids['audio'] . "'\n";
echo "export ELANG_CMID_LONG='" . $longinfo->coursemodule . "'\n";
echo "export ELANG_STUDENT='" . $studentname . "'\n";
echo "export ELANG_STUDENT_PASS='" . $studentpassword . "'\n";
