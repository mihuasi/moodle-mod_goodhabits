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
     * Generates the top 'ribbon' in the activity, showing a series of days or weeks.
     *
     * @param gh\FlexiCalendar $calendar
     * @param int $instanceid
     * @return string
     * @throws moodle_exception
     */
    public function print_calendar(gh\FlexiCalendar $calendar, $instanceid) {
        $displayset = $calendar->get_display_set();

        $periodduration = $calendar->get_period_duration();

        $html = "<div class='calendar' data-period-duration='$periodduration'>";
        $html .= "    <div class='dates'>";

        $year = gh\Helper::display_year($displayset);

        $html .= "        <div class='year'>$year</div>";

        $days = array();

        $backurl = $calendar->get_back_url($instanceid);
        $forwardurl = $calendar->get_forward_url($instanceid);

        $html .= "<div class='arrow-left-container'><div class=\"arrow-left\">
<a href=\"$backurl\">
        <span class=\"link-spanner\"></span>
    </a>
</div></div>";

        foreach ($displayset as $k => $unit) {
            $month = $unit->display_month();

            $display = $unit->display_unit();
            $topline = $display['topLine'];

            $singlelinedisplay = $topline . ' ' . $display['bottomLine'];

            $unitcontents = '<div class="top-line">'.$topline.'</div>';
            $unitcontents .= '<div class="bottom-line">'.$display['bottomLine'].'</div>';

            $monthhtml = ($month) ? '<div class="month">'.$month.'</div>' : '';
            $implode = implode(' ', $unit->get_classes());
            $day = '<div data-text="'.$singlelinedisplay.'" class="time-unit '. $implode .'">';
            $day .= $monthhtml . $unitcontents.'</div>';
            $days[] = $day;
        }

        $html .= implode('', $days);

        if ($forwardurl) {
            $html .= "<div class='arrow-right-container'><div class=\"arrow-right\">
<a href=\"$forwardurl\">
        <span class=\"link-spanner right\"></span>
    </a>
</div></div>";
        }

        $html .= "    </div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * Generates the list of habits.
     *
     * @param gh\FlexiCalendar $calendar
     * @param array $habits
     * @param null $userid
     * @return string
     * @throws coding_exception
     */
    public function print_habits(gh\FlexiCalendar $calendar, $habits, $userid = null) {
        $arr = array();
        foreach ($habits as $habit) {
            $arr[] = $this->print_habit($calendar, $habit, $userid);
        }

        return '<div class="habits">' . implode('', $arr) . '</div>';
    }

    /**
     * Generates a single habit - the name on the LHS and the "habit entries" on the RHS.
     *
     * @param gh\FlexiCalendar $calendar
     * @param gh\Habit $habit
     * @param int $userid
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function print_habit(gh\FlexiCalendar $calendar, gh\Habit $habit, $userid = null) {
        $html = "<div class='habit habit-".$habit->id."'>";

        $editglobal = has_capability('mod/goodhabits:manage_activity_habits', $this->page->context);
        $editpersonal = has_capability('mod/goodhabits:manage_personal_habits', $this->page->context);
        $isactivitylevel = $habit->is_activity_habit();

        $canmanage = false;
        if ($isactivitylevel AND $editglobal) {
            $canmanage = true;
        }
        if (!$isactivitylevel AND $editpersonal) {
            $canmanage = true;
        }

        $canmanageclass = ($canmanage) ? ' can-edit ' : '';

        $data = ' data-habit-id="'.$habit->id.'" data-is-global="'.$isactivitylevel.'" ';
        $activityclass = ($isactivitylevel) ? 'activity' : 'personal';
        $titletextid = $activityclass . '_title_text';
        $titletext = get_string($titletextid, 'mod_goodhabits');
        $title = " title='$titletext' ";

        $html .= '<div '.$title.' class="streak ' . $canmanageclass . ' ' . $activityclass . '" ' . $data . '></div>';

        $html .= '<div class="title"><div class="habit-name">'.format_text($habit->name).'</div>';
        $html .= '    <div class="description">'.format_text($habit->description).'</div></div>';

        $html .= '    <div class="time-line">';

        $html .= $this->print_checkmarks($calendar, $habit, $userid);

        $html .= '        <div class="clear-both"></div>';

        $html .= '    </div>';

        $html .= '    <div class="clear-both"></div>';

        $html .= "</div>";

        $html .= '<div class="habit-grid-container habit-grid-container-'.$habit->id.'"></div>';

        return $html;
    }

    /**
     * 'Checkmarks' here are the circular entities used to manage habit entries.
     *
     * @param gh\FlexiCalendar $calendar
     * @param gh\Habit $habit
     * @param int $userid
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    private function print_checkmarks(gh\FlexiCalendar $calendar, gh\Habit $habit, $userid = null) {
        global $USER;

        $html = '';

        $displayset = $calendar->get_display_set();

        $isreview = (int) $userid;

        $userid = ($userid) ? $userid : $USER->id;
        $entries = $habit->get_entries($userid, $calendar->get_period_duration());

        $canmanageentries = has_capability('mod/goodhabits:manage_entries', $this->page->context);

        $isactivitylevel = $habit->is_activity_habit();

        foreach ($displayset as $unit) {
            $dataxytxt = '';
            $txt = '<div class="empty-day">  </div>';
            $timestamp = $unit->getTimestamp();
            $isinbreak = gh\BreaksHelper::is_in_a_break($timestamp);
            $classxy = 'noxy';
            $title = get_string('checkmark_title_empty', 'mod_goodhabits');
            if (isset($entries[$timestamp])) {
                $entry = $entries[$timestamp];
                $xval = $entry->x_axis_val;
                $yval = $entry->y_axis_val;
                $title = get_string('checkmark_title', 'mod_goodhabits', $entry);
                $dataxytxt = ' data-x="'. $xval .'" data-y="'. $yval .'" ';
                $txt = $xval . ' / ' . $yval;
                $separator = "<span class='xy-separator'>/</span>";
                $txt = "<span class='x-val'>$xval</span> $separator <span class='y-val'>$yval</span>";
                $classxy = 'x-val-' . $xval . ' y-val-' . $yval;
            }

            // Only show title text if in review, as otherwise we will need it to update as entries are saved.
            $titletxt = ($isreview) ? ' title="'.$title.'" ' : '';

            $caninteract = $canmanageentries AND !$isinbreak;
            $caninteractclass = ($caninteract) ? '' : ' no-interact ';

            $classes = 'checkmark ' . $caninteractclass . ' ' . $classxy;
            if ($isinbreak) {
                $classes .= ' is-in-break';
            }
            $html .= '<div ' . $titletxt . ' class="' . $classes . '" data-timestamp="'. $timestamp .'" '.$dataxytxt.'>';
            $html .= $txt . '</div>';
        }
        $classes = 'checkmarks';
        if ($isactivitylevel) {
            $classes .= ' activity';
        }

        return "<div class='$classes' data-id='".$habit->id."'>$html</div>";
    }

    /**
     * Used to arrange the calendar and the habits html.
     *
     * @param string $calendar
     * @param string $habits
     * @param array $extraclasses
     * @return string
     */
    public function print_module($calendar, $habits, $extraclasses = array()) {
        $extraclasses = implode(' ', $extraclasses);
        $html = "<div class='goodhabits-container $extraclasses' id='goodhabits-container'>$calendar
                       <div class=\"clear-both\"></div>
                 $habits
                 </div><br /><br /> ";
        return $html;
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
    private function print_link_as_form(moodle_url $url, $text) {
        $url = $url->out();
        $submit = "<input type='submit' value='$text' />";
        $form = "<br /><form class='manage-breaks-form' method='post' action='$url'>$submit</form>";
        echo $form;
    }

    /**
     * Generates output when there have been no habits set up.
     *
     * @throws coding_exception
     */
    public function print_no_habits() {
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
     * Generates the overall calendar area.
     *
     * @param $calendar
     * @param $instanceid
     * @param $habits
     * @param array $extraclasses
     * @param int $userid
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function print_calendar_area($calendar, $instanceid, $habits, $extraclasses = array(), $userid = null) {
        if ($habits) {
            $calendarhtml = $this->print_calendar($calendar, $instanceid);

            $habitshtml = $this->print_habits($calendar, $habits, $userid);

            echo $this->print_module($calendarhtml, $habitshtml, $extraclasses);
        } else {
            echo $this->print_no_habits();
        }
    }

    public function print_viewport_too_small_message() {
        $msg = get_string('small_viewport_message', 'mod_goodhabits');
        $out = html_writer::div($msg, 'viewport-too-small');
        return $out;
    }
}