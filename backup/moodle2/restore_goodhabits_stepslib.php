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
 * Structure step to restore one goodhabits activity.
 *
 * @package   mod_goodhabits
 * @category  backup
 * @copyright 2021 Joe Cape <joe.sc.cape@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_goodhabits_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines structure of path elements to be processed during the restore
     *
     * @return array of {@link restore_path_element}
     */
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');
        $paths[] = new restore_path_element('goodhabits', '/activity/goodhabits');

        if ($userinfo) {
            $path = '/activity/goodhabits/mod_goodhabits_item';
            $paths[] = new restore_path_element('mod_goodhabits_item', $path);
            $path = '/activity/goodhabits/mod_goodhabits_item/mod_goodhabits_entry';
            $paths[] = new restore_path_element('mod_goodhabits_entry', $path);
            $path = '/activity/goodhabits/mod_goodhabits_break';
            $paths[] = new restore_path_element('mod_goodhabits_break', $path);
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process the given restore path element data
     *
     * @param array $data parsed element data
     */
    protected function process_goodhabits($data) {
        global $DB;

        $data = (object)$data;

        $data->course = $this->get_courseid();

        if (empty($data->timecreated)) {
            $data->timecreated = time();
        }

        if (empty($data->timemodified)) {
            $data->timemodified = time();
        }

        // Create the goodhabits instance.
        $newitemid = $DB->insert_record('goodhabits', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process a habit, preserving the link to the Good Habits activity.
     *
     * @param array $data
     * @throws dml_exception
     * @throws restore_step_exception
     */
    protected function process_mod_goodhabits_item($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $parentid = $this->get_new_parentid('goodhabits');

        $data->instanceid = $parentid;

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->addedby = $this->get_mappingid('user', $data->addedby);

        $newitemid = $DB->insert_record('mod_goodhabits_item', $data);
        $this->set_mapping('mod_goodhabits_item', $oldid, $newitemid, true);
    }

    /**
     * Process a habit entry, preserving the link to the habit.
     *
     * @param array $data
     * @throws dml_exception
     * @throws restore_step_exception
     */
    protected function process_mod_goodhabits_entry($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $parentid = $this->get_new_parentid('mod_goodhabits_item');

        $data->habit_id = $parentid;
        $data->userid = $this->get_mappingid('user', $data->userid);

        $data->endofperiod_timestamp = $this->apply_date_offset($data->endofperiod_timestamp);

        $newitemid = $DB->insert_record('mod_goodhabits_entry', $data);
        $this->set_mapping('mod_goodhabits_entry', $oldid, $newitemid, true);
    }

    /**
     * Process a break, preserving the link to the Good Habits activity.
     *
     * @param array $data
     * @throws dml_exception
     * @throws restore_step_exception
     */
    protected function process_mod_goodhabits_break($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;

        $parentid = $this->get_new_parentid('goodhabits');

        $data->instanceid = $parentid;

        $data->createdby = $this->get_mappingid('user', $data->createdby);

        $data->timestart = $this->apply_date_offset($data->timestart);
        $data->timeend = $this->apply_date_offset($data->timeend);

        $newitemid = $DB->insert_record('mod_goodhabits_break', $data);
        $this->set_mapping('mod_goodhabits_break', $oldid, $newitemid, true);
    }

    /**
     * Post-execution actions
     */
    protected function after_execute() {
        $this->add_related_files('mod_goodhabits', 'intro', null);
    }
}
