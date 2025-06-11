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

$string['pluginname'] = 'Good habits';
$string['modulename'] = 'Good habits activity';
$string['modulenameplural'] = 'Good habits activities';
$string['goodhabitsname'] = 'Name';
$string['goodhabitsname_help'] = 'Name';
$string['goodhabitssettings'] = 'Settings';
$string['general_settings'] = 'General settings';
$string['default_settings'] = 'Activity defaults';
$string['goodhabits:addinstance'] = 'Good habits - add instance';

$string['pluginadministration'] = 'Good habits admin';

$string['by_day'] = 'By day';
$string['by_week'] = 'By week';
$string['x_days'] = '{$a} days';
$string['submit_text_change_cal'] = 'Change calendar';
$string['add_new_habit_name'] = 'Name';
$string['add_new_habit_desc'] = 'Description';
$string['add_new_habit'] = 'Add new habit';
$string['week_displayunit'] = 'WEEK';
$string['settings_heading'] = 'Good habits';
$string['settings_desc'] = 'The intention of this plugin is to help track habits over time. <a href="{$a}">Click here to start using.</a>
<p></p><p></p><p>A quick guide to how to use this is <a href="https://www.youtube.com/watch?v=t5myhN3wvAc">available here</a>.</p>';

$string['good_habits:manage_entries'] = 'Manage entries';
$string['good_habits:manage_global_habits'] = 'Manage global habits';
$string['good_habits:view'] = 'View';
$string['delete_all_entries'] = 'Delete all my habit entries';
$string['confirm_delete_entries_text'] = 'Do you really want to delete all of your habit entries?';

$string['privacy:metadata:mod_goodhabits_entry'] = 'Entries in the habit-tracking calendar.';
$string['privacy:metadata:userid'] = 'The ID of the user with this habit entry.';
$string['privacy:metadata:x_axis_val'] = 'The X-axis value of this habit entry.';
$string['privacy:metadata:y_axis_val'] = 'The Y-axis value of this habit entry.';
$string['mod_goodhabits_subcontext'] = 'Habit entries';

$string['habit_added'] = 'Habit added';
$string['habit_entries_deleted'] = 'All habit entries removed';
$string['add_new_habit_personal'] = 'Add new personal habit';
$string['add_new_habit_global'] = '[Admin] Add new global habit';

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
$string['overlay_1_1'] = 'Stars align';
$string['overlay_1_2'] = 'Doing more with less';
$string['overlay_1_3'] = 'Going far with effort';
$string['overlay_2_1'] = 'Enjoy easy gains';
$string['overlay_2_2'] = 'Going places at a solid pace';
$string['overlay_2_3'] = 'Overcoming challenges';
$string['overlay_3_1'] = 'Every step counts';
$string['overlay_3_2'] = 'Sticking at it';
$string['overlay_3_3'] = 'Persevering with a challenge';

$string['manage_breaks'] = 'Manage breaks';
$string['manage_habits'] = 'Manage habits';
$string['manage_habits_title'] = 'Manage habits';
$string['manage_activity_habits_title'] = '[Admin] Manage habits for everyone';
$string['manage_breaks_title'] = 'Manage breaks';
$string['manage_prefs_title'] = 'Personal preferences';
$string['fromdate_text'] = 'From date';
$string['todate_text'] = 'To date';
$string['addbreak_submit_text'] = 'Add break';
$string['addhabit_submit_text'] = 'Add habit';
$string['activity_addhabit_submit_text'] = 'Add habit for everyone';
$string['edithabit_submit_text'] = 'Edit habit';
$string['activity_edithabit_submit_text'] = 'Edit habit for everyone';
$string['home_link'] = 'Go back to {$a}';
$string['actions'] = 'Actions';
$string['delete'] = 'Delete';
$string['edit'] = 'Edit';
$string['break_deleted'] = 'Break deleted';
$string['break_added'] = 'Break added';
$string['no_habits'] = 'It looks like you don\'t have any habits set up yet - add some here';
$string['review_no_habits'] = 'It looks like this user doesn\'t have any habits set up yet';
$string['habit_level'] = 'Ownership';
$string['new_habit_name'] = 'Habit name';
$string['new_habit_desc'] = 'Description';
$string['habit_type'] = 'Type';
$string['habit_type_activity'] = 'Activity';
$string['habit_type_personal'] = 'Personal';
$string['habit_deleted'] = 'Habit deleted';
$string['habit_edited'] = 'Habit edited';
$string['habit_entries_deleted'] = 'Habit entries deleted';
$string['habit_num_entries'] = 'Number of entries';
$string['delete_entries'] = 'Delete entries';
$string['js_confirm'] = 'Are you sure you want to do this? {$a}';
$string['js_confirm_deletehabit'] = 'This will delete the habit {$a}.';
$string['js_confirm_deletehabitentries'] = 'This will delete all your habit entries for {$a}.';
$string['js_confirm_deletebreak'] = 'This will delete the break.';
$string['freq'] = 'Frequency';
$string['freq_help'] = 'How often you want people using this to be adding Habit Entries.';
$string['freq_desc'] = 'Activity default: How often you want people using this to be adding Habit Entries.';
$string['review_entries'] = 'Review entries';
$string['review_entries_as_admin'] = '[Admin] Review entries';
$string['review_entries_as_peer'] = 'Review peer entries';
$string['review_entries_name'] = 'Review entries [{$a}]';
$string['access_review_entries_as_admin'] = 'You are reviewing as admin';
$string['access_review_entries_as_peer'] = 'Peer review';
$string['select_users'] = 'Select user';
$string['review_select_submit'] = 'Review user entries';
$string['checkmark_title'] = 'Effort: {$a->x_axis_val}
Outcome: {$a->y_axis_val}';
$string['checkmark_title_empty'] = 'No entry for this date';
$string['accessing_review_when_disabled'] = 'Reviews are disabled';

$string['activity'] = 'Activity';
$string['personal'] = 'Personal';
$string['name_append_is_activity'] = '{$a}';
$string['habit_name_title_activity'] = 'This a habit that has been set up for everyone in the activity {$a} so you cannot edit it.';
$string['manage_activity_habits'] = '[Admin] Manage habits for everyone';
$string['activity_title_text'] = 'This is a habit visible to everyone using this activity';
$string['personal_title_text'] = 'This is one of your personal habits only being used by you';
$string['showhide'] = 'Show';
$string['habit_not_published_title'] = 'The habit {$a} is currently hidden.';
$string['completionentriesgroup'] = 'Completion entries';
$string['completionentries'] = 'Student must make the following number of entries:';
$string['goodhabits:review_as_admin'] = 'Access the review features of this plugin to view the entries of other users';
$string['goodhabits:manage_activity_habits'] = 'Manage habits for everyone - I.e. habits that are set up for all users in the activity';
$string['review_disable'] = 'Disable reviews';
$string['review_enable_no_opting'] = 'Enable reviews';
$string['review_enable_opt_in'] = 'Enable reviews - students must opt in';
$string['review_enable_opt_out'] = 'Enable reviews - students can opt out';
$string['review'] = 'Reviews';
$string['review_help'] = 'Enables/disables the review feature. This allows for: <br /><br />* admin with appropriate capabilities reviewing student\'s habit calendars, and<br />* students\' peer review of their habit calendars.<br /><br />Each of these can be configured within the activity, and then as a student preference.';
$string['small_viewport_message'] = 'The minimum viewport width to use this activity is 640px. If you are using a phone, you can try tilting it horizontally to view it in landscape mode.';
$string['mobile_view'] = 'Mobile view';
$string['exit_mobile_view'] = 'Exit mobile view';
$string['notification_skip_added'] = 'This has been skipped. To undo, go to "Manage breaks".';
$string['answer_questions'] = 'Answer questions';
$string['skip'] = 'Skip';
$string['saved'] = 'Saved';
$string['all_complete'] = 'Complete';
$string['completion_calendar'] = 'Student must complete the following number of days/weeks:';
$string['days'] = 'days';
$string['weeks'] = 'weeks';
$string['blocks_of_days'] = 'blocks-of-days';
$string['day'] = 'day';
$string['week'] = 'week';
$string['blocks_of_day'] = 'block-of-days';
$string['label_num_completed'] = 'Number of {$a} completed';
//$string['completed_cal_units'] = 'Number of {$a->period_duration} completed: {$a->completed}';
$string['label_remaining'] = 'To complete';
$string['completion_habits'] = 'Student must track at least the following number of habits:';
//$string['completionentries'] = 'You have to add a minimum number of entries';
//$string['completioncalendarunits'] = 'You have to complete a minimum number of days/weeks';
$string['completiondetail:min_habits'] = 'track at least {$a} habits';
$string['completiondetail:min_entries'] = 'make at least {$a} entries';
$string['completiondetail:min_cal_units'] = 'complete {$a->min} {$a->units}';
$string['starting'] = 'starting';
$string['simple_view_effort'] = 'Effort:';
$string['simple_view_outcome'] = 'Outcome:';
$string['simple_view_back'] = 'Back';
$string['allow_reviews_peers'] = 'Allow peers to review your tracker data (and you to see theirs)';
$string['allow_reviews_peers_help'] = 'Allows others on the course to see your tracker data. <br /><br />If you enable this, you will be able to see the habit tracker data of the other users who have opted in.';
$string['allow_reviews_admin'] = 'Allow admin to review your tracker data';
$string['allow_reviews_admin_help'] = 'Allows admin to review your tracker data. <br /><br />That is, the ratings you have given yourself.';
$string['allow_review_others'] = 'Allow others who opt in to review your tracker data';
$string['prefs_tracker_privacy_header'] = 'Tracker privacy';
$string['prefs_appearance'] = 'Appearance';
$string['pref_updated'] = 'Preferences updated';
$string['default_settings_desc'] = 'Determines the defaults when creating a new activity.';
$string['disabled'] = 'Disabled';
$string['required'] = 'Required';
$string['opt_def_disallow'] = 'Optional [default: Don\'t allow]';
$string['opt_def_allow'] = 'Optional [default: Allow]';
$string['cm_reviews_admin'] = 'Admin reviews';
$string['cm_reviews_peers'] = 'Peer reviews';
$string['feature_disabled'] = 'This feature has been disabled';
$string['no_access'] = 'You do not have access to this';
$string['grid_box_wording_intro'] = 'You can customise this text:';
$string['goodhabits:review_as_peer'] = 'See the habit entries of others who also have this capability and who have enabled this';
$string['lacking_peer_caps'] = 'You are set up as a peer but do not have the capabilities required to review as a peer.';
$string['cm_reviews_admin_help'] = 'Whether to allow admin review of user entries.<br /><br />
Admin means a user with the following capabilities within this activity:<br /><br />{$a}<br /><br />
*=== Options ===*<br /><br />
Disabled - No-one can review as admin<br /><br />
Optional [default: Don\'t allow] - Students control whether to allow admin review access. By default, admin do not have access.<br /><br />
Optional [default: Allow] - As above, students can set whether to allow in their preferences page. By default, admin have access.<br /><br />
Required - Admin can review and students are not able to change this.
';
$string['cm_reviews_peers_help'] = 'Whether to allow peer review of user entries.<br /><br />
A \'peer\' means a user with the following capabilities within this activity:<br /><br />{$a}<br /><br />
Note that students who disallow peer review cannot access this.<br /><br />
*=== Options ===*<br /><br />
Disabled - No-one can review as a peer<br /><br />
Optional [default: Don\'t allow] - Students control whether to allow peer review. By default, peer review is enabled.<br /><br />
Optional [default: Allow] - As above, students can set whether to allow in their preferences page. By default, peer review is disabled.<br /><br />
Required - Peer review is enabled and students are not able to change this.
';
$string['get_started'] = 'Need some help getting started? {$a}';
$string['answer_latest_questions'] = 'Answer questions about the latest {$a}.';
$string['answer_latest_day'] = 'Answer questions about the latest day.';
$string['answer_latest_week'] = 'Answer questions about the latest week.';
$string['answer_latest_block_of_days'] = 'Answer questions about the most recent days.';
$string['simple_all_complete'] = 'You have answered all questions about:';
$string['show_scores'] = 'Show numbers in the tracker';
$string['show_scores_help'] = 'Whether to show the numbers that you have entered in the grid.
<br /><br />For example: <span style="font-weight: bold">6 / 7</span>.
<br /><br />If you hide this, you can still get a general sense of the values from the colours.';
$string['enable_help'] = 'Enable help';
$string['enable_help_help'] = 'Whether to provide additional links and guidance.<br /><br />
This is designed to help you get familiar with how the tracker works.<br /><br />
It also supports your engagement in the self-reflective practice.';
$string['example'] = 'Example';

// Did you know? section.
$string['dyk_heading'] = 'Some basic tips';
$string['dyk_add_entry_heading'] = 'Tap, reflect, save';
$string['dyk_add_entry_1'] = 'You can tap/click the habit entry circle';
//$string['dyk_add_entry_2'] = 'This opens up a grid to allow you to reflect on a habit for a particular {$a}.';
$string['dyk_add_entry_3'] = 'Think about an appropriate placement, considering effort and outcome.';
$string['dyk_add_entry_4'] = 'To place, just double-tap (or click) the square on the grid.';
$string['dyk_add_entry_5'] = 'Tap the same circle you used to open up the grid to save and close.';

$string['dyk_cal_unit_options_heading'] = 'Explore options for each {$a}';
$string['dyk_cal_unit_options_1'] = 'You can tap/click the {$a} button';
$string['dyk_cal_unit_options_2'] = 'This will show you more options for {$a}';
$string['dyk_cal_unit_options_3'] = 'You can answer questions about each habit in sequence for {$a}';
//$string['dyk_cal_unit_options_4'] = 'Skipping a {$a} will grey it out, so you know you don\'t want to add any entries for that week, and it can be ignored.';
$string['dyk_cal_unit_options_5'] = '{$a} is an example of a break. These can be managed under *Manage breaks*, below. You can also add a longer break.';

$string['dyk_prefs_heading'] = 'Manage your preferences';
$string['dyk_prefs_1'] = 'See "Personal preferences", below.';
$string['dyk_prefs_2'] = 'Choose whether anyone else can see your tracker data - and whether you want to see other people\'s data.';
$string['dyk_prefs_3'] = 'You can turn off this help feature.';
$string['dyk_prefs_4'] = 'Change things about the appearance of the tracker.';

$string['dyk_mng_habits_heading'] = 'Manage the habits you track';
$string['dyk_mng_habits_1'] = 'Click on *Manage habits*, below, for the following:';
$string['dyk_mng_habits_2'] = 'You can add, edit and delete habits';
$string['dyk_mng_habits_3'] = 'You can hide a habit by changing *Show* to *Hide*';
$string['dyk_mng_habits_4'] = 'You can delete all your entries for a specific habit';
$string['dyk_mng_habits_5'] = 'Some habits may have been set up for the whole group, in which case you cannot or delete or edit the habit.';

$string['simple_view_effort_0'] = 'Using the sliding scale, how much effort would you say you put in - go with a gut feel:';
$string['simple_view_effort_1'] = 'How much do you feel you put in? Remember that it is normal for effort to fluctuate:';
$string['simple_view_effort_2'] = 'Imagine you did nothing at all for this habit. <br /><br /> Then think of all the things you did that you might not have done. This is one way to rate your effort:';
$string['simple_view_effort_3'] = 'How much of a priority did you make this habit?';
$string['simple_view_effort_4'] = 'How would you rate the energy and focus you’ve dedicated to this habit?';
$string['simple_view_effort_5'] = 'Effort:';

$string['simple_view_outcome_0'] = 'What benefit did this bring? Were any goals achieved? Go with whatever makes intuitive sense:';
$string['simple_view_outcome_1'] = 'Did you achieve your goals? Whatever rating feels right is simply a point of reflection, not a final grade:';
$string['simple_view_outcome_2'] = 'Are you seeing results?';
$string['simple_view_outcome_3'] = 'Sometimes you might have a clear marker of objective success, which makes it easy to rate the outcome highly. But it\'s ok to take a more subjective measure - simply feeling good about this habit at this time:';
$string['simple_view_outcome_4'] = 'Sometimes objective measures of success are highly uncertain. You don\'t really know the final outcome until much later (think of taking a test).<br /><br /> This can be another reason to prefer rating your subjective level of satisfaction with the habit:';
$string['simple_view_outcome_5'] = 'Outcome:';

$string['block_of_days'] = 'block of days';
$string['def_article_day'] = 'the day';
$string['def_article_week'] = 'the week';
$string['def_article_block_of_days'] = 'the block-of-days';
$string['chosen_day'] = 'the chosen day';
$string['chosen_week'] = 'the chosen week';
$string['chosen_block_of_days'] = 'the chosen block-of-days';
$string['skipped_day'] = 'A skipped day';
$string['skipped_week'] = 'A skipped week';
$string['skipped_block_of_days'] = 'A skipped block-of-days';
$string['skip_help_day'] = 'Skipping a day will grey it out, so you know you don\'t want to add any entries for that day, and it can be ignored.';
$string['skip_help_week'] = 'Skipping a week will grey it out, so you know you don\'t want to add any entries for that week, and it can be ignored.';
$string['skip_help_block_of_days'] = 'Skipping a block-of-days will grey it out, so you know you don\'t want to add any entries for that block-of-days, and it can be ignored.';
$string['grid_open_help_day'] = 'This opens up a grid to allow you to reflect on a habit for a particular day.';
$string['grid_open_help_week'] = 'This opens up a grid to allow you to reflect on a habit for a particular week.';
$string['grid_open_help_block_of_days'] = 'This opens up a grid to allow you to reflect on a habit for a particular block-of-days.';
$string['average_eff_out'] = 'Average (effort / outcome)';
$string['first_entry'] = 'First entry';
$string['no_entries'] = 'No entries.';
$string['historical_data'] = 'Historical data';
$string['select_habit'] = 'Select habit';
$string['startdate'] = 'Start date';
$string['enddate'] = 'End date';
$string['goodhabits:view_own_historical_data'] = 'View own historical_data';
$string['advancedsettings'] = 'Advanced';
$string['customgraphsource'] = 'Custom graph';
$string['effort'] = 'effort';
$string['outcome'] = 'outcome';
$string['difference'] = 'difference';
$string['bardata'] = 'Bar data';
$string['linedata'] = 'Line data';
$string['difflabel'] = 'Difference (Outcome - Effort)';



