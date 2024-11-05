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
 * @package   mod_goodhabits
 * @copyright 2021 Joe Cape
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_goodhabits;

class select_user_review extends \moodleform {

    public function definition() {
        global $USER;
        $mform = $this->_form;

        $instanceid = (isset($this->_customdata['instance'])) ? $this->_customdata['instance'] : 0;
        $courseid = (isset($this->_customdata['courseid'])) ? $this->_customdata['courseid'] : 0;

        $options = [
            'ajax' => 'mod_goodhabits/form-user-selector',
            'multiple' => false,
            'noselectionstring' => '',
            'courseid' => $courseid,
            'instanceid' => $instanceid,
            'reviewer_user_id' => $USER->id,
            'valuehtmlcallback' => function($value) {
                global $DB, $OUTPUT;
                $user = $DB->get_record('user', ['id' => (int) $value], '*', IGNORE_MISSING);
                if (!$user) {
                    return false;
                }

                $details = user_get_user_details($user);
                return $OUTPUT->render_from_template(
                    'mod_goodhabits/form_user_selector_suggestion', $details);
            }
        ];

        $name = get_string('select_users', 'mod_goodhabits');
        $mform->addElement('autocomplete', 'userid', $name, [], $options);
        $mform->addElement('hidden', 'instance', $instanceid);
        $mform->setType('instance', PARAM_INT);
        $text = get_string('review_select_submit', 'mod_goodhabits');
        $this->add_action_buttons(null, $text);
    }
}
