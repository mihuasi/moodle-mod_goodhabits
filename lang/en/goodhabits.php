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
 * Plugin strings are defined here.
 *
 * @package     mod_goodhabits
 * @category    string
 * @copyright   2021 Joe Cape <joe.sc.cape@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Good Habits';
$string['modulename'] = 'Good Habits Activity';
$string['modulenameplural'] = 'Good Habits Activities';
$string['goodhabitsname'] = 'Name';
$string['goodhabitsname_help'] = 'Name';
$string['goodhabitssettings'] = 'Settings';

$string['pluginadministration'] = 'Good Habits Admin';

$string['by_day'] = 'By day';
$string['by_week'] = 'By week';
$string['x_days'] = '{$a} days';
$string['submit_text_change_cal'] = 'Change Calendar';
$string['add_new_habit_name'] = 'Name';
$string['add_new_habit_desc'] = 'Description';
$string['add_new_habit'] = 'Add New Habit';
$string['week_displayunit'] = 'WEEK';
$string['settings_heading'] = 'Good Habits';
$string['settings_desc'] = 'The intention of this plugin is to help track habits over time. <a href="{$a}">Click here to start using.</a>
<p></p><p></p><p>A quick guide to how to use this is <a href="https://www.youtube.com/watch?v=t5myhN3wvAc">available here</a>.</p>';

$string['good_habits:manage_entries'] = 'Manage Entries';
$string['good_habits:manage_global_habits'] = 'Manage Global Habits';
$string['good_habits:view'] = 'View';
$string['delete_all_entries'] = 'Delete All My Habit Entries';
$string['confirm_delete_entries_text'] = 'Do you really want to delete all of your habit entries?';

$string['privacy:metadata:mod_goodhabits_entry'] = 'Entries in the habit-tracking calendar.';
$string['privacy:metadata:userid'] = 'The ID of the user with this habit entry.';
$string['privacy:metadata:x_axis_val'] = 'The X-axis value of this habit entry.';
$string['privacy:metadata:y_axis_val'] = 'The Y-axis value of this habit entry.';
$string['mod_goodhabits_subcontext'] = 'Habit Entries';

$string['habit_added'] = 'Habit Added';
$string['habit_entries_deleted'] = 'All Habit Entries Removed';
$string['add_new_habit_personal'] = 'Add New Personal Habit';
$string['add_new_habit_global'] = '[Admin] Add New Global Habit';

$string['entry_for'] = 'Entry for:';
$string['cancel'] = 'Cancel';
$string['save'] = 'Save';

$string['imagetitle'] = 'Place using the drop-downs';
$string['xlabel'] = 'Effort';
$string['ylabel'] = 'Outcome';
$string['x_small_label_left'] = 'Low';
$string['x_small_label_center'] = 'Medium';
$string['x_small_label_right'] = 'High';
$string['y_small_label_bottom'] = 'Low';
$string['y_small_label_center'] = 'Medium';
$string['y_small_label_top'] = 'High';
$string['x_select_label'] = 'Effort';
$string['y_select_label'] = 'Outcome';
$string['x_default'] = 'Select';
$string['y_default'] = 'Select';
$string['overlay_1_1'] = 'Easy mastery';
$string['overlay_1_2'] = 'High rewards without breaking a sweat!';
$string['overlay_1_3'] = 'Working hard to see good results';
$string['overlay_2_1'] = 'Casual achievement';
$string['overlay_2_2'] = 'Achievement through effort';
$string['overlay_2_3'] = 'Working hard to achieve';
$string['overlay_3_1'] = 'You get what you put in!';
$string['overlay_3_2'] = 'Sticking at it';
$string['overlay_3_3'] = 'Persevering with a challenge';

$string['manage_breaks'] = 'Manage Breaks';
$string['manage_habits'] = 'Manage Habits';
$string['manage_habits_title'] = '{$a} - Manage Habits';
$string['manage_activity_habits_title'] = '{$a} - [Admin] Manage Activity Habits';
$string['manage_breaks_title'] = '{$a} - Manage Breaks';
$string['fromdate_text'] = 'From date';
$string['todate_text'] = 'To date';
$string['addbreak_submit_text'] = 'Add Break';
$string['addhabit_submit_text'] = 'Add Habit';
$string['activity_addhabit_submit_text'] = 'Add Activity Habit';
$string['edithabit_submit_text'] = 'Edit Habit';
$string['activity_edithabit_submit_text'] = 'Edit Activity Habit';
$string['home_link'] = 'Go back to {$a}';
$string['actions'] = 'Actions';
$string['delete'] = 'Delete';
$string['edit'] = 'Edit';
$string['break_deleted'] = 'Break Deleted';
$string['break_added'] = 'Break Added';
$string['no_habits'] = '[No habits have been set up yet]';
$string['habit_level'] = 'Ownership';
$string['new_habit_name'] = 'Habit Name';
$string['new_habit_desc'] = 'Habit Description';
$string['habit_deleted'] = 'Habit Deleted';
$string['habit_edited'] = 'Habit Edited';
$string['habit_entries_deleted'] = 'Habit Entries Deleted';
$string['habit_num_entries'] = 'Number of Entries';
$string['delete_entries'] = 'Delete Entries';
$string['js_confirm'] = 'Are you sure you want to do this? {$a}';
$string['js_confirm_deletehabit'] = 'This will delete the habit {$a}.';
$string['js_confirm_deletehabitentries'] = 'This will delete all your habit entries for {$a}.';
$string['js_confirm_deletebreak'] = 'This will delete the break.';
$string['freq'] = 'Frequency';
$string['freq_help'] = 'How often you want people using this to be adding Habit Entries.';

$string['activity'] = 'Activity';
$string['personal'] = 'Personal';
$string['name_append_is_activity'] = '{$a}';
$string['habit_name_title_activity'] = 'This a habit that has been set up for everyone in the activity {$a} so you cannot edit it.';
$string['manage_activity_habits'] = '[Admin] Manage Activity Habits';
$string['activity_title_text'] = 'This is an activity habit visible to everyone using this activity';
$string['personal_title_text'] = 'This is one of your personal habits only being used by you';
$string['showhide'] = 'Show';
$string['habit_not_published_title'] = 'The habit {$a} is currently hidden.';
