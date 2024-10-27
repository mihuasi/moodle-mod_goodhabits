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
 * @copyright 2024 Joe Cape
 * @basedon   tool_dataprivacy\external
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_goodhabits;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 *  The external API for this activity module.
 */
class External extends external_api {

    /**
     * @return external_function_parameters
     */
    public static function get_review_subjects_parameters() {
        return new external_function_parameters([
            'query' => new external_value(PARAM_TEXT, 'The search query', VALUE_REQUIRED),
            'instanceid' => new external_value(PARAM_INT, 'The goodhabits instanceid', VALUE_REQUIRED),
            'userid' => new external_value(PARAM_INT, 'The user ID of the reviewer', VALUE_REQUIRED),
        ]);
    }

    /**
     * Gets review subjects that are available for the provided user to review.
     *
     * @param $query
     * @param $instanceid
     * @param $userid
     * @return array
     * @throws \coding_exception
     * @throws \core_external\restricted_context_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public static function get_review_subjects($query, $instanceid, $userid) {
        $params = external_api::validate_parameters(self::get_review_subjects_parameters(), [
            'query' => $query,
            'instanceid' => $instanceid,
            'userid' => $userid,
        ]);
        $query = $params['query'];
        $instanceid = $params['instanceid'];
        $userid = $params['userid'];

        $moduleinstance = Helper::get_module_instance($instanceid);
        $course = get_course($moduleinstance->course);
        $cm = get_coursemodule_from_instance('goodhabits', $moduleinstance->id, $course->id, false, MUST_EXIST);

        $context = \context_module::instance($cm->id);

        // Validate context.
        self::validate_context($context);

        $reviewer = new \mod_goodhabits\review\Reviewer($instanceid, $userid, $context);
        $reviewer->init();
        if ($query) {
            $reviewer->set_query($query);
        }

        $subjects = $reviewer->get_subjects();

        foreach ($subjects as $subject) {
            $user = $subject->get_user();
            $useroption = (object)[
                'id' => $user->id,
                'fullname' => fullname($user)
            ];
            $useroption->extrafields = [];

            $useroptions[] = $useroption;
        }

        if (empty($subject)) {
            $useroptions = [];
        }

        return $useroptions;
    }

    /**
     * @return external_multiple_structure
     * @throws \coding_exception
     */
    public static function get_review_subjects_returns() {
        return new external_multiple_structure(new external_single_structure(
            [
                'id' => new external_value(\core_user::get_property_type('id'), 'ID of the user'),
                'fullname' => new external_value(\core_user::get_property_type('firstname'), 'The fullname of the user'),
            ]
        ));
    }
}
