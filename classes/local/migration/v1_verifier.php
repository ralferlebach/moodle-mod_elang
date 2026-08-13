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
 * Verifies an already-migrated activity —
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
            $discrepancies[] = get_string('verify:jarothreshold', 'mod_elang', (object) [
                'actual' => $actualjarothreshold,
                'expected' => $expectedjarothreshold,
            ]);
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
     * @param int $elangid The activity instance id.
     * @param int $versionid The published version to check cues/gaps against
     * @param \stdClass[] $v1cues The v1cues to use.
     * @param string $expectedalgorithm The expectedalgorithm to use.
     * @param string[] $discrepancies Mutated in place
     * @return void No return value.
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

        // Batched up front: the comparison below used to issue a query per cue,
        // per gap and per gap's hint, which does not survive a large activity.
        // Everything the loop needs is read here in three queries and indexed by
        // the stable keys the migration assigns.
        $v2cues = $DB->get_records('elang_cue', ['versionid' => $versionid]);
        $cuesbykey = [];
        foreach ($v2cues as $v2cue) {
            $cuesbykey[$v2cue->cuekey] = $v2cue;
        }

        $gapsbycueandkey = [];
        $hintedgapids = [];
        if (!empty($v2cues)) {
            [$cueinsql, $cueinparams] = $DB->get_in_or_equal(array_keys($v2cues), SQL_PARAMS_NAMED);
            $v2gaps = $DB->get_records_select('elang_gap', "cueid $cueinsql", $cueinparams);
            foreach ($v2gaps as $v2gap) {
                $gapsbycueandkey[(int) $v2gap->cueid][$v2gap->gapkey] = $v2gap;
            }

            if (!empty($v2gaps)) {
                [$gapinsql, $gapinparams] = $DB->get_in_or_equal(array_keys($v2gaps), SQL_PARAMS_NAMED);
                $hintedgapids = array_flip(array_map('intval', $DB->get_fieldset_select(
                    'elang_gaphint',
                    'DISTINCT gapid',
                    "gapid $gapinsql",
                    $gapinparams
                )));
            }
        }

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
            $cue = $cuesbykey[$cuekey] ?? null;
            if (!$cue) {
                $discrepancies[] = get_string('verify:missingcue', 'mod_elang', $cuekey);
                continue;
            }

            if ($cue->transcript !== $parsed->transcript) {
                $discrepancies[] = get_string('verify:transcriptmismatch', 'mod_elang', $cuekey);
            }

            foreach ($parsed->gaps as $gapindex => $gap) {
                $position = $gapindex + 1;
                $gapkey = 'v1-gap-' . $v1cue->id . '-' . $position;
                $expectedgapkeys[$gapkey] = true;

                $gaprecord = $gapsbycueandkey[(int) $cue->id][$gapkey] ?? null;
                if (!$gaprecord) {
                    $discrepancies[] = get_string('verify:missinggap', 'mod_elang', $gapkey);
                    continue;
                }

                if ($gaprecord->solution !== $gap->solution) {
                    $discrepancies[] = get_string('verify:solutionmismatch', 'mod_elang', (object) [
                        'gapkey' => $gapkey,
                        'actual' => $gaprecord->solution,
                        'expected' => $gap->solution,
                    ]);
                }
                if ((int) $gaprecord->charstart !== $gap->charstart || (int) $gaprecord->charlength !== $gap->charlength) {
                    $discrepancies[] = get_string('verify:rangemismatch', 'mod_elang', $gapkey);
                }
                if ($gaprecord->gradingalgorithm !== $expectedalgorithm) {
                    $discrepancies[] = get_string('verify:algorithmmismatch', 'mod_elang', (object) [
                        'gapkey' => $gapkey,
                        'actual' => $gaprecord->gradingalgorithm,
                        'expected' => $expectedalgorithm,
                    ]);
                }

                $hashint = isset($hintedgapids[(int) $gaprecord->id]);
                if ($gap->hintsallowed && !$hashint) {
                    $discrepancies[] = get_string('verify:missinghint', 'mod_elang', $gapkey);
                } else if (!$gap->hintsallowed && $hashint) {
                    $discrepancies[] = get_string('verify:unexpectedhint', 'mod_elang', $gapkey);
                }
            }
        }

        // Cues/gaps present in V2 with no corresponding V1 source at all —
        // would mean either data invented by the migration or a V1 row
        // deleted after migration without a matching V2 cleanup, neither of
        // which should be possible, checked anyway rather than assumed.
        $expectedcuekeys = array_map(static fn ($v1cue) => 'v1-cue-' . $v1cue->id, $v1cues);
        foreach ($v2cues as $v2cue) {
            if (!in_array($v2cue->cuekey, $expectedcuekeys, true)) {
                $discrepancies[] = get_string('verify:orphancue', 'mod_elang', $v2cue->cuekey);
                continue;
            }

            foreach ($gapsbycueandkey[(int) $v2cue->id] ?? [] as $v2gap) {
                if (!isset($expectedgapkeys[$v2gap->gapkey])) {
                    $discrepancies[] = get_string('verify:orphangap', 'mod_elang', $v2gap->gapkey);
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
     * @param int $elangid The activity instance id.
     * @param int $versionid The content version id.
     * @param string[] $discrepancies Mutated in place
     * @return void No return value.
     */
    private function verify_learners(int $elangid, int $versionid, array &$discrepancies): void {
        global $DB;

        $expectedlearnercount = (int) $DB->count_records_sql(
            'SELECT COUNT(DISTINCT id_user) FROM {elang_users} WHERE id_elang = ?',
            [$elangid]
        );
        $actuallearnercount = (int) $DB->count_records('elang_attempt', ['elangid' => $elangid, 'versionid' => $versionid]);
        if ($expectedlearnercount !== $actuallearnercount) {
            $discrepancies[] = get_string('verify:attemptcount', 'mod_elang', (object) [
                'actual' => $actuallearnercount,
                'expected' => $expectedlearnercount,
            ]);
        }

        $v1cueids = $DB->get_fieldset_select('elang_cues', 'id', 'id_elang = ?', [$elangid]);
        $v1cueids = array_map('intval', $v1cueids);

        $v1users = $DB->get_records('elang_users', ['id_elang' => $elangid]);
        $expectedresponsesperuser = [];
        foreach ($v1users as $v1user) {
            if (!in_array((int) $v1user->id_cue, $v1cueids, true)) {
                // An orphaned V1 row —
                // v1_migrator reports these but cannot migrate them, so they
                // correctly contribute nothing here either.
                continue;
            }
            $state = json_decode((string) $v1user->json, true) ?? [];
            $userid = (int) $v1user->id_user;
            $expectedresponsesperuser[$userid] = ($expectedresponsesperuser[$userid] ?? 0) + count($state);
        }

        // The attempts and their response counts are read in two queries rather
        // than two per learner, so verification stays flat in the number of
        // learners on the activity.
        $attemptsbyuser = [];
        $attempts = $DB->get_records('elang_attempt', ['elangid' => $elangid, 'versionid' => $versionid]);
        foreach ($attempts as $attempt) {
            $attemptsbyuser[(int) $attempt->userid] = $attempt;
        }

        $responsecounts = [];
        if (!empty($attempts)) {
            [$attemptinsql, $attemptinparams] = $DB->get_in_or_equal(array_keys($attempts), SQL_PARAMS_NAMED);
            $counts = $DB->get_records_sql(
                "SELECT attemptid, COUNT(1) AS responses
                   FROM {elang_response}
                  WHERE attemptid $attemptinsql
               GROUP BY attemptid",
                $attemptinparams
            );
            foreach ($counts as $count) {
                $responsecounts[(int) $count->attemptid] = (int) $count->responses;
            }
        }

        foreach ($expectedresponsesperuser as $userid => $expectedcount) {
            $attempt = $attemptsbyuser[(int) $userid] ?? null;
            if (!$attempt) {
                $discrepancies[] = get_string('verify:missingattempt', 'mod_elang', $userid);
                continue;
            }

            $actualcount = $responsecounts[(int) $attempt->id] ?? 0;
            if ($actualcount !== $expectedcount) {
                $discrepancies[] = get_string('verify:responsecount', 'mod_elang', (object) [
                    'userid' => $userid,
                    'actual' => $actualcount,
                    'expected' => $expectedcount,
                ]);
            }
        }
    }
}
