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
use mod_elang\local\authoring\gap_rule_generator;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/elang/classes/external/authoring_helper.php');

/**
 * Generate gap definitions from a rule for the authoring editor.
 *
 * A read-only helper: given the transcript being edited and a rule (a word list
 * or every nth word), it returns the gap spans the editor should create, without
 * saving anything. The editor applies them and persists through save_draft.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generate_rule_gaps extends external_api {

    use authoring_helper;

    /**
     * Describe the parameters.
     *
     * @return external_function_parameters The expected parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'versionid' => new external_value(PARAM_INT, 'Id of the draft version being edited, for authorisation'),
            'transcript' => new external_value(PARAM_RAW, 'The transcript to generate gaps from'),
            'rule' => new external_single_structure([
                'type' => new external_value(PARAM_ALPHA, 'Rule type: "words" or "everynth"'),
                'words' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'A word to blank out'),
                    'Target words for the "words" rule',
                    VALUE_DEFAULT,
                    []
                ),
                'n' => new external_value(PARAM_INT, 'Interval for the "everynth" rule', VALUE_DEFAULT, 1),
                'offset' => new external_value(PARAM_INT, 'Zero-based start index for "everynth"', VALUE_DEFAULT, 0),
                'casesensitive' => new external_value(
                    PARAM_BOOL,
                    'Whether word matching is case sensitive',
                    VALUE_DEFAULT,
                    false
                ),
            ], 'The gap-generation rule'),
        ]);
    }

    /**
     * Generate the gaps.
     *
     * @param int $versionid The draft version id, for authorisation
     * @param string $transcript The transcript to generate gaps from
     * @param array $rule The gap-generation rule
     * @return array The generated gaps
     */
    public static function execute(int $versionid, string $transcript, array $rule): array {
        [
            'versionid' => $versionid,
            'transcript' => $transcript,
            'rule' => $rule,
        ] = self::validate_parameters(self::execute_parameters(), [
            'versionid' => $versionid,
            'transcript' => $transcript,
            'rule' => $rule,
        ]);

        self::require_manage_version($versionid);

        $known = [gap_rule_generator::RULE_WORDS, gap_rule_generator::RULE_EVERY_NTH];
        if (!in_array($rule['type'], $known, true)) {
            throw new \moodle_exception('error:unknowngaprule', 'mod_elang', '', $rule['type']);
        }

        $gaps = [];
        foreach ((new gap_rule_generator())->generate($transcript, $rule) as $span) {
            $gaps[] = [
                'charstart' => $span->charstart,
                'charlength' => $span->charlength,
                'solution' => $span->solution,
            ];
        }

        return ['gaps' => $gaps];
    }

    /**
     * Describe the return value.
     *
     * @return external_single_structure The generated gaps
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'gaps' => new external_multiple_structure(new external_single_structure([
                'charstart' => new external_value(PARAM_INT, 'Codepoint offset of the gap in the transcript'),
                'charlength' => new external_value(PARAM_INT, 'Codepoint length of the gap'),
                'solution' => new external_value(PARAM_RAW, 'The word blanked out'),
            ]), 'The generated gaps, in transcript order'),
        ]);
    }
}
