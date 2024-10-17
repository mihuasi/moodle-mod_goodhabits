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

declare(strict_types=1);

namespace mod_goodhabits\completion;


use core_completion\activity_custom_completion;
use mod_goodhabits\Helper;
use mod_goodhabits\ViewHelper;

/**
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion {

    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $userid = $this->userid;
        $gh_id = $this->cm->instance;

        if (!$goodhabits = $DB->get_record('goodhabits', ['id' => $gh_id])) {
            throw new \moodle_exception('Unable to find goodhabits with id ' . $gh_id);
        }
        $min_entries = $goodhabits->completionentries ?? null;
        $min_cal_units = $goodhabits->completioncalendarunits ?? null;

        switch ($rule) {
            case 'completionentries':
                $num_entries = \mod_goodhabits\HabitItemsHelper::get_total_num_entries($goodhabits->id, $userid);
                $status = $num_entries >= $min_entries;
                break;
            case 'completioncalendarunits':
                $complete = \mod_goodhabits\Helper::get_cal_units_with_all_complete($goodhabits->id, $userid);
                $status = (count($complete) >= $min_cal_units);
                break;
            default:
                $status = false;
                break;
        }

        return $status ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return [
            'completionentries',
            'completioncalendarunits',
        ];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        global $DB;
        $gh_id = $this->cm->instance;

        if (!$goodhabits = $DB->get_record('goodhabits', ['id' => $gh_id])) {
            throw new \moodle_exception('Unable to find goodhabits with id ' . $gh_id);
        }

        $calendar = ViewHelper::get_flexi_calendar($goodhabits);
        $units = $calendar->get_period_duration_string();

        $completionentries = $this->cm->customdata['customcompletionrules']['completionentries'] ?? 0;
        $completioncalendarunits = $this->cm->customdata['customcompletionrules']['completioncalendarunits'] ?? 0;

        $strobj = new \stdClass();
        $strobj->min = $completioncalendarunits;
        $strobj->units = $units;

        return [
            'completionentries' => Helper::get_string('completiondetail:min_entries', $completionentries),
            'completioncalendarunits' => Helper::get_string('completiondetail:min_cal_units', $strobj),
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            'completionentries',
            'completioncalendarunits',
        ];
    }
}
