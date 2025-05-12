<?php
namespace mod_goodhabits\form;

use mod_goodhabits\Helper;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class insights_filter extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $habits = $this->_customdata['selectable_habits'];
        $selected = $this->_customdata['selected'];
        $start = $this->_customdata['start'];
        $end = $this->_customdata['end'];
        $instanceid = $this->_customdata['instanceid'];

        // Hidden instance field
        $mform->addElement('hidden', 'instance', $instanceid);
        $mform->setType('instance', PARAM_INT);

        // Habit checkboxes
        $habitoptions = [];
        foreach ($habits as $habit) {
            $habitoptions[$habit->id] = $habit->name;
        }
        $mform->addElement('select', 'habit', Helper::get_string('select_habit'), $habitoptions);
//        $mform->getElement('habit')->setMultiple(true);
        $mform->setDefault('habit', $selected);

        // Date range
        $mform->addElement('date_selector', 'start', Helper::get_string('startdate'));
        $mform->setDefault('start', $start);

        $mform->addElement('date_selector', 'end', Helper::get_string('enddate'));
        $mform->setDefault('end', $end);

        $mform->addElement('submit', 'submitbutton', Helper::get_string('insights'));
    }
}
