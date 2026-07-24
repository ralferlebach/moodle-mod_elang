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
 * Detects a version 1 (mod_elang 1.x) install alongside V2, and produces the
 * read-only "Trockenlauf" (dry run) report Migration_V1_V2.md chapter 2 step
 * 2 calls for — before any migration step is allowed to write anything.
 *
 * V1 and V2 share the same `elang` table — a V1 activity's row already
 * exists before any V2 schema step runs (Moodle requires exactly one
 * activity instance row per course module) and simply gains new columns
 * over time. Since 2026072407 (Migration_V1_V2.md chapter 1.2, decision A)
 * that includes a nullable `options` column purely so V1's JSON blob
 * survives from schema upgrade to data migration; the table's mere
 * existence is still never a usable signal on its own (every elang row has
 * one), so "pending" is decided from `options` being present and
 * `currentversionid` still being empty on the SAME row, not from a second
 * table. `elang_cues` (plural, V1-only, distinct from V2's singular
 * `elang_cue`) is the signal for whether V1 legacy content exists at all —
 * see Migration_V1_V2.md chapter 1.1 ("Grundsatz: Die Migration wird an der
 * Existenz der Legacy-Tabellen festgemacht, nicht an Versionsnummern").
 *
 * This class only reads. It does not migrate anything, does not create the
 * progress-tracking schema a resumable migration task will need, and does
 * not decide how elang_check/elang_help's aggregate counts should be
 * reported — those remain open per Migration_V1_V2.md chapter 3 and are
 * deliberately out of scope here.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class v1_detector {
    /**
     * Whether V1 legacy tables are present on this site at all.
     *
     * @return bool
     */
    public static function v1_tables_present(): bool {
        global $DB;

        $dbman = $DB->get_manager();

        return $dbman->table_exists(new \xmldb_table('elang_cues'));
    }

    /**
     * The elang.id of every V1 activity that still has legacy content
     * (at least one elang_cues row) and has not yet been migrated.
     *
     * "Not yet migrated" is necessarily approximate at this stage: without
     * the progress-tracking schema a real migration task will introduce,
     * the only signal available is the same elang row's own
     * currentversionid still being empty. That is a reasonable dry-run
     * heuristic, not the authoritative check the actual migration step will
     * need (a resumable task must not silently re-migrate an activity whose
     * publish step succeeded but whose own progress marker was never
     * written, for instance) — see the class docblock.
     *
     * @return int[] Sorted, unique elang ids
     */
    public static function pending_activity_ids(): array {
        global $DB;

        if (!self::v1_tables_present()) {
            return [];
        }

        $ids = $DB->get_fieldset_sql('SELECT DISTINCT id_elang FROM {elang_cues} ORDER BY id_elang');
        $ids = array_map('intval', $ids);

        return array_values(array_filter($ids, function (int $id): bool {
            global $DB;
            $currentversionid = $DB->get_field('elang', 'currentversionid', ['id' => $id], IGNORE_MISSING);

            // A false return means no elang row with this id at all — on a
            // real site this would be unexpected (Moodle requires exactly
            // one activity instance row per course module), but fail safe
            // (treat as pending) rather than fatal. A non-empty
            // currentversionid means it already has a published V2 version,
            // treat as already migrated.
            return $currentversionid === false || empty($currentversionid);
        }));
    }

    /**
     * Build the dry-run report for every pending V1 activity: quantities
     * only, no writes, safe to run repeatedly and safe to run on a large
     * site (Migration_V1_V2.md chapter 4, "speicherschonend" — reads one
     * activity's cues at a time, never the whole table at once).
     *
     * @return object[] One entry per pending activity, each with ->elangid,
     *         ->name, ->cuecount, ->gapcount, ->learnercount,
     *         ->gradingalgorithm ('exact'|'wordrecognized', see
     *         Migration_V1_V2.md chapter 1.2 for the mapping rule),
     *         ->jarothreshold, and ->parseerrors (string[], empty when every
     *         cue parsed cleanly)
     */
    public static function dry_run_report(): array {
        global $DB;

        $report = [];

        foreach (self::pending_activity_ids() as $elangid) {
            $elang = $DB->get_record('elang', ['id' => $elangid]);
            if (!$elang || $elang->options === null) {
                // No options blob to read: either a race with something
                // deleting the row between the two queries, or (should not
                // happen given v1_tables_present() and pending_activity_ids()
                // already checked) a V2-only activity that coincidentally
                // shares this id. Fail safe by skipping, not guessing.
                continue;
            }

            $options = json_decode((string) $elang->options, true) ?? [];
            [$gradingalgorithm, $jarothreshold] = v1_options_mapper::map_grading_algorithm($options);

            $cuerecords = $DB->get_records('elang_cues', ['id_elang' => $elangid]);
            $gapcount = 0;
            $parseerrors = [];

            foreach ($cuerecords as $cuerecord) {
                try {
                    $parsed = v1_cue_parser::parse($cuerecord->json);
                    $gapcount += count($parsed->gaps);
                } catch (\Throwable $e) {
                    $parseerrors[] = "cue id {$cuerecord->id} (number {$cuerecord->number}): " . $e->getMessage();
                }
            }

            $learnercount = $DB->count_records_sql(
                'SELECT COUNT(DISTINCT id_user) FROM {elang_users} WHERE id_elang = ?',
                [$elangid]
            );

            $report[] = (object) [
                'elangid' => $elangid,
                'name' => (string) $elang->name,
                'cuecount' => count($cuerecords),
                'gapcount' => $gapcount,
                'learnercount' => (int) $learnercount,
                'gradingalgorithm' => $gradingalgorithm,
                'jarothreshold' => $jarothreshold,
                'parseerrors' => $parseerrors,
            ];
        }

        return $report;
    }
}
