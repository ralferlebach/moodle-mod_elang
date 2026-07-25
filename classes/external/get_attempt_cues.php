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

namespace mod_elang\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Return a page of cues, with their gaps, for the version an attempt is
 * pinned to (elang_attempt.versionid) — never the currently published
 * version. See get_attempt_exercise for why the learner read API is bound to
 * the attempt's version rather than the published one.
 *
 * Every returned transcript has gone through
 * \mod_elang\local\domain\transcript_masker::mask() — solution text is
 * never included in the response (Lastenheft P12). Gaps deliberately do NOT
 * include charstart/charlength: the masked transcript's {{gap:<gapkey>}}
 * token already tells the player where to place an input, and returning the
 * original character length would hand out the solution's length as an
 * unrequested, unpenalised "wordlength" hint — exactly the kind of hint
 * elang_gaphint models as something a learner has to deliberately request.
 * Gaps carry only what a player needs to render an input and show
 * client-side algorithm/length UI; the solution and accepted answer
 * variants are never returned.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_attempt_cues extends external_api {
    use attempt_helper;

    /** @var int Hard cap on the page size, regardless of what a caller requests. */
    private const MAX_LIMIT = 200;

    /**
     * Describe the parameters this function accepts.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'attemptid' => new external_value(PARAM_INT, 'Id of the in-progress attempt to read'),
            'offset' => new external_value(PARAM_INT, 'Number of cues to skip, ordered by sortorder', VALUE_DEFAULT, 0),
            'limit' => new external_value(
                PARAM_INT,
                'Maximum number of cues to return (capped at ' . self::MAX_LIMIT . ')',
                VALUE_DEFAULT,
                50
            ),
        ]);
    }

    /**
     * Return one page of cues and their gaps, transcript solution-masked.
     *
     * @param int $attemptid Attempt id
     * @param int $offset Number of cues to skip
     * @param int $limit Maximum number of cues to return
     * @return array See execute_returns()
     */
    public static function execute(int $attemptid, int $offset = 0, int $limit = 50): array {
        global $DB;

        [
            'attemptid' => $attemptid,
            'offset' => $offset,
            'limit' => $limit,
        ] = self::validate_parameters(self::execute_parameters(), [
            'attemptid' => $attemptid,
            'offset' => $offset,
            'limit' => $limit,
        ]);

        if ($offset < 0) {
            throw new \invalid_parameter_exception('offset must not be negative');
        }
        if ($limit < 1 || $limit > self::MAX_LIMIT) {
            throw new \invalid_parameter_exception('limit must be between 1 and ' . self::MAX_LIMIT);
        }

        $attempt = $DB->get_record('elang_attempt', ['id' => $attemptid], '*', MUST_EXIST);
        $context = self::require_attempt_ownership($attempt);
        require_capability('mod/elang:attempt', $context);

        $versionid = (int) $attempt->versionid;

        $totalcues = (int) $DB->count_records('elang_cue', ['versionid' => $versionid]);

        $cuerecords = $DB->get_records(
            'elang_cue',
            ['versionid' => $versionid],
            'sortorder ASC',
            'id, cuekey, sortorder, starttime, endtime, transcript, transcriptformat',
            $offset,
            $limit
        );

        // Load every gap for this page of cues in one query instead of one
        // query per cue — with a page of up to MAX_LIMIT cues, the previous
        // per-cue query pattern meant up to MAX_LIMIT + 1 database round trips
        // for what is otherwise a single, boundedly-sized page of content.
        $gaprecordsbycue = [];
        if (!empty($cuerecords)) {
            [$cueinsql, $cueinparams] = $DB->get_in_or_equal(array_keys($cuerecords));
            $allgaprecords = $DB->get_records_select(
                'elang_gap',
                "cueid $cueinsql",
                $cueinparams,
                'cueid ASC, sortorder ASC',
                'id, cueid, gapkey, sortorder, charstart, charlength, gradingalgorithm, maxlength, linkurl'
            );
            foreach ($allgaprecords as $allgaprecord) {
                $gaprecordsbycue[$allgaprecord->cueid][] = $allgaprecord;
            }
        }

        $cues = [];
        foreach ($cuerecords as $cuerecord) {
            $gaprecords = $gaprecordsbycue[$cuerecord->id] ?? [];

            $gaps = [];
            foreach ($gaprecords as $gaprecord) {
                $gaps[] = [
                    'id' => (int) $gaprecord->id,
                    'gapkey' => $gaprecord->gapkey,
                    'sortorder' => (int) $gaprecord->sortorder,
                    'gradingalgorithm' => $gaprecord->gradingalgorithm,
                    'maxlength' => $gaprecord->maxlength !== null ? (int) $gaprecord->maxlength : 0,
                    'linkurl' => $gaprecord->linkurl ?? '',
                ];
            }

            $cues[] = [
                'id' => (int) $cuerecord->id,
                'cuekey' => $cuerecord->cuekey,
                'sortorder' => (int) $cuerecord->sortorder,
                'starttime' => (int) $cuerecord->starttime,
                'endtime' => (int) $cuerecord->endtime,
                'transcript' => \mod_elang\local\domain\transcript_masker::mask($cuerecord->transcript, $gaprecords),
                'transcriptformat' => (int) $cuerecord->transcriptformat,
                'gaps' => $gaps,
            ];
        }

        return [
            'cues' => $cues,
            'totalcues' => $totalcues,
            'offset' => $offset,
            'limit' => $limit,
        ];
    }

    /**
     * Describe the structure this function returns.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cues' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Cue id'),
                    'cuekey' => new external_value(PARAM_RAW, 'Version-stable cue key'),
                    'sortorder' => new external_value(PARAM_INT, 'Sort order within the version'),
                    'starttime' => new external_value(PARAM_INT, 'Start time in milliseconds'),
                    'endtime' => new external_value(PARAM_INT, 'End time in milliseconds'),
                    'transcript' => new external_value(
                        PARAM_RAW,
                        'Transcript with every gap range replaced by a {{gap:<gapkey>}} token — never contains solution text'
                    ),
                    'transcriptformat' => new external_value(PARAM_INT, 'Moodle text format constant for the transcript'),
                    'gaps' => new external_multiple_structure(
                        new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'Gap id'),
                            'gapkey' => new external_value(PARAM_RAW, 'Version-stable gap key, matches the transcript token'),
                            'sortorder' => new external_value(PARAM_INT, 'Sort order within the cue'),
                            'gradingalgorithm' => new external_value(PARAM_ALPHA, 'exact or wordrecognized'),
                            'maxlength' => new external_value(PARAM_INT, 'Per-gap response length override, or 0 if unset'),
                            'linkurl' => new external_value(
                                PARAM_RAW,
                                'Optional link URL associated with the gap, or empty if unset'
                            ),
                        ])
                    ),
                ])
            ),
            'totalcues' => new external_value(PARAM_INT, 'Total number of cues in the version, independent of pagination'),
            'offset' => new external_value(PARAM_INT, 'The offset that was applied'),
            'limit' => new external_value(PARAM_INT, 'The limit that was applied'),
        ]);
    }
}
