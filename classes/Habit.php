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

class Habit {

    public $id;

    private $instancerecord;

    public function __construct($id) {
        $this->id = $id;
        $this->init();
    }

    private function init() {
        global $DB;

        $habitrecord = $DB->get_record('mod_goodhabits_item', array('id' => $this->id), '*', MUST_EXIST);

        $this->instancerecord = $DB->get_record('goodhabits', array('id' => $habitrecord->instanceid));

        foreach ($habitrecord as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function is_global() {
        if (!$this->userid) {
            return true;
        }
        return false;
    }

    public function is_activity_habit() {
        return $this->level == 'activity';
    }

    public function get_entries($userid, $periodduration) {
        global $DB;
        $params = array('habit_id' => $this->id, 'userid' => $userid, 'period_duration' => $periodduration);
        $entries = $DB->get_records('mod_goodhabits_entry', $params);
        $entriesbytime = array();
        foreach ($entries as $entry) {
            $entriesbytime[$entry->endofperiod_timestamp] = $entry;
        }
        return $entriesbytime;
    }

    public function delete() {
        global $DB;
        $this->require_permission_to_edit();
        $DB->delete_records('mod_goodhabits_item', array('id' => $this->id));
        $this->delete_orphans();
    }

    public function delete_user_entries() {
        global $DB, $USER;
        $this->require_permission_to_edit_entries();
        $DB->delete_records('mod_goodhabits_entry', array('habit_id' => $this->id, 'userid' => $USER->id));
    }

    public function update_from_form($data) {
        global $DB;
        $this->require_permission_to_edit_entries();
        $this->name = $data->name;
        $this->description = $data->description;
        $this->published = $data->published;
        $DB->update_record('mod_goodhabits_item', $this);
    }

    public function get_cm() {
        return Helper::get_coursemodule_from_instance($this->instancerecord->id, $this->instancerecord->course);
    }

    public function get_course_id() {
        return $this->instancerecord->course;
    }

    public function get_instance_record() {
        return $this->instancerecord;
    }

    private function delete_orphans() {
        global $DB;
        $DB->delete_records('mod_goodhabits_entry', array('habit_id' => $this->id));
    }

    private function require_permission_to_edit() {
        if (!$this->can_edit()) {
            throw new \moodle_exception('nopermissions');
        }
    }

    private function require_permission_to_edit_entries() {
        if (!$this->can_edit_entries()) {
            throw new \moodle_exception('nopermissions');
        }
    }

    private function can_edit() {
        global $PAGE, $USER;
        $haspermission = true;
        $level = $this->level;
        if (!has_capability('mod/goodhabits:manage_' . $level . '_habits', $PAGE->context)) {
            $haspermission = false;
        }
        if ($this->addedby != $USER->id) {
            $haspermission = false;
        }
        return $haspermission;
    }

    private function can_edit_entries() {
        global $PAGE;
        $haspermission = true;
        if (!has_capability('mod/goodhabits:manage_entries', $PAGE->context)) {
            $haspermission = false;
        }
        return $haspermission;
    }
}