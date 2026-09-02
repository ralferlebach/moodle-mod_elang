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
 * add_completion_rules()), plus the exercise language, the answer-comparison
 * threshold and what of the transcript learners may download. Media, subtitle
 * import and gaps are edited in the authoring editor (edit.php), not here.
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
     * @return void No return value.
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'elanggrading', get_string('gradingheading', 'mod_elang'));

        // The offered content languages honour the site's mod_elang/
        // allowedlanguages restriction and always keep this instance's stored
        // value. New instances default to the course language (falling back to
        // the site language, mapped to its base code) when that language is
        // offered; editing keeps the stored value via set_data.
        $current = (string) ($this->current->language ?? '');
        $languageoptions = \mod_elang\local\settings\language_options::form_options($current);
        $mform->addElement('select', 'language', get_string('language', 'mod_elang'), $languageoptions);
        $mform->setType('language', PARAM_ALPHANUMEXT);
        $candidate = \mod_elang\local\settings\language_options::base_code(
            (string) ($this->get_course()->lang ?: ($CFG->lang ?? ''))
        );
        $mform->setDefault('language', isset($languageoptions[$candidate]) ? $candidate : '');
        $mform->addHelpButton('language', 'language', 'mod_elang');

        $mform->addElement('text', 'jarothreshold', get_string('jarothreshold', 'mod_elang'), ['size' => 6]);
        $mform->setType('jarothreshold', PARAM_FLOAT);
        $mform->setDefault('jarothreshold', 1.0);
        $mform->addHelpButton('jarothreshold', 'jarothreshold', 'mod_elang');

        $mform->addElement('header', 'elangplayback', get_string('playbackheading', 'mod_elang'));

        $mform->addElement('select', 'subtitleposition', get_string('subtitleposition', 'mod_elang'), [
            'below' => get_string('subtitleposition_below', 'mod_elang'),
            'overlaybottom' => get_string('subtitleposition_overlaybottom', 'mod_elang'),
            'overlaytop' => get_string('subtitleposition_overlaytop', 'mod_elang'),
        ]);
        $mform->setType('subtitleposition', PARAM_ALPHA);
        $mform->setDefault('subtitleposition', 'below');
        $mform->addHelpButton('subtitleposition', 'subtitleposition', 'mod_elang');

        $mform->addElement('select', 'cuepausemode', get_string('cuepausemode', 'mod_elang'), [
            'auto' => get_string('cuepausemode_auto', 'mod_elang'),
            'stop' => get_string('cuepausemode_stop', 'mod_elang'),
            'nostop' => get_string('cuepausemode_nostop', 'mod_elang'),
        ]);
        $mform->setType('cuepausemode', PARAM_ALPHA);
        $mform->setDefault('cuepausemode', 'auto');
        $mform->addHelpButton('cuepausemode', 'cuepausemode', 'mod_elang');

        // Hidden for the overlay positions: an overlay shows only the cue that
        // is playing, so running on would take the sentence being answered off
        // the screen. playback_settings::resolve() enforces the same thing, so
        // hiding the field does not open a gap between form and behaviour.
        $mform->hideIf('cuepausemode', 'subtitleposition', 'eq', 'overlaybottom');
        $mform->hideIf('cuepausemode', 'subtitleposition', 'eq', 'overlaytop');

        $mform->addElement('static', 'playbackoverlayhint', '', get_string('playbackoverlayhint', 'mod_elang'));
        $mform->hideIf('playbackoverlayhint', 'subtitleposition', 'eq', 'below');

        $mform->addElement('static', 'playbackproviderhint', '', get_string('playbackproviderhint', 'mod_elang'));

        $mform->addElement('header', 'elangtranscript', get_string('transcriptheading', 'mod_elang'));

        // Learners hold mod/elang:exporttranscript by default, so without these
        // two settings every activity would offer its worksheet to everyone.
        // Both start closed; handing out a worksheet or the solutions stays a
        // deliberate decision per activity.
        $mform->addElement(
            'advcheckbox',
            'allowtranscriptdownload',
            get_string('allowtranscriptdownload', 'mod_elang'),
            get_string('allowtranscriptdownload_label', 'mod_elang')
        );
        $mform->setType('allowtranscriptdownload', PARAM_INT);
        $mform->setDefault('allowtranscriptdownload', 0);
        $mform->addHelpButton('allowtranscriptdownload', 'allowtranscriptdownload', 'mod_elang');

        $mform->addElement('select', 'solutionavailability', get_string('solutionavailability', 'mod_elang'), [
            'never' => get_string('solutionavailability_never', 'mod_elang'),
            'aftersubmission' => get_string('solutionavailability_aftersubmission', 'mod_elang'),
            'always' => get_string('solutionavailability_always', 'mod_elang'),
        ]);
        $mform->setType('solutionavailability', PARAM_ALPHA);
        $mform->setDefault('solutionavailability', 'never');
        $mform->addHelpButton('solutionavailability', 'solutionavailability', 'mod_elang');

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

        // The selects only ever offer these values, but a hand-crafted post must
        // not be able to store one the player would not understand.
        $positions = \mod_elang\local\player\playback_settings::positions();
        if (isset($data['subtitleposition']) && !in_array($data['subtitleposition'], $positions, true)) {
            $errors['subtitleposition'] = get_string('error_invalidsubtitleposition', 'mod_elang');
        }

        $pausemodes = \mod_elang\local\player\playback_settings::pausemodes();
        if (isset($data['cuepausemode']) && !in_array($data['cuepausemode'], $pausemodes, true)) {
            $errors['cuepausemode'] = get_string('error_invalidcuepausemode', 'mod_elang');
        }

        // The select only ever offers these three, but a hand-crafted post must
        // not be able to store a value the export page would not understand.
        $allowed = ['never', 'aftersubmission', 'always'];
        if (isset($data['solutionavailability']) && !in_array($data['solutionavailability'], $allowed, true)) {
            $errors['solutionavailability'] = get_string('error_invalidsolutionavailability', 'mod_elang');
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
     * @return bool True when the condition holds, false otherwise.
     */
    public function completion_rule_enabled($data) {
        $suffix = $this->get_suffix();

        return !empty($data['completionfinishattempt' . $suffix]);
    }
}
