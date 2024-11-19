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
 * @copyright 2024 Joe Cape
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_goodhabits;

use mod_goodhabits\calendar\FlexiCalendar;
use mod_goodhabits\calendar\FlexiCalendarUnit;

defined('MOODLE_INTERNAL') || die();

/**
 * Handles logic around automatically redirecting users to complete the simple questions.
 */
class AutoSimple
{
    protected FlexiCalendar $calendar;

    protected FlexiCalendarUnit $latest;

    protected $instanceid;

    protected $userid;
    public function __construct(FlexiCalendar $calendar, $instanceid, $userid)
    {
        $this->calendar = $calendar;
        $this->latest = $calendar->get_latest();
        $this->instanceid = $instanceid;
        $this->userid = $userid;
    }

    public function execute()
    {
        $limits = $this->latest->get_limits();
        if (BreaksHelper::is_in_a_break($this->latest->getTimestamp())) {
            return false;
        }
        $entries = Helper::get_entries($this->instanceid, $this->userid, $limits);
        if (!empty($entries)) {
            return false;
        }

        $url = $this->latest->answer_questions_url($this->instanceid);

        redirect($url);
    }
}