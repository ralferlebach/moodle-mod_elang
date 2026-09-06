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

namespace mod_elang\local\domain;

/**
 * Running a unit of work in a database transaction that actually rolls back.
 *
 * Moodle's delegated transactions do not unwind on their own. Starting one and
 * letting an exception escape leaves the work half-done: the statements that
 * already ran stay, and Moodle reports a transaction that was not closed
 * correctly. A transaction only undoes anything if the failure is handed to
 * moodle_transaction::rollback().
 *
 * That mattered here. save_draft_content() deletes a draft's whole content
 * before writing the new set, so a save that broke half-way through the insert
 * left the author with neither their old work nor their new one — the exact
 * case a transaction is for.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait transaction_trait {
    /**
     * Run a unit of work in a transaction, rolling back on any failure.
     *
     * @param callable $work The work to run; its return value is passed through
     * @return mixed Whatever the work returned
     */
    protected function in_transaction(callable $work) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        try {
            $result = $work();
            $transaction->allow_commit();

            return $result;
        } catch (\Throwable $e) {
            // Handing the failure to rollback() undoes the work and rethrows it, so the
            // caller still sees the original failure rather than a transaction
            // error standing in front of it. The throw below is unreachable in
            // practice and kept so the control flow reads correctly here.
            $transaction->rollback($e);

            throw $e;
        }
    }
}
