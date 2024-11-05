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

defined('MOODLE_INTERNAL') || die();

/**
 * Models a "two-dimensional" Habit Entry (i.e. the entry takes place on a grid, with an X and a Y axis).
 *
 * Class HabitEntryTwoDimensional
 * @package mod_goodhabits
 */
class HabitEntryTwoDimensional extends HabitEntry {

    /**
     * @var int the X value for this entry.
     */
    protected $xval;

    /**
     * @var int the Y value for this entry.
     */
    protected $yval;

    /**
     * HabitEntryTwoDimensional constructor.
     * @param Habit $habit
     * @param int $userid
     * @param int $endofperiodtimestamp
     * @param int $periodduration
     * @param int $xval
     * @param int $yval
     */
    public function __construct(Habit $habit, $userid, $endofperiodtimestamp, $periodduration, $xval, $yval) {
        parent::__construct($habit, $userid, $endofperiodtimestamp, $periodduration);
        $this->xval = $xval;
        $this->yval = $yval;
        $this->entrytype = HabitEntry::ENTRY_TYPE_TWO_DIMENSIONAL;
    }

    /**
     * Inserts a habit entry record into the DB.
     *
     * @return void|null
     * @throws \dml_exception
     */
    public function save() {
        global $DB;
        $record = new \stdClass();
        if (!$time = $this->get_snap_to_time()) {
            $time = $this->flexiunit->getTimestamp();
        }
        $record->habit_id = $this->habit->id;
        $record->userid = $this->userid;
        $record->entry_type = $this->entrytype;
        $record->period_duration = $this->periodduration;
        $record->endofperiod_timestamp = $time;
        $record->x_axis_val = $this->xval;
        $record->y_axis_val = $this->yval;
        $record->timecreated = time();
        $record->timemodified = time();
        $DB->insert_record('mod_goodhabits_entry', $record);
    }

    /**
     * Updates the habit entry record in the DB.
     *
     * @return void|null
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function update() {
        global $DB;
        if (!$this->existingrecord) {
            throw new \moodle_exception('existingRecord not found');
        }
        $this->existingrecord->x_axis_val = $this->xval;
        $this->existingrecord->y_axis_val = $this->yval;
        $this->existingrecord->timemodified = time();
        $DB->update_record('mod_goodhabits_entry', $this->existingrecord);
    }

}
