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
 * Restore structure step for mod_elang.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one elang activity.
 *
 * Restores the content tree and, when present, the learner attempts and
 * responses, remapping every internal reference: a version's author, an
 * attempt's version and user, a response's gap, and — once all versions exist —
 * the activity's forward reference to its current version.
 */
class restore_elang_activity_structure_step extends restore_activity_structure_step {
    /**
     * Define the paths of the elang backup that this step restores.
     *
     * @return mixed The wrapped activity structure
     */
    protected function define_structure() {
        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('elang', '/activity/elang');
        $paths[] = new restore_path_element('elang_version', '/activity/elang/versions/version');
        $paths[] = new restore_path_element('elang_cue', '/activity/elang/versions/version/cues/cue');
        $paths[] = new restore_path_element('elang_gap', '/activity/elang/versions/version/cues/cue/gaps/gap');
        $paths[] = new restore_path_element(
            'elang_gapanswer',
            '/activity/elang/versions/version/cues/cue/gaps/gap/answers/answer'
        );
        $paths[] = new restore_path_element(
            'elang_gaphint',
            '/activity/elang/versions/version/cues/cue/gaps/gap/hints/hint'
        );

        if ($userinfo) {
            $paths[] = new restore_path_element('elang_attempt', '/activity/elang/attempts/attempt');
            $paths[] = new restore_path_element(
                'elang_response',
                '/activity/elang/attempts/attempt/responses/response'
            );
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restore the elang activity record.
     *
     * currentversionid is left pointing at the old id here and remapped in
     * after_execute, once the versions it refers to have been recreated.
     *
     * @param array $data The elang record from the backup
     * @return void
     */
    protected function process_elang($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->get_courseid();
        if (!empty($data->migrationapproveduserid)) {
            $data->migrationapproveduserid = $this->get_mappingid('user', $data->migrationapproveduserid)
                ?: $data->migrationapproveduserid;
        }

        $newitemid = $DB->insert_record('elang', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Restore one version and record its id mapping (with files).
     *
     * @param array $data The version record from the backup
     * @return void
     */
    protected function process_elang_version($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->elangid = $this->get_new_parentid('elang');
        if (!empty($data->usermodified)) {
            $data->usermodified = $this->get_mappingid('user', $data->usermodified) ?: $data->usermodified;
        }

        $newitemid = $DB->insert_record('elang_version', $data);
        // The true flag records that this element owns files (media, poster).
        $this->set_mapping('elang_version', $oldid, $newitemid, true);
    }

    /**
     * Restore one cue.
     *
     * @param array $data The cue record from the backup
     * @return void
     */
    protected function process_elang_cue($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->versionid = $this->get_new_parentid('elang_version');

        $newitemid = $DB->insert_record('elang_cue', $data);
        $this->set_mapping('elang_cue', $oldid, $newitemid);
    }

    /**
     * Restore one gap and record its id mapping, so responses can find it.
     *
     * @param array $data The gap record from the backup
     * @return void
     */
    protected function process_elang_gap($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->cueid = $this->get_new_parentid('elang_cue');

        $newitemid = $DB->insert_record('elang_gap', $data);
        $this->set_mapping('elang_gap', $oldid, $newitemid);
    }

    /**
     * Restore one accepted-answer variant.
     *
     * @param array $data The answer record from the backup
     * @return void
     */
    protected function process_elang_gapanswer($data) {
        global $DB;

        $data = (object) $data;
        $data->gapid = $this->get_new_parentid('elang_gap');

        $DB->insert_record('elang_gapanswer', $data);
    }

    /**
     * Restore one hint.
     *
     * @param array $data The hint record from the backup
     * @return void
     */
    protected function process_elang_gaphint($data) {
        global $DB;

        $data = (object) $data;
        $data->gapid = $this->get_new_parentid('elang_gap');

        $DB->insert_record('elang_gaphint', $data);
    }

    /**
     * Restore one learner attempt, remapping its version and user.
     *
     * @param array $data The attempt record from the backup
     * @return void
     */
    protected function process_elang_attempt($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->elangid = $this->get_new_parentid('elang');
        $data->versionid = $this->get_mappingid('elang_version', $data->versionid);
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('elang_attempt', $data);
        $this->set_mapping('elang_attempt', $oldid, $newitemid);
    }

    /**
     * Restore one response, remapping the gap it answered.
     *
     * @param array $data The response record from the backup
     * @return void
     */
    protected function process_elang_response($data) {
        global $DB;

        $data = (object) $data;
        $data->attemptid = $this->get_new_parentid('elang_attempt');
        $data->gapid = $this->get_mappingid('elang_gap', $data->gapid);

        $DB->insert_record('elang_response', $data);
    }

    /**
     * Reattach files and remap the activity's current-version pointer once every
     * version has been recreated.
     *
     * @return void
     */
    protected function after_execute() {
        global $DB;

        $this->add_related_files('mod_elang', 'intro', null);
        $this->add_related_files('mod_elang', 'media', 'elang_version');
        $this->add_related_files('mod_elang', 'poster', 'elang_version');

        $elangid = $this->task->get_activityid();
        $currentversionid = $DB->get_field('elang', 'currentversionid', ['id' => $elangid]);
        if (!empty($currentversionid)) {
            $mapped = $this->get_mappingid('elang_version', $currentversionid);
            if ($mapped) {
                $DB->set_field('elang', 'currentversionid', $mapped, ['id' => $elangid]);
            }
        }
    }
}
