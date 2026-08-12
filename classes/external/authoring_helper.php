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

use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Helpers shared by the authoring-related external functions.
 *
 * Kept as a trait rather than a base class so each external function class
 * can still directly extend \core_external\external_api, which the Moodle
 * external API framework expects. It also holds the cue/gap/answer/hint
 * structure builders, so the read endpoint's return and the save endpoint's
 * parameters share one definition and round-trip cleanly.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait authoring_helper {
    /**
     * Build a version_manager for the authoring operations.
     *
     * @return \mod_elang\local\domain\version_manager
     */
    private static function get_version_manager(): \mod_elang\local\domain\version_manager {
        return new \mod_elang\local\domain\version_manager();
    }

    /**
     * Load a version and authorise the current user to author its activity,
     * confirming the mod/elang:manage capability in the activity context.
     * Every authoring external function begins with this exact
     * load-and-authorise sequence, so it lives here once.
     *
     * @param int $versionid The elang_version id being authored
     * @return array A two-element list: the elang_version record and its \context_module
     */
    private static function require_manage_version(int $versionid): array {
        global $DB;

        $version = $DB->get_record('elang_version', ['id' => $versionid], '*', IGNORE_MISSING);
        if ($version === false) {
            // A friendly message rather than a raw dml_missing_record_exception
            // when a caller (a direct web-service call, not the editor) passes a
            // version id that does not exist.
            throw new \moodle_exception('error:versionnotfound', 'mod_elang');
        }
        $cm = get_coursemodule_from_instance('elang', $version->elangid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/elang:manage', $context);

        return [$version, $context];
    }

    /**
     * Require the higher-privileged mod/elang:useregex capability when, and
     * only when, the incoming content actually stores a regular-expression
     * answer variant. mod/elang:manage lets a teacher author gaps, but a regex
     * variant runs author-supplied PCRE against learner input at grade time, so
     * it is deliberately gated behind a separate capability that the editing
     * teacher archetype does not hold by default. The capability must be
     * enforced here on the server: the React editor normally sends isregex 0,
     * but that is a UI convenience, not authorisation.
     *
     * @param array $cues The cue list, each with nested gaps/answers
     * @param \context $context The activity context to check the capability in
     * @return void
     * @throws \required_capability_exception When a regex variant is present without the capability
     */
    private static function require_useregex_if_needed(array $cues, \context $context): void {
        foreach ($cues as $cue) {
            foreach ($cue['gaps'] ?? [] as $gap) {
                foreach ($gap['answers'] ?? [] as $answer) {
                    if ((int) $answer['isregex'] === 1) {
                        require_capability('mod/elang:useregex', $context);
                        return;
                    }
                }
            }
        }
    }

    /**
     * Describe the structure of one accepted-answer variant.
     *
     * @return external_single_structure
     */
    private static function answer_structure(): external_single_structure {
        return new external_single_structure([
            'sortorder' => new external_value(PARAM_INT, 'Sort order within the gap'),
            'answer' => new external_value(PARAM_RAW, 'Accepted answer variant'),
            'isregex' => new external_value(PARAM_INT, '1 if the variant is a regular expression, otherwise 0'),
        ]);
    }

    /**
     * Describe the structure of one graded hint.
     *
     * @return external_single_structure
     */
    private static function hint_structure(): external_single_structure {
        return new external_single_structure([
            'level' => new external_value(PARAM_INT, 'Hint level, counting up from 1'),
            'hinttype' => new external_value(PARAM_ALPHA, 'Hint type, e.g. text, firstletter, wordlength'),
            'hinttext' => new external_value(PARAM_RAW, 'Hint text shown to the learner', VALUE_DEFAULT, ''),
            'penalty' => new external_value(PARAM_FLOAT, 'Score penalty applied when this hint is revealed'),
        ]);
    }

    /**
     * Describe the structure of one gap, including its solution — this is the
     * authoring view, never sent to a learner.
     *
     * @return external_single_structure
     */
    private static function gap_structure(): external_single_structure {
        return new external_single_structure([
            'gapkey' => new external_value(PARAM_ALPHANUMEXT, 'Version-stable gap key'),
            'sortorder' => new external_value(PARAM_INT, 'Sort order within the cue'),
            'charstart' => new external_value(PARAM_INT, 'Character offset of the gap within the transcript'),
            'charlength' => new external_value(PARAM_INT, 'Character length of the gap'),
            'solution' => new external_value(PARAM_RAW, 'Primary model answer'),
            'gradingalgorithm' => new external_value(PARAM_ALPHA, 'exact or wordrecognized'),
            'maxlength' => new external_value(PARAM_INT, 'Per-gap response length override, 0 for none', VALUE_DEFAULT, 0),
            'linkurl' => new external_value(PARAM_URL, 'Optional supplementary link, empty for none', VALUE_DEFAULT, ''),
            'answers' => new external_multiple_structure(self::answer_structure(), 'Accepted answer variants', VALUE_DEFAULT, []),
            'hints' => new external_multiple_structure(self::hint_structure(), 'Graded hints', VALUE_DEFAULT, []),
        ]);
    }

    /**
     * Describe the structure of one cue with its gaps.
     *
     * @return external_single_structure
     */
    private static function cue_structure(): external_single_structure {
        return new external_single_structure([
            'cuekey' => new external_value(PARAM_ALPHANUMEXT, 'Version-stable cue key'),
            'sortorder' => new external_value(PARAM_INT, 'Sort order within the version'),
            'starttime' => new external_value(PARAM_INT, 'Start time in milliseconds'),
            'endtime' => new external_value(PARAM_INT, 'End time in milliseconds'),
            'transcript' => new external_value(PARAM_RAW, 'Transcript text for the cue'),
            'transcriptformat' => new external_value(PARAM_INT, 'Moodle text format constant for the transcript'),
            'gaps' => new external_multiple_structure(self::gap_structure(), 'Gaps within the cue', VALUE_DEFAULT, []),
        ]);
    }
}
