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

class preferences extends \moodleform
{

    public function definition() {
        global $USER, $PAGE;
        $mform = $this->_form;
        $instanceid = (isset($this->_customdata['instance'])) ? $this->_customdata['instance'] : 0;
        
        $mgr = new PreferencesManager($instanceid, $USER->id);

        $mform->addElement('header', 'privacy_header', Helper::get_string('prefs_tracker_privacy_header'));

        $mform->addElement('advcheckbox', 'allow_reviews_admin', Helper::get_string('allow_reviews_admin'));
        $mform->addElement('advcheckbox', 'allow_reviews_peers', Helper::get_string('allow_reviews_peers'));

        $mform->addHelpButton('allow_reviews_admin', 'allow_reviews_admin', 'mod_goodhabits');
        $mform->addHelpButton('allow_reviews_peers', 'allow_reviews_peers', 'mod_goodhabits');
        $mform->setType('instance', PARAM_INT);

        $allow_review_admin = $mgr->get_review_status('reviews_admin');
        $allow_review_peers = $mgr->get_review_status('reviews_peers');

        $show_scores = $mgr->show_scores();

        $enable_help = $mgr->enable_help();

        $this->set_data(
            [
                'allow_reviews_admin' => $allow_review_admin,
                'allow_reviews_peers' => $allow_review_peers,
                'show_scores' => $show_scores,
                'enable_help' => $enable_help,
            ]
        );

        if ($allow_review_peers) {
            // Then check that user also has required caps to review.
            $context = $PAGE->context;

            $caps = Reviewer::get_peer_required_caps();

            $has_all = has_all_capabilities($caps, $context);
            if (!$has_all) {
                Helper::form_warning_text($mform, Helper::get_string('lacking_peer_caps'));
            }
        }

        if (!$mgr->is_review_option_enabled('reviews_admin')) {
            $mform->freeze('allow_reviews_admin');
        }

        if (!$mgr->is_review_option_enabled('reviews_peers')) {
            $mform->freeze('allow_reviews_peers');
        }


        // *** Appearance ***.

        $mform->addElement('header', 'appearance_header', Helper::get_string('prefs_appearance'));

        $mform->addElement('advcheckbox', 'enable_help', Helper::get_string('enable_help'));
        $mform->addHelpButton('enable_help', 'enable_help', 'mod_goodhabits');

        $mform->addElement('advcheckbox', 'show_scores', Helper::get_string('show_scores'));
        $mform->addHelpButton('show_scores', 'show_scores', 'mod_goodhabits');

        $cells_text = [
            1 => $mgr->get_preferred_string('overlay_1_1'),
            2 => $mgr->get_preferred_string('overlay_1_2'),
            3 => $mgr->get_preferred_string('overlay_1_3'),
            4 => $mgr->get_preferred_string('overlay_2_1'),
            5 => $mgr->get_preferred_string('overlay_2_2'),
            6 => $mgr->get_preferred_string('overlay_2_3'),
            7 => $mgr->get_preferred_string('overlay_3_1'),
            8 => $mgr->get_preferred_string('overlay_3_2'),
            9 => $mgr->get_preferred_string('overlay_3_3'),
        ];


        $mform->addElement('html', '
<br />
            <p>' . Helper::get_string('grid_box_wording_intro') . '</p>
            <table class="grid-box-wording">
    <tr>
        <td class="grid-box-wording-cell" data-cell="1_1">' . $cells_text[1] . '</td>
        <td class="grid-box-wording-cell" data-cell="1_2">' . $cells_text[2] . '</td>
        <td class="grid-box-wording-cell" data-cell="1_3">' . $cells_text[3] . '</td>
    </tr>
    <tr>
        <td class="grid-box-wording-cell" data-cell="2_1">' . $cells_text[4] . '</td>
        <td class="grid-box-wording-cell" data-cell="2_2">' . $cells_text[5] . '</td>
        <td class="grid-box-wording-cell" data-cell="2_3">' . $cells_text[6] . '</td>
    </tr>
    <tr>
        <td class="grid-box-wording-cell" data-cell="3_1">' . $cells_text[7] . '</td>
        <td class="grid-box-wording-cell" data-cell="3_2">' . $cells_text[8] . '</td>
        <td class="grid-box-wording-cell" data-cell="3_3">' . $cells_text[9] . '</td>
    </tr>
</table>');

        $mform->addElement('hidden', 'instance', $instanceid);

        $this->add_action_buttons();
    }

}
