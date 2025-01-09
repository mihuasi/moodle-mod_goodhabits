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
require_once($CFG->dirroot . '/mod/goodhabits/classes/form/add_break.php');

require_login();

$instanceid = optional_param('instance', 0, PARAM_INT);
$moduleinstance = gh\Helper::get_module_instance($instanceid);
$course = get_course($moduleinstance->course);
$cm = get_coursemodule_from_instance('goodhabits', $moduleinstance->id, $course->id, false, MUST_EXIST);
$name = $moduleinstance->name;

$context = context_module::instance($cm->id);

require_capability('mod/goodhabits:manage_personal_breaks', $context);
$pagetitle = get_string('manage_breaks_title', 'mod_goodhabits', $name);

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_course($course);
$PAGE->set_cm($cm);

$params = array('instance' => $instanceid);
$pageurl = new moodle_url('/mod/goodhabits/manage_breaks.php', $params);

$PAGE->set_url($pageurl);
$PAGE->navbar->add($pagetitle, $pageurl);

$renderer = $PAGE->get_renderer('mod_goodhabits');

gh\BreaksHelper::check_delete_break();

$table = new html_table();

$fromtext = get_string('fromdate_text', 'mod_goodhabits');
$totext = get_string('todate_text', 'mod_goodhabits');
$actionstext = get_string('actions', 'mod_goodhabits');
$table->head = array($fromtext, $totext, $actionstext);

$mform = new gh\add_break(null, $params);

if ($data = $mform->get_data()) {
    gh\BreaksHelper::add_personal_break($data);
    $msg = get_string('break_added', 'mod_goodhabits');
    redirect($PAGE->url, $msg);
}

$breaks = gh\BreaksHelper::get_personal_breaks($instanceid);

foreach ($breaks as $break) {
    $row = array();
    $row[] = userdate($break->timestart, '%A %d %h, %G');
    $row[] = userdate($break->timeend, '%A %d %h, %G');
    $jsconfirmtxt = get_string('js_confirm_deletebreak', 'mod_goodhabits');
    $jsconfirm = gh\Helper::js_confirm_text($jsconfirmtxt);
    $delparams = array('action' => 'delete', 'breakid' => $break->id, 'sesskey' => sesskey(), 'instance' => $instanceid);
    $deleteurl = new moodle_url('/mod/goodhabits/manage_breaks.php', $delparams);
    $deltext = get_string('delete', 'mod_goodhabits');
    $icon = $OUTPUT->pix_icon('t/delete', $deltext);
    $deltext = $icon . $deltext;
    $row[] = html_writer::link($deleteurl, $deltext, $jsconfirm);
    $table->data[] = $row;
}

echo $OUTPUT->header();

if ($breaks) {
    echo html_writer::table($table);
}

echo html_writer::start_div('add_break');
$text = get_string('addbreak_submit_text', 'mod_goodhabits');
echo html_writer::tag('p', $text, array('class' => 'add_break'));
$mform->display();
echo html_writer::end_div();

echo $renderer->print_home_link($name);

echo $OUTPUT->footer();
