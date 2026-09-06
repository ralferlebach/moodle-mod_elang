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
 * Fill a version 1 installation with an activity, its files and learner work.
 *
 * Run while **version 1 of the plugin is installed**; the tables it writes to
 * are the ones V1's own db/install.xml created. Nothing here defines the V1
 * schema — it only puts data into it, which is what keeps the fixture honest:
 * if V1's schema were different from what the migration expects, this script
 * would fail rather than paper over it.
 *
 * The rows are written directly rather than through V1's forms. Driving a
 * plugin from 2018 through a browser on Moodle 4.5 would test that plugin's
 * compatibility with a Moodle it was never written for, which is not the
 * question. The question is whether *migration* moves V1-shaped data correctly,
 * and for that the data has to be V1-shaped — not the route it took to get
 * there.
 *
 * Prints shell exports the assertion script reads back.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/clilib.php');

if (!$DB->get_manager()->table_exists(new xmldb_table('elang_cues'))) {
    cli_error('elang_cues does not exist — version 1 of the plugin is not installed.');
}

$admin = get_admin();
\core\session\manager::set_user($admin);

$unique = time();
$course = create_course((object) [
    'fullname' => 'Migration fixture ' . $unique,
    'shortname' => 'migv1_' . $unique,
    'category' => 1,
]);

// The V1 activity row. Its options are the ones the mapper reads: the grading
// flags V1 applied uniformly across every gap, and the display settings.
$options = [
    'usecasesensitive' => false,
    'usetransliteration' => true,
    'jaroDistance' => 90,
    'showtitles' => true,
    'size' => 12,
    'repeatedattempts' => 3,
];

$elangid = $DB->insert_record('elang', (object) [
    'course' => $course->id,
    'name' => 'Dictée de test',
    'intro' => '<p>Une activité migrée depuis la version 1.</p>',
    'introformat' => FORMAT_HTML,
    'timecreated' => $unique,
    'timemodified' => $unique,
    'language' => 'fr',
    'options' => json_encode($options),
]);

$module = $DB->get_record('modules', ['name' => 'elang'], '*', MUST_EXIST);
$cmid = add_course_module((object) [
    'course' => $course->id,
    'module' => $module->id,
    'instance' => $elangid,
    'section' => 0,
    'visible' => 1,
    'visibleoncoursepage' => 1,
    'groupmode' => 0,
    'groupingid' => 0,
]);
course_add_cm_to_section($course->id, $cmid, 0);

$context = context_module::instance($cmid);
$fs = get_file_storage();

// Both files go into V1's own file areas, at itemid 0 as V1 stored them.
foreach ([['videos', 'lesson.mp4'], ['subtitle', 'lesson.vtt']] as [$area, $name]) {
    $fs->create_file_from_pathname([
        'contextid' => $context->id,
        'component' => 'mod_elang',
        'filearea' => $area,
        'itemid' => 0,
        'filepath' => '/',
        'filename' => $name,
    ], __DIR__ . '/../fixtures/v1/' . $name);
}

// The cues, in V1's own JSON shape. A cue is a list of segments; a segment is
// either plain text or an "input", and an input's content is the solution. The
// gap word stays part of the transcript — that convention is V1's, and the
// migration reproduces it rather than inventing another.
$cues = [
    [
        'title' => 'Cue 1',
        'begin' => 0,
        'end' => 3000,
        'segments' => [
            ['type' => 'text', 'content' => 'Le '],
            ['type' => 'input', 'content' => 'chat', 'help' => true],
            ['type' => 'text', 'content' => ' dort sur le '],
            ['type' => 'input', 'content' => 'canapé'],
            ['type' => 'text', 'content' => '.'],
        ],
    ],
    [
        'title' => 'Cue 2',
        'begin' => 3000,
        'end' => 6500,
        'segments' => [
            ['type' => 'text', 'content' => 'Le '],
            ['type' => 'input', 'content' => 'chien', 'link' => 'https://example.org/chien'],
            ['type' => 'text', 'content' => ' court dans le jardin.'],
        ],
    ],
    [
        'title' => 'Cue 3',
        'begin' => 6500,
        'end' => 10000,
        'segments' => [
            ['type' => 'text', 'content' => 'Les '],
            ['type' => 'input', 'content' => 'oiseaux'],
            ['type' => 'text', 'content' => ' chantent le '],
            ['type' => 'input', 'content' => 'matin'],
            ['type' => 'text', 'content' => '.'],
        ],
    ],
];

$cueids = [];
foreach ($cues as $index => $cue) {
    // The columns begin and end are reserved words in PostgreSQL, and Moodle's
    // insert_record() does not quote column identifiers, so the row is written
    // with explicitly quoted columns. This is V1's schema; it cannot be renamed
    // here without making the fixture inauthentic.
    $sql = 'INSERT INTO {elang_cues} (id_elang, number, "begin", "end", title, json)
                 VALUES (?, ?, ?, ?, ?, ?)';
    if ($DB->get_dbfamily() === 'mysql') {
        // MariaDB quotes identifiers with backticks, PostgreSQL with double
        // quotes, and both need quoting here because begin and end are reserved
        // words. Built from chr() so the file itself contains no backtick,
        // which the coding standard reads as a shell command.
        $tick = chr(96);
        $quoted = $tick . 'begin' . $tick . ', ' . $tick . 'end' . $tick;
        $sql = "INSERT INTO {elang_cues} (id_elang, number, $quoted, title, json)
                     VALUES (?, ?, ?, ?, ?, ?)";
    }
    $DB->execute($sql, [
        $elangid,
        $index + 1,
        $cue['begin'],
        $cue['end'],
        $cue['title'],
        json_encode($cue['segments']),
    ]);
    $cueids[$index] = (int) $DB->get_field_sql(
        'SELECT MAX(id) FROM {elang_cues} WHERE id_elang = ?',
        [$elangid]
    );
}

// Four learners whose work covers the cases the migration has to tell apart.
// Deliberately not four variations of "typed it right": a migration that only
// moves correct answers would pass a test made of them.
$learners = [
    'exact' => [
        // Everything right, exactly as written.
        0 => [1 => ['content' => 'chat'], 2 => ['content' => 'canapé']],
        1 => [1 => ['content' => 'chien']],
        2 => [1 => ['content' => 'oiseaux'], 2 => ['content' => 'matin']],
    ],
    'accents' => [
        // Right word, missing accent. With usetransliteration the migration
        // has to accept it; a plain string comparison would not.
        0 => [1 => ['content' => 'chat'], 2 => ['content' => 'canape']],
        1 => [1 => ['content' => 'CHIEN']],
        2 => [1 => ['content' => 'oiseaux'], 2 => ['content' => 'matin']],
    ],
    'partial' => [
        // Some right, some wrong, one left empty, one answered after a hint.
        0 => [1 => ['content' => 'chat'], 2 => ['content' => 'sofa']],
        1 => [1 => ['content' => '']],
        2 => [1 => ['content' => 'oiseaux', 'help' => true], 2 => ['content' => 'soir']],
    ],
    'untouched' => [
        // Opened the exercise and answered nothing. Their attempt must still
        // exist afterwards: "no answers" is data, not absence of data.
        0 => [1 => ['content' => ''], 2 => ['content' => '']],
    ],
];

$userids = [];
foreach ($learners as $label => $work) {
    $username = 'migv1_' . $label . '_' . $unique;
    $userid = user_create_user((object) [
        'username' => $username,
        'auth' => 'manual',
        'confirmed' => 1,
        'mnethostid' => $CFG->mnet_localhost_id,
        'email' => $username . '@example.invalid',
        'firstname' => ucfirst($label),
        'lastname' => 'Migrationstest',
        'password' => 'Migv1-' . $unique . '!',
    ], true, false);

    $studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
    enrol_try_internal_enrol($course->id, $userid, $studentrole->id);
    $userids[$label] = $userid;

    foreach ($work as $cueindex => $state) {
        $DB->insert_record('elang_users', (object) [
            'id_elang' => $elangid,
            'id_cue' => $cueids[$cueindex],
            'id_user' => $userid,
            'json' => json_encode($state),
        ]);
    }
}

echo "export MIGV1_COURSEID='" . $course->id . "'\n";
echo "export MIGV1_ELANGID='" . $elangid . "'\n";
echo "export MIGV1_CMID='" . $cmid . "'\n";
echo "export MIGV1_CONTEXTID='" . $context->id . "'\n";
foreach ($userids as $label => $userid) {
    echo "export MIGV1_USER_" . strtoupper($label) . "='" . $userid . "'\n";
}
echo "# Seeded a V1 activity: " . count($cues) . " cues, 7 gaps, "
    . count($learners) . " learners.\n";
