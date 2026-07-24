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
 * V1 and V2 both happen to use a table literally called `elang` (same name,
 * completely different columns — V1's has `options`/`language` text
 * columns, V2's has `currentversionid`/`jarothreshold`/`grade`), so that
 * table's mere existence is never a usable signal on its own. The reliable
 * signal is `elang_cues` (plural): a V1-only table name that cannot collide
 * with anything V2 declares (`elang_cue`, singular, is a different table).
 * See Migration_V1_V2.md chapter 1.1 ("Grundsatz: Die Migration wird an der
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
     * the only signal available is whether the real V2 `elang` row for the
     * same id already has a currentversionid set — see
     * v1_legacy_schema::create_tables()'s docblock for why the V1-only
     * metadata (name/options/language) is read from a separately-modelled
     * `elang_v1` table rather than from `elang` itself in this test
     * environment; a real migration reads that data from the very same
     * `elang` row instead, since it is the same row before and after the
     * schema upgrade adds the new V2 columns.
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
            // one activity instance row per course module), but in this
            // test environment it just means nobody has inserted one; treat
            // it as pending rather than erroring. A non-empty
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
            $elang = $DB->get_record('elang_v1', ['id' => $elangid]);
            if (!$elang) {
                continue;
            }

            $options = json_decode((string) $elang->options, true) ?? [];
            [$gradingalgorithm, $jarothreshold] = self::map_grading_algorithm($options);

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

    /**
     * Decide gradingalgorithm and jarothreshold for a V1 activity from its
     * options, per the rule confirmed against the V1 source
     * (Migration_V1_V2.md chapter 1.2, server.php:315-340): V1 applies
     * usecasesensitive/usetransliteration/jaroDistance uniformly to every
     * gap in the activity, OR-combined, so every gap gets the SAME mapped
     * algorithm — there is no per-gap source to honour even in principle.
     *
     * @param array $options Decoded elang.options
     * @return array{0: string, 1: float} [gradingalgorithm, jarothreshold]
     */
    private static function map_grading_algorithm(array $options): array {
        $casesensitive = $options['usecasesensitive'] ?? true;
        $transliteration = $options['usetransliteration'] ?? false;
        $jarodistance = isset($options['jaroDistance']) ? (float) $options['jaroDistance'] : 1.0;

        $lenient = !$casesensitive || $transliteration || $jarodistance < 1.0;

        if (!$lenient) {
            return ['exact', 1.0];
        }

        return ['wordrecognized', $jarodistance];
    }
}
