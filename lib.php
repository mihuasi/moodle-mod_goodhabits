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
 * Library of interface functions and constants.
 *
 * @package     mod_goodhabits
 * @copyright   2021 Joe Cape <joe.sc.cape@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_goodhabits\habit\HabitItemsHelper;

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function goodhabits_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_goodhabits into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_goodhabits_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function goodhabits_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('goodhabits', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_goodhabits in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_goodhabits_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function goodhabits_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('goodhabits', $moduleinstance);
}

/**
 * Removes an instance of the mod_goodhabits from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function goodhabits_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('goodhabits', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $habits = \mod_goodhabits\habit\HabitItemsHelper::get_all_activity_instance_habits($id);
    foreach ($habits as $habit) {
        $habit = new \mod_goodhabits\habit\Habit($habit->id);
        $habit->delete();
    }

    $breaks = \mod_goodhabits\BreaksHelper::get_all_activity_instance_breaks($id);
    foreach ($breaks as $break) {
        $DB->delete_records('mod_goodhabits_break', array('id' => $break->id));
    }

    $DB->delete_records('goodhabits', array('id' => $id));

    return true;
}

/**
 * Obtains the automatic completion state for this Good Habits activity based on any conditions
 * in activity settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool
 */
function goodhabits_get_completion_state($course, $cm, $userid, $type) {
    global $DB;
    $goodhabits = $DB->get_record('goodhabits', array('id' => $cm->instance));
    if (!$goodhabits) {
        throw new moodle_exception('Cannot find module instance');
    }
    $result = $type;

    if ($min_habits = $goodhabits->completionhabits) {
        $habits = HabitItemsHelper::get_all_habits_for_user($goodhabits->id, $userid);
        $value = count($habits) >= $min_habits;

        if ($type == COMPLETION_AND) {
            $result = $result && $value;
        } else {
            $result = $result || $value;
        }
    }

    if ($numrequired = $goodhabits->completionentries) {
        $num = \mod_goodhabits\habit\HabitItemsHelper::get_total_num_entries($goodhabits->id, $userid);
        $value = $num >= $numrequired;

        if ($type == COMPLETION_AND) {
            $result = $result && $value;
        } else {
            $result = $result || $value;
        }
    }

    if ($num_cal_rqd = $goodhabits->completioncalendarunits) {
        $complete = \mod_goodhabits\Helper::get_cal_units_with_all_complete($goodhabits->id, $userid);
        $value = (count($complete) >= $num_cal_rqd);

        if ($type == COMPLETION_AND) {
            $result = $result && $value;
        } else {
            $result = $result || $value;
        }
    }

    return $result;
}

function goodhabits_get_coursemodule_info($coursemodule) {
    global $DB;


    $dbparams = ['id' => $coursemodule->instance];

    if (!$goodhabits = $DB->get_record('goodhabits', $dbparams, '*')) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $goodhabits->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('goodhabits', $goodhabits, $coursemodule->id, false);
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionhabits'] = $goodhabits->completionhabits;
        $result->customdata['customcompletionrules']['completionentries'] = $goodhabits->completionentries;
        $result->customdata['customcompletionrules']['completioncalendarunits'] = $goodhabits->completioncalendarunits;
    }

    return $result;
}

function goodhabits_get_completion_active_rule_descriptions($cm) {
    return mod_goodhabits_get_completion_active_rule_descriptions($cm);
}

function mod_goodhabits_get_completion_active_rule_descriptions($cm) {

    $descriptions = [];
    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
        switch ($key) {
            case 'completionhabits':
                if (!empty($val)) {
                    $descriptions[] = \mod_goodhabits\Helper::get_string('completiondetail:min_habits');
                }
                break;
            case 'completionentries':
                if (!empty($val)) {
                    $descriptions[] = \mod_goodhabits\Helper::get_string('completiondetail:min_entries');
                }
                break;
            case 'completioncalendarunits':
                if (!empty($val)) {
                    $descriptions[] = \mod_goodhabits\Helper::get_string('completiondetail:min_cal_units');
                }
                break;
            default:
                break;
        }
    }
    return $descriptions;
}


