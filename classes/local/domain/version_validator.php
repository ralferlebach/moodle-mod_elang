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
            $problems[] = get_string('validate:nocues', 'mod_elang');
            return $problems;
        }

        // Load every gap and every hint level for the whole version in one query
        // each, grouped in PHP, so a publish costs three queries regardless of
        // how many cues and gaps it has rather than one per cue and one per gap.
        $gapsbycue = $this->gaps_by_cue(array_keys($cues));
        $gapids = [];
        foreach ($gapsbycue as $gaps) {
            foreach ($gaps as $gap) {
                $gapids[] = (int) $gap->id;
            }
        }
        $levelsbygap = $this->hint_levels_by_gap($gapids);

        $totalgaps = 0;
        foreach ($cues as $cue) {
            $transcriptlength = \core_text::strlen((string) $cue->transcript);
            $gaps = $gapsbycue[(int) $cue->id] ?? [];
            $totalgaps += count($gaps);

            $previousend = null;
            foreach ($gaps as $gap) {
                $charstart = (int) $gap->charstart;
                $charlength = (int) $gap->charlength;
                $where = get_string('validate:where', 'mod_elang', (object) [
                    'gapkey' => $gap->gapkey,
                    'cuekey' => $cue->cuekey,
                ]);

                if (trim((string) $gap->solution) === '') {
                    $problems[] = get_string('validate:emptysolution', 'mod_elang', $where);
                }

                if (!in_array($gap->gradingalgorithm, $knownalgorithms, true)) {
                    $problems[] = get_string('validate:unknownalgorithm', 'mod_elang', (object) [
                        'where' => $where,
                        'algorithm' => $gap->gradingalgorithm,
                    ]);
                }

                if ($charlength <= 0) {
                    $problems[] = get_string('validate:nonpositivelength', 'mod_elang', $where);
                } else if ($charstart < 0 || $charstart + $charlength > $transcriptlength) {
                    $problems[] = get_string('validate:rangeoutside', 'mod_elang', $where);
                } else {
                    if ($previousend !== null && $charstart < $previousend) {
                        // Gaps are ordered by charstart, so an overlap shows up
                        // as this gap starting before the previous one ended.
                        $problems[] = get_string('validate:rangeoverlap', 'mod_elang', $where);
                    }
                    $previousend = $charstart + $charlength;
                }

                $levels = $levelsbygap[(int) $gap->id] ?? [];
                sort($levels);
                foreach ($levels as $index => $level) {
                    if ($level !== $index + 1) {
                        $problems[] = get_string('validate:hintlevels', 'mod_elang', $where);
                        break;
                    }
                }
            }
        }

        if ($totalgaps === 0) {
            $problems[] = get_string('validate:nogaps', 'mod_elang');
        }

        return $problems;
    }

    /**
     * Load every gap of the given cues in one query and group it by cue id, in
     * the same order the per-cue validation expects (charstart, then sortorder,
     * then id).
     *
     * @param int[] $cueids The cue ids to load gaps for
     * @return array<int, \stdClass[]> Gap records keyed by cue id
     */
    private function gaps_by_cue(array $cueids): array {
        global $DB;

        if (empty($cueids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($cueids);
        $gaps = $DB->get_records_select(
            'elang_gap',
            "cueid $insql",
            $params,
            'cueid ASC, charstart ASC, sortorder ASC, id ASC'
        );

        $grouped = [];
        foreach ($gaps as $gap) {
            $grouped[(int) $gap->cueid][] = $gap;
        }

        return $grouped;
    }

    /**
     * Load every hint level of the given gaps in one query and group the levels
     * by gap id, as plain integers.
     *
     * @param int[] $gapids The gap ids to load hint levels for
     * @return array<int, int[]> Hint levels keyed by gap id
     */
    private function hint_levels_by_gap(array $gapids): array {
        global $DB;

        if (empty($gapids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($gapids);
        $hints = $DB->get_records_select('elang_gaphint', "gapid $insql", $params, '', 'id, gapid, level');

        $grouped = [];
        foreach ($hints as $hint) {
            $grouped[(int) $hint->gapid][] = (int) $hint->level;
        }

        return $grouped;
    }
}
