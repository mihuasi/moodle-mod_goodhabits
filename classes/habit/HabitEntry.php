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

namespace mod_goodhabits\habit;

use mod_goodhabits\Helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Models a Habit Entry.
 *
 * Class HabitEntry
 * @package mod_goodhabits
 */
abstract class HabitEntry {

    /**
     * @var Habit
     */
    protected $habit;

    /**
     * @var int
     */
    protected $userid;

    /**
     * @var \mod_goodhabits\calendar\FlexiCalendarUnit
     */
    protected $flexiunit;

    /**
     * @var int
     */
    protected $periodduration;

    /**
     * @var string - the entry type of this Habit. Set by the child class.
     */
    protected $entrytype;

    /**
     * @var \stdClass - the DB record for this entry.
     */
    protected $existingrecord;

    const ENTRY_TYPE_TWO_DIMENSIONAL = 'two-dimensional';

    /**
     * HabitEntry constructor.
     * @param Habit $habit
     * @param int $userid
     * @param int $endofperiodtimestamp
     * @param int $periodduration
     */
    public function __construct(Habit $habit, $userid, $endofperiodtimestamp, $periodduration) {
        $this->habit = $habit;
        $this->userid = $userid;
        $this->flexiunit = Helper::get_flexi_cal_unit_from_timestamp($endofperiodtimestamp, $periodduration);
        $this->periodduration = $periodduration;
        $this->init_existing_record();
    }

    /**
     * Initialises the DB record for this entry, if it exists.
     *
     * TODO: Check from-to timestamp.
     *
     * @throws \dml_exception
     */
    public function init_existing_record() {
        global $DB;
        $params = $this->get_sql_params();
        $sql = "SELECT * FROM {mod_goodhabits_entry} e
                    WHERE userid = :userid AND habit_id = :habit_id
                      AND e.period_duration = :period_duration
                      AND e.endofperiod_timestamp >= :lower AND e.endofperiod_timestamp <= :upper";
        $this->existingrecord = $DB->get_record_sql($sql, $params);
    }

    protected function get_sql_params()
    {
        $limits = $this->flexiunit->get_limits();
        $params = array(
            'habit_id' => $this->habit->id,
            'userid' => $this->userid,
            'entry_type' => $this->entrytype,
            'period_duration' => $this->periodduration,
            'lower' => $limits['lower'],
            'upper' => $limits['upper'],
        );
        return $params;
    }

    protected function get_any_existing_similar_timestamp_record()
    {
        global $DB;
        $params = $this->get_sql_params();
        unset($params['habit_id']);
        $sql = "SELECT * FROM {mod_goodhabits_entry} e
                    WHERE userid = :userid 
                      AND e.period_duration = :period_duration
                      AND e.endofperiod_timestamp >= :lower AND e.endofperiod_timestamp <= :upper";
        $all = $DB->get_records_sql($sql, $params);
        $example = reset($all);
        return $example;
    }

    public function get_snap_to_time()
    {
        if ($example = $this->get_any_existing_similar_timestamp_record()) {
            return $example->endofperiod_timestamp;
        }
        return false;
    }

    public function upsert()
    {
        if ($this->already_exists()) {
            $this->update();
        } else {
            $this->save();
        }
    }

    /**
     * Returns whether there is currently a DB record for this entry.
     *
     * @return bool
     */
    public function already_exists() {
        return (boolean) $this->existingrecord;
    }

    /**
     * @return null
     */
    abstract public function save();

    /**
     * @return null
     */
    abstract public function update();
}
