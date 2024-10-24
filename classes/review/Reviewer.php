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

use mod_goodhabits\Helper;
use mod_goodhabits\PreferencesManager;

class Reviewer extends ReviewUser
{
    protected array $candidates;

    protected array $subjects;

    protected array $missing_caps;

    protected string $query;

    protected function get_candidates()
    {
        global $DB;

        $sql = "SELECT u.* FROM {user} u 
JOIN {user_enrolments} ue ON (u.id = ue.userid)
JOIN {enrol} e ON (e.id = ue.enrolid) 
                    WHERE e.courseid = :courseid AND u.id != :reviewer_user_id ";
        $params = ['courseid' => $this->courseid, 'reviewer_user_id' => $this->userid];

        if (isset($this->query)) {
            $sql .= " AND (" . $DB->sql_like('u.firstname', ':firstname', false);
            $sql .= " OR " . $DB->sql_like('u.lastname', ':lastname', false) . ")";
            $params['firstname'] = '%' . $this->query . '%';
            $params['lastname'] = '%' . $this->query . '%';
        }

        $users = $DB->get_records_sql($sql, $params);

        $candidates = [];

        foreach ($users as $id => $user) {
            $candidate = new ReviewSubject($this->instance, $user);
            $candidates[$user->id] = $candidate;
        }

        $this->candidates = $candidates;

        return $this->candidates;
    }

    protected function filter_candidates()
    {
        $success = [];
        foreach ($this->candidates as $candidate) {
            if ($this->candidate_success($candidate)) {
                $success[$candidate->get_userid()] = $candidate;
            }
        }
        $this->subjects = $success;
        return $this->subjects;
    }

    protected function candidate_success(ReviewSubject $candidate)
    {
        return $candidate->allow_review($this->is_admin, $this->allow_reviews_peers);
    }

    public function init()
    {
        $this->instance = Helper::get_instance_from_instance_id($this->instanceid);
        $this->courseid = $this->instance->course;
        $this->init_is_admin();
        $this->init_missing_caps();
        $this->init_allow_peers();
    }

    protected function init_is_admin()
    {
        $this->is_admin = has_capability('mod/goodhabits:review_as_admin', $this->context);
    }

    protected function init_missing_caps()
    {
        $required = ['moodle/course:viewparticipants'];
        $this->missing_caps = [];

        foreach ($required as $cap) {
            if (!has_capability($cap, $this->context)) {
                $this->missing_caps[] = $cap;
            }
        }
    }

    protected function init_allow_peers()
    {
        $pref_manager = new PreferencesManager($this->instanceid, $this->userid);
        $is_peer = $pref_manager->get_review_status('reviews_peers');
        $has_peer_cap = has_capability('mod/goodhabits:review_as_peer', $this->context);
        $this->allow_reviews_peers = ($is_peer AND $has_peer_cap);
    }

    public function set_query($query)
    {
        $this->query = $query;
    }

    public function get_subjects()
    {
        if (!empty($this->missing_caps)) {
            return [];
        }

        $this->get_candidates();

        $this->filter_candidates();

        return $this->subjects;
    }

    public function get_missing_caps()
    {
        return $this->missing_caps;
    }

}