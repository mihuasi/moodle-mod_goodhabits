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

namespace mod_goodhabits\habit;

use mod_goodhabits\calendar\FlexiCalendar;
use mod_goodhabits\Helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Models a habit within an activity.
 *
 * Class Habit
 * @package mod_goodhabits
 */
class Habit {

    /**
     * @var int - Habit ID.
     */
    public $id;

    /**
     * @var \stdClass - the DB record for the activity the habit belongs to.
     */
    private $instancerecord;

    /**
     * Habit constructor.
     * @param int $id
     * @throws \dml_exception
     */
    public function __construct($id) {
        $this->id = $id;
        $this->init();
    }

    /**
     * Initialises the Habit object.
     *
     * @throws \dml_exception
     */
    private function init() {
        global $DB;

        $habitrecord = $DB->get_record('mod_goodhabits_item', array('id' => $this->id), '*', MUST_EXIST);

        $this->instancerecord = $DB->get_record('goodhabits', array('id' => $habitrecord->instanceid));

        foreach ($habitrecord as $k => $v) {
            $this->{$k} = $v;
        }
    }

    /**
     * Returns whether the Habit is an 'activity habit' (applies to all users in the activity) or personal.
     *
     * @return bool
     */
    public function is_activity_habit() {
        return $this->level == 'activity';
    }

    /**
     * Returns all of the entries for this Habit for a particular user, given their calendar setting.
     *
     * @param int $userid
     * @param FlexiCalendar $calendar
     * @return array
     * @throws \dml_exception
     */
    public function get_entries($userid, FlexiCalendar $calendar) {
        global $DB;
        $periodduration = $calendar->get_period_duration();
        $from = $calendar->get_earliest_limit();
        $to = $calendar->get_latest_limit();

        $sql = "SELECT * FROM {mod_goodhabits_entry}
        WHERE habit_id = :habit_id
          AND userid = :userid AND period_duration = :period_duration
          AND endofperiod_timestamp > :from AND endofperiod_timestamp < :to
        ORDER BY endofperiod_timestamp ASC";

        $params = [
            'habit_id' => $this->id,
            'period_duration' => $periodduration,
            'userid' => $userid,
            'from' => $from,
            'to' => $to
        ];

        $entries = $DB->get_records_sql($sql, $params);

        $entriesbytime = array();
        foreach ($entries as $entry) {
            $entriesbytime[$entry->endofperiod_timestamp] = $entry;
        }
        return $entriesbytime;
    }

    /**
     * Deletes this Habit and associated entries.
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function delete() {
        global $DB;
        $this->require_permission_to_edit();
        $DB->delete_records('mod_goodhabits_item', array('id' => $this->id));
        $this->delete_orphans();
    }

    /**
     * Deletes the current users entries.
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function delete_user_entries() {
        global $DB, $USER;
        $this->require_permission_to_edit_entries();
        $DB->delete_records('mod_goodhabits_entry', array('habit_id' => $this->id, 'userid' => $USER->id));
    }

    /**
     * Updates the DB for this Habit.
     *
     * @param \stdClass $data
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function update_from_form($data) {
        global $DB;
        $this->require_permission_to_edit_entries();
        $this->name = $data->name;
        $this->description = $data->description;
        $this->published = $data->published;
        $DB->update_record('mod_goodhabits_item', $this);
    }

    /**
     * Gets the course module related to this Habit.
     *
     * @return \stdClass
     * @throws \coding_exception
     */
    public function get_cm() {
        return Helper::get_coursemodule_from_instance($this->instancerecord->id, $this->instancerecord->course);
    }

    /**
     * Gets the course ID.
     *
     * @return mixed
     */
    public function get_course_id() {
        return $this->instancerecord->course;
    }

    /**
     * Gets the DB record for the activity this Habit belongs to.
     *
     * @return \stdClass
     */
    public function get_instance_record() {
        return $this->instancerecord;
    }

    /**
     * Deletes the entries for this Habit. Should only be called privately, when deleting the Habit.
     *
     * @throws \dml_exception
     */
    private function delete_orphans() {
        global $DB;
        $DB->delete_records('mod_goodhabits_entry', array('habit_id' => $this->id));
    }

    /**
     * Ensures that {@see can_edit} returns true.
     *
     * @throws \moodle_exception
     */
    private function require_permission_to_edit() {
        if (!$this->can_edit()) {
            throw new \moodle_exception('nopermissions');
        }
    }

    /**
     * Ensures that {@see can_edit_entries} returns true.
     *
     * @throws \moodle_exception
     */
    private function require_permission_to_edit_entries() {
        if (!$this->can_edit_entries()) {
            throw new \moodle_exception('nopermissions');
        }
    }

    /**
     * Returns whether the user has permission to edit this Habit.
     *
     * @return bool
     * @throws \coding_exception
     */
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

    /**
     * Returns whether the user has permission to edit this Habit's entries.
     *
     * @return bool
     * @throws \coding_exception
     */
    private function can_edit_entries() {
        global $PAGE;
        $haspermission = true;
        if (!has_capability('mod/goodhabits:manage_entries', $PAGE->context)) {
            $haspermission = false;
        }
        return $haspermission;
    }
}
