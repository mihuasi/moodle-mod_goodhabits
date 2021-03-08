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

class BreaksHelper {

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

    public static function get_personal_breaks($instanceid) {
        global $DB, $USER;
        $userid = $USER->id;
        $params = array(
            'userid' => $userid,
            'instanceid' => $instanceid
        );
        $breaks = $DB->get_records('mod_goodhabits_break', $params);
        return $breaks;
    }

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

    public static function delete_break($breakid) {
        global $DB, $USER;
        $DB->delete_records('mod_goodhabits_break', array('id' => $breakid, 'createdby' => $USER->id));
    }

    public static function is_in_a_break($timestamp) {
        $instanceid = IndexHelper::get_instance_id_from_url();
        $breaks = static::get_personal_breaks($instanceid);
        foreach ($breaks as $break) {
            $start = $break->timestart;
            $end = $break->timeend;
            if ($timestamp >= $start AND $timestamp <= $end) {
                return true;
            }
        }
        return false;
    }
}