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
require_once($CFG->dirroot . '/lib/completionlib.php');

// ... module instance id.
$g  = optional_param('g', 0, PARAM_INT);
$timestamp  = optional_param('timestamp', 0, PARAM_INT);

$moduleinstance = gh\Helper::get_module_instance($g);
$course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
$cm             = get_coursemodule_from_instance('goodhabits', $moduleinstance->id, $course->id, false, MUST_EXIST);

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$instanceid = $moduleinstance->id;

require_capability('mod/goodhabits:view', $modulecontext);
//TODO: Combine logic with view.php in single class.
$event = \mod_goodhabits\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('goodhabits', $moduleinstance);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/goodhabits/simple.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));

//$PAGE->set_secondary_active_tab("goodhabits_simple");

$PAGE->set_context($modulecontext);

$PAGE->requires->jquery_plugin('ui');

$PAGE->requires->js('/mod/goodhabits/talentgrid/talentgrid-plugin.js', true);
$PAGE->requires->js('/mod/goodhabits/js/simple.js', false);

$PAGE->requires->css('/mod/goodhabits/talentgrid/talentgrid-style.css');

$renderer = $PAGE->get_renderer('mod_goodhabits');

$calendar = gh\ViewHelper::get_flexi_calendar($moduleinstance);

if ($timestamp) {
    $calendar_unit = gh\Helper::get_flexi_cal_unit_from_timestamp($timestamp, $calendar->get_period_duration());
} else {
    $calendar_unit = $calendar->get_latest();
}

$display_unit_inline = $calendar_unit->display_unit_inline();
//$interval = new DateInterval('P7D'); //For week TODO: change for others.
//$date = $calendar_unit->add($interval)->format('Y-m-d');

$date = $calendar_unit->format('Y-m-d');
$timestamp = $calendar_unit->getTimestamp();

$habits_objs = gh\HabitItemsHelper::get_incomplete_for_user_date($instanceid, $USER->id, $calendar_unit);
$habits = [];

foreach ($habits_objs as $habits_obj) {
    $arr = [];
    $arr['name'] = $habits_obj->name;
    $arr['id'] = $habits_obj->id;
    $habits[] = $arr;
}

echo $OUTPUT->header();

echo $renderer->print_hidden_data();

$view_url = new moodle_url('/mod/goodhabits/view.php', array('id' => $cm->id));

$template_data = [
    'heading' => $display_unit_inline,
    'date' => $date,
    'timestamp' => $timestamp,
    'habits' => $habits,
    'view-url' => $view_url->out(),
];

echo $OUTPUT->render_from_template('mod_goodhabits/simple', $template_data);

echo $OUTPUT->footer();
