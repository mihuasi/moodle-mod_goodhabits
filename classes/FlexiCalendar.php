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
     * @var array - An array of FlexiCalendarUnits. One for every Habit Entry date.
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

    /**
     * @return array
     */
    public function get_display_set() {
        return $this->displayset;
    }

    /**
     * @return int
     */
    public function get_period_duration() {
        return $this->periodduration;
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
    private function current_span() {
        return ($this->numentries * $this->periodduration);
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
     * @param int $instanceid
     * @return \moodle_url
     * @throws \moodle_exception
     */
    public function get_back_url($instanceid) {
        $offset = $this->current_span() - 1;
        $backdate = Helper::new_date_time($this->basedate, '-' . $offset . ' day');
        $backdatemysql = Helper::date_time_to_mysql($backdate);
        $params = array('toDate' => $backdatemysql, 'g' => $instanceid);
        $url = '/mod/goodhabits/view.php';
        if ($this->pluginarea == self::PLUGIN_AREA_REVIEW) {
            $url = '/mod/goodhabits/review.php';
            $params['instance'] = $instanceid;
            $params['userid'] = $this->userid;
        }
        $url = new \moodle_url($url, $params);
        return $url;
    }

    /**
     * Gets the URL for moving forwards through the calendar.
     *
     * @param int $instanceid
     * @return \moodle_url|null
     * @throws \moodle_exception
     */
    public function get_forward_url($instanceid) {
        $forwarddate = Helper::new_date_time($this->basedate, '+' . $this->current_span(). ' day');
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
        $url = '/mod/goodhabits/view.php';
        if ($this->pluginarea == self::PLUGIN_AREA_REVIEW) {
            $url = '/mod/goodhabits/review.php';
            $params['instance'] = $instanceid;
            $params['userid'] = $this->userid;
        }
        $url = new \moodle_url($url, $params);
        return $url;
    }


}