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

use mod_goodhabits as gh;
use mod_goodhabits\habit\HabitItemsHelper;

require_once('../../config.php');
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot . '/mod/goodhabits/classes/form/select_user_review.php');

require_login();

$instanceid = required_param('instance', PARAM_INT);
$habit_id = optional_param('habit_id', 0, PARAM_INT);
$end = optional_param('end_time', time(), PARAM_INT);
$moduleinstance = gh\Helper::get_module_instance($instanceid);
$course = get_course($moduleinstance->course);
$cm = get_coursemodule_from_instance('goodhabits', $moduleinstance->id, $course->id, false, MUST_EXIST);
$name = $moduleinstance->name;

$userid = $USER->id;

$context = context_module::instance($cm->id);

require_capability('mod/goodhabits:view_own_insights', $context);

$habits = HabitItemsHelper::get_all_habits_for_user($instanceid, $userid);

$titleid = 'insights';
$pagetitle = gh\Helper::get_string($titleid);

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_course($course);
$PAGE->set_cm($cm);

$params = array('instance' => $instanceid);

$pageurl = new moodle_url('/mod/goodhabits/insights.php', $params);

$PAGE->set_url($pageurl);

$PAGE->navbar->add($pagetitle, $pageurl);

echo $OUTPUT->header();

$start = strtotime('-30 day', $end);


//if (empty($habit_ids)) {
//    if ($habit_id) {
//        $habit_ids = [$habit_id];
//    } else {
//        $first_habit = reset($habits);
//        $habit_ids = [$first_habit->id];
//    }
//}
if (!$habit_id) {
    $first_habit = reset($habits);
    $habit_id = $first_habit->id;
}

// Prepare form data
$formdata = [
    'selectable_habits' => $habits,
    'selected' => $habit_id,
    'start' => $start,
    'end' => $end,
    'instanceid' => $instanceid
];

$mform = new \mod_goodhabits\form\insights_filter(null, $formdata);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/goodhabits/insights.php', ['instance' => $instanceid]));
} else if ($data = $mform->get_data()) {
    // Use submitted data
    $habit_id = $data->habit;
    $start = $data->start;
    $end = $data->end;
}
$limits = [
    'lower' => $start,
    'upper' => $end
];


$entries = gh\insights\Helper::get_habit_entries($instanceid, $userid, $limits, [$habit_id]);

$entries_data = gh\insights\Helper::structure_data($entries);

$dates = gh\insights\Helper::get_graph_dates();

//print_object($entries_data);

$chart = new \core\chart_bar();

$x_series = gh\insights\Helper::populate_effort_outcome_series($entries_data, 'x');
$y_series = gh\insights\Helper::populate_effort_outcome_series($entries_data, 'y');

foreach ($y_series as $series_item) {
    $chart->add_series($series_item);
}

foreach ($x_series as $series_item) {
    $chart->add_series($series_item);
}


$labels = $dates;

$chart->set_labels($labels);

$chart->set_title('Effort and Outcome Over Time');
$chart->set_legend_options(['display' => true, 'position' => 'top']);


echo $OUTPUT->render($chart);

$mform->display();

echo $OUTPUT->footer();