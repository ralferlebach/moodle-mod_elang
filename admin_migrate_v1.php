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
 * Site administration page for the V1 -> V2 migration (Migration_V1_V2.md
 * chapter 2) — the web-based counterpart to cli/migrate_v1.php, covering
 * the same two actions (queue the migration, review + approve afterwards)
 * plus the part the CLI script does not: showing v1_verifier's report and
 * recording an explicit administrator sign-off (step 4,
 * classes/local/migration/v1_signoff.php).
 *
 * Deliberately thin, same principle as the CLI script: every actual
 * decision already lives in v1_detector/v1_migrator/v1_verifier/
 * v1_signoff/migrate_v1_activities_task, each tested on their own. This
 * page only presents their output and asks for confirmation before
 * anything is queued to write.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use mod_elang\local\migration\v1_detector;
use mod_elang\local\migration\v1_signoff;
use mod_elang\local\migration\v1_verifier;
use mod_elang\task\migrate_v1_activities_task;

admin_externalpage_setup('elangmigratev1');

$pageurl = new moodle_url('/mod/elang/admin_migrate_v1.php');
$action = optional_param('action', '', PARAM_ALPHA);
$elangid = optional_param('elangid', 0, PARAM_INT);
$confirmed = optional_param('confirm', 0, PARAM_BOOL);

if ($action === 'migrate' && confirm_sesskey()) {
    if (!$confirmed) {
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(
            get_string('migratev1:confirmmigrate', 'mod_elang'),
            new moodle_url($pageurl, ['action' => 'migrate', 'confirm' => 1, 'sesskey' => sesskey()]),
            $pageurl
        );
        echo $OUTPUT->footer();
        exit;
    }

    migrate_v1_activities_task::queue();
    redirect($pageurl, get_string('migratev1:queued', 'mod_elang'), null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($action === 'approve' && $elangid > 0 && confirm_sesskey()) {
    v1_signoff::approve($elangid, (int) $USER->id);
    redirect($pageurl, get_string('migratev1:approved', 'mod_elang', $elangid), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('migratev1:heading', 'mod_elang'));

if (!v1_detector::v1_tables_present()) {
    echo $OUTPUT->notification(get_string('migratev1:notablespresent', 'mod_elang'), \core\output\notification::NOTIFY_INFO);
    echo $OUTPUT->footer();
    exit;
}

// Section 1: activities not migrated yet.
echo $OUTPUT->heading(get_string('migratev1:pendingheading', 'mod_elang'), 3);

$pending = v1_detector::dry_run_report();
if (empty($pending)) {
    echo html_writer::tag('p', get_string('migratev1:nonepending', 'mod_elang'));
} else {
    $table = new html_table();
    $table->head = [
        get_string('migratev1:colactivity', 'mod_elang'),
        get_string('migratev1:colcues', 'mod_elang'),
        get_string('migratev1:colgaps', 'mod_elang'),
        get_string('migratev1:collearners', 'mod_elang'),
        get_string('migratev1:colalgorithm', 'mod_elang'),
        get_string('migratev1:colissues', 'mod_elang'),
    ];
    foreach ($pending as $entry) {
        $issuecount = count($entry->parseerrors);
        $table->data[] = [
            s($entry->name) . ' (id ' . $entry->elangid . ')',
            $entry->cuecount,
            $entry->gapcount,
            $entry->learnercount,
            s($entry->gradingalgorithm),
            $issuecount > 0
                ? get_string('migratev1:parseerrorcount', 'mod_elang', $issuecount)
                : get_string('migratev1:noissues', 'mod_elang'),
        ];
    }
    echo html_writer::table($table);
    echo $OUTPUT->single_button(
        new moodle_url($pageurl, ['action' => 'migrate']),
        get_string('migratev1:migratebutton', 'mod_elang')
    );
}

// Section 2: migrated, awaiting review and sign-off.
echo $OUTPUT->heading(get_string('migratev1:approvalheading', 'mod_elang'), 3);

$needapproval = v1_signoff::pending_approval_ids();
if (empty($needapproval)) {
    echo html_writer::tag('p', get_string('migratev1:nonependingapproval', 'mod_elang'));
} else {
    $verifier = new v1_verifier();

    foreach ($needapproval as $id) {
        echo $OUTPUT->heading('elang ' . $id, 4);

        try {
            $result = $verifier->verify_activity($id);
        } catch (\Throwable $e) {
            echo $OUTPUT->notification(
                get_string('migratev1:verifyfailed', 'mod_elang', s($e->getMessage())),
                \core\output\notification::NOTIFY_ERROR
            );
            continue;
        }

        if ($result->ok) {
            echo $OUTPUT->notification(
                get_string('migratev1:verifiedclean', 'mod_elang'),
                \core\output\notification::NOTIFY_SUCCESS
            );
        } else {
            echo $OUTPUT->notification(
                get_string('migratev1:verifieddiscrepancies', 'mod_elang', count($result->discrepancies)),
                \core\output\notification::NOTIFY_WARNING
            );
            $items = array_map('s', $result->discrepancies);
            echo html_writer::alist($items);
        }

        echo $OUTPUT->single_button(
            new moodle_url($pageurl, ['action' => 'approve', 'elangid' => $id]),
            get_string('migratev1:approvebutton', 'mod_elang')
        );
    }
}

echo $OUTPUT->footer();
