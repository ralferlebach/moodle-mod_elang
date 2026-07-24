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
 * Records an administrator's explicit sign-off on a migrated activity —
 * Migration_V1_V2.md chapter 2 step 4 ("Freigabe durch Administration"), the
 * decision that follows reviewing a v1_verifier report, not the review
 * itself.
 *
 * Deliberately a separate concept from "migrated"
 * (elang.currentversionid): an activity can be fully migrated and sit
 * unreviewed for as long as an administrator needs, and — this is a
 * conscious choice, not an oversight — approving it does NOT require
 * v1_verifier to have found zero discrepancies first. A human reviewing a
 * handful of cosmetic discrepancies and deciding they are acceptable is
 * exactly the judgement this step exists to allow; hard-blocking on a clean
 * report would remove that judgement rather than support it. Nothing in
 * this class re-runs verification itself — the caller (the admin page) is
 * expected to have shown a current report before offering the approve
 * action.
 *
 * Approval is currently only ever a manual, one-way action: there is no
 * "unapprove". Nothing downstream (Migration_V1_V2.md chapter 2 step 5,
 * "Abbau" — removing the legacy tables and elang.options) reads this yet;
 * that step is a separate, later release and will decide for itself what,
 * if anything, it requires approval for.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class v1_signoff {
    /**
     * Record that $userid has reviewed and approved this activity's
     * migration.
     *
     * @param int $elangid
     * @param int $userid
     * @return void
     * @throws \coding_exception if the activity has not been migrated yet
     *         (no currentversionid) — approving something that has not
     *         happened is never meaningful, regardless of intent
     */
    public static function approve(int $elangid, int $userid): void {
        global $DB;

        $elang = $DB->get_record('elang', ['id' => $elangid], '*', MUST_EXIST);
        if (empty($elang->currentversionid)) {
            throw new \coding_exception("elang $elangid has not been migrated (no currentversionid); nothing to approve");
        }

        $DB->set_field('elang', 'migrationapproveduserid', $userid, ['id' => $elangid]);
        $DB->set_field('elang', 'migrationapprovedtime', time(), ['id' => $elangid]);
    }

    /**
     * Whether an activity has been signed off.
     *
     * @param int $elangid
     * @return bool
     */
    public static function is_approved(int $elangid): bool {
        global $DB;

        $approveduserid = $DB->get_field('elang', 'migrationapproveduserid', ['id' => $elangid], IGNORE_MISSING);

        return !empty($approveduserid);
    }

    /**
     * The full sign-off status for one activity, for the admin page to
     * render without needing to know the underlying field names.
     *
     * @param int $elangid
     * @return object{elangid: int, migrated: bool, approved: bool,
     *         approveduserid: ?int, approvedtime: ?int}
     * @throws \dml_missing_record_exception if the activity does not exist
     */
    public static function get_status(int $elangid): object {
        global $DB;

        $elang = $DB->get_record('elang', ['id' => $elangid], '*', MUST_EXIST);

        return (object) [
            'elangid' => $elangid,
            'migrated' => !empty($elang->currentversionid),
            'approved' => !empty($elang->migrationapproveduserid),
            'approveduserid' => $elang->migrationapproveduserid !== null ? (int) $elang->migrationapproveduserid : null,
            'approvedtime' => $elang->migrationapprovedtime !== null ? (int) $elang->migrationapprovedtime : null,
        ];
    }

    /**
     * Every migrated activity that has not been signed off yet — what the
     * admin page's "needs review" list is built from.
     *
     * @return int[] elang ids, sorted
     */
    public static function pending_approval_ids(): array {
        global $DB;

        $ids = $DB->get_fieldset_select(
            'elang',
            'id',
            'currentversionid IS NOT NULL AND migrationapproveduserid IS NULL',
            []
        );
        $ids = array_map('intval', $ids);
        sort($ids);

        return $ids;
    }
}
