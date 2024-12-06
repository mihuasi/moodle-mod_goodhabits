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

require_login();

$instanceid = required_param('instance', PARAM_INT);
$moduleinstance = gh\Helper::get_module_instance($instanceid);
$course = get_course($moduleinstance->course);
$cm = get_coursemodule_from_instance('goodhabits', $moduleinstance->id, $course->id, false, MUST_EXIST);
$name = $moduleinstance->name;

$userid = optional_param('userid', 0, PARAM_INT);

$context = context_module::instance($cm->id);
//TODO: Check settings.
$canreview = (has_capability('mod/goodhabits:review_as_admin', $context) OR has_capability('mod/goodhabits:review_as_peer', $context));
if (!$canreview) {
    throw new moodle_exception(get_string('no_access', 'mod_goodhabits'));
}
$reviewconf = get_config('goodhabits', 'review');
if ($reviewconf == gh\ViewHelper::REVIEW_OPTION_DISABLE) {
    throw new moodle_exception(get_string('accessing_review_when_disabled', 'mod_goodhabits'));
}

$titleid = 'review_entries';
$pagetitle = get_string($titleid, 'mod_goodhabits');

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

//$reviewer = new gh\review\Reviewer($instanceid, $userid, $context);
//$reviewer->init();
//$reviewer->set_query('St');
//$subjects = $reviewer->get_subjects();
//print_object($subjects);
//exit;
$selectform = new gh\select_user_review(null, $params);

$selectform->display();

$data = $selectform->get_data();

$userid = (isset($data->userid)) ? $data->userid : $userid;

if ($userid) {
    // Only allow if the user being queried can access this module.
    require_capability('mod/goodhabits:view', $context, $userid);

    $accessing_as = gh\PreferencesManager::access_review_feature_as($instanceid, $userid);
    $access_as_string = 'access_review_entries_as_admin';
    if ($accessing_as == gh\PreferencesManager::ACCESS_AS_PEER) {
        $access_as_string = 'access_review_entries_as_peer';
    }

    $fullname = gh\ViewHelper::get_name($userid);

    $renderer->print_review_intro($fullname, $access_as_string);

    $calendar = gh\ViewHelper::get_flexi_calendar($moduleinstance, $userid);

    $habits = gh\habit\HabitItemsHelper::get_all_habits_for_user($instanceid, $userid);

    $extraclasses = array('review');
    $renderer->print_templated_calendar_area($calendar, $instanceid, $habits, $extraclasses, $userid, true);
}

echo $renderer->print_home_link($name);

echo $OUTPUT->footer();
