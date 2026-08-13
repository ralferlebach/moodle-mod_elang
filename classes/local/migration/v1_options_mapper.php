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

namespace mod_elang\local\migration;

/**
 * Maps a V1 activity's decoded options blob onto the V2 grading settings
 * that apply to EVERY gap of that activity.
 *
 * Extracted into its own class purely to avoid v1_detector's dry-run report
 * and v1_migrator's actual write path silently drifting apart on this
 * decision — both call the exact same method rather than each keeping their
 * own copy of the mapping rule.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class v1_options_mapper {
    /**
     * Decide gradingalgorithm and jarothreshold for a V1 activity from its
     * options, per the rule confirmed against the V1 source: V1 applies
     * usecasesensitive/usetransliteration/jaroDistance uniformly to every
     * gap in the activity, OR-combined, so every gap gets the SAME mapped
     * algorithm — there is no per-gap source to honour even in principle.
     *
     * @param array $options Decoded elang.options
     * @return array{0: string, 1: float} [gradingalgorithm, jarothreshold]
     */
    public static function map_grading_algorithm(array $options): array {
        $casesensitive = $options['usecasesensitive'] ?? true;
        $transliteration = $options['usetransliteration'] ?? false;
        $jarodistance = isset($options['jaroDistance']) ? (float) $options['jaroDistance'] : 1.0;

        $lenient = !$casesensitive || $transliteration || $jarodistance < 1.0;

        if (!$lenient) {
            return ['exact', 1.0];
        }

        return ['wordrecognized', $jarodistance];
    }
}
