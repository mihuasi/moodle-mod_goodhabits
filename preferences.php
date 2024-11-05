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
 * @copyright 2024 Joe Cape
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_goodhabits as gh;
use mod_goodhabits\PreferencesManager;

require_once('../../config.php');
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot . '/mod/goodhabits/classes/form/preferences.php');

require_login();

$instanceid = optional_param('instance', 0, PARAM_INT);
$moduleinstance = gh\Helper::get_module_instance($instanceid);
$course = get_course($moduleinstance->course);
$cm = get_coursemodule_from_instance('goodhabits', $moduleinstance->id, $course->id, false, MUST_EXIST);
$name = $moduleinstance->name;

$context = context_module::instance($cm->id);

require_capability('mod/goodhabits:manage_personal_prefs', $context);

$pagetitle = gh\Helper::get_string('manage_prefs_title', $name);

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_course($course);
$PAGE->set_cm($cm);

$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js('/mod/goodhabits/js/preferences.js', false);

$params = array('instance' => $instanceid);
$pageurl = new moodle_url('/mod/goodhabits/preferences.php', $params);

$PAGE->set_url($pageurl);
$PAGE->navbar->add($pagetitle, $pageurl);

$renderer = $PAGE->get_renderer('mod_goodhabits');

$params = array('instance' => $instanceid);
$mform = new gh\preferences(null, $params);
$userid = $USER->id;

$mgr = new PreferencesManager($instanceid, $USER->id);

if ($data = $mform->get_data()) {
    $mgr->process_form($data);

    $msg_success = gh\Helper::get_string('pref_updated');
    redirect($pageurl, $msg_success, null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

$mform->display();

echo $renderer->print_home_link($name);

echo $OUTPUT->footer();
