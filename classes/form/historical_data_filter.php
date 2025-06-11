<?php
namespace mod_goodhabits\form;

use mod_goodhabits\Helper;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class historical_data_filter extends \moodleform {

    protected function definition() {
        $mform = $this->_form;
        $habits = $this->_customdata['selectable_habits'];
        $selected = $this->_customdata['selected'];
        $start = $this->_customdata['start'];
        $end = $this->_customdata['end'];
        $instanceid = $this->_customdata['instanceid'];
        $subject_id = $this->_customdata['subject_id'];

        // Hidden instance field
        $mform->addElement('hidden', 'instance', $instanceid);
        if ($subject_id) {
            $mform->addElement('hidden', 'subject_id', $subject_id);
        }

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

        $this->add_advanced_options($mform, $habits);

        $this->add_action_buttons();
    }

    protected function add_advanced_options($mform, $habits)
    {
        // Advanced section: Custom graph source checkbox.
        $mform->addElement('header', 'advancedsettingshdr', Helper::get_string('advancedsettings'));
        $mform->setExpanded('advancedsettingshdr', false);

        $mform->addElement(
            'advcheckbox',
            'customgraphsource',
            Helper::get_string('customgraphsource')
        );

        // Prepare options for bar and line data selects.
        $graphoptions = [
            0 => '--' . get_string('hide') . '--'
        ];
        foreach ($habits as $habit) {
            $graphoptions[$habit->id . '_effort'] = $habit->name . ' - ' . Helper::get_string('effort');
            $graphoptions[$habit->id . '_outcome'] = $habit->name . ' - ' . Helper::get_string('outcome');
            $graphoptions[$habit->id . '_difference'] = $habit->name . ' - ' . Helper::get_string('difference');
        }

        $mform->addElement(
            'select',
            'bardata',
            Helper::get_string('bardata'),
            $graphoptions
        );

        // Default: selected habit - Effort.
        $selected = $this->optional_param('habit', 0, PARAM_INT);
        if ($selected) {
            $mform->setDefault('bardata', $selected . '_effort');
        }

        $mform->addElement(
            'select',
            'linedata',
            Helper::get_string('linedata'),
            $graphoptions
        );

        // Disable bar/line selects unless custom graph source is checked.
        $mform->disabledIf('bardata', 'customgraphsource', 'notchecked');
        $mform->disabledIf('linedata', 'customgraphsource', 'notchecked');

        // Disable the main habit select if custom graph source is checked.
        $mform->disabledIf('habit', 'customgraphsource', 'checked');

    }
}
