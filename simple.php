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
 * "Simple view" page.
 *
 * @package     mod_goodhabits
 * @copyright   2024 Joe Cape <joe.sc.cape@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_goodhabits as gh;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');

// ... module instance id.
$g  = optional_param('g', 0, PARAM_INT);
$timestamp  = optional_param('timestamp', 0, PARAM_INT);

$layout = optional_param('layout', '', PARAM_TEXT);
$moduleinstance = gh\Helper::get_module_instance($g);
$course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
$cm             = get_coursemodule_from_instance('goodhabits', $moduleinstance->id, $course->id, false, MUST_EXIST);

$is_basic_mobile = ($layout == gh\Helper::LAYOUT_BASIC_MOBILE);

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

if ($is_basic_mobile) {
    $PAGE->set_pagelayout('embedded');
}

$renderer = $PAGE->get_renderer('mod_goodhabits');

$calendar = gh\ViewHelper::get_flexi_calendar($moduleinstance);

if ($timestamp) {
    $calendar_unit = gh\Helper::get_flexi_cal_unit_from_timestamp($timestamp, $calendar->get_period_duration());
} else {
    $calendar_unit = $calendar->get_latest_for_questions();
}

$all_complete = gh\Helper::get_cal_units_with_all_complete($instanceid, $USER->id);
$questions = gh\Helper::get_simple_questions($all_complete);

$heading = $calendar_unit->display_unit_inline();

$date = null;
if ($calendar->get_period_duration() == 7) {
    // Only show "starting on" for weeks.
    $date = $calendar_unit->get_moodle_user_date('%A, %d %B, %Y');
} else if ($calendar->get_period_duration() === 1) {
    $heading = $calendar_unit->get_moodle_user_date('%A, %d %B, %Y');
}

$timestamp = $calendar_unit->getTimestamp();

$limits = $calendar_unit->get_limits();
$item_ids = gh\Helper::get_habits_with_missing_entries($instanceid, $USER->id, $limits);

$habits_recs = gh\habit\HabitItemsHelper::habit_item_ids_to_recs($item_ids);
$habits_recs = gh\habit\HabitItemsHelper::order_by_sortorder($habits_recs);
$habits = [];

foreach ($habits_recs as $habits_rec) {
    $arr = [];
    $arr['name'] = $habits_rec->name;
    $arr['desc'] = $habits_rec->description;
    $arr['id'] = $habits_rec->id;
    $arr['effort'] = $questions['effort'];
    $arr['outcome'] = $questions['outcome'];
    $habits[] = $arr;
}

echo $OUTPUT->header();

echo $renderer->print_hidden_data($instanceid);

$params = array('id' => $cm->id);
$next_calendar_unit = gh\Helper::new_date_time($calendar_unit, '+1day');
$to_date = gh\Helper::date_time_to_mysql($next_calendar_unit);
$params['toDate'] = $to_date;
$view_url = new moodle_url('/mod/goodhabits/view.php', $params);

gh\Helper::add_layout_url_param($view_url);

$template_data = [
    'periodduration' => $calendar->get_period_duration(),
    'heading' => $heading,
    'date' => $date,
    'timestamp' => $timestamp,
    'has_remaining_habits' => !empty($habits),
    'habits' => $habits,
    'view-url' => $view_url->out(),
    'sesskey' => sesskey(),
    'questions' => $questions
];

echo $OUTPUT->render_from_template('mod_goodhabits/simple', $template_data);

echo $OUTPUT->footer();
