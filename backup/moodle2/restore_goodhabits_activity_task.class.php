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
 * Restore task for the goodhabits activity module.
 *
 * Provides all the settings and steps to perform complete restore of the activity.
 *
 * @package   mod_goodhabits
 * @category  backup
 * @copyright 2021 Joe Cape <joe.sc.cape@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/goodhabits/backup/moodle2/restore_goodhabits_stepslib.php');

class restore_goodhabits_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // We have just one structure step here.
        $this->add_step(new restore_goodhabits_activity_structure_step('goodhabits_structure', 'goodhabits.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    public static function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('goodhabits', array('intro'), 'goodhabits');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    public static function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('GHINDEX', '/mod/goodhabits/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('GHVIEWBYID', '/mod/goodhabits/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('GHVIEWBYF', '/mod/goodhabits/view.php?f=$1', 'goodhabits');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * goodhabits logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    public static function define_restore_log_rules() {
        $rules = array();

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    public static function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('goodhabits', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}