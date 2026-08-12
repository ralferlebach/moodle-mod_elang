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
 * CLI seeder for the load tests: build a large published exercise and mint a
 * REST token, then print the exports the load runners consume.
 *
 * Creates a throwaway course, an elang activity, and a published version with
 * many cues and gaps, enables the REST web service for
 * mod_elang_get_version_content and mints a permanent admin token. It prints
 * `export BASE_URL/TOKEN/CMID/VERSIONID` lines, which `make load-seed` captures.
 *
 * This writes data and enables a web-service protocol: run it on a disposable
 * dev/staging site only, never in production.
 *
 * Usage:  php seed_large.php [numcues=200]
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

global $CFG, $DB;

if (empty($CFG->wwwroot)) {
    cli_error('No wwwroot configured.');
}

$numcues = isset($argv[1]) ? max(1, (int) $argv[1]) : 200;

$admin = get_admin();
\core\session\manager::set_user($admin);

// A throwaway course and an elang activity in it.
$unique = time();
$course = create_course((object) [
    'fullname' => 'Elang load ' . $unique,
    'shortname' => 'elangload' . $unique,
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
    'name' => 'Load exercise ' . $unique,
    'intro' => 'Load test exercise',
    'introformat' => FORMAT_HTML,
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

// Build a large draft and publish it. Each cue gets a single-word gap, all keys
// unique — the shape save_draft_content validates.
$cues = [];
for ($c = 1; $c <= $numcues; $c++) {
    $cues[] = [
        'cuekey' => 'c' . $c,
        'sortorder' => $c,
        'starttime' => $c * 2000,
        'endtime' => $c * 2000 + 1800,
        'transcript' => 'Le chat numero ' . $c . ' dort ici',
        'transcriptformat' => FORMAT_PLAIN,
        'gaps' => [[
            'gapkey' => 'g' . $c,
            'sortorder' => 1,
            'charstart' => 3,
            'charlength' => 4,
            'solution' => 'chat',
            'gradingalgorithm' => 'exact',
            'maxlength' => 0,
            'linkurl' => '',
            'answers' => [['sortorder' => 1, 'answer' => 'chats', 'isregex' => 0]],
            'hints' => [['level' => 1, 'hinttype' => 'text', 'hinttext' => 'animal', 'penalty' => 0.1]],
        ]],
    ];
}

$manager = new \mod_elang\local\domain\version_manager();
$draft = $manager->get_or_create_draft($elangid, (int) $admin->id);
$manager->save_draft_content((int) $draft->id, $cues);
$manager->publish((int) $draft->id, (int) $admin->id);
$version = $manager->get_published($elangid);
if ($version === null) {
    cli_error('Publishing the seeded version failed.');
}

// Enable the REST web service and mint a permanent admin token limited to the
// read endpoint under test.
set_config('enablewebservices', 1);
$protocols = array_filter(explode(',', (string) get_config('core', 'webserviceprotocols')));
if (!in_array('rest', $protocols, true)) {
    $protocols[] = 'rest';
    set_config('webserviceprotocols', implode(',', $protocols));
}

$servicename = 'Elang load test';
$service = $DB->get_record('external_services', ['name' => $servicename]);
if (!$service) {
    $service = (object) [
        'name' => $servicename,
        'enabled' => 1,
        'restrictedusers' => 0,
        'downloadfiles' => 0,
        'uploadfiles' => 0,
        'timecreated' => time(),
        'timemodified' => time(),
        'shortname' => 'elang_load_test',
    ];
    $service->id = $DB->insert_record('external_services', $service);
}
$functionlink = [
    'externalserviceid' => $service->id,
    'functionname' => 'mod_elang_get_version_content',
];
if (!$DB->record_exists('external_services_functions', $functionlink)) {
    $DB->insert_record('external_services_functions', $functionlink);
}

$token = external_generate_token(
    EXTERNAL_TOKEN_PERMANENT,
    $service,
    (int) $admin->id,
    \context_system::instance()
);

// Print the exports the Makefile / load runners consume.
echo "export BASE_URL='" . $CFG->wwwroot . "'\n";
echo "export TOKEN='" . $token . "'\n";
echo "export CMID='" . $cmid . "'\n";
echo "export VERSIONID='" . $version->id . "'\n";
echo "# Seeded {$numcues} cues into course {$course->id} (cmid {$cmid}).\n";
