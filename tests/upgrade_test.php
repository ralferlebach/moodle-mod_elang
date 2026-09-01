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

use database_manager;
use mod_elang\fixtures\v1_legacy_schema;
use mod_elang\local\migration\v1_migrator;
use xmldb_field;
use xmldb_index;
use xmldb_table;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/upgradelib.php');
require_once($CFG->dirroot . '/mod/elang/db/upgrade.php');

/**
 * Exercises the real V1 to 2.0 upgrade path.
 *
 * A fresh PHPUnit install builds the current schema directly from install.xml
 * and so never runs upgrade.php — but a production V1 site upgrades through it,
 * where a mistaken add_field or index clash would abort the admin upgrade. This
 * test reconstructs the V1 database state (drops the 2.0-only tables and the
 * 2.0-only elang columns, leaving a V1-shaped activity plus its legacy cue
 * tables), runs xmldb_elang_upgrade() from the V1 baseline version, and asserts
 * the full 2.0 schema is rebuilt and that the one-way content migration then
 * still runs with stable identities on that really-upgraded schema.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \xmldb_elang_upgrade
 */
final class upgrade_test extends \advanced_testcase {
    /** The V1 mod_elang version; anything below the first savepoint is "V1". */
    private const V1_VERSION = 2018091012;

    /** The tables the 2.0 upgrade creates (absent in V1). */
    private const V2_TABLES = [
        'elang_version', 'elang_cue', 'elang_gap', 'elang_gapanswer',
        'elang_gaphint', 'elang_attempt', 'elang_response',
    ];

    /** The elang columns the 2.0 upgrade adds (absent in V1). */
    private const V2_ELANG_FIELDS = [
        'currentversionid', 'completionfinishattempt', 'jarothreshold',
        'subtitleposition', 'cuepausemode',
        'allowtranscriptdownload', 'solutionavailability',
        'migrationapproveduserid', 'migrationapprovedtime',
    ];

    /**
     * Require the legacy-schema fixture before the class runs.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void {
        require_once(__DIR__ . '/fixtures/v1_legacy_schema.php');
        parent::setUpBeforeClass();
    }

    /**
     * Reconstruct V1, run the real upgrade, and migrate — asserting the schema
     * is rebuilt and the migration keeps stable identities.
     *
     * @return void
     */
    public function test_real_upgrade_from_v1_rebuilds_schema_and_migrates(): void {
        global $DB;

        $this->resetAfterTest();
        $dbman = $DB->get_manager();

        $this->downgrade_to_v1_schema($dbman);

        // Confirm we really are at a V1 baseline before upgrading.
        $this->assertFalse($dbman->table_exists(new xmldb_table('elang_version')));
        $this->assertFalse(
            $dbman->field_exists(new xmldb_table('elang'), new xmldb_field('currentversionid'))
        );

        // A V1 activity (id 1) with its legacy cue content.
        v1_legacy_schema::create_tables();
        v1_legacy_schema::insert_sample_activity();

        // Tell Moodle the plugin is installed at the V1 version, so the upgrade
        // savepoints move it forward rather than rejecting a "downgrade".
        set_config('version', self::V1_VERSION, 'mod_elang');

        // Run the actual upgrade DDL from the V1 version. A schema clash would
        // throw here, exactly as it would in a real admin upgrade.
        $this->assertTrue(xmldb_elang_upgrade(self::V1_VERSION));

        // The whole 2.0 schema is back.
        foreach (self::V2_TABLES as $tablename) {
            $this->assertTrue(
                $dbman->table_exists(new xmldb_table($tablename)),
                "Table {$tablename} is missing after the upgrade."
            );
        }
        $elangtable = new xmldb_table('elang');
        foreach (self::V2_ELANG_FIELDS as $fieldname) {
            $this->assertTrue(
                $dbman->field_exists($elangtable, new xmldb_field($fieldname)),
                "Field elang.{$fieldname} is missing after the upgrade."
            );
        }

        // The pre-existing activity survived and is in the "not yet migrated"
        // state; its legacy source rows are preserved for the migrator.
        $elang = $DB->get_record('elang', ['id' => 1], '*', MUST_EXIST);
        $this->assertSame('Test', $elang->name);
        $this->assertEmpty($elang->currentversionid);
        $this->assertTrue($DB->record_exists('elang_cues', ['id_elang' => 1]));

        // The one-way content migration runs on the really-upgraded schema.
        $report = (new v1_migrator())->migrate_activity(1);
        $this->assertSame(1, $report->elangid);

        $elang = $DB->get_record('elang', ['id' => 1], '*', MUST_EXIST);
        $this->assertNotEmpty($elang->currentversionid);
        $version = $DB->get_record('elang_version', ['id' => $elang->currentversionid], '*', MUST_EXIST);
        $this->assertSame('published', $version->status);

        // Cues and gaps came across with the stable keys the migrator assigns.
        $cues = $DB->get_records('elang_cue', ['versionid' => $version->id]);
        $this->assertNotEmpty($cues);
        foreach ($cues as $cue) {
            $this->assertNotEmpty($cue->cuekey);
        }
        $gap = $DB->get_record('elang_gap', ['gapkey' => 'v1-gap-12-1'], '*', MUST_EXIST);
        $this->assertSame('https://de.wikipedia.org/wiki/File', $gap->linkurl);
    }

    /**
     * Drop the 2.0-only tables and elang columns to return the database to a
     * V1-shaped baseline. The currentversionid index is dropped before its
     * column, as the database requires.
     *
     * @param database_manager $dbman The schema manager
     * @return void
     */
    private function downgrade_to_v1_schema(database_manager $dbman): void {
        foreach (self::V2_TABLES as $tablename) {
            $table = new xmldb_table($tablename);
            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
        }

        $elang = new xmldb_table('elang');
        $index = new xmldb_index('currentversionid', XMLDB_INDEX_NOTUNIQUE, ['currentversionid']);
        if ($dbman->index_exists($elang, $index)) {
            $dbman->drop_index($elang, $index);
        }
        foreach (self::V2_ELANG_FIELDS as $fieldname) {
            $field = new xmldb_field($fieldname);
            if ($dbman->field_exists($elang, $field)) {
                $dbman->drop_field($elang, $field);
            }
        }
    }
}
