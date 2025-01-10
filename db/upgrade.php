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

    if ($oldversion < 2024101701) {
        $table = new xmldb_table('goodhabits');

        $field = new xmldb_field('completionhabitsenabled', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL,
            null, 0, 'freq');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('completionhabits', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL,
            null, 0, 'completionhabitsenabled');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2024102301) {

        // Define table mod_goodhabits_prefs to be created.
        $table = new xmldb_table('mod_goodhabits_prefs');

        // Adding fields to table mod_goodhabits_prefs.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('instanceid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, null);
        $table->add_field('allow_reviews_admin', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('allow_reviews_peers', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('text_overlay_1_1', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('text_overlay_1_2', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('text_overlay_1_3', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('text_overlay_2_1', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('text_overlay_2_2', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('text_overlay_2_3', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('text_overlay_3_1', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('text_overlay_3_2', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('text_overlay_3_3', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('question_version', XMLDB_TYPE_INTEGER, '9', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table mod_goodhabits_prefs.
        $table->add_key('id', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for mod_goodhabits_prefs.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Goodhabits savepoint reached.
        upgrade_mod_savepoint(true, 2024102301, 'goodhabits');
    }

    if ($oldversion < 2024102302) {
        $table = new xmldb_table('goodhabits');

        $field = new xmldb_field('cm_reviews_admin', XMLDB_TYPE_CHAR, '20', null, null,
            null, '', 'freq');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('cm_reviews_peers', XMLDB_TYPE_CHAR, '20', null, null,
            null, '', 'cm_reviews_admin');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2024111901) {
        $table = new xmldb_table('mod_goodhabits_prefs');

        $field = new xmldb_field('show_scores', XMLDB_TYPE_INTEGER, '9', null,
            null, null, null, 'allow_reviews_peers');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2024112601) {
        $table = new xmldb_table('mod_goodhabits_prefs');

        $field = new xmldb_field('enable_help', XMLDB_TYPE_INTEGER, '9', null,
            null, null, null, 'show_scores');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2025010902) {
        $table = new xmldb_table('mod_goodhabits_item');

        $field = new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, '10', null,
            null, null, null, 'colour');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}
