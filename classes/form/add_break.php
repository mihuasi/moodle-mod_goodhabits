<?php

namespace mod_goodhabits;

class add_break extends \moodleform
{

    public function definition()
    {
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