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

namespace mod_elang\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for mod_elang.
 *
 * Covers elang_attempt and elang_response, the two tables that hold learner
 * data: who attempted an exercise, when, how far they got, what they typed
 * for each gap and how it was evaluated. Exercise content (elang_version,
 * elang_cue, elang_gap, elang_gapanswer, elang_gaphint) is not personal data
 * and is out of scope here.
 *
 * This replaces the null_provider that was correct for the schema-only
 * skeleton (2.0.0-alpha.1/alpha.2): the external functions introduced
 * alongside this provider are the first code path that can actually write to
 * elang_attempt/elang_response from outside a test, which is exactly the
 * point at which a full provider must exist.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describe the personal data stored by this plugin.
     *
     * @param collection $collection The metadata collection to add to
     * @return collection The updated metadata collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('elang_attempt', [
            'versionid' => 'privacy:metadata:elang_attempt:versionid',
            'userid' => 'privacy:metadata:elang_attempt:userid',
            'attemptnumber' => 'privacy:metadata:elang_attempt:attemptnumber',
            'state' => 'privacy:metadata:elang_attempt:state',
            'totalgaps' => 'privacy:metadata:elang_attempt:totalgaps',
            'answeredgaps' => 'privacy:metadata:elang_attempt:answeredgaps',
            'exactgaps' => 'privacy:metadata:elang_attempt:exactgaps',
            'correctgaps' => 'privacy:metadata:elang_attempt:correctgaps',
            'hintedgaps' => 'privacy:metadata:elang_attempt:hintedgaps',
            'score' => 'privacy:metadata:elang_attempt:score',
            'timestart' => 'privacy:metadata:elang_attempt:timestart',
            'timefinish' => 'privacy:metadata:elang_attempt:timefinish',
            'timemodified' => 'privacy:metadata:elang_attempt:timemodified',
        ], 'privacy:metadata:elang_attempt');

        $collection->add_database_table('elang_response', [
            'responsetext' => 'privacy:metadata:elang_response:responsetext',
            'resultstate' => 'privacy:metadata:elang_response:resultstate',
            'accepted' => 'privacy:metadata:elang_response:accepted',
            'tries' => 'privacy:metadata:elang_response:tries',
            'hintlevel' => 'privacy:metadata:elang_response:hintlevel',
            'score' => 'privacy:metadata:elang_response:score',
            'timecreated' => 'privacy:metadata:elang_response:timecreated',
            'timemodified' => 'privacy:metadata:elang_response:timemodified',
        ], 'privacy:metadata:elang_response');

        $collection->add_database_table('elang_version', [
            'usermodified' => 'privacy:metadata:elang_version:usermodified',
        ], 'privacy:metadata:elang_version');

        // The activity also records which user signed off the one-way migration
        // of its 1.x content, which is a user identifier like any other.
        $collection->add_database_table('elang', [
            'migrationapproveduserid' => 'privacy:metadata:elang:migrationapproveduserid',
        ], 'privacy:metadata:elang');

        return $collection;
    }

    /**
     * Return every context that holds personal data for the given user.
     *
     * @param int $userid The user to search for
     * @return contextlist The list of contexts containing personal data for this user
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // A user leaves personal data behind in three ways: as a learner who
        // attempted the exercise, as an author whose id is stamped on a content
        // version, and as the administrator who signed off the 1.x migration.
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {elang} e ON e.id = cm.instance
             LEFT JOIN {elang_attempt} a ON a.elangid = e.id AND a.userid = :attemptuserid
             LEFT JOIN {elang_version} v ON v.elangid = e.id AND v.usermodified = :versionuserid
                 WHERE a.id IS NOT NULL
                    OR v.id IS NOT NULL
                    OR e.migrationapproveduserid = :approveduserid";

        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'elang',
            'attemptuserid' => $userid,
            'versionuserid' => $userid,
            'approveduserid' => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Add every user with personal data in the given context to the userlist.
     *
     * @param userlist $userlist The userlist to add users to
     * @return void Nothing; the users are added to the given userlist.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }

        $sql = "SELECT a.userid
                  FROM {course_modules} cm
                  JOIN {elang} e ON e.id = cm.instance
                  JOIN {elang_attempt} a ON a.elangid = e.id
                 WHERE cm.id = :cmid";
        $userlist->add_from_sql('userid', $sql, ['cmid' => $context->instanceid]);

        // Authors of content versions, and the migration sign-off user, are in
        // the activity's data too even when they never attempted it.
        $authorsql = "SELECT v.usermodified
                        FROM {course_modules} cm
                        JOIN {elang} e ON e.id = cm.instance
                        JOIN {elang_version} v ON v.elangid = e.id
                       WHERE cm.id = :cmid AND v.usermodified > 0";
        $userlist->add_from_sql('usermodified', $authorsql, ['cmid' => $context->instanceid]);

        $approvedsql = "SELECT e.migrationapproveduserid
                          FROM {course_modules} cm
                          JOIN {elang} e ON e.id = cm.instance
                         WHERE cm.id = :cmid AND e.migrationapproveduserid > 0";
        $userlist->add_from_sql('migrationapproveduserid', $approvedsql, ['cmid' => $context->instanceid]);
    }

    /**
     * Export all personal data for the approved contexts and user.
     *
     * Loads every response for every one of the user's attempts at an
     * activity in a single query (grouped by attemptid afterwards in PHP)
     * rather than one query per attempt, which used to make export cost
     * grow with the number of attempts a learner had made.
     *
     * @param approved_contextlist $contextlist List of approved contexts for one user
     * @return void No return value; the data is handed to the writer.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        if (!$contextlist->count()) {
            return;
        }

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id('elang', $context->instanceid, 0, false, MUST_EXIST);

            $attempts = $DB->get_records('elang_attempt', ['elangid' => $cm->instance, 'userid' => $user->id]);
            $responsesbyattempt = empty($attempts)
                ? []
                : self::get_responses_by_attempt(array_keys($attempts));

            $exportedattempts = [];
            foreach ($attempts as $attempt) {
                $exportedresponses = [];
                foreach ($responsesbyattempt[$attempt->id] ?? [] as $response) {
                    $exportedresponses[] = (object) [
                        'responsetext' => $response->responsetext,
                        'resultstate' => $response->resultstate,
                        'accepted' => (bool) $response->accepted,
                        'tries' => (int) $response->tries,
                        'hintlevel' => (int) $response->hintlevel,
                        'score' => (float) $response->score,
                        'timecreated' => transform::datetime((int) $response->timecreated),
                        'timemodified' => transform::datetime((int) $response->timemodified),
                    ];
                }

                $exportedattempts[] = (object) [
                    'versionid' => (int) $attempt->versionid,
                    'attemptnumber' => (int) $attempt->attemptnumber,
                    'state' => $attempt->state,
                    'totalgaps' => (int) $attempt->totalgaps,
                    'answeredgaps' => (int) $attempt->answeredgaps,
                    'exactgaps' => (int) $attempt->exactgaps,
                    'correctgaps' => (int) $attempt->correctgaps,
                    'hintedgaps' => (int) $attempt->hintedgaps,
                    'score' => (float) $attempt->score,
                    'timestart' => transform::datetime((int) $attempt->timestart),
                    'timefinish' => $attempt->timefinish ? transform::datetime((int) $attempt->timefinish) : null,
                    'timemodified' => transform::datetime((int) $attempt->timemodified),
                    'responses' => $exportedresponses,
                ];
            }

            // A user may also appear as the author of content versions or as the
            // administrator who signed off the 1.x migration, without ever
            // having attempted the exercise; export those too.
            $authored = $DB->get_records(
                'elang_version',
                ['elangid' => $cm->instance, 'usermodified' => $user->id],
                'id ASC',
                'id, versionnumber, status, timecreated'
            );
            $exportedversions = [];
            foreach ($authored as $version) {
                $exportedversions[] = (object) [
                    'versionnumber' => (int) $version->versionnumber,
                    'status' => $version->status,
                    'timecreated' => transform::datetime((int) $version->timecreated),
                ];
            }

            $approvedtime = $DB->get_field(
                'elang',
                'migrationapprovedtime',
                ['id' => $cm->instance, 'migrationapproveduserid' => $user->id]
            );

            if (empty($exportedattempts) && empty($exportedversions) && empty($approvedtime)) {
                continue;
            }

            $exportdata = ['attempts' => $exportedattempts];
            if (!empty($exportedversions)) {
                $exportdata['authoredversions'] = $exportedversions;
            }
            if (!empty($approvedtime)) {
                $exportdata['migrationapprovedtime'] = transform::datetime((int) $approvedtime);
            }

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'mod_elang')],
                (object) $exportdata
            );
        }
    }

    /**
     * Delete all personal data for every user in the given context.
     *
     * @param \context $context The context to delete personal data within
     * @return void No return value.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('elang', $context->instanceid);
        if (!$cm) {
            return;
        }

        self::delete_attempts_and_responses_where('elangid = :elangid', ['elangid' => $cm->instance]);
        self::anonymise_authoring_where((int) $cm->instance, ':column > 0', []);
    }

    /**
     * Delete all personal data for one user across the approved contexts.
     *
     * @param approved_contextlist $contextlist List of approved contexts for one user
     * @return void No return value.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id('elang', $context->instanceid);
            if (!$cm) {
                continue;
            }

            self::delete_attempts_and_responses_where(
                'elangid = :elangid AND userid = :userid',
                ['elangid' => $cm->instance, 'userid' => $userid]
            );
            self::anonymise_authoring_where(
                (int) $cm->instance,
                ':column = :authoruserid',
                ['authoruserid' => $userid]
            );
        }
    }

    /**
     * Detach a user's identity from the authoring trail of one activity.
     *
     * The content versions themselves belong to the course, not to the user, so
     * they are kept; only the identifying reference is cleared, which is the
     * anonymisation the GDPR right to erasure asks for here.
     *
     * @param int $elangid The activity to clean.
     * @param string $wheresql A WHERE fragment matching the users to detach, on the alias-free column.
     * @param array $params The parameters for that fragment.
     * @return void No return value.
     */
    private static function anonymise_authoring_where(int $elangid, string $wheresql, array $params): void {
        global $DB;

        $DB->set_field_select(
            'elang_version',
            'usermodified',
            0,
            'elangid = :elangid AND ' . str_replace(':column', 'usermodified', $wheresql),
            $params + ['elangid' => $elangid]
        );

        $DB->set_field_select(
            'elang',
            'migrationapproveduserid',
            0,
            'id = :elangid AND ' . str_replace(':column', 'migrationapproveduserid', $wheresql),
            $params + ['elangid' => $elangid]
        );
    }

    /**
     * Delete personal data for the approved users in one context.
     *
     * Deletes all approved users in a single set-based statement (userid IN
     * (...)) rather than one delete per user, which used to make this scale
     * with the number of users in the erasure request instead of running as
     * one bounded operation.
     *
     * @param approved_userlist $userlist The approved users in one context
     * @return void No return value.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('elang', $context->instanceid);
        if (!$cm) {
            return;
        }

        $userids = array_map('intval', $userlist->get_userids());
        if (empty($userids)) {
            return;
        }

        [$insql, $inparams] = $DB->get_in_or_equal($userids);

        self::delete_attempts_and_responses_where(
            "elangid = ? AND userid $insql",
            array_merge([$cm->instance], $inparams)
        );

        // Detach the same users from the authoring trail, keeping the content.
        $DB->set_field_select(
            'elang_version',
            'usermodified',
            0,
            "elangid = ? AND usermodified $insql",
            array_merge([$cm->instance], $inparams)
        );
        $DB->set_field_select(
            'elang',
            'migrationapproveduserid',
            0,
            "id = ? AND migrationapproveduserid $insql",
            array_merge([$cm->instance], $inparams)
        );
    }

    /**
     * Load every elang_response row for a set of attempt ids in a single
     * query, grouped by attemptid.
     *
     * @param int[] $attemptids The attempt ids to read.
     * @return array<int, \stdClass[]> Responses keyed by attemptid
     */
    private static function get_responses_by_attempt(array $attemptids): array {
        global $DB;

        if (empty($attemptids)) {
            return [];
        }

        [$insql, $inparams] = $DB->get_in_or_equal(array_map('intval', $attemptids));
        $responses = $DB->get_records_select('elang_response', "attemptid $insql", $inparams);

        $byattempt = [];
        foreach ($responses as $response) {
            $byattempt[$response->attemptid][] = $response;
        }

        return $byattempt;
    }

    /**
     * Delete elang_response and elang_attempt rows matching a WHERE clause
     * against elang_attempt, using a subquery rather than loading id lists
     * into PHP first. Runs both deletes inside one delegated transaction, so
     * an interrupted erasure request cannot leave attempts deleted with
     * their responses still present, or be recorded as complete when only
     * half-applied.
     *
     * @param string $attemptwheresql WHERE clause fragment against elang_attempt
     * @param array $params Parameters for the WHERE clause (named or positional, matching the SQL fragment)
     * @return void No return value.
     */
    private static function delete_attempts_and_responses_where(string $attemptwheresql, array $params): void {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        $DB->delete_records_select(
            'elang_response',
            "attemptid IN (SELECT id FROM {elang_attempt} WHERE $attemptwheresql)",
            $params
        );
        $DB->delete_records_select('elang_attempt', $attemptwheresql, $params);

        $transaction->allow_commit();
    }
}
