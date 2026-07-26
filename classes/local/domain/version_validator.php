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

use mod_elang\local\grading\answer_evaluator;

/**
 * Checks whether an exercise version's content is coherent enough to publish.
 *
 * The authoring layer runs this before publishing a draft so a version that
 * would break the player or grading — a gap pointing outside its transcript,
 * an empty solution, hint levels the reveal API could never step through — is
 * rejected with a list of problems rather than silently shipped. It is a
 * read-only inspection: it never changes any content. V1 migration deliberately
 * does not run it (imperfect legacy data is migrated as-is and reported through
 * v1_verifier instead), which is why version_manager::publish() only validates
 * when asked to.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class version_validator {
    /**
     * Inspect a version and return every problem that would prevent it from
     * being published, as a list of human-readable descriptions. An empty
     * array means the version is publishable.
     *
     * @param int $versionid The elang_version id to inspect
     * @return string[] The problems found, empty when the version is valid
     */
    public function validate(int $versionid): array {
        global $DB;

        $problems = [];
        $knownalgorithms = [answer_evaluator::ALGORITHM_EXACT, answer_evaluator::ALGORITHM_WORDRECOGNIZED];

        $cues = $DB->get_records('elang_cue', ['versionid' => $versionid], 'sortorder ASC, id ASC');
        if (empty($cues)) {
            $problems[] = 'The version has no cues.';
            return $problems;
        }

        $totalgaps = 0;
        foreach ($cues as $cue) {
            $transcriptlength = \core_text::strlen((string) $cue->transcript);
            $gaps = $DB->get_records('elang_gap', ['cueid' => $cue->id], 'charstart ASC, sortorder ASC, id ASC');
            $totalgaps += count($gaps);

            $previousend = null;
            foreach ($gaps as $gap) {
                $charstart = (int) $gap->charstart;
                $charlength = (int) $gap->charlength;
                $where = "gap {$gap->gapkey} in cue {$cue->cuekey}";

                if (trim((string) $gap->solution) === '') {
                    $problems[] = "The solution for {$where} is empty.";
                }

                if (!in_array($gap->gradingalgorithm, $knownalgorithms, true)) {
                    $problems[] = "The grading algorithm '{$gap->gradingalgorithm}' for {$where} is not recognised.";
                }

                if ($charlength <= 0) {
                    $problems[] = "The character length of {$where} must be positive.";
                } else if ($charstart < 0 || $charstart + $charlength > $transcriptlength) {
                    $problems[] = "The character range of {$where} lies outside its transcript.";
                } else {
                    if ($previousend !== null && $charstart < $previousend) {
                        // Gaps are ordered by charstart, so an overlap shows up
                        // as this gap starting before the previous one ended.
                        $problems[] = "The character range of {$where} overlaps another gap.";
                    }
                    $previousend = $charstart + $charlength;
                }

                $levels = array_map('intval', $DB->get_fieldset_select('elang_gaphint', 'level', 'gapid = ?', [$gap->id]));
                sort($levels);
                foreach ($levels as $index => $level) {
                    if ($level !== $index + 1) {
                        $problems[] = "The hint levels for {$where} are not a contiguous sequence starting at 1.";
                        break;
                    }
                }
            }
        }

        if ($totalgaps === 0) {
            $problems[] = 'The version has no gaps to answer.';
        }

        return $problems;
    }
}
