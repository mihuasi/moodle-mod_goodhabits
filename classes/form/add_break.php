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

class add_break extends \moodleform
{

    public function definition() {
        $mform = $this->_form;
        $instanceid = (isset($this->_customdata['instance'])) ? $this->_customdata['instance'] : 0;
        $text = get_string('fromdate_text', 'mod_goodhabits');
        $mform->addElement('date_selector', 'fromdate', $text);
        $text = get_string('todate_text', 'mod_goodhabits');
        $mform->addElement('date_selector', 'todate', $text);
        $text = get_string('addbreak_submit_text', 'mod_goodhabits');
        $mform->addElement('hidden', 'instance', $instanceid);
        $mform->setType('instance', PARAM_INT);
        $this->add_action_buttons(null, $text);
    }

}
