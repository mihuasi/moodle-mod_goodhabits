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
 * Helper class for general methods.
 *
 * Class Helper
 * @package mod_goodhabits
 */
class Helper {

    /**
     * @var int - used to cache the instance ID and avoid repeated DB calls.
     */
    public static $instanceid;

    /**
     * Returns whether the period duration is valid.
     *
     * @param int $periodduration
     * @return bool
     */
    public static function validate_period_duration($periodduration) {
        $possiblevals = array_keys(static::possible_period_durations());
        return in_array($periodduration, $possiblevals);
    }

    /**
     * Returns an array of all of the allowable period durations.
     *
     * @return array
     * @throws \coding_exception
     */
    public static function possible_period_durations() {
        $vals = array(
            1 => get_string('by_day', 'mod_goodhabits'),
            3 => get_string('x_days', 'mod_goodhabits', 3),
            5 => get_string('x_days', 'mod_goodhabits', 5),
            7 => get_string('by_week', 'mod_goodhabits'),
        );
        return $vals;
    }

    /**
     * Returns the timestamp at the end of the period currently being displayed.
     *
     * @param int $periodduration
     * @param \DateTime $basedate
     * @return int
     */
    public static function get_end_period_timestamp($periodduration, \DateTime $basedate) {
        $timestamp = $basedate->getTimestamp();
        $days = static::unix_days($timestamp);
        $fraction = $days / $periodduration;
        $endperiodnumdays = floor($fraction) * ($periodduration);
        if ($endperiodnumdays < $days) {
            $endperiodnumdays += $periodduration;
        }
        $endperiodtime = static::days_to_time($endperiodnumdays);
        return $endperiodtime;
    }

    /**
     * Same as {@see get_end_period_timestamp} but returns a \DateTime object.
     *
     * @param $periodduration
     * @param \DateTime $basedate
     * @return \DateTime
     */
    public static function get_end_period_date_time($periodduration, \DateTime $basedate) {
        $timestamp = static::get_end_period_timestamp($periodduration, $basedate);
        return static::timestamp_to_date_time($timestamp);
    }

    /**
     * Converts timestamp into days.
     *
     * @param int $timestamp
     * @return int
     */
    private static function unix_days($timestamp) {
        $numdays = $timestamp / 60 / 60 / 24;
        return floor($numdays);
    }

    /**
     * Converts days into timestamp.
     *
     * @param int $days
     * @return int
     */
    public static function days_to_time($days) {
        return $days * 60 * 60 * 24;
    }

    /**
     * Converts timestamp into \DateTime object.
     *
     * @param int $timestamp
     * @return \DateTime
     * @throws \Exception
     */
    public static function timestamp_to_date_time($timestamp) {
        $dt = new \DateTime();
        $dt->setTimestamp($timestamp);
        return $dt;
    }

    /**
     * Returns a new \DateTime, modified by the offset, if provided.
     *
     * @param \DateTime $dt
     * @param null|string $offset
     * @return \DateTime
     */
    public static function new_date_time(\DateTime $dt, $offset = null) {
        $newdt = clone $dt;
        if ($offset) {
            $newdt->modify($offset);
        }
        return $newdt;
    }

    /**
     * Converts a \DateTime object into a MySQL date.
     *
     * @param \DateTime $dt
     * @return string
     */
    public static function date_time_to_mysql(\DateTime $dt) {
        return $dt->format('Y-m-d');
    }

    /**
     * Displays the year corresponding to the first date in the display set.
     *
     * @param array $displayset
     * @return string
     */
    public static function display_year($displayset) {
        $firstunit = reset($displayset);

        if ($firstunit->format('Y') != date('Y')) {
            return $firstunit->format('Y');
        }
        return '';
    }

    /**
     * Gets the period duration, based on the current activity instance.
     *
     * @param \stdClass $instance
     * @return int
     */
    public static function get_period_duration($instance) {
        $default = 1;
        $freq = (int) $instance->freq;
        if (!$freq) {
            $duration = $default;
        } else {
            $duration = $freq;
        }

        return $duration;
    }

    /**
     * Returns a string of data attributes for the lang strings related to the IDs.
     *
     * @param array $ids
     * @param string $module
     * @return string
     * @throws \coding_exception
     */
    public static function lang_string_as_data($ids, $module = 'mod_goodhabits') {
        $data = '';
        foreach ($ids as $id) {
            $data .= ' data-lang-' .$id . '="'. get_string($id, $module).'" ';
        }
        return $data;
    }

    /**
     * Gets the module instance from the ID.
     *
     * @param int $id
     * @return false|\stdClass
     * @throws \dml_exception
     */
    public static function get_module_instance($id) {
        global $DB;
        $moduleinstance = $DB->get_record('goodhabits', array('id' => $id), '*', MUST_EXIST);
        return $moduleinstance;
    }

    /**
     * Returns an array with the JS for a confirm box with the relevant text.
     *
     * @param $actiontext
     * @return array
     * @throws \coding_exception
     */
    public static function js_confirm_text($actiontext) {
        $txt = get_string('js_confirm', 'mod_goodhabits', $actiontext);
        $action = 'return confirm(\''.$txt.'\')';
        return array('onclick' => $action);
    }

    /**
     * Gets the instance ID given the URL vars expected while in view.php. Caches the result from ID
     *      after the first time it is run.
     *
     * @return mixed
     * @throws \coding_exception
     */
    public static function get_instance_id_from_url() {
        // Module instance id.
        $g = optional_param('g', 0, PARAM_INT);
        if ($g) {
            return $g;
        }
        $instanceid = optional_param('instance', 0, PARAM_INT);
        if ($instanceid) {
            return $instanceid;
        }
        if (!empty(static::$instanceid)) {
            return static::$instanceid;
        }
        // Course module ID.
        $id = optional_param('id', 0, PARAM_INT);
        $cm = static::get_coursemodule_from_cm_id($id);
        $instanceid = $cm->instance;
        static::$instanceid = $instanceid;
        return $instanceid;
    }

    /**
     * Wrapper for Moodle method get_coursemodule_from_id().
     *
     * @param int $id
     * @return \stdClass
     * @throws \coding_exception
     */
    public static function get_coursemodule_from_cm_id($id) {
        $cm = get_coursemodule_from_id('goodhabits', $id, 0, false, MUST_EXIST);
        return $cm;
    }

    /**
     * Wrapper for Moodle method get_coursemodule_from_instance().
     *
     * @param $instanceid
     * @param $courseid
     * @return \stdClass
     * @throws \coding_exception
     */
    public static function get_coursemodule_from_instance($instanceid, $courseid) {
        $cm = get_coursemodule_from_instance('goodhabits', $instanceid, $courseid, false, MUST_EXIST);
        return $cm;
    }

}