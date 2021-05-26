<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_goodhabits.
 *
 * @package     mod_goodhabits
 * @copyright   2021 Joe Cape <joe.sc.cape@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_goodhabits as gh;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

require_once('classes/Habit.php');
require_once('classes/FlexiCalendar.php');
require_once('classes/FlexiCalendarUnit.php');
require_once('classes/Helper.php');
require_once('classes/HabitItemsHelper.php');
require_once('classes/BreaksHelper.php');

// Course module ID, or...
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$g  = optional_param('g', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('goodhabits', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('goodhabits', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($g) {
    $moduleinstance = gh\Helper::get_module_instance($g);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('goodhabits', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception(get_string('missingidandcmid', 'mod_goodhabits'));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$instanceid = $moduleinstance->id;

require_capability('mod/goodhabits:view', $modulecontext);

$event = \mod_goodhabits\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('goodhabits', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/goodhabits/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$PAGE->requires->jquery_plugin('ui');

$PAGE->requires->js('/mod/goodhabits/talentgrid/talentgrid-plugin.js', true);
$PAGE->requires->js('/mod/goodhabits/js/calendar.js', false);

$PAGE->requires->css('/mod/goodhabits/talentgrid/talentgrid-style.css');

$renderer = $PAGE->get_renderer('mod_goodhabits');
$todate = optional_param('toDate', null, PARAM_TEXT);

$periodduration = gh\Helper::get_period_duration($moduleinstance);
$numentries = 8;

if ($todate) {
    $currentdate = new DateTime($todate);
} else {
    $currentdate = new DateTime();
}

$basedate = gh\Helper::get_end_period_date_time($periodduration, $currentdate);

$calendar = new gh\FlexiCalendar($periodduration, $basedate, $numentries);

$habits = gh\HabitItemsHelper::get_activity_habits($instanceid, true);
$habits += gh\HabitItemsHelper::get_habits($instanceid, true);

echo $OUTPUT->header();

echo $renderer->print_hidden_data();

$renderer->print_act_intro($moduleinstance);

if ($habits) {
    $calendarhtml = $renderer->print_calendar($calendar, $instanceid);

    $habitshtml = $renderer->print_habits($calendar, $habits);

    echo $renderer->print_module($calendarhtml, $habitshtml);
} else {
    echo $renderer->print_no_habits();
}

$canmanagepersonal = has_capability('mod/goodhabits:manage_personal_habits', $PAGE->context);
$canmanageactivityhabits = has_capability('mod/goodhabits:manage_activity_habits', $PAGE->context);
$canmanagebreaks = has_capability('mod/goodhabits:manage_personal_breaks', $PAGE->context);

if ($canmanageactivityhabits) {
    $renderer->print_manage_activity_habits($instanceid);
}

if ($canmanagepersonal) {
    $renderer->print_manage_habits($instanceid);
}

if ($canmanagebreaks) {
    $renderer->print_manage_personal_breaks($instanceid);
}

echo $OUTPUT->footer();
