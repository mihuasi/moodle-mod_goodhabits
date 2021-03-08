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

defined('MOODLE_INTERNAL') || die();

class mod_goodhabits_renderer extends plugin_renderer_base {

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

        //$html .= html_writer::link($backurl, html_writer::div('', 'arrow-left'));
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

    public function print_habits(gh\FlexiCalendar $calendar, $habits) {
        $arr = array();
        foreach ($habits as $habit) {
            $arr[] = $this->print_habit($calendar, $habit);
        }

        return '<div class="habits">' . implode('', $arr) . '</div>';
    }

    public function print_habit(gh\FlexiCalendar $calendar, gh\Habit $habit) {
        global $PAGE;
        $html = "<div class='habit habit-".$habit->id."'>";

        $editglobal = has_capability('mod/goodhabits:manage_activity_habits', $PAGE->context);
        $editpersonal = has_capability('mod/goodhabits:manage_personal_habits', $PAGE->context);
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

        $html .= $this->print_checkmarks($calendar, $habit);

        $html .= '        <div class="clear-both"></div>';

        $html .= '    </div>';

        $html .= '    <div class="clear-both"></div>';

        $html .= "</div>";

        $html .= '<div class="habit-grid-container habit-grid-container-'.$habit->id.'"></div>';

        return $html;
    }

    private function print_checkmarks(gh\FlexiCalendar $calendar, gh\Habit $habit) {
        global $USER, $PAGE;

        $html = '';

        $displayset = $calendar->get_display_set();

        $entries = $habit->get_entries($USER->id, $calendar->get_period_duration());

        $canmanageentries = has_capability('mod/goodhabits:manage_entries', $PAGE->context);

        $isactivitylevel = $habit->is_activity_habit();

        foreach ($displayset as $unit) {
            $dataxytxt = '';
            $txt = '<div class="empty-day">  </div>';
            $timestamp = $unit->getTimestamp();
            $isinbreak = gh\BreaksHelper::is_in_a_break($timestamp);
            $classxy = 'noxy';
            if (isset($entries[$timestamp])) {
                $entry = $entries[$timestamp];
                $xval = $entry->x_axis_val;
                $yval = $entry->y_axis_val;
                $dataxytxt = ' data-x="'. $xval .'" data-y="'. $yval .'" ';
                $txt = $xval . ' / ' . $yval;
                $classxy = 'x-val-' . $xval . ' y-val-' . $yval;
            }

            $caninteract = $canmanageentries AND !$isinbreak;
            $caninteractclass = ($caninteract) ? '' : ' no-interact ';

            $classes = 'checkmark ' . $caninteractclass . ' ' . $classxy;
            if ($isinbreak) {
                $classes .= ' is-in-break';
            }
            $html .= '<div class="' . $classes . '" data-timestamp="'. $timestamp .'" '.$dataxytxt.'>';
            $html .= $txt . '</div>';
        }
        $classes = 'checkmarks';
        if ($isactivitylevel) {
            $classes .= ' activity';
        }

        return "<div class='$classes' data-id='".$habit->id."'>$html</div>";
    }

    public function print_module($calendar, $habits) {
        $html = "<div class='goodhabits-container'>$calendar
                       <div class=\"clear-both\"></div>
                 $habits
                 </div><br /><br /> ";
        return $html;
    }

    private function print_back_link(moodle_url $url, $text) {
        return html_writer::link($url, '&#8592; ' . $text);
    }

    private function print_forward_link(moodle_url $url, $text) {
        return html_writer::link($url, $text . ' &#8594;');
    }

    public function print_hidden_data() {
        global $CFG, $PAGE;

        $data = array(
            'wwwroot' => $CFG->wwwroot,
            'sesskey' => sesskey(),
            'can-interact' => (int) has_capability('mod/goodhabits:manage_entries', $PAGE->context),
            'can-manage' => (int) has_capability('mod/goodhabits:manage_activity_habits', $PAGE->context),
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

    public function time_period_selector($options, $selected) {
        $optionstxt = '';
        foreach ($options as $k => $option) {
            $selectedtxt = ($selected == $k) ? ' selected="selected" ' : '';
            $optionstxt .= "<option value='$k' $selectedtxt>$option</option>";
        }

        $sessionkey = $this->print_hidden_session_key();

        $select = " <select name='time-period-selector' autocomplete='off'>$optionstxt</select>";

        $submittxt = get_string('submit_text_change_cal', 'mod_goodhabits');

        $submit = "<input type='submit' value='$submittxt'> </input>";
        $html = "<form> $sessionkey {$select} $submit </form>";
        return $html;
    }

    public function print_hidden_session_key() {
        $sessionkey = sesskey();
        return "<input type='hidden' name='sesskey' value='$sessionkey'> </input>";
    }

    public function print_manage_activity_habits($instanceid) {
        $params = array('instance' => $instanceid, 'level' => 'activity');
        $url = new moodle_url('/mod/goodhabits/manage_habits.php', $params);
        $text = get_string('manage_activity_habits', 'mod_goodhabits');
        echo $this->print_link_as_form($url, $text);
    }

    public function print_manage_habits($instanceid) {
        $params = array('instance' => $instanceid);
        $url = new moodle_url('/mod/goodhabits/manage_habits.php', $params);
        $text = get_string('manage_habits', 'mod_goodhabits');
        echo $this->print_link_as_form($url, $text);
    }

    public function print_manage_personal_breaks($instanceid) {
        $params = array('instance' => $instanceid);
        $url = new moodle_url('/mod/goodhabits/manage_breaks.php', $params);
        $text = get_string('manage_breaks', 'mod_goodhabits');
        echo $this->print_link_as_form($url, $text);
    }

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

    public function print_link_as_form(moodle_url $url, $text) {
        $url = $url->out();
        $submit = "<input type='submit' value='$text' />";
        $form = "<br /><form class='manage-breaks-form' method='post' action='$url'>$submit</form>" ;
        echo $form;
    }

    public function print_no_habits() {
        $string = get_string('no_habits', 'mod_goodhabits');
        echo html_writer::div($string, 'no-habits');
    }

    public function print_act_intro($instance) {
        $string = html_writer::div($instance->name, 'intro-name');
        $string .= html_writer::div($instance->intro, 'intro-intro');
        echo html_writer::div($string, 'intro');
    }
}