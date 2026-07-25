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

namespace mod_elang\local\migration;

/**
 * Removes the V1 legacy tables and the elang.options column —
 * Migration_V1_V2.md chapter 2 step 5 ("Abbau"), the last and only
 * irreversible step in the whole migration.
 *
 * Deliberately NOT a normal db/upgrade.php step. Every other schema change
 * this plugin has ever made (including adding options itself, chapter 1.3)
 * runs automatically the moment a site upgrades, because ADD COLUMN/CREATE
 * TABLE is safe to apply unconditionally. Dropping the only remaining copy
 * of a site's V1 source data is not: doing that automatically on upgrade
 * would delete it on every site running this plugin the moment they
 * install this version, whether or not their migration was ever verified,
 * whether or not anyone signed off on it. This class is only ever invoked
 * explicitly — cli/decommission_v1.php or the admin page — never from
 * db/upgrade.php, and only once blockers() is empty.
 *
 * Migration_V1_V2.md's own principle ("mindestens ein Release Abstand" —
 * at least one release between migrating and decommissioning) is
 * deliberately NOT encoded as a version-number check here: that would
 * invite exactly the false confidence a real release boundary is meant to
 * prevent. The actual safety property enforced here — every V1 activity
 * migrated AND every migrated activity signed off — is the one that
 * matters; how long an administrator chooses to wait beyond that remains
 * their judgement.
 *
 * elang.migrationapproveduserid/migrationapprovedtime are deliberately kept
 * even after decommissioning, unlike options: they are an audit record of
 * who approved what and when, with lasting value long after the source
 * data they were approving is gone. options, in contrast, is genuinely
 * disposable — its only purpose was surviving the gap between schema
 * upgrade and data migration (Migration_V1_V2.md chapter 1.3), a gap that
 * has definitionally closed for every activity this class allows itself to
 * run for.
 *
 * options is only ever dropped if at least one activity on the site has
 * actually been signed off (migrationapproveduserid set somewhere) — never
 * merely because blockers() came back empty. Those are not the same
 * condition: blockers() is empty just as much for a site that never had
 * any V1 data at all as for one that completed a real migration cycle, and
 * the former must leave the (harmless, unused, permanent) options column
 * alone.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class v1_decommissioner {
    /**
     * Reasons decommissioning cannot proceed right now. Empty means safe.
     *
     * @return string[]
     */
    public static function blockers(): array {
        if (!v1_detector::v1_tables_present()) {
            return [];
        }

        $blockers = [];

        $pendingmigration = v1_detector::pending_activity_ids();
        if (!empty($pendingmigration)) {
            $blockers[] = count($pendingmigration) . ' V1 activity/activities not yet migrated (elang id: '
                . implode(', ', $pendingmigration) . ')';
        }

        $pendingapproval = v1_signoff::pending_approval_ids();
        if (!empty($pendingapproval)) {
            $blockers[] = count($pendingapproval) . ' migrated activity/activities not yet approved (elang id: '
                . implode(', ', $pendingapproval) . ')';
        }

        return $blockers;
    }

    /**
     * Drop the V1 legacy tables and elang.options.
     *
     * @return object{droppedtables: string[], droppedfields: string[]}
     * @throws \coding_exception if blockers() is non-empty — never called
     *         "just to see", the caller must have already shown blockers()
     *         to a human and gotten explicit confirmation that none remain
     */
    public static function decommission(): object {
        global $DB;

        $blockers = self::blockers();
        if (!empty($blockers)) {
            throw new \coding_exception(
                'Refusing to decommission: ' . implode('; ', $blockers)
            );
        }

        $dbman = $DB->get_manager();
        $result = (object) ['droppedtables' => [], 'droppedfields' => []];

        foreach (['elang_cues', 'elang_users', 'elang_help', 'elang_check'] as $name) {
            $table = new \xmldb_table($name);
            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
                $result->droppedtables[] = $name;
            }
        }

        // Only ever drop elang.options if at least one activity on this
        // site actually went through the full migrate-then-approve cycle
        // (migrationapproveduserid is never cleared once set, so this is a
        // permanent, reliable signal — see the class docblock on why that
        // field survives decommissioning). Without it, blockers() being
        // empty could equally mean "no V1 tables because this site never
        // had any V1 data at all" (a native V2 site, or one where the
        // legacy tables genuinely never existed) — options is then a
        // harmless, unused, permanent column that must not be touched,
        // not something to strip just because it happens to exist.
        $eversignedoff = $DB->record_exists_select('elang', 'migrationapproveduserid IS NOT NULL');
        if ($eversignedoff) {
            $elangtable = new \xmldb_table('elang');
            $optionsfield = new \xmldb_field('options', XMLDB_TYPE_TEXT);
            if ($dbman->field_exists($elangtable, $optionsfield)) {
                $dbman->drop_field($elangtable, $optionsfield);
                $result->droppedfields[] = 'elang.options';
            }
        }

        return $result;
    }
}
