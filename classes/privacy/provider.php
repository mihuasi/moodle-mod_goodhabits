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

namespace mod_goodhabits\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper as request_helper;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\writer;

global $CFG;

require_once($CFG->dirroot . '/mod/goodhabits/classes/Helper.php');

/**
 * Implementation of the privacy subsystem plugin provider for the Good Habits activity module.
 *
 * Class provider
 * @package mod_goodhabits\privacy
 */
class provider implements
    // This plugin does store personal user data.
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection) : collection {

        $collection->add_database_table(
            'mod_goodhabits_entry',
            [
                'userid' => 'privacy:metadata:mod_goodhabits_entry:userid',
                'habit_id' => 'privacy:metadata:mod_goodhabits_entry:habit_id',
                'habit_name' => 'privacy:metadata:mod_goodhabits_item:name',
                'endofperiod_timestamp' => 'privacy:metadata:mod_goodhabits_entry:endofperiod_timestamp',
                'x_axis_val' => 'privacy:metadata:mod_goodhabits_entry:x_axis_val',
                'y_axis_val' => 'privacy:metadata:mod_goodhabits_entry:y_axis_val',
            ],
            'privacy:metadata:mod_goodhabits_entry'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * In the case of Good Habits, that is any course module where the user has made any entries.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {goodhabits} gh ON gh.id = cm.instance
            INNER JOIN {mod_goodhabits_item} h ON h.instanceid = gh.id
            INNER JOIN {mod_goodhabits_entry} e ON e.habit_id = h.id AND e.userid = :userid";

        $params = [
            'modname' => 'goodhabits',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $sql = "SELECT e.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {goodhabits} gh ON gh.id = cm.instance
                  JOIN {mod_goodhabits_item} h ON h.instanceid = gh.id
                  JOIN {mod_goodhabits_entry} e ON e.habit_id = h.id
                 WHERE cm.id = :cmid";

        $params = [
            'cmid' => $context->instanceid,
            'modname' => 'goodhabits',
        ];

        $userlist->add_from_sql('userid', $sql, $params);

    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist
     * @return |null
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return null;
        }

        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT e.id as entryid,
                       cm.id as cmid,
                       h.id as habit_id,
                       h.name as habit_name,
                       e.x_axis_val as x_val,
                       e.y_axis_val as y_val,
                       e.endofperiod_timestamp as eop_timestamp
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {goodhabits} gh ON gh.id = cm.instance
            INNER JOIN {mod_goodhabits_item} h ON h.instanceid = gh.id
            INNER JOIN {mod_goodhabits_entry} e ON e.habit_id = h.id
                 WHERE c.id {$contextsql}
                       AND e.userid = :userid
              ORDER BY cm.id";

        $params = ['modname' => 'goodhabits', 'contextlevel' => CONTEXT_MODULE, 'userid' => $user->id] + $contextparams;

        $entries = $DB->get_records_sql($sql, $params);

        $entrydatas = array();

        foreach ($entries as $entry) {
            $cmid = $entry->cmid;

            $entrydata = [
                'habit_id' => $entry->habit_id,
                'habit_name' => $entry->habit_name,
                'endofperiod_timestamp' => \core_privacy\local\request\transform::datetime($entry->eop_timestamp),
                'x_axis_val' => $entry->x_val,
                'y_axis_val' => $entry->y_val,
            ];

            if (!isset($entrydatas[$cmid])) {
                $entrydatas[$cmid] = array();
            }
            $entrydatas[$cmid][] = $entrydata;
        }

        foreach ($entrydatas as $cmid => $entrydata) {
            static::export_cm_user_data($entrydata, $cmid, $user);
        }
    }

    /**
     * Export for a single CM for the provided user.
     *
     * @param array $entrydata
     * @param int $cmid
     * @param \stdClass $user
     */
    private static function export_cm_user_data($entrydata, $cmid, $user) {
        $context = \context_module::instance($cmid);
        $contextdata = request_helper::get_context_data($context, $user);
        $data = (object) array_merge((array) $contextdata, $entrydata);
        writer::with_context($context)->export_data([], $data);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        if ($cm = get_coursemodule_from_id('goodhabits', $context->instanceid)) {
            $habits = $DB->get_records('mod_goodhabits_item', array('instanceid' => $cm->instance));
            foreach ($habits as $habit) {
                $DB->delete_records('mod_goodhabits_entry', ['habit_id' => $habit->id]);
            }
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist
     * @throws \dml_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {

            if (!$context instanceof \context_module) {
                continue;
            }
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid]);
            if (!$instanceid) {
                continue;
            }

            $habits = $DB->get_records('mod_goodhabits_item', array('instanceid' => $instanceid));
            foreach ($habits as $habit) {
                $DB->delete_records('mod_goodhabits_entry', ['habit_id' => $habit->id, 'userid' => $userid]);
            }
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist
     * @return void|null
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('goodhabits', $context->instanceid);

        if (!$cm) {
            return null;
        }

        $userids = $userlist->get_userids();
        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $habits = $DB->get_records('mod_goodhabits_item', array('instanceid' => $cm->instance));

        foreach ($habits as $habit) {
            $select = "habit_id = :habit_id AND userid $usersql";
            $params = ['habit_id' => $habit->id] + $userparams;
            $DB->delete_records_select('mod_goodhabits_entry', $select, $params);
        }
    }
}
