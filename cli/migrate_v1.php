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
 * CLI entry point for the V1 -> V2 migration
 * — the only way to trigger it right now; there is no admin page yet.
 * Deliberately thin: every actual decision (what counts as pending, how a
 * site's own gradingalgorithm maps, how a block is processed) lives in
 * v1_detector/v1_migrator/migrate_v1_activities_task, already tested on
 * their own. This script only presents their output and asks for
 * confirmation before anything is queued to write.
 *
 * Usage:
 *   php cli/migrate_v1.php --dry-run
 *   php cli/migrate_v1.php --execute [--blocksize=20] [--yes]
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

use mod_elang\local\migration\v1_detector;
use mod_elang\task\migrate_v1_activities_task;

[$options, $unrecognised] = cli_get_params(
    [
        'help' => false,
        'dry-run' => false,
        'execute' => false,
        'blocksize' => migrate_v1_activities_task::DEFAULT_BLOCK_SIZE,
        'yes' => false,
    ],
    ['h' => 'help']
);

if ($unrecognised) {
    $unrecognised = implode("\n  ", $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}

if ($options['help'] || (!$options['dry-run'] && !$options['execute'])) {
    echo "V1 -> V2 migration for mod_elang.

Options:
  --dry-run Show what would be migrated, write nothing.
  --execute Queue the migration adhoc task.
  --blocksize=<n>       Activities per task execution (default "
        . migrate_v1_activities_task::DEFAULT_BLOCK_SIZE . ", --execute only).
  --yes Skip the confirmation prompt (--execute only).
  -h, --help This message.

Exactly one of --dry-run or --execute is required.
";

    exit($options['help'] ? 0 : 1);
}

if (!v1_detector::v1_tables_present()) {
    cli_writeln('No V1 legacy tables found on this site. Nothing to migrate.');
    exit(0);
}

$report = v1_detector::dry_run_report();

if (empty($report)) {
    cli_writeln('No pending V1 activities found (either none exist, or every one already has a published V2 version).');
    exit(0);
}

cli_writeln(count($report) . ' pending V1 activities:');
cli_writeln('');

foreach ($report as $entry) {
    cli_writeln(
        "  elang {$entry->elangid} \"{$entry->name}\": {$entry->cuecount} cues, {$entry->gapcount} gaps, "
            . "{$entry->learnercount} learners, algorithm={$entry->gradingalgorithm}"
    );
    foreach ($entry->parseerrors as $message) {
        cli_writeln("    parse error: {$message}");
    }
}

if ($options['dry-run']) {
    cli_writeln('');
    cli_writeln('Dry run only, nothing was written. Re-run with --execute to migrate.');
    exit(0);
}

$blocksize = (int) $options['blocksize'];
if ($blocksize < 1) {
    cli_error('--blocksize must be a positive integer.');
}

if (!$options['yes']) {
    cli_writeln('');
    $confirm = cli_input(
        'This will queue an adhoc task that writes new elang_version/elang_cue/elang_gap/elang_attempt/'
            . 'elang_response data for the activities listed above, ' . $blocksize . ' per task run. '
            . 'The legacy tables and elang.options are left untouched. Type "yes" to continue:'
    );
    if (strtolower(trim($confirm)) !== 'yes') {
        cli_writeln('Aborted, nothing was queued.');
        exit(1);
    }
}

migrate_v1_activities_task::queue($blocksize);

cli_writeln('');
cli_writeln('Migration task queued. It runs on the next cron pass (php admin/cli/cron.php),');
cli_writeln('or immediately via: php admin/cli/adhoc_task.php --execute');
exit(0);
