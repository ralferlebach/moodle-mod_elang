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

namespace mod_elang\task;

use mod_elang\local\migration\v1_detector;
use mod_elang\local\migration\v1_migrator;

/**
 * Resumable, block-wise migration of every pending V1 activity — the
 * "Migration" step (Migration_V1_V2.md chapter 2, step 3) wrapped around the
 * per-activity unit of work v1_migrator already provides.
 *
 * Resumability is not implemented via a dedicated progress-tracking table:
 * v1_detector::pending_activity_ids() is itself the progress marker
 * (Migration_V1_V2.md chapter 1.2's `elang.currentversionid` check — an
 * activity that migrated successfully is never returned again, from this
 * task run or any later one, cron-restarted or not). Nothing here decides
 * what "pending" means; that stays entirely v1_detector's responsibility, so
 * the two can never drift apart on the definition.
 *
 * One execute() call processes at most BLOCK_SIZE activities
 * (Migration_V1_V2.md chapter 4, "blockweise") and, if pending activities
 * remain afterwards, queues another instance of itself to continue — an
 * adhoc task is expected to finish in bounded time per cron run, not to
 * migrate an entire large site in one execution.
 *
 * A single activity's own failure (an exception from
 * v1_migrator::migrate_activity(), for whatever reason) is caught, logged
 * via mtrace(), and does not stop the rest of the block — the task's job is
 * to make as much forward progress as the data allows, the same principle
 * v1_migrator itself already applies to individual cues/gaps/responses
 * within one activity.
 *
 * Deliberately not yet built: an admin page or CLI script to queue this
 * task, and the "Verifikation" step (Migration_V1_V2.md chapter 2, step 4)
 * that would compare migrated content against the source afterwards and
 * require explicit administrator sign-off before anything is trusted.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class migrate_v1_activities_task extends \core\task\adhoc_task {
    /** @var int Default activities processed per execute() call, overridable via custom data. */
    public const DEFAULT_BLOCK_SIZE = 20;

    /**
     * Queue one instance of this task, ready to pick up wherever a previous
     * run (if any) left off — safe to call even while an instance is
     * already queued or running, since v1_detector::pending_activity_ids()
     * is re-queried at the start of every execute(), never assumed stale.
     *
     * @param int $blocksize Activities to process per execute() call
     * @return void
     */
    public static function queue(int $blocksize = self::DEFAULT_BLOCK_SIZE): void {
        $task = new self();
        $task->set_custom_data((object) ['blocksize' => $blocksize]);
        \core\task\manager::queue_adhoc_task($task);
    }

    /**
     * The task's display name, shown in admin/tasklogs.php and similar.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task:migratev1activities', 'mod_elang');
    }

    /**
     * Process up to one block of pending activities, and re-queue if more remain.
     *
     * @return void
     */
    public function execute(): void {
        $blocksize = (int) ($this->get_custom_data()?->blocksize ?? self::DEFAULT_BLOCK_SIZE);
        if ($blocksize < 1) {
            $blocksize = self::DEFAULT_BLOCK_SIZE;
        }

        if (!v1_detector::v1_tables_present()) {
            mtrace('mod_elang: no V1 legacy tables present, nothing to migrate.');

            return;
        }

        $pending = v1_detector::pending_activity_ids();
        if (empty($pending)) {
            mtrace('mod_elang: no pending V1 activities, nothing to do.');

            return;
        }

        $block = array_slice($pending, 0, $blocksize);
        mtrace('mod_elang: migrating ' . count($block) . ' of ' . count($pending) . ' pending V1 activities.');

        $migrator = new v1_migrator();
        $succeeded = 0;
        $failed = 0;

        foreach ($block as $elangid) {
            try {
                $report = $migrator->migrate_activity($elangid);
                $succeeded++;
                mtrace(
                    "  elang {$elangid}: migrated ({$report->cuecount} cues, {$report->gapcount} gaps, "
                        . "{$report->attemptcount} attempts, {$report->responsecount} responses, "
                        . "{$report->mediafilecount} media files, {$report->posterfilecount} poster files, "
                        . count($report->parseerrors) . ' parse errors, '
                        . count($report->invalidlinks) . ' invalid links, '
                        . count($report->orphanedresponses) . ' orphaned responses)'
                );
            } catch (\Throwable $e) {
                $failed++;
                mtrace("  elang {$elangid}: FAILED — " . $e->getMessage());
            }
        }

        mtrace("mod_elang: block complete, {$succeeded} succeeded, {$failed} failed.");

        if (count($pending) > count($block)) {
            mtrace('mod_elang: more pending activities remain, queueing another block.');
            self::queue($blocksize);
        }
    }
}
