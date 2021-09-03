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
 * Saves a Habit Entry via AJAX.
 *
 * @package   mod_goodhabits
 * @copyright 2021 Joe Cape
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_goodhabits as gh;

require_once('../../config.php');
require_once('classes/Habit.php');
require_once('classes/HabitEntry.php');
require_once('classes/HabitEntryTwoDimensional.php');
require_once('classes/FlexiCalendar.php');
require_once('classes/FlexiCalendarUnit.php');
require_once('classes/Helper.php');
require_once('lib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');

define('AJAX_SCRIPT', true);

require_login();

$habitid = required_param('habitId', PARAM_INT);
$timestamp = required_param('timestamp', PARAM_INT);
$duration = required_param('periodDuration', PARAM_INT);
$x = required_param('x', PARAM_INT);
$y = required_param('y', PARAM_INT);

require_sesskey();

$habit = new gh\Habit($habitid);

$courseid = $habit->get_course_id();

$cm = $habit->get_cm();

$instance = $habit->get_instance_record();

$modulecontext = context_module::instance($cm->id);

require_capability('mod/goodhabits:view', $modulecontext);
require_capability('mod/goodhabits:manage_entries', $modulecontext);

$userid = $USER->id;

$course = get_course($courseid);

$entry = new gh\HabitEntryTwoDimensional($habit, $userid, $timestamp, $duration, $x, $y);

if ($entry->already_exists()) {
    $entry->update();
} else {
    $entry->save();
}

$completion = new completion_info($course);

if ($completion->is_enabled($cm) && $instance->completionentries) {
    $iscomplete = goodhabits_get_completion_state($course, $cm, $userid, COMPLETION_AND);
    if ($iscomplete) {
        $completion->update_state($cm, COMPLETION_COMPLETE);
    }
}
