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
 * Contains methods for output.
 * @package   mod_goodhabits
 * @copyright 2021 Joe Cape
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_goodhabits as gh;

defined('MOODLE_INTERNAL') || die();

class mod_goodhabits_renderer extends plugin_renderer_base {

    /**
     * Gets template data for the top 'ribbon' in the activity, showing a series of days or weeks.
     *
     * @param gh\FlexiCalendar $calendar
     * @param $instanceid
     * @return array
     * @throws moodle_exception
     */
    protected function get_calendar_data(gh\FlexiCalendar $calendar, $instanceid, $userid) {
        global $USER;
        $userid = ($userid) ? $userid : $USER->id;

        $data = [];
        $displayset = $calendar->get_display_set();

        $periodduration = $calendar->get_period_duration();

        $data['periodduration'] = $periodduration;

        $year = gh\Helper::display_year($displayset);

        $to_date = $calendar->get_to_date();

        $backurl = $calendar->get_back_url($instanceid);
        $forwardurl = $calendar->get_forward_url($instanceid);

        $data['year'] = $year;
        $data['backurl'] = $backurl;
        $data['forwardurl'] = $forwardurl;
        $data['flexi_cal_units'] = [];

        foreach ($displayset as $k => $unit) {
            $display = $unit->display_unit();

            $endofperiod_timestamp = $unit->getTimestamp();
            $missing = gh\Helper::get_missing_entries($instanceid, $userid, $endofperiod_timestamp);
            $all_complete = (empty($missing));

            $month = $unit->display_month();

            $imploded_classes = implode(' ', $unit->get_classes());

            $flexi_cal_unit = [];
            $flexi_cal_unit['top_line'] = $display['topLine'];
            $flexi_cal_unit['bottom_line'] = $display['bottomLine'];
            $flexi_cal_unit['month'] = $month;
            $flexi_cal_unit['class'] = $imploded_classes;
            $flexi_cal_unit['skip_url'] = $unit->skip_url($instanceid, $to_date);
            $flexi_cal_unit['answer_questions_url'] = $unit->answer_questions_url($instanceid);
            $flexi_cal_unit['all_complete'] = $all_complete;

            $data['flexi_cal_units'][] = $flexi_cal_unit;
        }

        return $data;
    }


    /**
     * Gets template data for the list of habits.
     * @param gh\FlexiCalendar $calendar
     * @param $habits
     * @param $userid
     * @return array
     */
    protected function get_habits_data(gh\FlexiCalendar $calendar, $habits, $userid = null) {
        $data = [];
        foreach ($habits as $habit) {
            $data[] = $this->get_habit_data($calendar, $habit, $userid);
        }

        return $data;
    }

    /**
     * Gets template data for a single habit - the name on the LHS and the "habit entries" on the RHS.
     *
     * @param gh\FlexiCalendar $calendar
     * @param gh\Habit $habit
     * @param $userid
     * @return array
     * @throws coding_exception
     */
    public function get_habit_data(gh\FlexiCalendar $calendar, gh\Habit $habit, $userid = null) {
        $habit_data = [];
        $habit_data['id'] = $habit->id;

        $editglobal = has_capability('mod/goodhabits:manage_activity_habits', $this->page->context);
        $editpersonal = has_capability('mod/goodhabits:manage_personal_habits', $this->page->context);
        $isactivitylevel = $habit->is_activity_habit();

        $habit_data['is_activity_level'] = $isactivitylevel;

        $canmanage = false;
        if ($isactivitylevel AND $editglobal) {
            $canmanage = true;
        }
        if (!$isactivitylevel AND $editpersonal) {
            $canmanage = true;
        }

        $canmanageclass = ($canmanage) ? ' can-edit ' : '';

        $activityclass = ($isactivitylevel) ? 'activity' : 'personal';

        $habit_data['can_manage_class'] = $canmanageclass;
        $habit_data['activity_class'] = $activityclass;
        $titletextid = $activityclass . '_title_text';

        $titletext = get_string($titletextid, 'mod_goodhabits');
        $habit_data['title_text'] = $titletext;
        $habit_data['habit_name'] = format_text($habit->name);
        $habit_data['habit_desc'] = format_text($habit->description);

        $checkmarks = $this->get_checkmarks_data($calendar, $habit, $userid);
        $habit_data['checkmarks'] = $checkmarks;

        return $habit_data;
    }

    /**
     * 'Checkmarks' here are the circular entities used to manage habit entries.
     *
     * @param gh\FlexiCalendar $calendar
     * @param gh\Habit $habit
     * @param $userid
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function get_checkmarks_data(gh\FlexiCalendar $calendar, gh\Habit $habit, $userid = null) {
        global $USER;

        $data = [];

        $data['units'] = [];

        $displayset = $calendar->get_display_set();

        $isreview = (int) $userid;

        $userid = ($userid) ? $userid : $USER->id;
        $entries = $habit->get_entries($userid, $calendar->get_period_duration());

        $canmanageentries = has_capability('mod/goodhabits:manage_entries', $this->page->context);

        $isactivitylevel = $habit->is_activity_habit();

        foreach ($displayset as $unit) {
            $data_checkmark = [];
            $timestamp = $unit->getTimestamp();
            $isinbreak = gh\BreaksHelper::is_in_a_break($timestamp);
            $classxy = 'noxy';
            $title = get_string('checkmark_title_empty', 'mod_goodhabits');
            $is_filled = false;
            if (isset($entries[$timestamp])) {
                $is_filled = true;
                $entry = $entries[$timestamp];
                $xval = $entry->x_axis_val;
                $yval = $entry->y_axis_val;
                $title = get_string('checkmark_title', 'mod_goodhabits', $entry);

                $classxy = 'x-val-' . $xval . ' y-val-' . $yval;
            }

            $caninteract = $canmanageentries AND !$isinbreak;
            $caninteractclass = ($caninteract) ? '' : ' no-interact ';

            $classes = 'checkmark ' . $caninteractclass . ' ' . $classxy;
            if ($isinbreak) {
                $classes .= ' is-in-break';
            }

            $data_checkmark['is_filled'] = $is_filled;
            $data_checkmark['is_review'] = $isreview;
            $data_checkmark['title'] = $title;
            $data_checkmark['x'] = $xval ?? null;
            $data_checkmark['y'] = $yval ?? null;
            $data_checkmark['timestamp'] = $timestamp;
            $data_checkmark['class'] = $classes;

            $data['units'][] = $data_checkmark;
        }
        $classes = 'checkmarks';
        if ($isactivitylevel) {
            $classes .= ' activity';
        }

        $data['class'] = $classes;

        return $data;
    }

    /**
     * Used to make data accessible to JS. There are also server-side checks on the relevant capabilities.
     *
     * @return string
     * @throws coding_exception
     */
    public function print_hidden_data() {
        global $CFG;

        $data = array(
            'wwwroot' => $CFG->wwwroot,
            'sesskey' => sesskey(),
            'can-interact' => (int) has_capability('mod/goodhabits:manage_entries', $this->page->context),
            'can-manage' => (int) has_capability('mod/goodhabits:manage_activity_habits', $this->page->context),
        );

        $datatext = '';
        foreach ($data as $key => $val) {
            $datatext .= ' data-'.$key.'="'.$val.'" ';
        }

        $hiddendata = '<div class="goodhabits-hidden-data" '.$datatext.'></div> ';

        $langstringids = array(
             'entry_for', 'cancel', 'save'
        );

        $gridstringids = array(
            'imagetitle', 'xlabel', 'ylabel', 'x_small_label_left', 'x_small_label_center', 'x_small_label_right',
            'y_small_label_bottom', 'y_small_label_center', 'y_small_label_top', 'x_select_label', 'y_select_label',
            'x_default', 'y_default', 'overlay_1_1', 'overlay_1_2', 'overlay_1_3', 'overlay_2_1', 'overlay_2_2',
            'overlay_2_3', 'overlay_3_1', 'overlay_3_2', 'overlay_3_3'
        );

        $langstringids = array_merge($langstringids, $gridstringids);

        $datalang = gh\Helper::lang_string_as_data($langstringids);

        $hiddenlangstrings = '<div class="goodhabits-hidden-lang" '.$datalang.'></div> ';

        return $hiddendata . $hiddenlangstrings;
    }

    /**
     * Generates the button for admin to manage Activity-level habits.
     *
     * @param int $instanceid
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function print_manage_activity_habits($instanceid) {
        $params = array('instance' => $instanceid, 'level' => 'activity');
        $url = new moodle_url('/mod/goodhabits/manage_habits.php', $params);
        $text = get_string('manage_activity_habits', 'mod_goodhabits');
        echo $this->print_link_as_form($url, $text);
    }

    /**
     * Generates the button for admin to view others' entries.
     *
     * @param int $instanceid
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function print_review_entries($instanceid) {
        $params = array('instance' => $instanceid);
        $url = new moodle_url('/mod/goodhabits/review.php', $params);
        $text = get_string('review_entries', 'mod_goodhabits');
        echo $this->print_link_as_form($url, $text);
    }

    public function print_exit_mobile_view($instanceid) {
        $params = array('g' => $instanceid);
        $url = new moodle_url('/mod/goodhabits/view.php', $params);
        $text = get_string('exit_mobile_view', 'mod_goodhabits');
        echo $this->print_link_as_form($url, $text, 'exit-mobile-view');
    }

    public function print_mobile_view($instanceid) {
        $params = array('g' => $instanceid);
        $params['layout'] = gh\Helper::LAYOUT_BASIC_MOBILE;
        $url = new moodle_url('/mod/goodhabits/view.php', $params);
        $text = get_string('mobile_view', 'mod_goodhabits');
        echo $this->print_link_as_form($url, $text, 'mobile-view');
    }

    /**
     * Generates the button for users to manage their habits.
     *
     * @param int $instanceid
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function print_manage_habits($instanceid) {
        $params = array('instance' => $instanceid);
        $url = new moodle_url('/mod/goodhabits/manage_habits.php', $params);
        $text = get_string('manage_habits', 'mod_goodhabits');
        echo $this->print_link_as_form($url, $text);
    }

    /**
     * Generates the button for users to manage their breaks.
     *
     * @param int $instanceid
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function print_manage_personal_breaks($instanceid) {
        $params = array('instance' => $instanceid);
        $url = new moodle_url('/mod/goodhabits/manage_breaks.php', $params);
        $text = get_string('manage_breaks', 'mod_goodhabits');
        echo $this->print_link_as_form($url, $text);
    }

    /**
     * Generates a home link button.
     *
     * @param bool|string $name
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function print_home_link($name = false) {
        $instanceid = required_param('instance', PARAM_INT);
        $params = array('g' => $instanceid);
        $url = new moodle_url('/mod/goodhabits/view.php', $params);
        if (!$name) {
            $name = get_string('pluginname', 'mod_goodhabits');
        }
        $text = get_string('home_link', 'mod_goodhabits', $name);
        echo $this->print_link_as_form($url, $text);
    }

    /**
     * Given a URL and text, returns a form with a submit button using the text.
     *
     * @param moodle_url $url
     * @param string $text
     */
    private function print_link_as_form(moodle_url $url, $text, $class = 'manage-breaks-form') {
        $url = $url->out();
        $submit = "<input type='submit' value='$text' />";
        $form = "<br /><form class='$class' method='post' action='$url'>$submit</form>";
        echo $form;
    }

    /**
     * Generates output when there have been no habits set up.
     *
     * @throws coding_exception
     */
    protected function print_no_habits() {
        $string = get_string('no_habits', 'mod_goodhabits');
        echo html_writer::div($string, 'no-habits');
    }

    /**
     * Generates HTML for the activity name and intro.
     *
     * @param object $instance
     */
    public function print_act_intro($instance) {
        $string = html_writer::div($instance->name, 'intro-name', array('id' => 'intro-name'));
        $string .= html_writer::div($instance->intro, 'intro-intro');
        echo html_writer::div($string, 'intro');
    }

    /**
     * Generates HTML for the review intro.
     *
     * @param $fullname
     * @throws coding_exception
     */
    public function print_review_intro($fullname) {
        $string = get_string('review_entries_name', 'mod_goodhabits', $fullname);
        $string = html_writer::div($string, 'intro-name', array('id' => 'intro-name'));
        echo html_writer::div($string, 'intro');
    }

    /**
     * Generates the overall calendar area using a mustache template.
     *
     * @param $calendar
     * @param $instanceid
     * @param $habits
     * @param $extraclasses
     * @param $userid
     * @return void|null
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function print_templated_calendar_area($calendar, $instanceid, $habits, $extraclasses = array(), $userid = null)
    {
        global $OUTPUT;
        if (empty($habits)) {
            $this->print_no_habits();
            return null;
        }

        $template_data = [];

        $template_data['calendar'] = $this->get_calendar_data($calendar, $instanceid, $userid);

        $template_data['habits'] = $this->get_habits_data($calendar, $habits, $userid);
        $template_data['extra_classes'] = $extraclasses;

        echo $OUTPUT->render_from_template('mod_goodhabits/calendar_area', $template_data);
    }

    public function print_viewport_too_small_message() {
        $msg = get_string('small_viewport_message', 'mod_goodhabits');
        $out = html_writer::div($msg, 'viewport-too-small');
        return $out;
    }
}
