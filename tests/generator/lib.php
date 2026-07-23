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

/**
 * Test data generator for mod_elang.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Generator class for mod_elang activity instances.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_elang_generator extends testing_module_generator {
    /**
     * Create a new elang instance.
     *
     * @param array|stdClass|null $record Instance data
     * @param array|null $options Generator options
     * @return stdClass The created instance record
     */
    public function create_instance($record = null, ?array $options = null) {
        $record = (object) (array) $record;

        if (!isset($record->name)) {
            $record->name = 'Language exercise ' . ($this->instancecount + 1);
        }
        if (!isset($record->intro)) {
            $record->intro = 'Language exercise description';
        }
        if (!isset($record->introformat)) {
            $record->introformat = FORMAT_HTML;
        }

        return parent::create_instance($record, (array) $options);
    }

    /**
     * Create an exercise version.
     *
     * @param array|stdClass|null $record Version data; must include elangid
     * @return stdClass The created elang_version record, including its new id
     */
    public function create_version($record = null) {
        global $DB;

        $record = (object) (array) $record;

        if (empty($record->elangid)) {
            throw new coding_exception('elangid is required to create an elang_version');
        }
        if (!isset($record->versionnumber)) {
            $record->versionnumber = $DB->count_records('elang_version', ['elangid' => $record->elangid]) + 1;
        }
        if (!isset($record->status)) {
            $record->status = 'draft';
        }
        if (!isset($record->contenthash)) {
            $record->contenthash = sha1(random_string(20));
        }
        if (!isset($record->usermodified)) {
            $record->usermodified = 2;
        }
        if (!isset($record->timecreated)) {
            $record->timecreated = time();
        }

        $record->id = $DB->insert_record('elang_version', $record);

        return $record;
    }

    /**
     * Create a transcript cue.
     *
     * @param array|stdClass|null $record Cue data; must include versionid
     * @return stdClass The created elang_cue record, including its new id
     */
    public function create_cue($record = null) {
        global $DB;

        $record = (object) (array) $record;

        if (empty($record->versionid)) {
            throw new coding_exception('versionid is required to create an elang_cue');
        }
        if (!isset($record->cuekey)) {
            $record->cuekey = random_string(40);
        }
        if (!isset($record->sortorder)) {
            $record->sortorder = $DB->count_records('elang_cue', ['versionid' => $record->versionid]) + 1;
        }
        if (!isset($record->starttime)) {
            $record->starttime = 0;
        }
        if (!isset($record->endtime)) {
            $record->endtime = 5000;
        }
        if (!isset($record->transcript)) {
            $record->transcript = 'Sample transcript text.';
        }
        if (!isset($record->transcriptformat)) {
            $record->transcriptformat = FORMAT_PLAIN;
        }

        $record->id = $DB->insert_record('elang_cue', $record);

        return $record;
    }

    /**
     * Create a gap within a cue.
     *
     * @param array|stdClass|null $record Gap data; must include cueid and solution
     * @return stdClass The created elang_gap record, including its new id
     */
    public function create_gap($record = null) {
        global $DB;

        $record = (object) (array) $record;

        if (empty($record->cueid)) {
            throw new coding_exception('cueid is required to create an elang_gap');
        }
        if (!isset($record->solution)) {
            throw new coding_exception('solution is required to create an elang_gap');
        }
        if (!isset($record->gapkey)) {
            $record->gapkey = random_string(40);
        }
        if (!isset($record->sortorder)) {
            $record->sortorder = $DB->count_records('elang_gap', ['cueid' => $record->cueid]) + 1;
        }
        if (!isset($record->charstart)) {
            $record->charstart = 0;
        }
        if (!isset($record->charlength)) {
            $record->charlength = mb_strlen($record->solution, 'UTF-8');
        }
        if (!isset($record->gradingalgorithm)) {
            $record->gradingalgorithm = \mod_elang\local\grading\answer_evaluator::ALGORITHM_EXACT;
        }

        $record->id = $DB->insert_record('elang_gap', $record);

        return $record;
    }

    /**
     * Create an accepted answer variant for a gap.
     *
     * @param array|stdClass|null $record Answer data; must include gapid and answer
     * @return stdClass The created elang_gapanswer record, including its new id
     */
    public function create_gapanswer($record = null) {
        global $DB;

        $record = (object) (array) $record;

        if (empty($record->gapid)) {
            throw new coding_exception('gapid is required to create an elang_gapanswer');
        }
        if (!isset($record->answer)) {
            throw new coding_exception('answer is required to create an elang_gapanswer');
        }
        if (!isset($record->sortorder)) {
            $record->sortorder = $DB->count_records('elang_gapanswer', ['gapid' => $record->gapid]) + 1;
        }
        if (!isset($record->isregex)) {
            $record->isregex = 0;
        }

        $record->id = $DB->insert_record('elang_gapanswer', $record);

        return $record;
    }

    /**
     * Create a graded hint level for a gap.
     *
     * @param array|stdClass|null $record Hint data; must include gapid
     * @return stdClass The created elang_gaphint record, including its new id
     */
    public function create_gaphint($record = null) {
        global $DB;

        $record = (object) (array) $record;

        if (empty($record->gapid)) {
            throw new coding_exception('gapid is required to create an elang_gaphint');
        }
        if (!isset($record->level)) {
            $record->level = $DB->count_records('elang_gaphint', ['gapid' => $record->gapid]) + 1;
        }
        if (!isset($record->hinttype)) {
            $record->hinttype = 'text';
        }
        if (!isset($record->penalty)) {
            $record->penalty = 0;
        }
        if (!isset($record->timecreated)) {
            $record->timecreated = time();
        }

        $record->id = $DB->insert_record('elang_gaphint', $record);

        return $record;
    }
}
