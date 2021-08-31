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
 * Helper class for methods in view and/or review.
 *
 * Class ViewHelper
 * @package mod_goodhabits
 */
class ViewHelper {

    const REVIEW_OPTION_DISABLE = 'disable';

    const REVIEW_OPTION_NO_OPTING = 'enable';

    const REVIEW_OPTION_OPT_IN = 'enable_opt_in';

    const REVIEW_OPTION_OPT_OUT = 'enable_opt_out';


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

    public static function get_review_options() {
        $vals = array(
            static::REVIEW_OPTION_DISABLE => get_string('review_disable', 'mod_goodhabits'),
            static::REVIEW_OPTION_NO_OPTING => get_string('review_enable_no_opting', 'mod_goodhabits'),
            static::REVIEW_OPTION_OPT_IN => get_string('review_enable_opt_in', 'mod_goodhabits'),
            static::REVIEW_OPTION_OPT_OUT => get_string('review_enable_opt_out', 'mod_goodhabits')
        );
        return $vals;
    }
}