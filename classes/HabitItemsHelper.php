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

defined('MOODLE_INTERNAL') || die();

/**
 * Helper class for managing user habits and activity habits.
 *
 * Class HabitItemsHelper
 * @package mod_goodhabits
 */
class HabitItemsHelper {

    const HABIT_NAME_MAXLENGTH = 24;
    const HABIT_DESC_MAXLENGTH = 62;

    /**
     * Returns the number of entries for a user within an activity.
     *
     * @param int $instanceid - the ID for the activity.
     * @param int $userid
     * @return int
     */
    public static function get_total_num_entries($instanceid, $userid) {
        $habits = static::get_all_habits_for_user($instanceid, $userid);
        $total = 0;
        foreach ($habits as $habit) {
            $total += static::get_num_entries($habit->id, $userid);
        }
        return $total;
    }

    /**
     * Returns an array of Habit objects for a user within an activity.
     *
     * @param int $instanceid
     * @param int $userid
     * @return array
     */
    public static function get_all_habits_for_user($instanceid, $userid) {
        $habits = static::get_activity_habits($instanceid, true);
        $habits += static::get_personal_habits($instanceid, $userid, true);
        return $habits;
    }

    /**
     * Like {@see get_all_habits_for_user} but only personal habits.
     *
     * @param int $instanceid
     * @param int $userid
     * @param bool $publishedonly
     * @return array
     * @throws \dml_exception
     */
    public static function get_personal_habits($instanceid, $userid, $publishedonly = false) {
        global $DB;
        $sql = 'SELECT * FROM {mod_goodhabits_item}
                    WHERE userid = :userid AND instanceid = :instanceid';
        $params = array('userid' => $userid, 'instanceid' => $instanceid);
        if ($publishedonly) {
            $sql .= ' AND published = :published';
            $params['published'] = 1;
        }
        $records = $DB->get_records_sql($sql, $params);
        $habits = static::records_to_habit_objects($records);
        return $habits;
    }

    /**
     * Returns an array of DB records for habits within an activity.
     *
     * @param int $instanceid
     * @return array
     * @throws \dml_exception
     */
    public static function get_all_activity_instance_habits($instanceid) {
        global $DB;
        return $DB->get_records('mod_goodhabits_item', array('instanceid' => $instanceid));
    }

    /**
     * Like {@see get_all_habits_for_user} but only activity habits.
     *
     * @param int $instanceid
     * @param bool $publishedonly
     * @return array
     * @throws \dml_exception
     */
    public static function get_activity_habits($instanceid, $publishedonly = false) {
        global $DB;
        $sql = 'SELECT * FROM {mod_goodhabits_item} WHERE (userid IS NULL OR userid = 0) AND instanceid = :instanceid';
        $params = array('instanceid' => $instanceid);
        if ($publishedonly) {
            $sql .= ' AND published = :published';
            $params['published'] = 1;
        }
        $records = $DB->get_records_sql($sql, $params);
        $habits = static::records_to_habit_objects($records);
        return $habits;
    }

    /**
     * Given an array of DB records corresponding to habits, returns an array of Habit objects.
     *
     * @param array $records
     * @return array
     */
    public static function records_to_habit_objects($records) {
        $arr = array();
        foreach ($records as $k => $habit) {
            $arr[$k] = new Habit($habit->id);
        }
        return $arr;
    }

    /**
     * Creates a new habit.
     *
     * @param \stdClass $data
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     */
    public static function add_habit($data) {
        global $DB, $USER, $PAGE;

        $name = $data->name;
        $desc = $data->description;
        $instanceid = $data->instance;
        $level = $data->level;
        require_capability('mod/goodhabits:manage_' . $level . '_habits', $PAGE->context);

        $params = array('name' => $name, 'userid' => $USER->id, 'instanceid' => $instanceid);
        if ($DB->record_exists('mod_goodhabits_item', $params)) {
            throw new \moodle_exception('Habit already exists with name ' . $name);
        }
        $record = new \stdClass();
        $record->instanceid = $instanceid;
        $record->userid = ($level == 'activity') ? null : $USER->id;
        $record->addedby = $USER->id;
        $record->level = $level;
        $record->published = $data->published;
        $record->name = $data->name;
        $record->description = $desc;
        $record->colour = '';
        $record->timecreated = time();
        $record->timemodified = $record->timecreated;

        $DB->insert_record('mod_goodhabits_item', $record);
    }

    /**
     * Updates a habit based on submitted form.
     *
     * @param \stdClass $data
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function edit_habit($data) {
        $id = $data->habitid;
        $habit = new Habit($id);
        $habit->update_from_form($data);
    }

    /**
     * Processes the edit/add habit form.
     *
     * @param \stdClass $data
     * @param string $action
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     */
    public static function process_form($data, $action) {
        global $PAGE;
        $msglangid = 'habit_added';
        $url = $PAGE->url;
        if ($action == 'edit') {
            static::edit_habit($data);
            $msglangid = 'habit_edited';
            $url->remove_params(array('action', 'habitid'));
        } else {
            static::add_habit($data);
        }
        $msg = get_string($msglangid, 'mod_goodhabits');
        redirect($url, $msg);
    }

    /**
     * Deletes a habit if this is requested in the URL params.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function check_delete_habit() {
        global $PAGE;
        $action = optional_param('action', '', PARAM_TEXT);
        if ($action == 'delete') {
            require_sesskey();
            $habitid = required_param('habitid', PARAM_INT);
            $habit = new Habit($habitid);
            $habit->delete();
            $msg = get_string('habit_deleted', 'mod_goodhabits');
            $url = $PAGE->url;
            $url->remove_params(array('action'));
            redirect($url, $msg);
        }
    }

    /**
     * Deletes a user's habit entries if this is requested in the URL params.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function check_delete_habit_entries() {
        global $PAGE;
        $action = optional_param('action', '', PARAM_TEXT);
        if ($action == 'delete_entries') {
            require_sesskey();
            $habitid = required_param('habitid', PARAM_INT);
            $habit = new Habit($habitid);
            $habit->delete_user_entries();
            $msg = get_string('habit_entries_deleted', 'mod_goodhabits');
            $url = $PAGE->url;
            $url->remove_params(array('action'));
            redirect($url, $msg);
        }
    }

    /**
     * Returns the number of entries for a user/habit.
     *
     * @param int $itemid
     * @param int $userid
     * @return int
     * @throws \dml_exception
     */
    public static function get_num_entries($itemid, $userid) {
        global $DB;
        $entries = $DB->get_records('mod_goodhabits_entry', array('habit_id' => $itemid, 'userid' => $userid));
        return count($entries);
    }

    /**
     * Gets the lang ID to use for the form submit button based on a the action (add/edit...) and level provided.
     *
     * @param string $action
     * @param string $level
     * @return string
     */
    public static function get_form_submit_lang_id($action, $level) {
        $formlangid = 'addhabit_submit_text';
        if ($level == 'activity') {
            $formlangid = 'activity_addhabit_submit_text';
        }
        if ($action == 'edit') {
            $formlangid = 'edithabit_submit_text';
        }
        if ($action == 'edit' AND $level == 'activity') {
            $formlangid = 'activity_edithabit_submit_text';
        }
        return $formlangid;
    }

    /**
     * Returns habit name for use in the 'manage habits' table.
     *
     * @param Habit $habit
     * @param boolean $ispersonal
     * @param string $activityname
     * @return string
     * @throws \coding_exception
     */
    public static function table_habit_name(Habit $habit, $ispersonal, $activityname) {
        $habitname = format_string($habit->name);
        $titles = array();
        $classes = array();
        if ($ispersonal AND $habit->level == 'activity') {
            $habitname = get_string('name_append_is_activity', 'mod_goodhabits', $habitname);
            $titles[] = get_string('habit_name_title_activity', 'mod_goodhabits', $activityname);
            $classes[] = 'activity_habit';
        }
        if (!$habit->published) {
            $titles[] = get_string('habit_not_published_title', 'mod_goodhabits', $habitname);
            $classes[] = 'habit_is_hidden';
        }
        if (!empty($classes) || !empty($titles)) {
            $titlesstr = implode('&#10;', $titles);
            $classes = implode(' ', $classes);
            $habitname = \html_writer::span($habitname, $classes, array('title' => $titlesstr));
        }
        return $habitname;
    }

    /**
     * Sets the table column headers.
     *
     * @param \html_table $table
     * @throws \coding_exception
     */
    public static function set_table_head($table) {
        $fromtext = get_string('new_habit_name', 'mod_goodhabits');
        $totext = get_string('new_habit_desc', 'mod_goodhabits');
        $numentriestext = get_string('habit_num_entries', 'mod_goodhabits');
        $actionstext = get_string('actions', 'mod_goodhabits');
        $table->head = array($fromtext, $totext, $numentriestext, $actionstext);
    }

    /**
     * Returns the actions available for this Habit as an array of links.
     *
     * @param Habit $habit
     * @param boolean $isactivity
     * @param int $level
     * @param int $instanceid
     * @return array
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function table_actions_arr($habit, $isactivity, $level, $instanceid) {
        $actions = array();
        $allowhabitedit = ($habit->level == 'personal') || $isactivity;
        $jsconfirmtxt = get_string('js_confirm_deletehabit', 'mod_goodhabits', $habit->name);
        $jsconfirm = Helper::js_confirm_text($jsconfirmtxt);
        $delparams = array('action' => 'delete', 'habitid' => $habit->id, 'sesskey' => sesskey(), 'instance' => $instanceid);
        $delparams['level'] = $level;
        if ($allowhabitedit) {
            $deleteurl = new \moodle_url('/mod/goodhabits/manage_habits.php', $delparams);
            $deltext = get_string('delete', 'mod_goodhabits');
            $actions[] = \html_writer::link($deleteurl, $deltext, $jsconfirm);

            $editparams = $delparams;
            $editparams['action'] = 'edit';
            $editurl = new \moodle_url('/mod/goodhabits/manage_habits.php', $editparams);
            $edittext = get_string('edit', 'mod_goodhabits');
            $actions[] = \html_writer::link($editurl, $edittext);
        }

        if ($level == 'personal') {
            $delentriesparams = $delparams;
            $delentriesparams['action'] = 'delete_entries';
            $url = new \moodle_url('/mod/goodhabits/manage_habits.php', $delentriesparams);
            $text = get_string('delete_entries', 'mod_goodhabits');
            $jsconfirmtxt = get_string('js_confirm_deletehabitentries', 'mod_goodhabits', $habit->name);
            $jsconfirm = Helper::js_confirm_text($jsconfirmtxt);
            $actions[] = \html_writer::link($url, $text, $jsconfirm);
        }

        return $actions;
    }
}