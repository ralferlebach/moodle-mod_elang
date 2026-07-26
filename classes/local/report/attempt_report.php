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
     * List every attempt for an activity, newest first.
     *
     * @param int $elangid The activity id
     * @return array A list of attempt summary arrays
     */
    public function list_for_activity(int $elangid): array {
        global $DB;

        $rows = $DB->get_records('elang_attempt', ['elangid' => $elangid], 'timestart DESC, id DESC');

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
                'timefinish' => (int) $row->timefinish,
            ];
        }

        return $summaries;
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
