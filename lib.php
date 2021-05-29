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
        case FEATURE_COMPLETION_TRACKS_VIEWS:
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

    $habits = \mod_goodhabits\HabitItemsHelper::get_all_activity_instance_habits($id);
    foreach ($habits as $habit) {
        $habit = new \mod_goodhabits\Habit($habit->id);
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

    if ($numrequired = $goodhabits->completionentries) {
        $num = \mod_goodhabits\HabitItemsHelper::get_total_num_entries($goodhabits->id, $userid);
        $value = $num >= $numrequired;

        if ($type == COMPLETION_AND) {
            $result = $result && $value;
        } else {
            $result = $result || $value;
        }
    }

    return $result;
}