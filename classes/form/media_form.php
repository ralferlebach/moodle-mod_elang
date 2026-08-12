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

namespace mod_elang\form;

/**
 * Upload the media (video/audio) and optional poster image for a draft version.
 *
 * A plain Moodle form with two file managers, so uploads use Moodle's built-in
 * file picker. The page prepares the draft file areas from the version's
 * current files and, on submit, hands the draft item ids to
 * version_manager::set_draft_media().
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class media_form extends \moodleform {
    /**
     * Define the form fields.
     *
     * @return void No return value.
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'versionid');
        $mform->setType('versionid', PARAM_INT);

        $mform->addElement(
            'filemanager',
            'mediafiles',
            get_string('editor:media', 'mod_elang'),
            null,
            $this->_customdata['mediaoptions']
        );

        $mform->addElement(
            'filemanager',
            'posterfiles',
            get_string('editor:poster', 'mod_elang'),
            null,
            $this->_customdata['posteroptions']
        );

        $this->add_action_buttons();
    }
}
