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

use mod_goodhabits\Helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Controls the current time period and dates within this that relate to Habit Entries.
 *
 * Class FlexiCalendar
 * @package mod_goodhabits
 */
class FlexiCalendar {

    /**
     * @var int - The number of days between each Habit Entry.
     */
    private $periodduration;

    /**
     * @var \DateTime - The date we are starting from when defining our set of dates.
     */
    private $basedate;

    /**
     * @var int - The number of Habit Entry dates.
     */
    private $numentries;

    /**
     * @var FlexiCalendarUnit[] - An array of FlexiCalendarUnits. One for every Habit Entry date.
     */
    private $displayset;

    /**
     * @var string - The area of the plugin this is being used in.
     */
    private $pluginarea;

    /**
     * @var int - User id of the user this calendar is being generated for.
     */
    private $userid;

    const DEFAULT_NUM_ENTRIES = 8;

    const PLUGIN_AREA_VIEW = 'view';

    const PLUGIN_AREA_REVIEW = 'review';

    const STRING_TYPE_SINGULAR = 'singular';
    const STRING_TYPE_PLURAL = 'plural';
    const STRING_TYPE_DEFINITE_ARTICLE = 'def_article';
    const STRING_TYPE_ANSWER_LATEST = 'answer_latest';
    const STRING_TYPE_CHOSEN = 'chosen';
    const STRING_TYPE_SKIPPED = 'skipped';
    const STRING_TYPE_SKIP_HELP = 'skip_help';
    const STRING_TYPE_GRID_OPEN_HELP = 'grid_open_help';

    /**
     * FlexiCalendar constructor.
     * @param int $periodduration
     * @param \DateTime $basedate
     * @param $numentries
     * @param string $pluginarea
     * @throws \moodle_exception
     */
    public function __construct($periodduration, \DateTime $basedate, $numentries, $pluginarea, $userid = null) {
        global $USER;
        $this->init_period_duration($periodduration);
        $this->basedate = $basedate;
        $this->numentries = $numentries;
        $this->pluginarea = $pluginarea;
        $this->userid = ($userid) ? $userid : $USER->id;
        $this->generate_display_set();
    }

    public function add_body_classes()
    {
        global $PAGE;
        $PAGE->add_body_class('period-duration-' . $this->periodduration);
        if ($this->periodduration !== 1 AND $this->periodduration !== 7) {
            $PAGE->add_body_class('period-duration-multi-day');
        }
    }

    /**
     * @return FlexiCalendarUnit[]
     */
    public function get_display_set() {
        return $this->displayset;
    }

    /**
     * Returns the earliest item from the set.
     *
     * @return FlexiCalendarUnit
     */
    public function get_earliest()
    {
        $earliest = reset($this->displayset);
        return $earliest;
    }

    /**
     * Returns the lower limit for the display set (timestamp).
     *
     * @return int
     */
    public function get_earliest_limit()
    {
        $earliest = $this->get_earliest();
        $earliest_limits = $earliest->get_limits();
        return $earliest_limits['lower'];
    }

    /**
     * Returns the latest item from the set.
     *
     * @return FlexiCalendarUnit
     */
    public function get_latest()
    {
        $reverse = array_reverse($this->displayset);
        $latest = reset($reverse);

        return $latest;
    }

    /**
     * Returns the lower limit for the display set (timestamp).
     *
     * @return int
     */
    public function get_latest_limit()
    {
        $latest = $this->get_latest();
        $latest_limits = $latest->get_limits();
        return $latest_limits['upper'];
    }

    /**
     * Returns the latest item from the set that is at least mostly in the past.
     *
     * @return FlexiCalendarUnit
     */
    public function get_latest_for_questions()
    {
        $time = time();
        $reverse = array_reverse($this->displayset);
        foreach ($reverse as $unit) {
            $timestamp = $unit->getTimestamp();
            // If day/week/etc is 80% complete, then select it for questions.
            $offset = $this->periodduration * 0.8;
            $timestamp += ($offset * DAYSECS);
            if ($timestamp < $time) {
                return $unit;
            }
        }
        // We shouldn't reach this, but make sure to return something.
        return $unit;
    }

    /**
     * @return int
     */
    public function get_period_duration() {
        return $this->periodduration;
    }

    public function get_period_duration_string($string_type = false)
    {
        if (empty($string_type)) {
            $string_type = static::STRING_TYPE_PLURAL;
        }
        $prefix = $string_type;
        switch ($this->periodduration) {
            case 1:
                $singular_id = 'day';
                $other_id = $prefix . '_' . $singular_id;
                $plural_id = 'days';
                break;
            case 7:
                $singular_id = 'week';
                $other_id = $prefix . '_' . $singular_id;
                $plural_id = 'weeks';
                break;
            default:
                $singular_id = 'block_of_days';
                $other_id = $prefix . '_' . $singular_id;
                $plural_id = 'blocks_of_days';
        }
        if ($string_type == static::STRING_TYPE_SINGULAR) {
            return Helper::get_string($singular_id);
        }
        if ($string_type == static::STRING_TYPE_PLURAL) {
            return Helper::get_string($plural_id);
        }
        return Helper::get_string($other_id);
    }

    /**
     * Validates and sets the period duration.
     *
     * @param int $periodduration
     * @throws \moodle_exception
     */
    private function init_period_duration($periodduration) {
        $periodduration = (int) $periodduration;
        if (!Helper::validate_period_duration($periodduration)) {
            throw new \moodle_exception('err');
        }
        $this->periodduration = $periodduration;
    }

    /**
     * Calculates the current span, i.e. the number of days within that time period.
     *
     * @return int
     */
    private function current_span($numentries_offset = 0) {
        $numentries = $this->numentries;
        if ($numentries_offset) {
            $numentries += $numentries_offset;
        }
        return ($numentries * $this->periodduration);
    }

    /**
     * Generates a set of FlexiCalendarUnits. Used for rendering the calendar and habit entries.
     *
     * @throws \moodle_exception
     */
    private function generate_display_set() {
        $numdaysago = $this->current_span() - 1;
        $startdate = Helper::new_date_time($this->basedate, '-' . $numdaysago . ' day');
        $currentdate = Helper::new_date_time($startdate);
        $displayset = array();
        while (count($displayset) < $this->numentries) {
            $unit = new FlexiCalendarUnit();
            $unit->setTimestamp($currentdate->getTimestamp());
            $unit->set_period_duration($this->periodduration);
            $displayset[] = $unit;
            $currentdate->modify('+'.$this->periodduration.' day');
        }
        $this->displayset = $displayset;
    }

    /**
     * Gets the URL for moving backwards through the calendar.
     *
     * @param $instanceid
     * @param $override_num_entries
     * @return \moodle_url
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_back_url($instanceid, $override_num_entries = 0) {
//        $offset = $this->current_span() - 1;
//        $backdate = Helper::new_date_time($this->basedate, '-' . $offset . ' day');
//        $backdatemysql = Helper::date_time_to_mysql($backdate);
        if ($override_num_entries) {
            $pre_num_entries = $this->numentries;
            $this->numentries = $override_num_entries;
        }
        $backdatemysql = $this->get_back_date();
        if ($override_num_entries) {
            $this->numentries = $pre_num_entries;
        }
        $params = array('toDate' => $backdatemysql, 'g' => $instanceid);
        $url = '/mod/goodhabits/view.php';
        if ($this->pluginarea == self::PLUGIN_AREA_REVIEW) {
            $url = '/mod/goodhabits/review.php';
            $params['instance'] = $instanceid;
            $params['userid'] = $this->userid;
        }
        $layout = optional_param('layout', '', PARAM_TEXT);
        if ($layout) {
            $params['layout'] = $layout;
        }
        $url = new \moodle_url($url, $params, 'intro-name');
        return $url;
    }

    public function get_back_date()
    {
        $offset = $this->current_span() - 1;
        $backdate = Helper::new_date_time($this->basedate, '-' . $offset . ' day');
        $backdatemysql = Helper::date_time_to_mysql($backdate);

        return $backdatemysql;
    }

    public function get_to_date()
    {
        $to_date = Helper::date_time_to_mysql($this->basedate);

        return $to_date;
    }

    /**
     * Gets the URL for moving forwards through the calendar.
     *
     * @param $instanceid
     * @param $override_num_entries
     * @return \moodle_url|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_forward_url($instanceid, $override_num_entries = 0) {
        if ($override_num_entries) {
            $pre_num_entries = $this->numentries;
            $this->numentries = $override_num_entries;
        }

        $add_days = $this->current_span(-1);
        $forwarddate = Helper::new_date_time($this->basedate, '+' . $add_days . ' day');
        if ($override_num_entries) {
            $this->numentries = $pre_num_entries;
        }
        $threshold = Helper::get_end_period_date_time($this->periodduration, new \DateTime());
        if ($forwarddate->getTimestamp() > $threshold->getTimestamp()) {
            $forwarddate = $threshold;
            if ($forwarddate->getTimestamp() <= $this->basedate->getTimestamp()) {
                return null;
            }
            $forwarddate->modify('+' . $this->periodduration . ' day');
        }
        $forwarddatemysql = Helper::date_time_to_mysql($forwarddate);
        $params = array('toDate' => $forwarddatemysql, 'g' => $instanceid);
        if ($forwarddate->getTimestamp() > time()) {
            // No toDate param to display latest.
            unset($params['toDate']);
        }
        $url = '/mod/goodhabits/view.php';
        if ($this->pluginarea == self::PLUGIN_AREA_REVIEW) {
            $url = '/mod/goodhabits/review.php';
            $params['instance'] = $instanceid;
            $params['userid'] = $this->userid;
        }
        $layout = optional_param('layout', '', PARAM_TEXT);
        if ($layout) {
            $params['layout'] = $layout;
        }
        $url = new \moodle_url($url, $params, 'intro-name');
        return $url;
    }

}
