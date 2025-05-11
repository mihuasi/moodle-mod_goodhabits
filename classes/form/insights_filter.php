<?php
namespace mod_goodhabits\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class insights_filter extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $habits = $this->_customdata['habits'];
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
        $mform->addElement('select', 'habits', get_string('select_habits', 'mod_goodhabits'), $habitoptions);
        $mform->getElement('habits')->setMultiple(true);
        $mform->setDefault('habits', $selected);

        // Date range
        $mform->addElement('date_selector', 'start', get_string('startdate', 'mod_goodhabits'));
        $mform->setDefault('start', $start);

        $mform->addElement('date_selector', 'end', get_string('enddate', 'mod_goodhabits'));
        $mform->setDefault('end', $end);

        $mform->addElement('submit', 'submitbutton', get_string('insights', 'mod_goodhabits'));
    }
}
