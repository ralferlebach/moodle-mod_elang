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
 * Filters for the attempt overview.
 *
 * A GET form, so a filtered view is a URL: it can be bookmarked, reloaded and
 * handed to a colleague, and the paging bar and the sortable column headings
 * only have to carry the same parameters rather than replay a POST.
 *
 * The form only collects; every value is validated again in
 * attempt_report::clean_filters() before it reaches a query.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_filter_form extends \moodleform {
    /**
     * Define the filter fields.
     *
     * @return void No return value.
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Only the learners who actually have an attempt here: offering every
        // enrolled user would make the list long and most of its entries would
        // return nothing.
        $mform->addElement(
            'select',
            'filteruserid',
            get_string('report:filteruser', 'mod_elang'),
            $this->_customdata['users']
        );
        $mform->setType('filteruserid', PARAM_INT);

        $mform->addElement('select', 'filterstate', get_string('report:filterstate', 'mod_elang'), [
            '' => get_string('report:filterany', 'mod_elang'),
            'inprogress' => get_string('report:state_inprogress', 'mod_elang'),
            'finished' => get_string('report:state_finished', 'mod_elang'),
            'abandoned' => get_string('report:state_abandoned', 'mod_elang'),
        ]);
        $mform->setType('filterstate', PARAM_ALPHA);

        $mform->addElement(
            'date_selector',
            'filterfrom',
            get_string('report:filterfrom', 'mod_elang'),
            ['optional' => true]
        );
        $mform->addElement(
            'date_selector',
            'filterto',
            get_string('report:filterto', 'mod_elang'),
            ['optional' => true]
        );

        $mform->addElement('text', 'filterattemptnumber', get_string('report:filterattempt', 'mod_elang'), ['size' => 4]);
        $mform->setType('filterattemptnumber', PARAM_INT);

        $mform->addElement('submit', 'applyfilters', get_string('report:filterapply', 'mod_elang'));
    }

    /**
     * Reject a reversed date range rather than silently returning nothing.
     *
     * An empty table after a typo looks like an activity nobody has attempted;
     * saying so is the difference between a mistake and a mystery.
     *
     * @param array $data The submitted values
     * @param array $files The submitted files
     * @return array Field name => error message
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $from = (int) ($data['filterfrom'] ?? 0);
        $to = (int) ($data['filterto'] ?? 0);
        if ($from > 0 && $to > 0 && $from > $to) {
            $errors['filterto'] = get_string('report:filterrangeerror', 'mod_elang');
        }

        return $errors;
    }
}
