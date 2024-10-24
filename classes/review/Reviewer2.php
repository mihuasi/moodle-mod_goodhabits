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
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_goodhabits\review;

class Reviewer2
{
    /**
     * @var int Current user ID.
     */
    protected int $userid;

    protected int $instanceid;

    /**
     * @var \context_module Current context.
     */
    protected \context_module $context;

    /**
     * @var bool Whether the current user is admin, for the purposes of reviewing students.
     */
    protected bool $is_admin;

    /**
     * @var bool Whether current user allows peer reviews.
     */
    protected bool $allow_reviews_peers;

    public function __construct($instanceid, $userid, $context) {
        $this->instanceid = $instanceid;
        $this->userid = $userid;
        $this->context = $context;
    }


}