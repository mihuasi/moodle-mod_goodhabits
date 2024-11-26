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

namespace mod_goodhabits;

use mod_goodhabits\review\Reviewer;

/**
 * Finds the status of settings, based on site config, activity config and user preferences.
 */
class PreferencesManager
{
    protected $instanceid;
    protected $userid;
    protected $site_config;

    protected $instance_rec;
    protected $pref_rec;

    const CM_OPTION_REQUIRED = 'required';
    const CM_OPTION_OPTIONAL_DEFAULT_ALLOW = 'opt_default_allow';
    const CM_OPTION_OPTIONAL_DEFAULT_DISALLOW = 'opt_default_disallow';
    const CM_OPTION_DISABLE = 'disabled';

    const ACCESS_AS_ADMIN = 'as_admin';
    const ACCESS_AS_PEER = 'as_peer';

    public function __construct($instanceid, $userid) {
        $this->instanceid = $instanceid;
        $this->userid = $userid;
        $this->site_config = get_config('goodhabits');
        $this->instance_rec = Helper::get_instance_from_instance_id($instanceid);
        $this->pref_rec = static::get_pref_rec($instanceid, $userid);
    }

    public function show_scores()
    {
        $val = static::default_yes_setting($this->pref_rec, 'show_scores');
        return $val;
    }

    public function enable_help()
    {
        $val = static::default_yes_setting($this->pref_rec, 'enable_help');
        return $val;
    }

    /**
     * Looks successively at global setting, activity level and preference to determine whether
     *      the review setting is enabled.
     *
     * @param $setting
     * @return bool
     */
    public function get_review_status($setting)
    {
        if ($this->site_config->review == 'disable') {
            return false;
        }
        $cm_setting = $this->instance_rec->{'cm_' . $setting};
        switch ($cm_setting) {
            case static::CM_OPTION_DISABLE:
                return false;
            case static::CM_OPTION_REQUIRED:
                return true;
            case static::CM_OPTION_OPTIONAL_DEFAULT_DISALLOW:
                if (empty($this->pref_rec)) {
                    return false;
                } else {
                    return $this->pref_rec->{'allow_' . $setting};
                }
            case static::CM_OPTION_OPTIONAL_DEFAULT_ALLOW:
                if (empty($this->pref_rec)) {
                    return true;
                } else {
                    return $this->pref_rec->{'allow_' . $setting};
                }
            default:
                break;
        }
        return false;
    }

    public function get_review_mod_status($setting)
    {
        if ($this->site_config->review == 'disable') {
            return false;
        }
        $cm_setting = $this->instance_rec->{'cm_' . $setting};
        if ($cm_setting == static::CM_OPTION_DISABLE) {
            return false;
        }

        return true;
    }

    public function is_review_opt_in($setting)
    {
        $cm_setting = $this->instance_rec->{'cm_' . $setting};
        if ($cm_setting == static::CM_OPTION_OPTIONAL_DEFAULT_DISALLOW) {
            return true;
        }
        return false;
    }

    public function is_review_option_enabled($setting)
    {
        if ($this->site_config->review == 'disable') {
            return false;
        }
        $cm_setting = $this->instance_rec->{'cm_' . $setting};
        if ($cm_setting == static::CM_OPTION_REQUIRED OR $cm_setting == static::CM_OPTION_DISABLE) {
            return false;
        }
        return true;
    }

    public function is_review_option_required($setting)
    {
        if (!$this->site_config->review) {
            return false;
        }
        $cm_setting = $this->instance_rec->{'cm_' . $setting};
        if ($cm_setting == static::CM_OPTION_REQUIRED) {
            return true;
        }
        return false;
    }

    public function get_preferred_string($string)
    {
        $default = Helper::get_string($string);
        if (empty($this->pref_rec)) {
            return $default;
        }
        $pref_field = 'text_' . $string;
        $pref_val = ($this->pref_rec->$pref_field) ?? null;
        if ($pref_val) {
            // Escapes the string for safe HTML output.
            return s($pref_val);
        }
        return $default;
    }

    public function process_form($data)
    {
        global $DB;

        $text_data = static::get_text_data_from_post();

        if ($this->pref_rec) {
            $pref = $this->pref_rec;
            $this->pop_pref_from_data($pref, $data, $text_data);

            $pref->timemodified = time();

            $DB->update_record('mod_goodhabits_prefs', $pref);
        } else {
            $pref = new \stdClass();
            $pref->instanceid = $this->instanceid;
            $pref->userid = $this->userid;
            $this->pop_pref_from_data($pref, $data, $text_data);

            $pref->created = time();
            $pref->timemodified = $pref->created;

            $DB->insert_record('mod_goodhabits_prefs', $pref);
        }
    }


    public static function get_text_data_from_post()
    {
        $text_data = [];

        foreach ($_POST as $key => $value) {
            $needle = 'wording_pref_';
            if (strpos($key, $needle) !== false) {
                $expl = explode($needle, $key);
                $cell = $expl[1];

                $field = 'text_overlay_' . $cell;
                $text_data[$field] = $value;
            }
        }

        return $text_data;
    }

    public function pop_pref_from_data($pref, $data, $text_data)
    {
        $pref->allow_reviews_admin = $data->allow_reviews_admin;
        $pref->allow_reviews_peers = $data->allow_reviews_peers;
        $pref->show_scores = $data->show_scores;
        $pref->enable_help = $data->enable_help;

        foreach ($text_data as $key => $val) {
            $pref->$key = $val;
        }
    }

    public static function get_pref_rec($instanceid, $userid)
    {
        global $DB;
        $params = [
            'instanceid' => $instanceid,
            'userid' => $userid,
        ];
        $pref = $DB->get_record('mod_goodhabits_prefs', $params);

        return $pref;
    }

    public static function get_cm_options()
    {
        return [
            static::CM_OPTION_DISABLE => Helper::get_string('disabled'),
            static::CM_OPTION_OPTIONAL_DEFAULT_DISALLOW => Helper::get_string('opt_def_disallow'),
            static::CM_OPTION_OPTIONAL_DEFAULT_ALLOW => Helper::get_string('opt_def_allow'),
            static::CM_OPTION_REQUIRED => Helper::get_string('required'),
        ];
    }

    public static function get_reviews_admin_default()
    {
        return static::CM_OPTION_OPTIONAL_DEFAULT_ALLOW;
    }

    public static function get_reviews_peers_default()
    {
        return static::CM_OPTION_OPTIONAL_DEFAULT_DISALLOW;
    }

//    public static function access_feature_as($instanceid)
//    {
////        global $PAGE, $USER;
////        $has_as_admin = has_capability('mod/goodhabits:review_as_admin', $PAGE->context);
////        $has_as_peer = has_capability('mod/goodhabits:review_as_peer', $PAGE->context);
////        $mgr = new PreferencesManager($instanceid, $USER->id);
////
////        $allow_admin = $mgr->get_review_status('reviews_peer');
////        $allow_peer = $mgr->get_review_status('reviews_peer');
////        if ($has_as_admin) {
////            return
////        }
//    }

    /**
     * Returns the user type the current user is accessing as -- admin or peer --
     *        or false if no access.
     *
     * @param $instanceid
     * @param $review_subject_id
     * @return false|string
     * @throws \coding_exception
     */
    public static function access_review_feature_as($instanceid, $review_subject_id = null)
    {
        global $PAGE, $USER;
        $access_as_admin = has_capability('mod/goodhabits:review_as_admin', $PAGE->context);
        $access_as_peer = has_capability('mod/goodhabits:review_as_peer', $PAGE->context);
        $other_required_caps = Reviewer::get_other_required_caps();
        $has_all_other = has_all_capabilities($other_required_caps, $PAGE->context);
        if (!$has_all_other) {
            return false;
        }

        $mgr = new PreferencesManager($instanceid, $USER->id);

        $allow_admin_mod = $mgr->get_review_mod_status('reviews_admin');
        if (!$allow_admin_mod) {
            // It is disabled for this activity.
            $access_as_admin = false;
        }

        if ($review_subject_id) {
            $subject_mgr = new PreferencesManager($instanceid, $review_subject_id);
            $subject_allow_admin = $subject_mgr->get_review_status('reviews_admin');
            if (!$subject_allow_admin) {
                $access_as_admin = false;
            }
        }

        $allow_peer = $mgr->get_review_status('reviews_peers');

        if (!$allow_peer) {
            // The current user must allow peer reviews to be a peer.
            $access_as_peer = false;
        }

        $is_peer_opt_in = $mgr->is_review_opt_in('reviews_peers');

        if ($is_peer_opt_in AND $access_as_peer) {
            $any_other_to_review = static::does_any_other_user_allow_peer_review($instanceid, $USER->id);
            if (!$any_other_to_review) {
                // They have the capability, but cannot use it, as there is no-one to review.
                $access_as_peer = false;
            }
        }
        if ($access_as_admin) {
            // Prefer access_as_admin if user has both.
            return static::ACCESS_AS_ADMIN;
        }
        if ($access_as_peer) {
            return static::ACCESS_AS_PEER;
        }
        return false;
    }

    public static function does_any_other_user_allow_peer_review($instanceid, $userid)
    {
        $all = static::get_users_who_allow_peer_review($instanceid);
        if (empty($all)) {
            $all = [];
        }
        $others = Helper::rm_from_array($all, $userid);
        return !empty($others);
    }

    public static function get_users_who_allow_peer_review($instanceid)
    {
        global $DB;

        $userids = $DB->get_fieldset('mod_goodhabits_prefs', 'userid',
            [
                'allow_reviews_peers' => 1,
                'instanceid' => $instanceid
            ]);
        return $userids;
    }

    public static function default_yes_setting($pref_rec, $setting)
    {
        if (empty($pref_rec)) {
            return true;
        }

        $val = $pref_rec->$setting;

        if (is_null($val)) {
            return true;
        }
        return $val;
    }

}