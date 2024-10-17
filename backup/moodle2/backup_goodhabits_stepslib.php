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
 * Structure step to back up one goodhabits activity.
 *
 * @package   mod_goodhabits
 * @category  backup
 * @copyright 2021 Joe Cape <joe.sc.cape@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete Good Habits structure for backup.
 *
 * Class backup_goodhabits_activity_structure_step
 */
class backup_goodhabits_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        // Get know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define the root element describing the goodhabits instance.
        $goodhabits = new backup_nested_element('goodhabits', array('id'), array(
            'course', 'name', 'freq', 'completionentriessenabled', 'completionentries', 'completioncalendarenabled',
            'completioncalendarunits', 'timecreated', 'timemodified', 'intro', 'introformat'));

        $habit = new backup_nested_element('mod_goodhabits_item', array('id'), array(
            'instanceid', 'userid', 'addedby', 'level', 'published', 'name',
            'description', 'colour', 'timecreated', 'timemodified'));

        $entry = new backup_nested_element('mod_goodhabits_entry', array('id'), array(
            'habit_id', 'userid', 'entry_type', 'period_duration',
            'endofperiod_timestamp', 'x_axis_val', 'y_axis_val',
            'timecreated', 'timemodified'));

        $break = new backup_nested_element('mod_goodhabits_break', array('id'), array(
            'userid', 'instanceid', 'createdby', 'timestart', 'timeend', 'timecreated', 'timemodified'));

        if ($userinfo) {
            $habit->add_child($entry);
            $goodhabits->add_child($break);
            $goodhabits->add_child($habit);
        }

        $goodhabits->set_source_table('goodhabits', array('id' => backup::VAR_ACTIVITYID));

        if ($userinfo) {
            $break->set_source_table('mod_goodhabits_break', array('instanceid' => backup::VAR_ACTIVITYID));
            $entry->set_source_table('mod_goodhabits_entry', array('habit_id' => backup::VAR_PARENTID));
            $habit->set_source_table('mod_goodhabits_item', array('instanceid' => backup::VAR_ACTIVITYID));
        }

        // Specify that habits are user data.
        $habit->annotate_ids('user', 'userid');
        $habit->annotate_ids('user', 'addedby');

        $habit->annotate_files('mod_goodhabits', 'intro', null);

        $entry->annotate_ids('user', 'userid');

        $break->annotate_ids('user', 'userid');
        $break->annotate_ids('user', 'createdby');

        return $this->prepare_activity_structure($goodhabits);
    }
}
