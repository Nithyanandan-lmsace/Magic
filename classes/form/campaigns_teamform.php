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
 * Form for campaign user access the campaigns teamform.
 *
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package auth_magic
 */
namespace auth_magic\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_helper.php');

/**
 * Create new campaigns teams form.
 *
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class campaigns_teamform extends \moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        global $USER, $DB;

        $mform = $this->_form;

        $campaignid = $this->_customdata['campaignid'];
        $code = $this->_customdata['code'];
        $token = $this->_customdata['token'];
        $coupon = $this->_customdata['coupon'];

        $campaignhelper = new \campaign_helper($campaignid);

        $mform->addElement('hidden', 'campaignid', $campaignid);
        $mform->setType('campaignid', PARAM_INT);

        $mform->addElement('hidden', 'userid', $USER->id);
        $mform->setType('userid',  PARAM_INT);

        $mform->addElement('hidden', 'code', $code);
        $mform->setType('code', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'coupon', $coupon);
        $mform->setType('coupon', PARAM_ALPHANUMEXT);

        // Get the team members for the parent user.
        $teamusers = [];
        $teamusersoptions = ['multiple' => true];
        $childusers = auth_magic_get_parent_child_users($USER->id, false, false);
        if ($childusers) {
            $childusers = user_get_users_by_id($childusers);
            $teamusers = auth_magic_get_usernames_choices($childusers);
            unset($teamusers[0]);
        }

        if ($teamusers) {
            foreach ($teamusers as $teamuserid => $teamuser) {
                if ($DB->record_exists('auth_magic_campaigns_users', ['campaignid' => $campaignid, 'userid' => $teamuserid])) {
                    unset($teamusers[$teamuserid]);
                }
            }
        }

        // Get the admin user for the all users.
        if (is_siteadmin()) {
            $teamusers = [];
            $teamusersoptions = [
                'ajax' => 'core_user/form_user_selector',
                'multiple' => true,
                'valuehtmlcallback' => function($userid) : string {
                    $user = \core_user::get_user($userid);
                    return fullname($user);
                },
            ];
        }

        if ($teamusers || is_siteadmin()) {

            $mform->addElement('autocomplete', 'teammembers', get_string('searchusers', 'auth_magic'),
                $teamusers, $teamusersoptions);
            $mform->addElement('static', 'teaminfo', '', get_string('campaign:teammemberinfo', 'auth_magic'));
            $mform->setType('teaminfo', PARAM_INT);
            $this->add_action_buttons(true, get_string('strapply', 'auth_magic'));
        } else {
            $mform->addElement('static', 'noteaminfo', '', get_string('campaign:noteaminfo', 'auth_magic'));
        }
    }
}
