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
 * Good habits mod external functions and services.
 *
 * @package     mod_goodhabits
 * @category    services
 * @copyright   2024 Joe Cape <joe.sc.cape@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
$functions = [
    'mod_goodhabits_get_review_subjects' => [
        'classname'     => 'mod_goodhabits\External',
        'methodname'    => 'get_review_subjects',
        'classpath'     => 'mod/goodhabits/external.php',
        'classpath'     => '',
        'description'   => 'Fetches a list of review subject users',
        'type'          => 'read',
        'capabilities'  => 'mod/goodhabits:view',
        'ajax'          => true,
        'loginrequired' => true,
    ],
];
