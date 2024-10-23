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

use mod_goodhabits\Helper;

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading(
        'goodhabits/general',
        Helper::get_string('general_settings'),
        '',
    ));

    $options = \mod_goodhabits\ViewHelper::get_review_options();
    $settings->add(
        new admin_setting_configselect(
            'goodhabits/review',
            get_string('review', 'mod_goodhabits'),
            get_string('review_help', 'mod_goodhabits'),
            \mod_goodhabits\ViewHelper::REVIEW_OPTION_NO_OPTING,
            $options
        )
    );

    $settings->add(new admin_setting_heading(
        'goodhabits/defaults',
        Helper::get_string('default_settings'),
        Helper::get_string('default_settings_desc')
    ));

    $options = Helper::possible_period_durations();
    $settings->add(
        new admin_setting_configselect(
            'goodhabits/freq',
            get_string('freq', 'mod_goodhabits'),
            get_string('freq_desc', 'mod_goodhabits'),
            7,
            $options
        )
    );

}
