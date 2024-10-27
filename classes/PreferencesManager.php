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

    public function __construct($instanceid, $userid) {
        $this->instanceid = $instanceid;
        $this->userid = $userid;
        $this->site_config = get_config('goodhabits');
        $this->instance_rec = Helper::get_instance_from_instance_id($instanceid);
        $this->pref_rec = static::get_pref_rec($instanceid, $userid);
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

    public static function display_review($instanceid)
    {
        global $PAGE, $USER;
        $has_as_admin = has_capability('mod/goodhabits:review_as_admin', $PAGE->context);
        $has_as_peer = has_capability('mod/goodhabits:review_as_peer', $PAGE->context);

        $mgr = new PreferencesManager($instanceid, $USER->id);

        $allow_peer = $mgr->get_review_status('reviews_peer');

        if (!$allow_peer) {
            // The current user must allow peer reviews to be a peer.
            $has_as_peer = false;
        }

        if ($has_as_peer) {
            $any_other_to_review = static::does_any_other_user_allow_peer_review($instanceid, $USER->id);
            if (!$any_other_to_review) {
                // They have the capability, but cannot use it, as there is no-one to review.
                $has_as_peer = false;
            }
        }
        $canreview = ($has_as_admin OR $has_as_peer);
        return $canreview;
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

}