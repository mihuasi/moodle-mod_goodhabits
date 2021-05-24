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
 * @copyright 2020 Joe Cape
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_goodhabits;

defined('MOODLE_INTERNAL') || die();

class HabitItemsHelper {

    const HABIT_NAME_MAXLENGTH = 24;
    const HABIT_DESC_MAXLENGTH = 62;

    public static function get_habits($instanceid, $publishedonly = false) {
        global $DB, $USER;
        $sql = 'SELECT * FROM {mod_goodhabits_item}
                    WHERE userid = :userid AND instanceid = :instanceid';
        $params = array('userid' => $USER->id, 'instanceid' => $instanceid);
        if ($publishedonly) {
            $sql .= ' AND published = :published';
            $params['published'] = 1;
        }
        $records = $DB->get_records_sql($sql, $params);
        $habits = static::records_to_habit_objects($records);
        return $habits;
    }

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

    public static function records_to_habit_objects($records) {
        $arr = array();
        foreach ($records as $k => $habit) {
            $arr[$k] = new Habit($habit->id);
        }
        return $arr;
    }

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

    public static function edit_habit($data) {
        $id = $data->habitid;
        $habit = new Habit($id);
        $habit->update_from_form($data);
    }

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

    public static function delete_break($breakid) {
        global $DB, $USER;
        $DB->delete_records('mod_goodhabits_break', array('id' => $breakid, 'createdby' => $USER->id));
    }

    public static function get_num_entries($itemid) {
        global $DB, $USER;
        $entries = $DB->get_records('mod_goodhabits_entry', array('habit_id' => $itemid, 'userid' => $USER->id));
        return count($entries);
    }

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

    public static function table_habit_name($habit, $ispersonal, $activityname) {
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

    public static function set_table_head($table) {
        $fromtext = get_string('new_habit_name', 'mod_goodhabits');
        $totext = get_string('new_habit_desc', 'mod_goodhabits');
        $numentriestext = get_string('habit_num_entries', 'mod_goodhabits');
        $actionstext = get_string('actions', 'mod_goodhabits');
        $table->head = array($fromtext, $totext, $numentriestext, $actionstext);
    }

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