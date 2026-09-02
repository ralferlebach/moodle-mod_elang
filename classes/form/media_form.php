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
            get_string('editor_media', 'mod_elang'),
            null,
            $this->_customdata['mediaoptions']
        );

        $mform->addElement(
            'filemanager',
            'posterfiles',
            get_string('editor_poster', 'mod_elang'),
            null,
            $this->_customdata['posteroptions']
        );

        // One field rather than a provider selector plus a reference field: a
        // teacher pastes whatever is in their address bar, and which provider
        // that is can be worked out from the URL itself.
        $mform->addElement('header', 'othersource', get_string('media_othersource', 'mod_elang'));
        $mform->setExpanded('othersource', false);

        $mform->addElement('text', 'mediaurl', get_string('media_sourceurl', 'mod_elang'), ['size' => 60]);
        $mform->setType('mediaurl', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('mediaurl', 'media_sourceurl', 'mod_elang');

        $mform->addElement(
            'static',
            'providerhint',
            '',
            get_string('media_providerhint', 'mod_elang', $this->_customdata['providers'])
        );

        $this->add_action_buttons();
    }

    /**
     * Reject a source URL that is neither a usable media URL nor a provider we
     * can embed, rather than storing something the player cannot play.
     *
     * @param array $data The submitted values
     * @param array $files The submitted files
     * @return array Field name => error message
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $url = trim((string) ($data['mediaurl'] ?? ''));
        if ($url === '') {
            return $errors;
        }

        // A recognised provider link is fine whatever its shape.
        if (\mod_elang\local\media\provider_registry::detect($url) !== null) {
            return $errors;
        }

        // Otherwise it has to be a plain http(s) URL the browser can load
        // directly.
        if (!preg_match('~^https?://~i', $url)) {
            $errors['mediaurl'] = get_string('error_invalidsourceurl', 'mod_elang');
        }

        return $errors;
    }
}
