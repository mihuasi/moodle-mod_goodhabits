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
     * @var int
     */
    protected $endofperiodtimestamp;

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
        $this->endofperiodtimestamp = $endofperiodtimestamp;
        $this->periodduration = $periodduration;
        $this->init_existing_record();
    }

    /**
     * Initialises the DB record for this entry, if it exists.
     *
     * @throws \dml_exception
     */
    public function init_existing_record() {
        global $DB;
        $params = array(
            'habit_id' => $this->habit->id,
            'userid' => $this->userid,
            'entry_type' => $this->entrytype,
            'period_duration' => $this->periodduration,
            'endofperiod_timestamp' => $this->endofperiodtimestamp);
        $this->existingrecord = $DB->get_record('mod_goodhabits_entry', $params);
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