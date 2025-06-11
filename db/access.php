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
 * @package     mod_goodhabits
 * @category    access
 * @copyright   2021 Joe Cape <joe.sc.cape@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$capabilities = array(
    'mod/goodhabits:addinstance' => array(
        'riskbitmask' => RISK_SPAM,
        'contextlevel' => CONTEXT_COURSE,
        'captype' => 'write',
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW
        )
    ),

    'mod/goodhabits:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        )
    ),

    'mod/goodhabits:manage_personal_prefs' => array(
        'riskbitmask' => RISK_PERSONAL,
        'contextlevel' => CONTEXT_MODULE,
        'captype' => 'write',
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        )
    ),

    'mod/goodhabits:manage_entries' => array(
        'riskbitmask' => RISK_SPAM,
        'contextlevel' => CONTEXT_MODULE,
        'captype' => 'write',
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        )
    ),

    'mod/goodhabits:manage_activity_habits' => array(
        'riskbitmask' => RISK_SPAM,
        'contextlevel' => CONTEXT_MODULE,
        'captype' => 'write',
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW
        )
    ),

    'mod/goodhabits:review_as_admin' => array(
        'riskbitmask' => RISK_PERSONAL,
        'contextlevel' => CONTEXT_MODULE,
        'captype' => 'read',
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
        )
    ),

    'mod/goodhabits:review_as_peer' => array(
        'riskbitmask' => RISK_PERSONAL,
        'contextlevel' => CONTEXT_MODULE,
        'captype' => 'read',
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW
        )
    ),

    'mod/goodhabits:manage_personal_habits' => array(
        'riskbitmask' => RISK_SPAM,
        'contextlevel' => CONTEXT_MODULE,
        'captype' => 'write',
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW
        )
    ),

    'mod/goodhabits:manage_personal_breaks' => array(
        'riskbitmask' => RISK_SPAM,
        'contextlevel' => CONTEXT_MODULE,
        'captype' => 'write',
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW
        )
    ),

    'mod/goodhabits:view_own_historical_data' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        )
    ),

    'mod/goodhabits:view_others_historical_data' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        )
    )
);
