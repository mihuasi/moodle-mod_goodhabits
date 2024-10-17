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

use mod_goodhabits\habit\HabitItemsHelper;

class add_habit extends \moodleform
{

    public function definition() {
        $mform = $this->_form;
        $instanceid = (isset($this->_customdata['instance'])) ? $this->_customdata['instance'] : 0;
        $level = $this->_customdata['level'];
        $action = $this->_customdata['action'];
        $habitid = $this->_customdata['habitid'];

        $namemax = HabitItemsHelper::HABIT_NAME_MAXLENGTH;
        $descmax = HabitItemsHelper::HABIT_DESC_MAXLENGTH;
        $text = get_string('new_habit_name', 'mod_goodhabits');
        $mform->addElement('text', 'name', $text, array('maxlength' => $namemax));
        $text = get_string('new_habit_desc', 'mod_goodhabits');
        $mform->addElement('textarea', 'description', $text, array('maxlength' => $descmax));

        $options = array(
            0 => get_string('hide'),
            1 => get_string('show')
        );
        $text = get_string('showhide', 'mod_goodhabits');
        $mform->addElement('select', 'published', $text, $options);

        $mform->addElement('hidden', 'instance', $instanceid);
        $mform->addElement('hidden', 'level', $level);
        $mform->addElement('hidden', 'action', $action);
        $mform->setType('instance', PARAM_INT);
        $mform->setType('name', PARAM_TEXT);
        $mform->setType('level', PARAM_TEXT);
        $mform->setType('action', PARAM_TEXT);
        $mform->setType('published', PARAM_INT);
        if ($habitid) {
            $mform->addElement('hidden', 'habitid', $habitid);
            $mform->setType('habitid', PARAM_INT);
        }

        if ($action != 'edit') {
            $mform->setDefault('published', 1);
        }
        $formlangid = HabitItemsHelper::get_form_submit_lang_id($action, $level);
        $text = get_string($formlangid, 'mod_goodhabits');
        $this->add_action_buttons(null, $text);
    }

}
