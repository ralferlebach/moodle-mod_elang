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
 * Upgrade steps for mod_elang.
 *
 * The migration of version 1 data (elang_cues, elang_users, elang_help,
 * elang_check) into the versioned version 2 data model is keyed on the presence
 * of the legacy tables rather than on a version number, because sites may have
 * skipped several plugin releases. It runs as a resumable ad-hoc task and is
 * introduced in a later step of phase 2; see docs/materials/Migration_V1_V2.md.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute the mod_elang upgrade steps.
 *
 * @param int $oldversion The version we are upgrading from
 * @return bool Success
 */
function xmldb_elang_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026072301) {
        // Extend the base elang table with the fields the versioned schema needs.
        $table = new xmldb_table('elang');

        // No default value: a NOTNULL CHAR column must have either a
        // meaningful default or none. Application code always supplies this
        // value explicitly on insert (see the matching comment in install.xml).
        $field = new xmldb_field('language', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null, 'introformat');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('currentversionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'language');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $index = new xmldb_index('currentversionid', XMLDB_INDEX_NOTUNIQUE, ['currentversionid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define table elang_version to be created.
        $table = new xmldb_table('elang_version');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('elangid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('versionnumber', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'draft');
        // No default value, same rationale as the language field above.
        $table->add_field('contenthash', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('elangid', XMLDB_KEY_FOREIGN, ['elangid'], 'elang', ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);
        $table->add_index('elangid-versionnumber', XMLDB_INDEX_UNIQUE, ['elangid', 'versionnumber']);
        $table->add_index('status', XMLDB_INDEX_NOTUNIQUE, ['status']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table elang_cue to be created.
        $table = new xmldb_table('elang_cue');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('versionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cuekey', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('endtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('transcript', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('transcriptformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('versionid', XMLDB_KEY_FOREIGN, ['versionid'], 'elang_version', ['id']);
        $table->add_index('versionid-sortorder', XMLDB_INDEX_NOTUNIQUE, ['versionid', 'sortorder']);
        $table->add_index('versionid-starttime', XMLDB_INDEX_NOTUNIQUE, ['versionid', 'starttime']);
        $table->add_index('versionid-cuekey', XMLDB_INDEX_UNIQUE, ['versionid', 'cuekey']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table elang_gap to be created.
        $table = new xmldb_table('elang_gap');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('cueid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('gapkey', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('charstart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('charlength', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('solution', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('gradingalgorithm', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'exact');
        $table->add_field('maxlength', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('linkurl', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('cueid', XMLDB_KEY_FOREIGN, ['cueid'], 'elang_cue', ['id']);
        $table->add_index('cueid-sortorder', XMLDB_INDEX_NOTUNIQUE, ['cueid', 'sortorder']);
        $table->add_index('cueid-gapkey', XMLDB_INDEX_UNIQUE, ['cueid', 'gapkey']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table elang_gapanswer to be created.
        $table = new xmldb_table('elang_gapanswer');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('gapid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('answer', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('isregex', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('gapid', XMLDB_KEY_FOREIGN, ['gapid'], 'elang_gap', ['id']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table elang_gaphint to be created.
        $table = new xmldb_table('elang_gaphint');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('gapid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('level', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('hinttype', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'text');
        $table->add_field('hinttext', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('penalty', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('gapid', XMLDB_KEY_FOREIGN, ['gapid'], 'elang_gap', ['id']);
        $table->add_index('gapid-level', XMLDB_INDEX_UNIQUE, ['gapid', 'level']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table elang_attempt to be created.
        $table = new xmldb_table('elang_attempt');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('elangid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('versionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('attemptnumber', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('state', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'inprogress');
        $table->add_field('totalgaps', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('answeredgaps', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('exactgaps', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('correctgaps', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('hintedgaps', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('score', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timestart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timefinish', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('elangid', XMLDB_KEY_FOREIGN, ['elangid'], 'elang', ['id']);
        $table->add_key('versionid', XMLDB_KEY_FOREIGN, ['versionid'], 'elang_version', ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_index('elangid-userid-attemptnumber', XMLDB_INDEX_UNIQUE, ['elangid', 'userid', 'attemptnumber']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table elang_response to be created.
        $table = new xmldb_table('elang_response');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('attemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('gapid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('responsetext', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('resultstate', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'empty');
        $table->add_field('accepted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('tries', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('hintlevel', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('score', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('attemptid', XMLDB_KEY_FOREIGN, ['attemptid'], 'elang_attempt', ['id']);
        $table->add_key('gapid', XMLDB_KEY_FOREIGN, ['gapid'], 'elang_gap', ['id']);
        $table->add_index('attemptid-gapid', XMLDB_INDEX_UNIQUE, ['attemptid', 'gapid']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_mod_savepoint(true, 2026072301, 'elang');
    }

    if ($oldversion < 2026072306) {
        // Add the standard Moodle grade/gradetype field, needed for
        // gradebook integration (elang_grade_item_update()/
        // elang_update_grades() in lib.php). No key or index touches this
        // field, so there is no risk of the KEY/INDEX collision fixed
        // earlier in this file (see the elang_gapanswer/elang_attempt
        // history above) — checked explicitly, not just assumed, given that
        // history.
        $table = new xmldb_table('elang');
        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '100', 'currentversionid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026072306, 'elang');
    }

    if ($oldversion < 2026072307) {
        // Backing field for the completionfinishattempt custom completion
        // rule (classes/completion/custom_completion.php). Without a real
        // column here, elang_get_coursemodule_info() has nothing to read
        // and core_completion\activity_custom_completion::validate_rule()
        // always rejects the rule as "not used by this activity", no matter
        // what get_state() itself does — confirmed against a real Moodle
        // 4.5.12 PHPUnit failure, not assumed. No key or index touches this
        // field.
        $table = new xmldb_table('elang');
        $field = new xmldb_field('completionfinishattempt', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'grade');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026072307, 'elang');
    }

    if ($oldversion < 2026072308) {
        // Self-healing step. On at least one real site, mdl_config_plugins
        // recorded 2026072307 as already applied, but the elang table was
        // actually still missing completionfinishattempt — which then made
        // the very next step below fail with a DDL error (ALTER TABLE ...
        // ADD COLUMN jarothreshold ... AFTER completionfinishattempt,
        // referencing a column that did not actually exist on that site).
        // The exact root cause of the mismatch between the recorded
        // version and the real schema was not established; this step does
        // not depend on figuring that out. It only depends on
        // field_exists() against the schema as it actually is right now,
        // and heals every field this plugin has ever added to elang, not
        // only the one observed missing — deliberately not positioned via
        // 'previous' (column order has no functional effect and a wrong
        // 'previous' reference is exactly what caused the original
        // failure).
        $table = new xmldb_table('elang');

        $fields = [
            new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '100'),
            new xmldb_field('completionfinishattempt', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0'),
        ];
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2026072308, 'elang');
    }

    if ($oldversion < 2026072401) {
        // Backing field for the Jaro-similarity threshold used by the
        // wordrecognized grading algorithm (see
        // classes/local/grading/answer_evaluator.php). Default '1' preserves
        // exactly the previous behaviour (identical reduced forms required)
        // for every activity that predates this field. No key or index
        // touches this field.
        $table = new xmldb_table('elang');
        $field = new xmldb_field(
            'jarothreshold',
            XMLDB_TYPE_NUMBER,
            '10, 5',
            null,
            XMLDB_NOTNULL,
            null,
            '1',
            'completionfinishattempt'
        );
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026072401, 'elang');
    }

    if ($oldversion < 2026072405) {
        // The compute_content_hash() method now produces a SHA-256 digest
        // (64 hex characters) instead of SHA-1 (40, the width the
        // 2026072301 step above still creates the column with). Sites that
        // ran an earlier build of this step under version
        // 2026072401-2026072404 (where it was nested inside the block above
        // and never actually executed once oldversion had already passed
        // 2026072401) still have the narrower column — this step runs
        // unconditionally against whatever the column's current width is,
        // so it corrects that too.
        $versiontable = new xmldb_table('elang_version');
        $contenthashfield = new xmldb_field('contenthash', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null, 'status');
        if ($dbman->field_exists($versiontable, $contenthashfield)) {
            $dbman->change_field_precision($versiontable, $contenthashfield);
        }

        upgrade_mod_savepoint(true, 2026072405, 'elang');
    }

    if ($oldversion < 2026072407) {
        // Migration_V1_V2.md chapter 1.2, decision A: a real V1 site's
        // elang row already exists (Moodle requires exactly one activity
        // instance row per course module) with V1's own options JSON blob
        // in it; this step only ADDS a column for that blob to survive in,
        // it does not populate it. Nullable, no default needed. Migration
        // code reads it later; every other V2 code path ignores it.
        $table = new xmldb_table('elang');
        $field = new xmldb_field('options', XMLDB_TYPE_TEXT, null, null, null, null, null, 'jarothreshold');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026072407, 'elang');
    }

    if ($oldversion < 2026072414) {
        // Migration_V1_V2.md chapter 2 step 4 ("Verifikation") / this
        // release's admin sign-off page: deliberately separate from
        // currentversionid — an activity can be migrated but not yet
        // reviewed, tracked by these two staying NULL until an
        // administrator explicitly approves it via
        // classes/local/migration/v1_signoff.php.
        $table = new xmldb_table('elang');

        $field = new xmldb_field('migrationapproveduserid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field(
            'migrationapprovedtime',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            null,
            null,
            null,
            'migrationapproveduserid'
        );
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026072414, 'elang');
    }

    if ($oldversion < 2026072506) {
        // Versioned media (Blueprint chapter on the player / media model):
        // media belongs to elang_version so that swapping a medium publishes a
        // new version and in-progress attempts keep the medium they started
        // with. file-kind media lives in the 'media'/'poster' file areas at
        // itemid = version id; url- and provider-kind media are described by
        // the columns below. Login-gated providers are out of scope for core
        // and become a separate paid subplugin, so no OAuth column is added.
        $table = new xmldb_table('elang_version');

        $fields = [
            new xmldb_field('mediakind', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'timecreated'),
            new xmldb_field('mediaurl', XMLDB_TYPE_TEXT, null, null, null, null, null, 'mediakind'),
            new xmldb_field('mediaprovider', XMLDB_TYPE_CHAR, '40', null, null, null, null, 'mediaurl'),
            new xmldb_field('mediaproviderref', XMLDB_TYPE_TEXT, null, null, null, null, null, 'mediaprovider'),
            new xmldb_field('mediamime', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'mediaproviderref'),
            new xmldb_field('mediaduration', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'mediamime'),
        ];
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2026072506, 'elang');
    }

    if ($oldversion < 2026072519) {
        // Grading settings become versioned content: the content language and
        // the Jaro similarity threshold now live on elang_version, so grading
        // an in-progress attempt uses the settings pinned when its version was
        // published rather than the activity's current (possibly edited)
        // values. revision is a per-version content counter the authoring layer
        // will bump on edits. Existing rows are backfilled from their parent
        // elang activity, which already holds these values.
        $table = new xmldb_table('elang_version');

        // The language column ends up NOTNULL with no schema-level default
        // (same rationale as elang.language). Add it with a temporary default so
        // the column can be created on populated tables, backfill real values,
        // then drop the default so the end state matches install.xml.
        $language = new xmldb_field('language', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'en', 'mediaduration');
        if (!$dbman->field_exists($table, $language)) {
            $dbman->add_field($table, $language);
        }

        $jarothreshold = new xmldb_field(
            'jarothreshold',
            XMLDB_TYPE_NUMBER,
            '10, 5',
            null,
            XMLDB_NOTNULL,
            null,
            '1',
            'language'
        );
        if (!$dbman->field_exists($table, $jarothreshold)) {
            $dbman->add_field($table, $jarothreshold);
        }

        $revision = new xmldb_field('revision', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1', 'jarothreshold');
        if (!$dbman->field_exists($table, $revision)) {
            $dbman->add_field($table, $revision);
        }

        // Backfill language and jarothreshold from each version's parent
        // activity. A per-activity loop keeps the update portable across
        // database engines (no correlated UPDATE subquery).
        $elangs = $DB->get_recordset('elang', null, '', 'id, language, jarothreshold');
        foreach ($elangs as $elang) {
            $DB->set_field('elang_version', 'language', $elang->language, ['elangid' => $elang->id]);
            $DB->set_field('elang_version', 'jarothreshold', $elang->jarothreshold, ['elangid' => $elang->id]);
        }
        $elangs->close();

        // Drop the temporary default on language so the column matches
        // install.xml, where it has no default and callers always supply it.
        $languagenodefault = new xmldb_field('language', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null, 'mediaduration');
        $dbman->change_field_default($table, $languagenodefault);

        upgrade_mod_savepoint(true, 2026072519, 'elang');
    }

    return true;
}
