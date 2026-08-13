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
 * CLI entry point for
 * irreversibly drops the V1 legacy tables and elang.options once every
 * activity is migrated and signed off, never before. See
 * classes/local/migration/v1_decommissioner.php for the full reasoning on
 * why this deliberately is not a normal db/upgrade.php step.
 *
 * Usage:
 *   php cli/decommission_v1.php --check
 *   php cli/decommission_v1.php --execute [--yes]
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

use mod_elang\local\migration\v1_decommissioner;

[$options, $unrecognised] = cli_get_params(
    ['help' => false, 'check' => false, 'execute' => false, 'yes' => false],
    ['h' => 'help']
);

if ($unrecognised) {
    $unrecognised = implode("\n  ", $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}

if ($options['help'] || (!$options['check'] && !$options['execute'])) {
    echo "Decommission V1 legacy data for mod_elang.

Drops elang_cues, elang_users, elang_help, elang_check and elang.options —
irreversibly. Only proceeds once every V1 activity has been migrated AND
every migrated activity has been signed off (see admin_migrate_v1.php).

Options:
  --check Show whether decommissioning is currently blocked, write nothing.
  --execute Decommission if nothing blocks it.
  --yes Skip the confirmation prompt (--execute only).
  -h, --help This message.

Exactly one of --check or --execute is required.
";

    exit($options['help'] ? 0 : 1);
}

$blockers = v1_decommissioner::blockers();

if (!empty($blockers)) {
    cli_writeln('Decommissioning is blocked:');
    foreach ($blockers as $blocker) {
        cli_writeln('  - ' . $blocker);
    }
    cli_writeln('');
    cli_writeln('Migrate and approve every activity first — see cli/migrate_v1.php and admin_migrate_v1.php.');
    exit(1);
}

cli_writeln('Nothing blocks decommissioning: every V1 activity is migrated and approved.');

if ($options['check']) {
    cli_writeln('Check only, nothing was written.');
    exit(0);
}

if (!$options['yes']) {
    cli_writeln('');
    $confirm = cli_input(
        'This IRREVERSIBLY drops elang_cues, elang_users, elang_help, elang_check and elang.options. '
            . 'There is no undo and no further use for this data once it is gone. Type "yes" to continue:'
    );
    if (strtolower(trim($confirm)) !== 'yes') {
        cli_writeln('Aborted, nothing was dropped.');
        exit(1);
    }
}

$result = v1_decommissioner::decommission();

cli_writeln('');
if (empty($result->droppedtables) && empty($result->droppedfields)) {
    cli_writeln('Nothing was present to drop (already decommissioned, or never had any V1 data).');
} else {
    cli_writeln('Dropped tables: ' . (empty($result->droppedtables) ? '(none)' : implode(', ', $result->droppedtables)));
    cli_writeln('Dropped fields: ' . (empty($result->droppedfields) ? '(none)' : implode(', ', $result->droppedfields)));
}
exit(0);
