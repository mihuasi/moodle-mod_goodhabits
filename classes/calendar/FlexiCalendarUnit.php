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

namespace mod_goodhabits\calendar;

use mod_goodhabits\BreaksHelper;
use mod_goodhabits\Helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Handles a single date and the rendering of this within the calendar.
 *
 * Class FlexiCalendarUnit
 * @package mod_goodhabits
 */
class FlexiCalendarUnit extends \DateTime {

    /**
     * @var int - The number of days between each Habit Entry (similar to FlexiCalendar->$periodduration).
     */
    private $periodduration;

    /**
     * Validates and sets the period duration (similar to FlexiCalendar->set_period_duration()).
     *
     * @param int $periodduration
     * @throws \moodle_exception
     */
    public function set_period_duration($periodduration) {
        if (!Helper::validate_period_duration($periodduration)) {
            throw new \moodle_exception('err');
        }
        $this->periodduration = $periodduration;
    }

    /**
     * Returns an array of what to display in the top line and the bottom line of a calendar unit.
     *
     * @return array
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function display_unit() {
        if (empty($this->periodduration)) {
            throw new \moodle_exception('must set periodDuration first');
        }
        $offset = $this->periodduration - 1;
        $toplinedatatime = Helper::new_date_time($this, '-' . $offset . ' day');
        $topline = $toplinedatatime->format('d/m') . ' - ';
        $bottomline = $this->format('d/m');
        switch ($this->periodduration) {
            case 1:
                $topline = $this->get_moodle_user_date("%a");
                $bottomline = $this->format('d');
                break;
            case 7:
                $topline = get_string('week_displayunit', 'mod_goodhabits');
                $bottomline = $this->format('W');
                break;
        }
        $display = array(
            'topLine' => $topline,
            'bottomLine' => $bottomline,
        );
        return $display;
    }

    public function get_moodle_user_date($format)
    {
        $timestamp = $this->getTimestamp();
        $date = userdate($timestamp, $format);

        return $date;
    }

    public function display_unit_inline()
    {
        $displayunit = $this->display_unit();
        return $displayunit['topLine'] . ' ' . $displayunit['bottomLine'];
    }

    public function skip_url($instanceid, $to_date)
    {
        $params = array('toDate' => $to_date, 'g' => $instanceid, 'skip' => $this->getTimestamp());
        $url = new \moodle_url('/mod/goodhabits/view.php', $params);
        Helper::add_layout_url_param($url);

        return $url->out();
    }

    public function answer_questions_url($instanceid)
    {
        $params = array('g' => $instanceid, 'timestamp' => $this->getTimestamp());
        $url = new \moodle_url('/mod/goodhabits/simple.php', $params);
        Helper::add_layout_url_param($url);

        return $url->out();
    }

    /**
     * Returns a string of the current month if this Calendar Unit occurs in a new month.
     *
     * @return string
     */
    public function display_month() {
        $offset = $this->periodduration;
        $previousdatetime = Helper::new_date_time($this, '-' . $offset . ' day');
        $previousmonth = $previousdatetime->format('M');
        $currentmonth = $this->format('M');
        if ($previousmonth != $currentmonth) {
            return $this->get_moodle_user_date("%b");
        }
        return '';
    }

    /**
     * Returns an array of CSS classes to apply to this calendar unit.
     *
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_classes($userid = null) {
        $month = $this->format('F');
        $month = strtolower($month);
        $timestamp = $this->getTimestamp();
        $isinbreak = BreaksHelper::is_in_a_break($timestamp, $userid);
        $classes = array($month, 'time-unit-' . $timestamp);
        if ($isinbreak) {
            $classes[] = 'is-in-break';
        }
        return $classes;
    }

    public function get_limits()
    {
        $timestamp = $this->getTimestamp();
        $error_margin = Helper::get_timestamp_error_margin();

        return [
            'lower' => $timestamp - $error_margin,
            'upper' => $timestamp + $error_margin,
        ];
    }

    public function get_closest_entry($entries)
    {
        $limits = $this->get_limits();
        $lower = $limits['lower'];
        $upper = $limits['upper'];

        foreach ($entries as $entry_time => $val) {
            if ($entry_time >= $lower AND $entry_time <= $upper) {
                return $val;
            }
        }
        return null;
    }
}
