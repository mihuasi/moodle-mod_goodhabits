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

namespace mod_goodhabits;

defined('MOODLE_INTERNAL') || die();

class IndexHelper {

    public static $instanceid;

    public static function get_instance_id_from_url() {
        // Module instance id.
        $g = optional_param('g', 0, PARAM_INT);
        if ($g) {
            return $g;
        }
        if (!empty(static::$instanceid)) {
            return static::$instanceid;
        }
        // Course module ID.
        $id = optional_param('id', 0, PARAM_INT);
        $cm = get_coursemodule_from_id('goodhabits', $id, 0, false, MUST_EXIST);
        $instanceid = $cm->instance;
        static::$instanceid = $instanceid;
        return $instanceid;
    }
}