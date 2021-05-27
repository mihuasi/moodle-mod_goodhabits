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

use mod_goodhabits as gh;

require_once('../../config.php');
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot . '/mod/goodhabits/classes/form/add_habit.php');
require_once($CFG->dirroot . '/mod/goodhabits/classes/Helper.php');
require_once($CFG->dirroot . '/mod/goodhabits/classes/HabitItemsHelper.php');
require_once($CFG->dirroot . '/mod/goodhabits/classes/Habit.php');

require_login();

$instanceid = required_param('instance', PARAM_INT);
$moduleinstance = gh\Helper::get_module_instance($instanceid);
$course = get_course($moduleinstance->course);
$cm = get_coursemodule_from_instance('goodhabits', $moduleinstance->id, $course->id, false, MUST_EXIST);
$name = $moduleinstance->name;

$level = optional_param('level', 'personal', PARAM_TEXT);
$action = optional_param('action', '', PARAM_TEXT);
$habitid = optional_param('habitid', '', PARAM_INT);

$context = context_module::instance($cm->id);
require_capability('mod/goodhabits:view', $context);

if ($level == 'activity') {
    $titleid = 'manage_activity_habits_title';
} else {
    $titleid = 'manage_habits_title';
}

$pagetitle = get_string($titleid, 'mod_goodhabits', $name);

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_course($course);
$PAGE->set_cm($cm);

$params = array('instance' => $instanceid);

$params['level'] = $level;
$params['action'] = $action;
$params['habitid'] = $habitid;

$pageurl = new moodle_url('/mod/goodhabits/manage_habits.php', $params);

$PAGE->set_url($pageurl);
$PAGE->navbar->add($pagetitle, $pageurl);


$renderer = $PAGE->get_renderer('mod_goodhabits');

gh\HabitItemsHelper::check_delete_habit();
gh\HabitItemsHelper::check_delete_habit_entries();

$table = new html_table();

gh\HabitItemsHelper::set_table_head($table);

$mform = new gh\add_habit(null, $params);

if ($action == 'edit') {
    $habit = new gh\Habit($habitid);
    $mform->set_data($habit);
}

if ($data = $mform->get_data()) {
    gh\HabitItemsHelper::process_form($data, $action);
}

$ispersonal = $level == 'personal';
$showonlypublished = ($ispersonal) ? true : false;
$habits = gh\HabitItemsHelper::get_activity_habits($instanceid, $showonlypublished);

$isactivity = !$ispersonal;

if ($ispersonal) {
    $habits += gh\HabitItemsHelper::get_personal_habits($instanceid, $USER->id);
}

foreach ($habits as $habit) {
    $row = array();

    $habitname = gh\HabitItemsHelper::table_habit_name($habit, $ispersonal, $name);

    $row[] = $habitname;
    $row[] = format_text($habit->description);
    $row[] = gh\HabitItemsHelper::get_num_entries($habit->id, $USER->id);

    $actions = gh\HabitItemsHelper::table_actions_arr($habit, $isactivity, $level, $instanceid);

    $row[] = implode('<br />', $actions);
    $table->data[] = $row;
}

echo $OUTPUT->header();

if ($habits AND $action != 'edit') {
    echo html_writer::table($table);
}

echo html_writer::start_div('add_habit');
$formlangid = gh\HabitItemsHelper::get_form_submit_lang_id($action, $level);

$text = get_string($formlangid, 'mod_goodhabits');

echo html_writer::tag('p', $text, array('class' => 'add_habit'));
$mform->display();
echo html_writer::end_div();

echo $renderer->print_home_link($name);

echo $OUTPUT->footer();