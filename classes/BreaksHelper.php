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

use mod_goodhabits\calendar\FlexiCalendar;

defined('MOODLE_INTERNAL') || die();

/**
 * Helper class for the Breaks feature.
 *
 * Class BreaksHelper
 * @package mod_goodhabits
 */
class BreaksHelper {

    /**
     * Insert a new break record if one does not exist already.
     *
     * @param object $data
     * @return null
     * @throws \dml_exception
     */
    public static function add_personal_break($data) {
        global $DB, $USER;
        $userid = $USER->id;
        $timestart = $data->fromdate;
        $timeend = $data->todate;
        $instanceid = $data->instance;
        $break = $DB->get_record('mod_goodhabits_break', array(
            'userid' => $userid,
            'timestart' => $timestart,
            'timeend' => $timeend,
            'instanceid' => $instanceid
        ));
        if ($break) {
            return null;
        }
        $break = new \stdClass();
        $break->instanceid = $instanceid;
        $break->userid = $userid;
        $break->createdby = $userid;
        $break->timestart = $timestart;
        $break->timeend = $timeend;
        $break->timecreated = time();
        $break->timemodified = $break->timecreated;
        $DB->insert_record('mod_goodhabits_break', $break);
    }

    /**
     * Gets all breaks that have been set up in the activity.
     *
     * @param $instanceid
     * @return array
     * @throws \dml_exception
     */
    public static function get_all_activity_instance_breaks($instanceid) {
        global $DB;
        $params = array(
            'instanceid' => $instanceid
        );
        $breaks = $DB->get_records('mod_goodhabits_break', $params);
        return $breaks;
    }

    /**
     * Gets a user's breaks.
     *
     * @param int $instanceid
     * @return array
     * @throws \dml_exception
     */
    public static function get_personal_breaks($instanceid, $userid = null) {
        global $DB, $USER;
        $userid = ($userid) ? $userid : $USER->id;
        $params = array(
            'userid' => $userid,
            'instanceid' => $instanceid
        );
        $breaks = $DB->get_records('mod_goodhabits_break', $params, 'timeend DESC');
        return $breaks;
    }

    /**
     * Deletes a break if that is the action specified by the URL var.
     *
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function check_delete_break() {
        global $PAGE;
        $action = optional_param('action', '', PARAM_TEXT);
        if ($action == 'delete') {
            require_sesskey();
            $breakid = required_param('breakid', PARAM_INT);
            static::delete_break($breakid);
            $msg = get_string('break_deleted', 'mod_goodhabits');
            redirect($PAGE->url, $msg);
        }
    }

    /**
     * Deletes a break with the ID provided.
     *
     * @param int $breakid
     * @throws \dml_exception
     */
    public static function delete_break($breakid) {
        global $DB, $USER;
        $DB->delete_records('mod_goodhabits_break', array('id' => $breakid, 'createdby' => $USER->id));
    }

    /**
     * Given a timestamp, returns whether or not this falls within one of the user's breaks.
     *
     * @param int $timestamp
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function is_in_a_break($timestamp, $userid = null) {
        $instanceid = Helper::get_instance_id_from_url();
        $error_margin = Helper::get_timestamp_error_margin();
        $breaks = static::get_personal_breaks($instanceid, $userid);
        foreach ($breaks as $break) {
            $start = $break->timestart - $error_margin;
            $end = $break->timeend + $error_margin;
            if ($timestamp >= $start AND $timestamp <= $end) {
                return true;
            }
        }
        return false;
    }

    public static function process_skip($instanceid, $skip_timestamp, FlexiCalendar $calendar)
    {
        global $OUTPUT;
        $data = new \stdClass();
        $duration = $calendar->get_period_duration();
        $multiplier = $duration - 1;
        $data->fromdate = $skip_timestamp;
        $data->todate = $skip_timestamp + ($multiplier * DAYSECS);
        $data->instance = $instanceid;

        static::add_personal_break($data);

        $break_added = Helper::get_string('notification_skip_added');
        \core\notification::success($break_added);
    }
}
