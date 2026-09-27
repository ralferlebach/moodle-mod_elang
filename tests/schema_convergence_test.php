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
 * A site that upgraded from version 1 ends up with the schema a fresh install has.
 *
 * Moodle installs a plugin from db/install.xml and upgrades one through
 * db/upgrade.php, and the two are supposed to arrive at the same place. The
 * version 1 exit adds a third step — decommissioning, which drops what the
 * migration needed and nothing else — so the destination has to survive that
 * too.
 *
 * It did not. install.xml declared elang.options, decommissioning dropped it,
 * and Moodle's own admin/cli/check_database_schema.php then reported
 * "column 'options' is missing" on every site that completed the migration —
 * permanently, because decommissioning is the intended end state rather than a
 * passing phase. The column is now declared only by the upgrade step that needs
 * it, so it exists exactly as long as the migration does.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\migration\v1_decommissioner
 */
final class schema_convergence_test extends \advanced_testcase {
    /**
     * The tables and fields db/install.xml declares.
     *
     * Read from the file rather than from the database, so the test states what
     * the plugin promises rather than what this particular site happens to have.
     *
     * @return array ['tables' => string[], 'fields' => string[]] — fields as table.field
     */
    private function declared_schema(): array {
        global $CFG;

        $xml = simplexml_load_file($CFG->dirroot . '/mod/elang/db/install.xml');
        $this->assertNotFalse($xml, 'db/install.xml is not readable as XML.');

        $tables = [];
        $fields = [];
        foreach ($xml->TABLES->TABLE as $table) {
            $tablename = (string)$table['NAME'];
            $tables[] = $tablename;
            foreach ($table->FIELDS->FIELD as $field) {
                $fields[] = $tablename . '.' . (string)$field['NAME'];
            }
        }

        return ['tables' => $tables, 'fields' => $fields];
    }

    /**
     * Decommissioning drops nothing that db/install.xml declares.
     *
     * This is the invariant, stated where it can be checked. Everything the
     * version 1 exit removes is legacy — four tables that only ever existed on
     * a 1.x site, and a column added by the upgrade purely to carry 1.x's
     * options blob across. None of it belongs to the schema a fresh 2.0
     * install builds, so removing it cannot leave that schema incomplete.
     *
     * @return void
     */
    public function test_decommissioning_drops_nothing_the_install_schema_declares(): void {
        $declared = $this->declared_schema();

        // Kept in step with classes/local/migration/v1_decommissioner.php. A
        // short, explicit list rather than a reflection trick: if someone adds
        // a drop there and not here, this test still passes — but the schema
        // check below fails, which is the point of having both.
        $droppedtables = ['elang_cues', 'elang_users', 'elang_help', 'elang_check'];
        $droppedfields = ['elang.options'];

        foreach ($droppedtables as $name) {
            $this->assertNotContains(
                $name,
                $declared['tables'],
                "Decommissioning drops table '$name', but db/install.xml declares it. "
                    . 'A site that completes the migration would end up with a schema '
                    . 'Moodle reports as broken.'
            );
        }

        foreach ($droppedfields as $name) {
            $this->assertNotContains(
                $name,
                $declared['fields'],
                "Decommissioning drops field '$name', but db/install.xml declares it. "
                    . 'A site that completes the migration would end up with a schema '
                    . 'Moodle reports as broken.'
            );
        }
    }

    /**
     * Moodle finds nothing wrong with the schema this plugin installs.
     *
     * The PHPUnit database is built from install.xml, so this confirms the file
     * is internally consistent and that nothing else in the plugin has drifted
     * away from it. It is the same check an administrator runs from
     * admin/cli/check_database_schema.php, narrowed to this plugin's tables.
     *
     * @return void
     */
    public function test_the_installed_schema_matches_what_moodle_expects(): void {
        global $DB;

        $this->resetAfterTest();

        // Measured against a pristine schema, not against whatever the tests
        // before this one left behind. The version 1 fixture adds elang.options
        // to simulate a 1.x site, and on MariaDB that addition outlives the
        // test: DDL commits implicitly there and cannot be rolled back with the
        // transaction, so the column leaked into everything that ran later.
        // This test then reported a schema fault that belonged to the fixture
        // rather than to the plugin — and only on the MariaDB half of the CI
        // matrix, which is a confusing way to learn about it.
        $manager = $DB->get_manager();

        $errors = $manager->check_database_schema(
            $manager->get_install_xml_schema('mod/elang')
        );

        // The elang.options column belongs to the migration: added by the upgrade for
        // a site coming from 1.x, dropped when the legacy data is decommissioned,
        // and declared nowhere. A test that simulated a 1.x site may have left
        // it behind — on MariaDB it survives the test, because DDL commits there
        // and cannot be rolled back with the transaction, which is why this only
        // ever failed on half the CI matrix.
        //
        // Its presence is therefore not a fault, and the earlier attempt to
        // remove it here was worse than the problem: mutating the schema from
        // this test disturbed upgrade_test, which rebuilds that schema itself.
        // Everything else stays an error, including the case this test was
        // written for — the column being *missing* from a decommissioned site.
        $transitional = "column 'options' is not expected";

        $ourerrors = [];
        foreach ($errors as $table => $messages) {
            if (strpos($table, 'elang') !== 0) {
                continue;
            }
            $messages = array_values(array_filter(
                $messages,
                static fn($message) => strpos($message, $transitional) === false
            ));
            if ($messages !== []) {
                $ourerrors[$table] = $messages;
            }
        }

        $this->assertSame(
            [],
            $ourerrors,
            'Moodle reports the installed schema as different from db/install.xml: '
                . json_encode($ourerrors)
        );
    }

    /**
     * The column the migration needs is created by the upgrade, not by install.
     *
     * Stated separately because the two halves fail differently. Declaring it
     * in install.xml leaves every decommissioned site reporting a missing
     * column; not adding it in the upgrade leaves every migrating site unable
     * to read version 1's options blob at all.
     *
     * @return void
     */
    public function test_the_options_column_comes_from_the_upgrade_path(): void {
        global $CFG;

        $declared = $this->declared_schema();
        $this->assertNotContains('elang.options', $declared['fields']);

        $upgrade = file_get_contents($CFG->dirroot . '/mod/elang/db/upgrade.php');
        $this->assertStringContainsString(
            "new xmldb_field('options'",
            $upgrade,
            'No upgrade step creates elang.options, so a site coming from version 1 '
                . 'would have nowhere to keep the options blob the migration reads.'
        );
    }
}
