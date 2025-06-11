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
     * @param \mod_goodhabits\calendar\FlexiCalendar $calendar
     * @param $instanceid
     * @return array
     * @throws moodle_exception
     */
    protected function get_calendar_data(gh\calendar\FlexiCalendar $calendar, $instanceid, $userid) {
        global $USER;
        $userid = ($userid) ? $userid : $USER->id;

        $data = [];
        $displayset = $calendar->get_display_set();

        $periodduration = $calendar->get_period_duration();

        $data['periodduration'] = $periodduration;

        $year = gh\Helper::display_year($displayset);

        $to_date = $calendar->get_to_date();

        $backurl = $calendar->get_back_url($instanceid);
        $backurl_small_screen = $calendar->get_back_url($instanceid, 4);
        $forwardurl = $calendar->get_forward_url($instanceid);
        $forwardurl_small_screen = $calendar->get_forward_url($instanceid, 4);

        $data['year'] = $year;
        $data['backurl'] = $backurl;
        $data['backurl_small_screen'] = $backurl_small_screen;
        $data['forwardurl_small_screen'] = $forwardurl_small_screen;
        $data['forwardurl'] = $forwardurl;
        $data['flexi_cal_units'] = [];

        $count = 1;

        foreach ($displayset as $k => $unit) {
            $display = $unit->display_unit();

            $limits = $unit->get_limits();
            $missing = gh\Helper::get_habits_with_missing_entries($instanceid, $userid, $limits);
            $all_complete = (empty($missing));

            $month = $unit->display_month();

            $classes = $unit->get_classes($userid);
            $classes[] = 'count-' . $count;
            $imploded_classes = implode(' ', $classes);

            $flexi_cal_unit = [];
            $flexi_cal_unit['top_line'] = $display['topLine'];
            $flexi_cal_unit['bottom_line'] = $display['bottomLine'];
            $flexi_cal_unit['month'] = $month;
            $flexi_cal_unit['class'] = $imploded_classes;
            $flexi_cal_unit['skip_url'] = $unit->skip_url($instanceid, $to_date);
            $flexi_cal_unit['answer_questions_url'] = $unit->answer_questions_url($instanceid);
            $flexi_cal_unit['all_complete'] = $all_complete;

            $data['flexi_cal_units'][] = $flexi_cal_unit;

            $count ++;
        }

        return $data;
    }


    /**
     * Gets template data for the list of habits.
     * @param \mod_goodhabits\calendar\FlexiCalendar $calendar
     * @param $habits
     * @param $userid
     * @return array
     */
    protected function get_habits_data(gh\calendar\FlexiCalendar $calendar, $habits, $userid = null, $instanceid) {
        $data = [];
        foreach ($habits as $habit) {
            $data[] = $this->get_habit_data($calendar, $habit, $userid, $instanceid);
        }

        return $data;
    }

    /**
     * Gets template data for a single habit - the name on the LHS and the "habit entries" on the RHS.
     *
     * @param \mod_goodhabits\calendar\FlexiCalendar $calendar
     * @param \mod_goodhabits\habit\Habit $habit
     * @param $userid
     * @return array
     * @throws coding_exception
     */
    public function get_habit_data(gh\calendar\FlexiCalendar $calendar, gh\habit\Habit $habit, $userid = null, $instanceid) {
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
        $habit_data['title_text'] = trim($titletext);
        $habit_data['habit_name'] = trim(format_text($habit->name));
        $habit_data['habit_desc'] = trim(format_text($habit->description));
        $effort_avg = gh\Helper::get_avg('x_axis_val', $habit, $userid, $instanceid);
        $outcome_avg = gh\Helper::get_avg('y_axis_val', $habit, $userid, $instanceid);

        $habit_data['effort_avg'] = round($effort_avg, 1);
        $habit_data['outcome_avg'] = round($outcome_avg, 1);
        $habit_data['effort_avg_rounded'] = round($effort_avg);
        $habit_data['outcome_avg_rounded'] = round($outcome_avg);

        $first_entry = gh\Helper::get_first_entry($habit, $userid, $instanceid, $calendar);
        $habit_data['has_first_entry'] = (bool) $first_entry;
        $timestamp = $first_entry->endofperiod_timestamp;

        $formatteddisplay = date('d/m/y', $timestamp);
        $mysqldate = date('Y-m-d', $timestamp);
        $url = new moodle_url('/mod/goodhabits/view.php', [
            'g' => $instanceid,
            'toDate' => $mysqldate
        ]);

        $habit_data['first_entry_display'] = $formatteddisplay;
        $habit_data['first_entry_url'] = $url->out();

        $current_period_end = $calendar->get_latest_limit();

        $url = new moodle_url('/mod/goodhabits/historical_data.php', [
            'instance' => $instanceid,
            'habit_id' => $habit->id,
            'end_time' => $current_period_end
        ]);

        $habit_data['historical_data_url'] = $url->out();

        $checkmarks = $this->get_checkmarks_data($calendar, $habit, $userid, $instanceid);
        $habit_data['checkmarks'] = $checkmarks;

        return $habit_data;
    }

    /**
     * 'Checkmarks' here are the circular entities used to manage habit entries.
     *
     * @param \mod_goodhabits\calendar\FlexiCalendar $calendar
     * @param \mod_goodhabits\habit\Habit $habit
     * @param $userid
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function get_checkmarks_data(gh\calendar\FlexiCalendar $calendar, gh\habit\Habit $habit, $userid = null, $instanceid) {
        global $USER;

        $data = [];

        $data['units'] = [];

        $displayset = $calendar->get_display_set();

        $isreview = (int) $userid;

        $userid = ($userid) ? $userid : $USER->id;
        $entries = $habit->get_entries($userid, $calendar);

        $prefs_mgr = new gh\PreferencesManager($instanceid, $userid);
        $pref_show_scores = $prefs_mgr->show_scores();
        if ($isreview) {
            $pref_show_scores = true;
        }

        $canmanageentries = has_capability('mod/goodhabits:manage_entries', $this->page->context);

        $isactivitylevel = $habit->is_activity_habit();

        $count = 1;

        foreach ($displayset as $unit) {
            $show_scores = $pref_show_scores;
            $data_checkmark = [];
            $timestamp = $unit->getTimestamp();
            $isinbreak = gh\BreaksHelper::is_in_a_break($timestamp, $userid);
            $classxy = 'noxy';
            $title = get_string('checkmark_title_empty', 'mod_goodhabits');
            $is_filled = false;
            $entry = $unit->get_closest_entry($entries);

            if ($entry) {
                $is_filled = true;
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
                $show_scores = false;
            }

            if (!$show_scores) {
                $classes .= ' hide-scores';
            }

            $classes .= ' count-' . $count;

            $data_checkmark['is_filled'] = $is_filled;
            $data_checkmark['is_review'] = $isreview;
            $data_checkmark['title'] = $title;
            $data_checkmark['x'] = $xval ?? null;
            $data_checkmark['y'] = $yval ?? null;
            $data_checkmark['timestamp'] = $timestamp;
            $data_checkmark['class'] = $classes;

            $data['units'][] = $data_checkmark;
            $count ++;
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
    public function print_hidden_data($instanceid) {
        global $CFG, $USER;

        $pref_mgr = new gh\PreferencesManager($instanceid, $USER->id);

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
            'x_default', 'y_default'
        );

        $langstringids = array_merge($langstringids, $gridstringids);

        $datalang = gh\Helper::lang_string_as_data($langstringids);

        $strings = [];
        $overlay_strings = ['overlay_1_1', 'overlay_1_2', 'overlay_1_3', 'overlay_2_1', 'overlay_2_2',
            'overlay_2_3', 'overlay_3_1', 'overlay_3_2', 'overlay_3_3'];
        foreach ($overlay_strings as $overlay_string) {
            $strings[$overlay_string] = $pref_mgr->get_preferred_string($overlay_string);
        }

        $datalang .= gh\Helper::strings_as_data($strings);

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
    public function print_review_entries($instanceid, $string_id) {
        $params = array('instance' => $instanceid);
        $url = new moodle_url('/mod/goodhabits/review.php', $params);
        $text = gh\Helper::get_string($string_id);
        echo $this->print_link_as_form($url, $text);
    }

    public function print_see_historical_data($instanceid, $subject_id = null) {
        $params = array('instance' => $instanceid);
        if ($subject_id) {
            $params['subject_id'] = $subject_id;
        }
        $url = new moodle_url('/mod/goodhabits/historical_data.php', $params);
        $text = gh\Helper::get_string('historical_data');
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

    public function print_preferences($instanceid) {
        $params = array('instance' => $instanceid);
        $url = new moodle_url('/mod/goodhabits/preferences.php', $params);
        $text = gh\Helper::get_string('manage_prefs_title');
        echo $this->print_link_as_form($url, $text);
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
    protected function print_no_habits($instanceid) {
        $string = get_string('no_habits', 'mod_goodhabits');
        $url = new moodle_url('/mod/goodhabits/manage_habits.php', ['instance' => $instanceid]);
        $link = html_writer::link($url, $string);
        echo html_writer::div($link, 'no-habits');
    }

    protected function print_review_no_habits($instanceid)
    {
        $string = get_string('review_no_habits', 'mod_goodhabits');
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

//    /**
//     * Generates HTML for the review intro.
//     *
//     * @param $fullname
//     * @throws coding_exception
//     */
//    public function print_review_intro($fullname, $accessing_as_string_id) {
//        $accessing_as_text = gh\Helper::get_string($accessing_as_string_id);
//        $accessing_as = html_writer::div($accessing_as_text, 'accessing-as');
//        $string = get_string('review_entries_name', 'mod_goodhabits', $fullname);
//        $string = html_writer::div($string, 'intro-name', array('id' => 'intro-name'));
//        $string .= $accessing_as;
//        echo html_writer::div($string, 'intro');
//    }

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
    public function print_templated_calendar_area($calendar, $instanceid, $habits, $extraclasses = array(), $userid = null, $review = false)
    {
        global $OUTPUT;
        if (empty($habits)) {
            if ($review) {
                $this->print_review_no_habits($instanceid);
            } else {
                $this->print_no_habits($instanceid);
            }

            return null;
        }

        $template_data = [];

        $template_data['completion'] = $this->get_completion_data($calendar, $instanceid, $userid);

        $template_data['calendar'] = $this->get_calendar_data($calendar, $instanceid, $userid);

        $template_data['habits'] = $this->get_habits_data($calendar, $habits, $userid, $instanceid);
        $template_data['extra_classes'] = implode(' ', $extraclasses);
        if (!$review) {
            $template_data['help'] = $this->get_help_data($calendar, $instanceid, $userid);
        }

        echo $OUTPUT->render_from_template('mod_goodhabits/calendar_area', $template_data);
    }

    protected function get_help_data(gh\calendar\FlexiCalendar $calendar, $instanceid, $userid)
    {
        global $USER;
        $userid = ($userid) ? $userid : $USER->id;

        $prefs_mgr = new gh\PreferencesManager($instanceid, $userid);
        $enable_help = $prefs_mgr->enable_help();

        $data = [];

        $data['show_help'] = 0;

        if (!$enable_help) {
            return $data;
        }

        // TODO: Switch types of duration string so that it can read: this week, today, etc...
        $data['period_string'] = $calendar->get_period_duration_string($calendar::STRING_TYPE_SINGULAR);
        $answer_latest_string = $calendar->get_period_duration_string($calendar::STRING_TYPE_ANSWER_LATEST);
        $units_with_all_complete = gh\Helper::get_cal_units_with_all_complete($instanceid, $userid);

        $url = new moodle_url('/mod/goodhabits/simple.php', ['g' => $instanceid]);

        $data['show_help'] = 1;
        //TODO: Change this to check for just one entry.
        if (empty($units_with_all_complete)) {
            // None are complete so give getting-started help.

            $text = gh\Helper::get_string('get_started', $answer_latest_string);
            $data['get_started'] = html_writer::link($url, $text);
        } else {
            // TODO: Review whether we need this.
//            $data['show_help'] = 1;
//            $latest_unit = $calendar->get_latest();
//            $latest_complete = gh\Helper::unit_has_all_complete($instanceid, $latest_unit, $userid);
//            if (!$latest_complete) {
//                // The latest is not complete, so link to answer Qs about latest.
//                $text = gh\Helper::get_string('answer_latest_questions', $data['period_string']);
//                $data['answer_latest'] = html_writer::link($url, $text);
//            }
        }

        if (empty($data['get_started']) && empty($data['answer_latest'])) {
            // Then show: Did you know?
            global $OUTPUT;
            $context = [
                'period_string' => $data['period_string'],
                'def_article_string' => $calendar->get_period_duration_string($calendar::STRING_TYPE_DEFINITE_ARTICLE),
                'chosen_string' => $calendar->get_period_duration_string($calendar::STRING_TYPE_CHOSEN),
                'skipped_string' => $calendar->get_period_duration_string($calendar::STRING_TYPE_SKIPPED),
                'skip_help_string' => $calendar->get_period_duration_string($calendar::STRING_TYPE_SKIP_HELP),
                'grid_open_help_string' => $calendar->get_period_duration_string($calendar::STRING_TYPE_GRID_OPEN_HELP),
            ];
            $dyk_help_text = $OUTPUT->render_from_template('mod_goodhabits/help', $context);
            $data['did_you_know'] = $dyk_help_text;
        }

        return $data;
    }

    public function print_viewport_too_small_message() {
        $msg = get_string('small_viewport_message', 'mod_goodhabits');
        $out = html_writer::div($msg, 'viewport-too-small');
        return $out;
    }

    public function get_completion_data(gh\calendar\FlexiCalendar $calendar, $instanceid, $userid)
    {
        global $DB, $USER;

        $userid = ($userid) ? $userid : $USER->id;
        $compl_data = [];
        $period_duration_string = $calendar->get_period_duration_string();
        $completed = gh\Helper::get_cal_units_with_all_complete($instanceid, $userid);
//        $str_obj = new stdClass();
//        $str_obj->period_duration = $period_duration_string;
        $num_complete = count($completed);
//        $str_obj->completed = $num_complete;

        $compl_data['label_num_completed'] = gh\Helper::get_string('label_num_completed', $period_duration_string);
        $compl_data['val_num_completed'] = $num_complete;

        $goodhabits = $DB->get_record('goodhabits', array('id' => $instanceid));

        $to_complete = $goodhabits->completioncalendarunits;
        if ($to_complete) {
            $compl_data['show_remaining'] = 1;
            $num_remaining = $to_complete - $num_complete;
            if ($num_remaining < 0) {
                $num_remaining = 0;
            }
            $compl_data['val_remaining'] = $num_remaining;
        }

        return $compl_data;
    }
}
