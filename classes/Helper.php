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
use mod_goodhabits\calendar\FlexiCalendarUnit;
use mod_goodhabits\habit\HabitItemsHelper;

defined('MOODLE_INTERNAL') || die();

/**
 * Helper class for general methods.
 *
 * Class Helper
 * @package mod_goodhabits
 */
class Helper {

    /**
     * @const string - used for showing a basic mobile layout.
     */
    public const LAYOUT_BASIC_MOBILE = 'basic_mobile';

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
        if ($endperiodnumdays <= $days) {
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
            $data .= ' data-lang-' .$id . '="' . get_string($id, $module) . '" ';
        }
        return $data;
    }

    public static function strings_as_data($strings)
    {
        $data = '';
        foreach ($strings as $id => $string) {
            $data .= ' data-lang-' .$id . '="'. $string . '" ';
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

    public static function add_layout_url_param(\moodle_url $url)
    {
        $layout = optional_param('layout', '', PARAM_TEXT);
        $is_basic_mobile = ($layout == static::LAYOUT_BASIC_MOBILE);
        if ($layout == $is_basic_mobile) {
            $url->param('layout', $layout);
        }
    }

    public static function get_string($string, $a = null) {
        return get_string($string, 'mod_goodhabits', $a);
    }

    public static function get_flexi_cal_unit_from_timestamp($time, $periodduration)
    {
        $unit = new FlexiCalendarUnit();
        $unit->setTimestamp($time);
        $unit->set_period_duration($periodduration);
        return $unit;
    }

    public static function get_instance_from_instance_id($instanceid)
    {
        global $DB;
        if (!$goodhabits = $DB->get_record('goodhabits', ['id' => $instanceid])) {
            throw new \moodle_exception('Unable to find goodhabits with id ' . $instanceid);
        }

        return $goodhabits;
    }

    public static function check_to_update_completion_state($course, $cm, $instance, $userid, $rules)
    {
        $any_rule = false;
        foreach ($rules as $rule) {
            if (!empty($instance->$rule)) {
                $any_rule = true;
                break;
            }
        }
        if (!$any_rule) {
            return false;
        }

        $completion = new \completion_info($course);

        if ($completion->is_enabled($cm)) {
            // We need to set to COMPLETION_UNKNOWN, so that individual rules are updated.
            $completion->update_state($cm, COMPLETION_UNKNOWN);
            return true;
        }
        return false;
    }

    public static function get_entries($instanceid, $userid, $limits) {
        global $DB;
        $sql = "SELECT e.*
            FROM {mod_goodhabits_item} i
            INNER JOIN {mod_goodhabits_entry} e ON e.habit_id = i.id 
                AND e.userid = :userid 
                AND e.endofperiod_timestamp >= :lower AND e.endofperiod_timestamp <= :upper
            WHERE i.instanceid = :instanceid";

        $lower = $limits['lower'];
        $upper = $limits['upper'];

        $params = [
            'instanceid' => $instanceid,
            'userid' => $userid,
            'lower' => $lower,
            'upper' => $upper,
        ];

        $recs = $DB->get_records_sql($sql, $params);

        return $recs;
    }

    public static function get_all_entries($instanceid, $userid) {
        global $DB;
        $sql = "SELECT e.*
            FROM {mod_goodhabits_item} i
            INNER JOIN {mod_goodhabits_entry} e ON e.habit_id = i.id 
                AND e.userid = :userid 
            WHERE i.instanceid = :instanceid";

        $params = [
            'instanceid' => $instanceid,
            'userid' => $userid,
        ];

        $recs = $DB->get_records_sql($sql, $params);

        return $recs;
    }

    public static function unit_has_all_complete($instanceid, FlexiCalendarUnit $unit, $userid)
    {
        $habits = HabitItemsHelper::get_all_habits_for_user($instanceid, $userid);
        if (empty($habits)) {
            // Don't count as complete if there are no habits.
            return false;
        }

        $missing = static::get_habits_with_missing_entries($instanceid, $userid, $unit->get_limits());
        return empty($missing);
    }

    /**
     * Finds which habits have not been filled out for a given timestamp (+ user + instance).
     *
     * @param $instanceid
     * @param $userid
     * @param $endofperiod_timestamp
     * @return array
     * @throws \dml_exception
     */
    public static function get_habits_with_missing_entries($instanceid, $userid, $limits)
    {
        global $DB;

        $sql = "SELECT DISTINCT(i.id) 
FROM {mod_goodhabits_item} i
LEFT JOIN {mod_goodhabits_entry} e ON e.habit_id = i.id 
    AND e.userid = :userid 
    AND e.endofperiod_timestamp >= :lower AND e.endofperiod_timestamp <= :upper
WHERE e.id IS NULL 
    AND i.instanceid = :instanceid AND i.published = 1
        AND (i.level = 'activity' OR i.userid = :userid2)";

        $lower = $limits['lower'];
        $upper = $limits['upper'];

        $params = [
            'instanceid' => $instanceid,
            'userid' => $userid,
            'userid2' => $userid,
            'lower' => $lower,
            'upper' => $upper,
        ];
        $recs = $DB->get_fieldset_sql($sql, $params);

        return $recs;
    }

    public static function has_completed_cal_units_crit($instance, $userid)
    {
        $min_cal_units = (int) $instance->completioncalendarunits ?? null;
        $pre_complete = \mod_goodhabits\Helper::get_cal_units_with_all_complete($instance->id, $userid);
        $status = (count($pre_complete) >= $min_cal_units);
        return $status;
    }

    /**
     * Finds how many calendar units (days / weeks / blocks of days) have been completed by a user
     *      in an instance.
     *
     * @param $instanceid
     * @param $userid
     * @return array
     * @throws \dml_exception
     */
    public static function get_cal_units_with_all_complete($instanceid, $userid)
    {
        global $DB;

        $sql = "SELECT e.endofperiod_timestamp, COUNT(DISTINCT e.habit_id) AS num_complete
FROM {mod_goodhabits_entry} e
JOIN {mod_goodhabits_item} i ON e.habit_id = i.id
JOIN {goodhabits} mod_instance ON (i.instanceid = mod_instance.id)
WHERE e.userid = :userid
  AND i.instanceid = :instanceid AND i.published = 1
  AND mod_instance.freq = e.period_duration
  AND NOT EXISTS (
      SELECT 1
      FROM {mod_goodhabits_break} b
      WHERE b.timestart <= e.endofperiod_timestamp 
        AND b.timeend >= e.endofperiod_timestamp
        AND b.userid = :break_userid
  )
GROUP BY e.endofperiod_timestamp
HAVING COUNT(DISTINCT e.habit_id) >= (
    SELECT COUNT(i2.id) 
    FROM {mod_goodhabits_item} i2
    WHERE i2.instanceid = :instanceid2 AND i2.published = 1
      AND (i2.level = 'activity' OR i2.userid = :userid2)
)";
        $params = [
            'instanceid' => $instanceid,
            'instanceid2' => $instanceid,
            'userid' => $userid,
            'break_userid' => $userid,
            'userid2' => $userid,
        ];

        $results = $DB->get_records_sql($sql, $params);

        return $results;
    }

    public static function rm_from_array($array, $val_to_remove)
    {
        $key = array_search($val_to_remove, $array);
        if ($key !== false) {
            unset($array[$key]);
        }
        return $array;
    }

    public static function get_timestamp_error_margin()
    {
        return HOURSECS * 5;
    }

    public static function get_context_from_instance_id($instanceid)
    {
        $moduleinstance = static::get_module_instance($instanceid);
        $course = get_course($moduleinstance->course);
        $cm = get_coursemodule_from_instance('goodhabits', $moduleinstance->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);

        return $context;
    }

    public static function form_warning_text($mform, $text)
    {
        $html = "<div class='form-warning'>$text</div>";
        $mform->addElement('html', $html);
    }

    public static function get_simple_questions($all_complete)
    {
        if (empty($all_complete)) {
            $num_all_complete = 0;
        } else {
            $num_all_complete = count($all_complete);
        }
        $suffix_1 = '_' . $num_all_complete;
        $suffix_2 = $suffix_1;
        $number_questions_in_sequence = 4;
        if ($num_all_complete > $number_questions_in_sequence) {
            $suffix_1 = '_' . rand(0,5);
            $suffix_2 = '_' . rand(0,5);
        }
        return [
            'effort' => static::get_string('simple_view_effort' . $suffix_1),
            'outcome' => static::get_string('simple_view_outcome' . $suffix_2),
        ];

    }

    public static function get_avg($column = 'x_axis_val', $habit, $userid, $instanceid)
    {
        global $DB, $USER;
        if (!$userid) {
            $userid = $USER->id;
        }

        $sql = "SELECT AVG( e.$column )
              FROM {mod_goodhabits_entry} e
              JOIN {mod_goodhabits_item} i ON e.habit_id = i.id
             WHERE e.habit_id = :habitid
               AND e.userid = :userid
               AND i.instanceid = :instanceid";

        $params = [
            'habitid' => $habit->id,
            'userid' => $userid,
            'instanceid' => $instanceid
        ];

        return (float) $DB->get_field_sql($sql, $params) ?: 0.0;
    }

    public static function get_first_entry($habit, $userid, $instanceid, FlexiCalendar $calendar) {
        global $DB, $USER;
        if (!$userid) {
            $userid = $USER->id;
        }
        $periodduration = $calendar->get_period_duration();

        $sql = "SELECT e.*
              FROM {mod_goodhabits_entry} e
              JOIN {mod_goodhabits_item} i ON e.habit_id = i.id
             WHERE e.userid = :userid
               AND e.period_duration = :period_duration
               AND e.habit_id = :habit
               AND i.instanceid = :instanceid
          ORDER BY e.endofperiod_timestamp ASC
             LIMIT 1";

        $params = [
            'userid' => $userid,
            'habit' => $habit->id,
            'period_duration' => $periodduration,
            'instanceid' => $instanceid
        ];

        return $DB->get_record_sql($sql, $params);
    }

}
