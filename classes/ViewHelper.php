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
 * Helper class for methods in view and/or review.
 *
 * Class ViewHelper
 * @package mod_goodhabits
 */
class ViewHelper {

    const REVIEW_OPTION_DISABLE = 'disable';

    const REVIEW_OPTION_NO_OPTING = 'enable';

    /**
     * Gets the current date up to which the calendar will display (either today or supplied by the URL).
     *
     * @return \DateTime
     * @throws \coding_exception
     */
    public static function get_current_date() {
        $todate = optional_param('toDate', null, PARAM_TEXT);
        if ($todate) {
            $currentdate = new \DateTime($todate);
        } else {
            $currentdate = new \DateTime();
        }
        return $currentdate;
    }

    /**
     * Gets an instance of the FlexiCalendar class.
     *
     * @param $moduleinstance
     * @param null $userid
     * @return FlexiCalendar
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function get_flexi_calendar($moduleinstance, $userid = null) {
        $periodduration = Helper::get_period_duration($moduleinstance);
        $numentries = FlexiCalendar::DEFAULT_NUM_ENTRIES;

        $currentdate = static::get_current_date();

        $basedate = Helper::get_end_period_date_time($periodduration, $currentdate);

        if ($userid) {
            $area = FlexiCalendar::PLUGIN_AREA_REVIEW;
        } else {
            $area = FlexiCalendar::PLUGIN_AREA_VIEW;
        }

        $calendar = new FlexiCalendar($periodduration, $basedate, $numentries, $area, $userid);

        return $calendar;
    }

    /**
     * Gets fullname given user ID.
     *
     * @param $userid
     * @return string
     * @throws \dml_exception
     */
    public static function get_name($userid) {
        global $DB;
        $user = $DB->get_record('user', array('id' => $userid));
        return fullname($user);
    }

    public static function get_access_review_as_string_id($instanceid, $userid)
    {
        $accessing_as = PreferencesManager::access_review_feature_as($instanceid, $userid);
        $access_as_string_id = 'access_review_entries_as_admin';
        if ($accessing_as == PreferencesManager::ACCESS_AS_PEER) {
            $access_as_string_id = 'access_review_entries_as_peer';
        }
        return $access_as_string_id;
    }

    /**
     * Generates HTML for the review intro.
     *
     * @param $fullname
     * @throws coding_exception
     */
    public static function print_review_intro($fullname, $accessing_as_string_id, $out = true) {
        $accessing_as_text = Helper::get_string($accessing_as_string_id);
        $accessing_as = \html_writer::div($accessing_as_text, 'accessing-as');
        $string = get_string('review_entries_name', 'mod_goodhabits', $fullname);
        $string = \html_writer::div($string, 'intro-name', array('id' => 'intro-name'));
        $string .= $accessing_as;
        $output = \html_writer::div($string, 'intro');
        echo $output;
//        if ($out) {
//            echo $output;
//        } else {
//            return $output;
//        }
    }

    /**
     * Returns an array of all of the allowable review options.
     *
     * @return array
     * @throws \coding_exception
     */
    public static function get_review_options() {
        $vals = array(
            static::REVIEW_OPTION_DISABLE => get_string('review_disable', 'mod_goodhabits'),
            static::REVIEW_OPTION_NO_OPTING => get_string('review_enable_no_opting', 'mod_goodhabits')
        );
        return $vals;
    }
}
