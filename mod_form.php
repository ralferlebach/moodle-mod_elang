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
 * Module instance settings form for mod_elang.
 *
 * General section, the standard grading elements (grade, grade category,
 * grade to pass) and the elang-specific completion rule (see
 * add_completion_rules()). Media, subtitle import and gap settings are
 * added in phase 4 (authoring tool).
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_elang_mod_form extends moodleform_mod {
    /**
     * Define the form elements.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'elanggrading', get_string('gradingheading', 'mod_elang'));

        $mform->addElement('text', 'language', get_string('language', 'mod_elang'), ['size' => 12]);
        $mform->setType('language', PARAM_ALPHANUMEXT);
        $mform->setDefault('language', '');
        $mform->addHelpButton('language', 'language', 'mod_elang');

        $mform->addElement('text', 'jarothreshold', get_string('jarothreshold', 'mod_elang'), ['size' => 6]);
        $mform->setType('jarothreshold', PARAM_FLOAT);
        $mform->setDefault('jarothreshold', 1.0);
        $mform->addHelpButton('jarothreshold', 'jarothreshold', 'mod_elang');

        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Validate the submitted form data.
     *
     * @param array $data The submitted form data
     * @param array $files The submitted files
     * @return array Any validation errors, keyed by form element name
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (isset($data['jarothreshold']) && ((float) $data['jarothreshold'] < 0 || (float) $data['jarothreshold'] > 1)) {
            $errors['jarothreshold'] = get_string('jarothresholdrange', 'mod_elang');
        }

        return $errors;
    }

    /**
     * Add elang's own completion rule control to the form's completion section.
     *
     * The field name carries $this->get_suffix() — required since Moodle
     * 4.3/4.4 (MDL-78516) so that multiple module instances editable on one
     * page (e.g. bulk activity completion) do not collide on field name.
     * Every version in our supported range (4.5 LTS and above) already
     * requires this; there is no older-Moodle branch to fall back to here.
     *
     * @return array The names of the completion-rule form elements added
     */
    public function add_completion_rules() {
        $mform = $this->_form;
        $suffix = $this->get_suffix();

        $mform->addElement(
            'checkbox',
            'completionfinishattempt' . $suffix,
            '',
            get_string('completionfinishattempt', 'mod_elang')
        );

        return ['completionfinishattempt' . $suffix];
    }

    /**
     * Whether elang's own completion rule is enabled, based on submitted form data.
     *
     * @param array $data Input data (not yet validated)
     * @return bool
     */
    public function completion_rule_enabled($data) {
        $suffix = $this->get_suffix();

        return !empty($data['completionfinishattempt' . $suffix]);
    }
}
