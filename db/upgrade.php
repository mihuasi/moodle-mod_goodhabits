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
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_goodhabits
 * @category    upgrade
 * @copyright   2021 Joe Cape <joe.sc.cape@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_goodhabits upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_goodhabits_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // For further information please read the Upgrade API documentation:
    // https://docs.moodle.org/dev/Upgrade_API
    //
    // You will also have to create the db/install.xml file by using the XMLDB Editor.
    // Documentation for the XMLDB Editor can be found at:
    // https://docs.moodle.org/dev/XMLDB_editor.

    if ($oldversion < 2024101603) {
        $table = new xmldb_table('goodhabits');

        $field = new xmldb_field('completionentriessenabled', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL,
            null, 0, 'freq');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('completioncalendarunits', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL,
            null, 0, 'completionentries');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('completioncalendarenabled', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL,
            null, 0, 'completionentries');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}
