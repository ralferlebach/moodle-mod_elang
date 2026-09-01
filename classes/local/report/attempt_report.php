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

namespace mod_elang\local\report;

/**
 * Read-only assembly of learner-attempt data for the teacher report.
 *
 * Gathers attempt summaries for an activity and the full gap-by-gap detail of a
 * single attempt. It never changes anything — grade overrides stay in the
 * Moodle gradebook.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class attempt_report {
    /**
     * The columns the overview may be sorted by, mapped to their SQL.
     *
     * A whitelist, not a passthrough: the sort column arrives in a request
     * parameter, and no request may ever choose SQL. Anything not named here
     * falls back to the default order.
     */
    private const SORT_COLUMNS = [
        'user' => 'u.lastname, u.firstname',
        'attemptnumber' => 'a.attemptnumber',
        'state' => 'a.state',
        'score' => 'a.score',
        'answered' => 'a.answeredgaps',
        'correct' => 'a.correctgaps',
        'finished' => 'a.timefinish',
    ];

    /** The states an attempt may be filtered to. */
    private const STATES = ['inprogress', 'finished', 'abandoned'];

    /**
     * Normalise a set of request filters to the ones this report understands.
     *
     * Every value is cast and range-checked here rather than at the call site,
     * so a filter can only ever reach the query in a shape the query expects.
     *
     * @param array $filters Raw filters: userid, state, from, to, attemptnumber
     * @return array The accepted filters, with anything unusable removed
     */
    public static function clean_filters(array $filters): array {
        $clean = [];

        if (!empty($filters['userid'])) {
            $clean['userid'] = (int) $filters['userid'];
        }
        if (!empty($filters['state']) && in_array($filters['state'], self::STATES, true)) {
            $clean['state'] = (string) $filters['state'];
        }
        if (!empty($filters['from'])) {
            $clean['from'] = (int) $filters['from'];
        }
        if (!empty($filters['to'])) {
            $clean['to'] = (int) $filters['to'];
        }
        if (!empty($filters['attemptnumber'])) {
            $clean['attemptnumber'] = (int) $filters['attemptnumber'];
        }

        // A reversed range would silently return nothing and look like a bug in
        // the data rather than a typo in the form.
        if (isset($clean['from'], $clean['to']) && $clean['from'] > $clean['to']) {
            unset($clean['to']);
        }

        return $clean;
    }

    /**
     * List the attempts for an activity, optionally restricted to one group and
     * to a set of filters, sorted and paged.
     *
     * @param int $elangid The activity id
     * @param int $groupid Only attempts by members of this group, or 0 for all
     * @param int $page The zero-based page to return
     * @param int $perpage The page size, or 0 to return every attempt unpaged
     * @param array $filters Filters as accepted by clean_filters()
     * @param string $sort A key of SORT_COLUMNS, or an empty string for the default order
     * @param string $direction ASC or DESC
     * @return array A list of attempt summary arrays
     */
    public function list_for_activity(
        int $elangid,
        int $groupid = 0,
        int $page = 0,
        int $perpage = 0,
        array $filters = [],
        string $sort = '',
        string $direction = 'DESC'
    ): array {
        global $DB;

        [$from, $where, $params] = $this->build_list_query($elangid, $groupid, $filters);

        $usernamefields = \core_user\fields::for_name()->get_sql('u', false, '', '', false)->selects;
        $sql = "SELECT a.*, {$usernamefields} FROM $from WHERE $where ORDER BY " . $this->order_by($sort, $direction);

        $limitfrom = $perpage > 0 ? $page * $perpage : 0;
        $rows = $DB->get_records_sql($sql, $params, $limitfrom, $perpage);

        $summaries = [];
        foreach ($rows as $row) {
            $summaries[] = [
                'attemptid' => (int) $row->id,
                'userid' => (int) $row->userid,
                'attemptnumber' => (int) $row->attemptnumber,
                'state' => $row->state,
                'totalgaps' => (int) $row->totalgaps,
                'answeredgaps' => (int) $row->answeredgaps,
                'correctgaps' => (int) $row->correctgaps,
                'exactgaps' => (int) $row->exactgaps,
                'hintedgaps' => (int) $row->hintedgaps,
                'score' => (float) $row->score,
                'timestart' => (int) $row->timestart,
                'timefinish' => (int) $row->timefinish,
                // The learner name is joined in SQL: sorting by it has to
                // happen in the database anyway, and having it here spares the
                // page a second query over every user on the page.
                'fullname' => fullname($row),
            ];
        }

        return $summaries;
    }

    /**
     * Build the ORDER BY clause from a whitelisted sort key.
     *
     * @param string $sort A key of SORT_COLUMNS, or an empty string
     * @param string $direction ASC or DESC
     * @return string The ORDER BY body
     */
    private function order_by(string $sort, string $direction): string {
        if (!isset(self::SORT_COLUMNS[$sort])) {
            return 'a.timestart DESC, a.id DESC';
        }

        $dir = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $column = self::SORT_COLUMNS[$sort];

        // A stable tiebreak, so paging cannot show the same attempt twice or
        // skip one when several rows share a sort value.
        return str_replace(', ', " $dir, ", $column) . " $dir, a.id DESC";
    }

    /**
     * Count the attempts an activity has, honouring the same group and filters
     * as the listing, so a paged list and its total can never disagree.
     *
     * @param int $elangid The activity id
     * @param int $groupid Only attempts by members of this group, or 0 for all
     * @param array $filters Filters as accepted by clean_filters()
     * @return int The total number of matching attempts
     */
    public function count_for_activity(int $elangid, int $groupid = 0, array $filters = []): int {
        global $DB;

        [$from, $where, $params] = $this->build_list_query($elangid, $groupid, $filters);

        return (int) $DB->count_records_sql("SELECT COUNT(1) FROM $from WHERE $where", $params);
    }

    /**
     * The headline figures for the attempts currently in view.
     *
     * One aggregate query over the same FROM/WHERE as the listing: the numbers
     * describe exactly the set the table is showing, and reading them costs one
     * query rather than one per attempt.
     *
     * @param int $elangid The activity id
     * @param int $groupid Only attempts by members of this group, or 0 for all
     * @param array $filters Filters as accepted by clean_filters()
     * @return array total, finished, averagescore (of finished attempts) and hinted counts
     */
    public function aggregate_for_activity(int $elangid, int $groupid = 0, array $filters = []): array {
        global $DB;

        [$from, $where, $params] = $this->build_list_query($elangid, $groupid, $filters);

        $sql = "SELECT COUNT(1) AS total,
                       SUM(CASE WHEN a.state = 'finished' THEN 1 ELSE 0 END) AS finished,
                       SUM(CASE WHEN a.state = 'finished' THEN a.score ELSE 0 END) AS finishedscore,
                       SUM(CASE WHEN a.hintedgaps > 0 THEN 1 ELSE 0 END) AS hinted
                  FROM $from
                 WHERE $where";

        $row = $DB->get_record_sql($sql, $params);

        $total = (int) ($row->total ?? 0);
        $finished = (int) ($row->finished ?? 0);

        return [
            'total' => $total,
            'finished' => $finished,
            // Averaged over finished attempts only: an attempt still in
            // progress has a score that is simply not comparable yet, and
            // including it would drag the figure down as a matter of timing
            // rather than of performance.
            'averagescore' => $finished > 0 ? ((float) ($row->finishedscore ?? 0)) / $finished : 0.0,
            'hinted' => (int) ($row->hinted ?? 0),
        ];
    }

    /**
     * Build the shared FROM and WHERE for the listing, the count and the
     * aggregate, so all three describe the same set of attempts.
     *
     * @param int $elangid The activity id
     * @param int $groupid Only attempts by members of this group, or 0 for all
     * @param array $filters Filters as accepted by clean_filters()
     * @return array{0: string, 1: string, 2: array} The FROM, the WHERE and their params
     */
    private function build_list_query(int $elangid, int $groupid = 0, array $filters = []): array {
        $params = ['elangid' => $elangid];
        $where = ['a.elangid = :elangid'];

        // Joined unconditionally: the overview sorts and displays by name, and
        // a LEFT JOIN keeps an attempt visible even when its user record has
        // gone.
        $from = "{elang_attempt} a
            LEFT JOIN {user} u ON u.id = a.userid";

        if ($groupid > 0) {
            $from .= "\n                 JOIN {groups_members} gm ON gm.userid = a.userid";
            $where[] = 'gm.groupid = :groupid';
            $params['groupid'] = $groupid;
        }

        $filters = self::clean_filters($filters);

        if (isset($filters['userid'])) {
            $where[] = 'a.userid = :filteruserid';
            $params['filteruserid'] = $filters['userid'];
        }
        if (isset($filters['state'])) {
            $where[] = 'a.state = :filterstate';
            $params['filterstate'] = $filters['state'];
        }
        if (isset($filters['from'])) {
            $where[] = 'a.timestart >= :filterfrom';
            $params['filterfrom'] = $filters['from'];
        }
        if (isset($filters['to'])) {
            $where[] = 'a.timestart <= :filterto';
            $params['filterto'] = $filters['to'];
        }
        if (isset($filters['attemptnumber'])) {
            $where[] = 'a.attemptnumber = :filterattemptnumber';
            $params['filterattemptnumber'] = $filters['attemptnumber'];
        }

        return [$from, implode(' AND ', $where), $params];
    }

    /**
     * The export column headings, keyed by the stable machine name each
     * export_rows() row uses. Kept next to export_rows() so the two never drift
     * apart, and passed straight to \core\dataformat::download_data().
     *
     * @return array<string, string> Column machine name mapped to its localised heading
     */
    public function export_columns(): array {
        return [
            'user' => get_string('report:user', 'mod_elang'),
            'attemptnumber' => get_string('report:attemptnumber', 'mod_elang'),
            'state' => get_string('report:state', 'mod_elang'),
            'score' => get_string('report:score', 'mod_elang'),
            'answered' => get_string('report:answered', 'mod_elang'),
            'correct' => get_string('report:correct', 'mod_elang'),
            'exact' => get_string('report:exact', 'mod_elang'),
            'hinted' => get_string('report:hinted', 'mod_elang'),
            'finished' => get_string('report:finished', 'mod_elang'),
        ];
    }

    /**
     * Flatten an activity's attempt summaries into export rows keyed by the
     * stable column names of export_columns(), streamed through the Moodle
     * Dataformat API (CSV/Excel/ODS/JSON). Rows are yielded one at a time from a
     * recordset, with the learner name joined in SQL, so memory does not grow
     * with the size of the attempt history. The caller is responsible for the
     * mod/elang:exportreports capability check.
     *
     * @param int $elangid The activity id
     * @param int $groupid Only attempts by members of this group, or 0 for all
     * @param array $filters Filters as accepted by clean_filters()
     * @param string $sort A key of SORT_COLUMNS, or an empty string for the default order
     * @param string $direction ASC or DESC
     * @return \Generator<int, array<string, string|int|float>> One associative row per attempt, yielded lazily
     */
    public function export_rows(
        int $elangid,
        int $groupid = 0,
        array $filters = [],
        string $sort = '',
        string $direction = 'DESC'
    ): \Generator {
        global $DB;

        // The same FROM/WHERE as the overview: an export that ignored the
        // filters on screen would quietly hand out more than the teacher was
        // looking at, which in separate-groups mode is a disclosure and
        // everywhere else is a surprise.
        [$from, $where, $params] = $this->build_list_query($elangid, $groupid, $filters);

        // Streamed rather than materialised: the export must not grow in memory
        // with the whole attempt history of a large activity. The user record is
        // joined in SQL so there is no second pass over every user, and the
        // recordset yields one row at a time straight into the Dataformat API.
        $usernamefields = \core_user\fields::for_name()->get_sql('u', false, '', '', false)->selects;

        $sql = "SELECT a.id, a.userid, a.attemptnumber, a.state, a.score, a.answeredgaps,
                       a.correctgaps, a.exactgaps, a.hintedgaps, a.timefinish, {$usernamefields}
                  FROM $from
                 WHERE $where
              ORDER BY " . $this->order_by($sort, $direction);

        $recordset = $DB->get_recordset_sql($sql, $params);
        try {
            foreach ($recordset as $record) {
                yield [
                    'user' => empty($record->id) ? (string) $record->userid : fullname($record),
                    'attemptnumber' => (int) $record->attemptnumber,
                    'state' => $record->state,
                    'score' => format_float((float) $record->score, 5),
                    'answered' => (int) $record->answeredgaps,
                    'correct' => (int) $record->correctgaps,
                    'exact' => (int) $record->exactgaps,
                    'hinted' => (int) $record->hintedgaps,
                    'finished' => $record->timefinish ? userdate((int) $record->timefinish) : '',
                ];
            }
        } finally {
            $recordset->close();
        }
    }

    /**
     * Assert that the current user may act on one specific attempt.
     *
     * This is the object-level authorisation for every per-attempt action
     * (viewing a detail, deleting). Capability checks alone are not enough: in
     * separate-groups mode a teacher may only reach attempts of learners who
     * share one of their groups, so every entry point must funnel through here
     * rather than repeating the check.
     *
     * @param int $attemptid The attempt being acted on.
     * @param int $elangid The activity the attempt must belong to.
     * @param \stdClass|\cm_info $cm The course module of that activity.
     * @param \context $context The module context, for the capability checks.
     * @return \stdClass The attempt record, once access is granted.
     * @throws \moodle_exception If the attempt belongs to another activity or another group.
     */
    public function require_attempt_access(int $attemptid, int $elangid, $cm, \context $context): \stdClass {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', IGNORE_MISSING);
        if ($attempt === false || (int) $attempt->elangid !== $elangid) {
            throw new \moodle_exception('invalidrecord', 'error');
        }

        $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
            $shared = false;
            foreach (array_keys(groups_get_activity_allowed_groups($cm)) as $groupid) {
                if (groups_is_member($groupid, (int) $attempt->userid)) {
                    $shared = true;
                    break;
                }
            }
            if (!$shared) {
                throw new \moodle_exception(
                    'nopermissions',
                    'error',
                    '',
                    get_string('report:heading', 'mod_elang')
                );
            }
        }

        return $attempt;
    }

    /**
     * Assemble the full detail of one attempt: its aggregates and every gap of
     * the attempt's version paired with the learner's response, in cue then gap
     * order.
     *
     * @param int $attemptid The attempt id
     * @return array An array with an 'attempt' summary and an ordered 'gaps' list
     */
    public function detail(int $attemptid): array {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);

        $cues = $DB->get_records('elang_cue', ['versionid' => $attempt->versionid], 'sortorder ASC, id ASC');
        $gaps = [];
        if (!empty($cues)) {
            [$cuein, $cueparams] = $DB->get_in_or_equal(array_keys($cues));
            $gaps = $DB->get_records_select('elang_gap', "cueid $cuein", $cueparams, 'cueid ASC, sortorder ASC, id ASC');
        }

        $responsesbygap = [];
        foreach ($DB->get_records('elang_response', ['attemptid' => $attemptid]) as $response) {
            $responsesbygap[(int) $response->gapid] = $response;
        }

        $gaprows = [];
        foreach ($gaps as $gap) {
            $cue = $cues[$gap->cueid];
            $response = $responsesbygap[(int) $gap->id] ?? null;
            $gaprows[] = [
                'transcript' => (string) $cue->transcript,
                'solution' => (string) $gap->solution,
                'gradingalgorithm' => (string) $gap->gradingalgorithm,
                'responsetext' => $response ? (string) $response->responsetext : '',
                'resultstate' => $response ? (string) $response->resultstate : '',
                'accepted' => $response ? (int) $response->accepted : 0,
                'tries' => $response ? (int) $response->tries : 0,
                'hintlevel' => $response ? (int) $response->hintlevel : 0,
                'score' => $response ? (float) $response->score : 0.0,
            ];
        }

        return [
            'attempt' => [
                'attemptid' => (int) $attempt->id,
                'elangid' => (int) $attempt->elangid,
                'userid' => (int) $attempt->userid,
                'versionid' => (int) $attempt->versionid,
                'state' => $attempt->state,
                'totalgaps' => (int) $attempt->totalgaps,
                'answeredgaps' => (int) $attempt->answeredgaps,
                'correctgaps' => (int) $attempt->correctgaps,
                'exactgaps' => (int) $attempt->exactgaps,
                'hintedgaps' => (int) $attempt->hintedgaps,
                'score' => (float) $attempt->score,
                'timestart' => (int) $attempt->timestart,
                'timefinish' => (int) $attempt->timefinish,
            ],
            'gaps' => $gaprows,
        ];
    }
}
