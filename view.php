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

// Course module ID, or...
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$g  = optional_param('g', 0, PARAM_INT);

$layout = optional_param('layout', '', PARAM_TEXT);
$skip = optional_param('skip', 0, PARAM_INT);
$is_basic_mobile = ($layout == gh\Helper::LAYOUT_BASIC_MOBILE);

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

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$calendar = gh\ViewHelper::get_flexi_calendar($moduleinstance);
$calendar->add_body_classes();

if ($skip) {
    gh\BreaksHelper::process_skip($instanceid, $skip, $calendar);
}

$auto = new gh\AutoSimple($calendar, $moduleinstance->id, $USER->id);
//$auto->execute();


$PAGE->set_url('/mod/goodhabits/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$PAGE->requires->jquery_plugin('ui');

$PAGE->requires->js('/mod/goodhabits/talentgrid/talentgrid-plugin.js', true);
$PAGE->requires->js('/mod/goodhabits/js/calendar.js', false);

$PAGE->requires->css('/mod/goodhabits/talentgrid/talentgrid-style.css');

if ($is_basic_mobile) {
    $PAGE->set_pagelayout('embedded');
}

$renderer = $PAGE->get_renderer('mod_goodhabits');

$habits = gh\habit\HabitItemsHelper::get_all_habits_for_user($instanceid, $USER->id, 1);

echo $OUTPUT->header();

echo $renderer->print_hidden_data($instanceid);

echo $renderer->print_viewport_too_small_message();

if (!$is_basic_mobile) {
    $renderer->print_act_intro($moduleinstance);
}

$renderer->print_templated_calendar_area($calendar, $instanceid, $habits);

if ($is_basic_mobile) {
    $renderer->print_exit_mobile_view($instanceid);
    echo $OUTPUT->footer();
    exit;
}

$canmanagepersonal = has_capability('mod/goodhabits:manage_personal_habits', $PAGE->context);
$canmanageactivityhabits = has_capability('mod/goodhabits:manage_activity_habits', $PAGE->context);
////TODO: Check settings.
//$canreview = (has_capability('mod/goodhabits:review_as_admin', $PAGE->context) OR has_capability('mod/goodhabits:review_as_peer', $PAGE->context));
$access_review_as = gh\PreferencesManager::access_review_feature_as($instanceid);

$access_review_string = 'review_entries_as_admin';
if ($access_review_as == gh\PreferencesManager::ACCESS_AS_PEER) {
    $access_review_string = 'review_entries_as_peer';
}

$canreview = !empty($access_review_as);
$canmanagebreaks = has_capability('mod/goodhabits:manage_personal_breaks', $PAGE->context);
$canmanageprefs = has_capability('mod/goodhabits:manage_personal_prefs', $PAGE->context);

if ($canmanageactivityhabits) {
    $renderer->print_manage_activity_habits($instanceid);
}

$reviewconf = get_config('goodhabits', 'review');
if ($reviewconf == gh\ViewHelper::REVIEW_OPTION_DISABLE) {
    $canreview = false;
}

if ($canreview) {
    $renderer->print_review_entries($instanceid, $access_review_string);
}

$renderer->print_see_historical_data($instanceid);

if ($canmanagepersonal) {
    $renderer->print_manage_habits($instanceid);
}

if ($canmanagebreaks) {
    $renderer->print_manage_personal_breaks($instanceid);
}

if ($canmanageprefs) {
    $renderer->print_preferences($instanceid);
}

//$renderer->print_mobile_view($instanceid);

echo $OUTPUT->footer();
