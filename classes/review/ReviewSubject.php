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

use mod_goodhabits\PreferencesManager;

/**
 * Models a user who another user is seeking to review.
 */
class ReviewSubject
{

    /**
     * @var PreferencesManager for the subject user, to determine whether they should be included in reviews.
     */
    protected PreferencesManager $pref_manager;
    protected $user;

    protected int $userid;

    protected int $instanceid;

    protected bool $allow_reviews_admin;
    protected bool $allow_reviews_peers;

    public function __construct($instance, $user) {
        $this->instanceid = $instance->id;
        $this->user = $user;
        $this->userid = $user->id;
        $this->pref_manager = new PreferencesManager($instance->id, $user->id);
        $this->allow_reviews_admin = $this->pref_manager->get_review_status('reviews_admin');
        $this->allow_reviews_peers = $this->pref_manager->get_review_status('reviews_peers');
    }

    /**
     * Whether the current review subject allows review by the reviewing user.
     *
     * @param $is_admin - Whether the reviewing user is admin.
     * @param $is_reviewer_peer - Whether the reviewing user is a peer.
     * @return bool
     */
    public function allow_review($is_admin, $is_reviewer_peer)
    {
        if ($is_admin AND $this->allow_reviews_admin) {
            return true;
        }
        if ($is_reviewer_peer AND $this->allow_reviews_peers) {
            return true;
        }
        return false;
    }

    public function get_user()
    {
        return $this->user;
    }

    public function get_userid()
    {
        return $this->userid;
    }


}