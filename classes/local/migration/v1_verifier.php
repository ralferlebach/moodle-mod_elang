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
 * Verifies an already-migrated activity — Migration_V1_V2.md chapter 2 step
 * 4, "Soll-/Ist-Abgleich" — before an administrator is asked to sign off on
 * it (that sign-off step itself is not built yet, see the class docblock's
 * end).
 *
 * Deliberately independent of v1_migrator's own migration report: that
 * report is what the migration BELIEVES it did, produced by the same code
 * path that could itself be wrong. This class re-derives what SHOULD exist
 * — by re-parsing the still-present V1 legacy tables with the same
 * v1_cue_parser/v1_options_mapper the migrator used — and compares it
 * against what is actually sitting in the V2 tables right now, read-only.
 * A verification pass that only re-checked the migrator's own report would
 * not catch a bug in the migrator itself; re-deriving independently can.
 *
 * Only meaningful for an activity that has ALREADY migrated
 * (elang.currentversionid set) and whose V1 legacy rows still exist — once
 * Migration_V1_V2.md chapter 2 step 5 ("Abbau") removes the legacy tables in
 * a later release, there is nothing left here to verify against, which is
 * exactly why step 5 must not happen until step 4 (this class) has been run
 * and reviewed.
 *
 * What this does NOT do: fix anything it finds wrong (a discrepancy is
 * reported, never silently corrected — matching the same
 * report-don't-guess principle v1_migrator itself already applies to
 * parse errors, invalid links and orphaned responses), or decide whether a
 * discrepancy is acceptable — that judgement, and the resulting
 * administrator sign-off, is a separate piece of work this class only
 * informs.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class v1_verifier {
    /**
     * Verify one already-migrated activity.
     *
     * @param int $elangid The V1/V2 shared activity id
     * @return object{elangid: int, ok: bool, discrepancies: string[]} ->ok is
     *         true only when ->discrepancies is empty
     * @throws \coding_exception if the activity does not exist, was never
     *         migrated (no currentversionid), or its V1 legacy rows are
     *         gone (elang_cues has no rows for it — nothing left to verify
     *         against, see the class docblock)
     */
    public function verify_activity(int $elangid): object {
        global $DB;

        $elang = $DB->get_record('elang', ['id' => $elangid], '*', MUST_EXIST);
        if (empty($elang->currentversionid)) {
            throw new \coding_exception("elang $elangid has not been migrated (no currentversionid); nothing to verify");
        }

        $v1cues = $DB->get_records('elang_cues', ['id_elang' => $elangid], 'id ASC');
        if (empty($v1cues)) {
            throw new \coding_exception(
                "elang $elangid has no V1 legacy elang_cues rows left to verify against "
                    . '(already cleaned up, or never had any)'
            );
        }

        $discrepancies = [];

        $options = json_decode((string) $elang->options, true) ?? [];
        [$expectedalgorithm, $expectedjarothreshold] = v1_options_mapper::map_grading_algorithm($options);

        $this->verify_cues_and_gaps($elangid, $elang->currentversionid, $v1cues, $expectedalgorithm, $discrepancies);
        $this->verify_learners($elangid, $elang->currentversionid, $discrepancies);

        $actualjarothreshold = (float) $elang->jarothreshold;
        if (abs($actualjarothreshold - $expectedjarothreshold) > 0.00001) {
            $discrepancies[] = "elang.jarothreshold is {$actualjarothreshold}, expected {$expectedjarothreshold}";
        }

        return (object) [
            'elangid' => $elangid,
            'ok' => empty($discrepancies),
            'discrepancies' => $discrepancies,
        ];
    }

    /**
     * Re-parse every V1 cue and compare it, and each of its gaps, against
     * the migrated elang_cue/elang_gap rows found by cuekey/gapkey.
     *
     * @param int $elangid
     * @param int $versionid The published version to check cues/gaps against
     * @param \stdClass[] $v1cues
     * @param string $expectedalgorithm
     * @param string[] $discrepancies Mutated in place
     * @return void
     */
    private function verify_cues_and_gaps(
        int $elangid,
        int $versionid,
        array $v1cues,
        string $expectedalgorithm,
        array &$discrepancies
    ): void {
        global $DB;

        $expectedgapkeys = [];

        foreach ($v1cues as $v1cue) {
            try {
                $parsed = v1_cue_parser::parse($v1cue->json);
            } catch (\Throwable $e) {
                // A cue the migration itself could not parse either — it was
                // reported as a parse error at migration time and never
                // produced an elang_cue, so there is nothing to compare here.
                continue;
            }

            $cuekey = 'v1-cue-' . $v1cue->id;
            $cue = $DB->get_record('elang_cue', ['versionid' => $versionid, 'cuekey' => $cuekey]);
            if (!$cue) {
                $discrepancies[] = "cuekey {$cuekey}: missing elang_cue row";
                continue;
            }

            if ($cue->transcript !== $parsed->transcript) {
                $discrepancies[] = "cuekey {$cuekey}: transcript does not match the re-parsed V1 source";
            }

            foreach ($parsed->gaps as $gapindex => $gap) {
                $position = $gapindex + 1;
                $gapkey = 'v1-gap-' . $v1cue->id . '-' . $position;
                $expectedgapkeys[$gapkey] = true;

                $gaprecord = $DB->get_record('elang_gap', ['cueid' => $cue->id, 'gapkey' => $gapkey]);
                if (!$gaprecord) {
                    $discrepancies[] = "gapkey {$gapkey}: missing elang_gap row";
                    continue;
                }

                if ($gaprecord->solution !== $gap->solution) {
                    $discrepancies[] = "gapkey {$gapkey}: solution is \"{$gaprecord->solution}\", expected \"{$gap->solution}\"";
                }
                if ((int) $gaprecord->charstart !== $gap->charstart || (int) $gaprecord->charlength !== $gap->charlength) {
                    $discrepancies[] = "gapkey {$gapkey}: charstart/charlength does not match the re-parsed V1 source";
                }
                if ($gaprecord->gradingalgorithm !== $expectedalgorithm) {
                    $discrepancies[] = "gapkey {$gapkey}: gradingalgorithm is {$gaprecord->gradingalgorithm}, "
                        . "expected {$expectedalgorithm}";
                }

                $hashint = $DB->record_exists('elang_gaphint', ['gapid' => $gaprecord->id]);
                if ($gap->hintsallowed && !$hashint) {
                    $discrepancies[] = "gapkey {$gapkey}: V1 marked this gap help-allowed, but no elang_gaphint exists";
                } else if (!$gap->hintsallowed && $hashint) {
                    $discrepancies[] = "gapkey {$gapkey}: V1 marked this gap help-disallowed, but an elang_gaphint exists";
                }
            }
        }

        // Cues/gaps present in V2 with no corresponding V1 source at all —
        // would mean either data invented by the migration or a V1 row
        // deleted after migration without a matching V2 cleanup, neither of
        // which should be possible, checked anyway rather than assumed.
        $v2cues = $DB->get_records('elang_cue', ['versionid' => $versionid]);
        $expectedcuekeys = array_map(static fn ($v1cue) => 'v1-cue-' . $v1cue->id, $v1cues);
        foreach ($v2cues as $v2cue) {
            if (!in_array($v2cue->cuekey, $expectedcuekeys, true)) {
                $discrepancies[] = "elang_cue id {$v2cue->id} (cuekey {$v2cue->cuekey}): no corresponding V1 cue found";
                continue;
            }

            $v2gaps = $DB->get_records('elang_gap', ['cueid' => $v2cue->id]);
            foreach ($v2gaps as $v2gap) {
                if (!isset($expectedgapkeys[$v2gap->gapkey])) {
                    $discrepancies[] = "elang_gap id {$v2gap->id} (gapkey {$v2gap->gapkey}): no corresponding V1 gap found";
                }
            }
        }
    }

    /**
     * Compare the number of migrated attempts/responses against what the
     * V1 legacy elang_users data independently implies they should be.
     *
     * Deliberately count-only, not a per-response content re-check: that
     * would mean re-running the full grading evaluation a second time
     * (exactly what v1_migrator itself already does), which verifies the
     * migrator agrees with itself, not that the counts genuinely match the
     * source data. Counting from the V1 side independently is the check
     * that actually catches a row silently dropped or duplicated.
     *
     * @param int $elangid
     * @param int $versionid
     * @param string[] $discrepancies Mutated in place
     * @return void
     */
    private function verify_learners(int $elangid, int $versionid, array &$discrepancies): void {
        global $DB;

        $expectedlearnercount = (int) $DB->count_records_sql(
            'SELECT COUNT(DISTINCT id_user) FROM {elang_users} WHERE id_elang = ?',
            [$elangid]
        );
        $actuallearnercount = (int) $DB->count_records('elang_attempt', ['elangid' => $elangid, 'versionid' => $versionid]);
        if ($expectedlearnercount !== $actuallearnercount) {
            $discrepancies[] = "attempt count is {$actuallearnercount}, expected {$expectedlearnercount} distinct V1 learners";
        }

        $v1cueids = $DB->get_fieldset_select('elang_cues', 'id', 'id_elang = ?', [$elangid]);
        $v1cueids = array_map('intval', $v1cueids);

        $v1users = $DB->get_records('elang_users', ['id_elang' => $elangid]);
        $expectedresponsesperuser = [];
        foreach ($v1users as $v1user) {
            if (!in_array((int) $v1user->id_cue, $v1cueids, true)) {
                // An orphaned V1 row (Migration_V1_V2.md chapter 3.1) —
                // v1_migrator reports these but cannot migrate them, so they
                // correctly contribute nothing here either.
                continue;
            }
            $state = json_decode((string) $v1user->json, true) ?? [];
            $userid = (int) $v1user->id_user;
            $expectedresponsesperuser[$userid] = ($expectedresponsesperuser[$userid] ?? 0) + count($state);
        }

        foreach ($expectedresponsesperuser as $userid => $expectedcount) {
            $attempt = $DB->get_record('elang_attempt', ['elangid' => $elangid, 'versionid' => $versionid, 'userid' => $userid]);
            if (!$attempt) {
                $discrepancies[] = "userid {$userid}: expected an elang_attempt, found none";
                continue;
            }

            $actualcount = (int) $DB->count_records('elang_response', ['attemptid' => $attempt->id]);
            if ($actualcount !== $expectedcount) {
                $discrepancies[] = "userid {$userid}: elang_response count is {$actualcount}, expected {$expectedcount}";
            }
        }
    }
}
