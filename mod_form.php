<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The main mod_goodhabits configuration form.
 *
 * @package     mod_goodhabits
 * @copyright   2021 Joe Cape <joe.sc.cape@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_goodhabits\Helper;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/goodhabits/classes/Helper.php');

/**
 * Module instance settings form.
 *
 * @package    mod_goodhabits
 * @copyright  2021 Joe Cape <joe.sc.cape@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_goodhabits_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('goodhabitsname', 'mod_goodhabits'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'goodhabitsname', 'mod_goodhabits');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the rest of mod_goodhabits settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.

        $options = Helper::possible_period_durations();
        $text = get_string('freq', 'mod_goodhabits');
        $mform->addElement('select', 'freq', $text, $options);
        $defaultvalue = get_config('goodhabits', 'freq');
        $mform->setDefault('freq', $defaultvalue);
        $mform->addHelpButton('freq', 'freq', 'mod_goodhabits');

        $mform->addElement('header', 'header_reviews', Helper::get_string('review'));

        $site_config = get_config('goodhabits');
        if ($site_config->review == 'disable') {
            $mform->addElement('static', 'reviews_disabled', '', Helper::get_string('feature_disabled'));
        } else {
            $options = \mod_goodhabits\PreferencesManager::get_cm_options();
            $text = Helper::get_string('cm_reviews_admin');
            $mform->addElement('select', 'cm_reviews_admin', $text, $options);

            $default = \mod_goodhabits\PreferencesManager::get_reviews_admin_default();
            $mform->setDefault('cm_reviews_admin', $default);

            $options = \mod_goodhabits\PreferencesManager::get_cm_options();
            $text = Helper::get_string('cm_reviews_peers');
            $mform->addElement('select', 'cm_reviews_peers', $text, $options);

            $default = \mod_goodhabits\PreferencesManager::get_reviews_peers_default();
            $mform->setDefault('cm_reviews_peers', $default);

            $caps = \mod_goodhabits\review\Reviewer::get_admin_required_caps();
            $caps_text = implode(', ', $caps);

            $mform->addHelpButton('cm_reviews_admin', 'cm_reviews_admin', 'mod_goodhabits', '', false, $caps_text);

            $caps = \mod_goodhabits\review\Reviewer::get_peer_required_caps();
            $caps_text = implode(', ', $caps);

            $mform->addHelpButton('cm_reviews_peers', 'cm_reviews_peers', 'mod_goodhabits', '', false, $caps_text);
        }


        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    /**
     * Add custom completion rules.
     *
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules() {
        $mform =& $this->_form;

        $group = array();

        $completionhabits = Helper::get_string('completion_habits');
        $completionentries = Helper::get_string('completionentries');
        $completioncalendar = Helper::get_string('completion_calendar');

        // Completion Habits.
        $group[] =& $mform->createElement(
            'checkbox',
            $this->get_suffixed_name('completionhabitsenabled'),
            '',
            $completionhabits
        );
        $group[] =& $mform->createElement('text', 'completionhabits', '', array('size' => 3));

        // Completion Entries.
        $group[] =& $mform->createElement(
            'checkbox',
            $this->get_suffixed_name('completionentriessenabled'),
            '',
            $completionentries
        );
        $group[] =& $mform->createElement('text', 'completionentries', '', array('size' => 3));

        // Completion Calendar Units.
        $group[] =& $mform->createElement(
            'checkbox',
            $this->get_suffixed_name('completioncalendarenabled'),
            '',
            $completioncalendar
        );
        $group[] =& $mform->createElement('text', 'completioncalendarunits', '', array('size' => 3));

        $mform->setType($this->get_suffixed_name('completionhabits'), PARAM_INT);
        $mform->setType($this->get_suffixed_name('completionentries'), PARAM_INT);
        $mform->setType($this->get_suffixed_name('completioncalendarunits'), PARAM_INT);

        $grouplabel = Helper::get_string('completionentriesgroup');

        $mform->addGroup(
            $group,
            $this->get_suffixed_name('completionentriesgroup'),
            $grouplabel,
            array(' '),
            false
        );
        $mform->disabledIf(
            $this->get_suffixed_name('completionhabits'),
            $this->get_suffixed_name('completionhabitsenabled'),
            'notchecked'
        );
        $mform->disabledIf(
            $this->get_suffixed_name('completionentries'),
            $this->get_suffixed_name('completionentriessenabled'),
            'notchecked'
        );
        $mform->disabledIf(
            $this->get_suffixed_name('completioncalendarunits'),
            $this->get_suffixed_name('completioncalendarenabled'),
            'notchecked'
        );




        return array($this->get_suffixed_name('completionentriesgroup'));
    }

    protected function get_suffixed_name(string $fieldname): string {
        if (!method_exists($this, 'get_suffix')) {
            // For compatibility with Moodle versions pre 4.3.
            return $fieldname;
        }
        return $fieldname . $this->get_suffix();
    }

    /**
     * Called during validation. Indicates if a module-specific completion rule is selected.
     *
     * @param array $data
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data) {
        $completion_entries_enabled = $this->get_suffixed_name('completionentriessenabled');
        $completion_entries = $this->get_suffixed_name('completionentries');
        $completion_entries_condition = ($data[$completion_entries] != 0 AND $completion_entries_enabled);

        $completion_cu_enabled = $this->get_suffixed_name('completioncalendarenabled');
        $completion_calendar_units = $this->get_suffixed_name('completioncalendarunits');
        $completion_calendar_units_condition = ($data[$completion_calendar_units] != 0 AND $completion_cu_enabled);
        return (
            $completion_entries_condition OR $completion_calendar_units_condition);
    }
}
