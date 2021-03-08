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

namespace mod_goodhabits\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use mod_goodhabits\Helper;

global $CFG;

require_once($CFG->dirroot . '/mod/goodhabits/classes/Helper.php');

class provider implements
    // This plugin does store personal user data.
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    public static function get_metadata(collection $collection) : collection {

        $collection->add_database_table(
            'mod_goodhabits_entry',
            [
                'userid' => 'privacy:metadata:mod_goodhabits_entry:userid',
                'habit_id' => 'privacy:metadata:mod_goodhabits_entry:habit_id',
                'x_axis_val' => 'privacy:metadata:mod_goodhabits_entry:x_axis_val',
                'y_axis_val' => 'privacy:metadata:mod_goodhabits_entry:y_axis_val',
            ],
            'privacy:metadata:mod_goodhabits_entry'
        );

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();
        $contextlist->add_system_context();
        return $contextlist;
    }

    public static function export_user_data(approved_contextlist $contextlist) {
        $contexts = $contextlist->get_contexts();
        foreach ($contexts as $context) {
            static::export($context);
        }
    }

    private static function export($context) {
        global $DB, $USER;
        $entries = $DB->get_records('mod_goodhabits_entry', array('userid' => $USER->id));

        $subcontext = array();

        $subcontext[] = get_string('mod_goodhabits_subcontext', 'mod_goodhabits');

        \core_privacy\local\request\writer::with_context($context)
            ->export_data($subcontext, (object) [
                'entries' => $entries,
            ]);
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        Helper::delete_all_entries();
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        $userid = $contextlist->get_user()->id;
        Helper::delete_entries($userid);
    }
}