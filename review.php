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

require_once('../../config.php');
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot . '/mod/goodhabits/classes/form/select_user_review.php');
require_once($CFG->dirroot . '/mod/goodhabits/classes/Helper.php');
require_once($CFG->dirroot . '/mod/goodhabits/classes/HabitItemsHelper.php');
require_once($CFG->dirroot . '/mod/goodhabits/classes/Habit.php');

require_login();

$instanceid = required_param('instance', PARAM_INT);
$moduleinstance = gh\Helper::get_module_instance($instanceid);
$course = get_course($moduleinstance->course);
$cm = get_coursemodule_from_instance('goodhabits', $moduleinstance->id, $course->id, false, MUST_EXIST);
$name = $moduleinstance->name;

$userid = optional_param('userid', 0, PARAM_INT);

$context = context_module::instance($cm->id);
require_capability('mod/goodhabits:review', $context);

$titleid = 'view_others_entries';
$pagetitle = get_string($titleid, 'mod_goodhabits');

//$PAGE->requires->jquery_plugin('ui');
//$PAGE->requires->js('/mod/goodhabits/talentgrid/talentgrid-plugin.js', true);
//$PAGE->requires->js('/mod/goodhabits/js/calendar.js', false);
$PAGE->requires->css('/mod/goodhabits/talentgrid/talentgrid-style.css');
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_course($course);
$PAGE->set_cm($cm);

$params = array('instance' => $instanceid);

$pageurl = new moodle_url('/mod/goodhabits/review.php', $params);

$PAGE->set_url($pageurl);

$PAGE->navbar->add($pagetitle, $pageurl);

$renderer = $PAGE->get_renderer('mod_goodhabits');

echo $OUTPUT->header();

$params['courseid'] = $course->id;

$selectform = new gh\select_user_review(null, $params);

$selectform->display();

$data = $selectform->get_data();

$userid = (isset($data->userid)) ? $data->userid : $userid;

if ($userid) {
    // TODO: Check permissions over user.
    $usercontext = context_user::instance($userid);
    require_capability('mod/goodhabits:review_others', $usercontext);
    // Only allow if the user being queried can access this module.
    require_capability('mod/goodhabits:view', $context, $userid);
    $todate = optional_param('toDate', null, PARAM_TEXT);
    $periodduration = gh\Helper::get_period_duration($moduleinstance);
    if ($todate) {
        // TODO: Refactor shared code with view.php.
        $currentdate = new DateTime($todate);
    } else {
        $currentdate = new DateTime();
    }

    $basedate = gh\Helper::get_end_period_date_time($periodduration, $currentdate);

    $numentries = gh\FlexiCalendar::DEFAULT_NUM_ENTRIES;
    $area = gh\FlexiCalendar::PLUGIN_AREA_REVIEW;
    $calendar = new gh\FlexiCalendar($periodduration, $basedate, $numentries, $area, $userid);

    $habits = gh\HabitItemsHelper::get_all_habits_for_user($instanceid, $userid);

    if ($habits) {
        echo $renderer->print_hidden_data();

        $calendarhtml = $renderer->print_calendar($calendar, $instanceid);

        $habitshtml = $renderer->print_habits($calendar, $habits, $userid);

        echo $renderer->print_module($calendarhtml, $habitshtml);
    } else {
        echo $renderer->print_no_habits();
    }
}

echo $renderer->print_home_link($name);

echo $OUTPUT->footer();