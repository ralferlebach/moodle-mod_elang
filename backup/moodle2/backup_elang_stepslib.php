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
 * Backup structure step for mod_elang.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete elang structure for backup, with file and id annotations.
 *
 * The published/draft content (versions, cues, gaps, answers, hints) is always
 * backed up; the learner data (attempts and their responses) only when the
 * backup includes user information. Media and poster files hang off each
 * version by its id, and the activity's forward reference to its current version
 * is carried as a plain value and remapped on restore.
 */
class backup_elang_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define the XML structure of an elang backup.
     *
     * @return backup_nested_element The wrapped activity root element
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $elang = new backup_nested_element('elang', ['id'], [
            'name', 'intro', 'introformat', 'language', 'currentversionid', 'grade',
            'completionfinishattempt', 'jarothreshold', 'options', 'timecreated', 'timemodified',
            'migrationapproveduserid', 'migrationapprovedtime',
        ]);

        $versions = new backup_nested_element('versions');
        $version = new backup_nested_element('version', ['id'], [
            'versionnumber', 'status', 'contenthash', 'usermodified', 'timecreated',
            'mediakind', 'mediaurl', 'mediaprovider', 'mediaproviderref', 'mediamime', 'mediaduration',
            'language', 'jarothreshold', 'revision',
        ]);

        $cues = new backup_nested_element('cues');
        $cue = new backup_nested_element('cue', ['id'], [
            'cuekey', 'sortorder', 'starttime', 'endtime', 'transcript', 'transcriptformat',
        ]);

        $gaps = new backup_nested_element('gaps');
        $gap = new backup_nested_element('gap', ['id'], [
            'gapkey', 'sortorder', 'charstart', 'charlength', 'solution', 'gradingalgorithm',
            'maxlength', 'linkurl',
        ]);

        $answers = new backup_nested_element('answers');
        $answer = new backup_nested_element('answer', ['id'], [
            'sortorder', 'answer', 'isregex',
        ]);

        $hints = new backup_nested_element('hints');
        $hint = new backup_nested_element('hint', ['id'], [
            'level', 'hinttype', 'hinttext', 'penalty', 'timecreated',
        ]);

        $attempts = new backup_nested_element('attempts');
        $attempt = new backup_nested_element('attempt', ['id'], [
            'versionid', 'userid', 'attemptnumber', 'state', 'totalgaps', 'answeredgaps',
            'exactgaps', 'correctgaps', 'hintedgaps', 'score', 'timestart', 'timefinish', 'timemodified',
        ]);

        $responses = new backup_nested_element('responses');
        $response = new backup_nested_element('response', ['id'], [
            'gapid', 'responsetext', 'resultstate', 'accepted', 'tries', 'hintlevel', 'score',
            'timecreated', 'timemodified',
        ]);

        // Build the tree.
        $elang->add_child($versions);
        $versions->add_child($version);
        $version->add_child($cues);
        $cues->add_child($cue);
        $cue->add_child($gaps);
        $gaps->add_child($gap);
        $gap->add_child($answers);
        $answers->add_child($answer);
        $gap->add_child($hints);
        $hints->add_child($hint);

        $elang->add_child($attempts);
        $attempts->add_child($attempt);
        $attempt->add_child($responses);
        $responses->add_child($response);

        // Define sources.
        $elang->set_source_table('elang', ['id' => backup::VAR_ACTIVITYID]);
        $version->set_source_table('elang_version', ['elangid' => backup::VAR_PARENTID], 'id ASC');
        $cue->set_source_table('elang_cue', ['versionid' => backup::VAR_PARENTID], 'id ASC');
        $gap->set_source_table('elang_gap', ['cueid' => backup::VAR_PARENTID], 'id ASC');
        $answer->set_source_table('elang_gapanswer', ['gapid' => backup::VAR_PARENTID], 'id ASC');
        $hint->set_source_table('elang_gaphint', ['gapid' => backup::VAR_PARENTID], 'id ASC');

        if ($userinfo) {
            $attempt->set_source_table('elang_attempt', ['elangid' => backup::VAR_PARENTID], 'id ASC');
            $response->set_source_table('elang_response', ['attemptid' => backup::VAR_PARENTID], 'id ASC');
        }

        // Define id annotations.
        $elang->annotate_ids('user', 'migrationapproveduserid');
        $version->annotate_ids('user', 'usermodified');
        $attempt->annotate_ids('user', 'userid');

        // Define file annotations.
        $elang->annotate_files('mod_elang', 'intro', null); // This file area has no itemid.
        $version->annotate_files('mod_elang', 'media', null); // Itemid is the version id.
        $version->annotate_files('mod_elang', 'poster', null); // Itemid is the version id.

        return $this->prepare_activity_structure($elang);
    }
}
