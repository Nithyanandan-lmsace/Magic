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
 * Create the new campaign.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Require config.
require(__DIR__.'/../../../config.php');
require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_helper.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->dirroot . '/login/lib.php');
require_once($CFG->dirroot. '/auth/magic/campaigns/locallib.php');

use auth_magic\campaign;

if (!is_enabled_auth('magic')) {
    throw new moodle_exception(get_string('pluginisdisabled', 'auth_magic'));
}

// PAGE URL.
$url = new moodle_url('/auth/magic/campaigns/view.php');
$code = optional_param('code', null, PARAM_ALPHANUMEXT);
$token = optional_param('token', null, PARAM_ALPHANUMEXT);
$coupon = optional_param('coupon', null, PARAM_ALPHANUMEXT);
$submissionuser = optional_param('submissionuser', 0, PARAM_INT);

if ($code) {
    $campaign = \auth_magic\campaign::get_campaign_fromcode($code);
    if (!$campaign || empty($campaign)) {
        throw new moodle_exception('campaigncodenotfound', 'auth_magic');
    }
    $campaignid = $campaign->id;
    $url->param('code', $code);
}

if ($token) {
    $url->param('token', $token);
}

if ($coupon) {
    $url->param('coupon', $coupon);
}

if ($submissionuser) {
    $url->param('submissionuser', $token);
}

if (!$code && !$token && !$submissionuser) {
    $campaignid = required_param('campaignid', PARAM_INT);
    $url->param('campaignid', $id);
}

// Page values.
$context = context_system::instance();
$strviewcampaign = get_string('strviewcampaign', 'auth_magic');
// Setup page values.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title("$SITE->fullname: ". $strviewcampaign);
$fields = campaign_helper::get_campaign_fields_instance($campaignid)->get_fields();
// For Style purpose.
$PAGE->add_body_class('magic-campaign-signup');

$passwordform = new campaign_rule_form();

$campaigninstance = campaign::instance($campaignid);

$campaignrecord = $campaigninstance->get_campaign();


$campaignhelper = new campaign_helper($campaignid);

$campaign = \campaign_helper::get_campaign($campaign->id);

if ($confirmdata = $passwordform->get_data()) {
    $campaigninstance->verify_password($confirmdata);
    redirect($PAGE->url);
}


$params = ['campaignid' => $campaignid , 'code' => $code, 'token' => $token,
'coupon' => $coupon, 'fields' => $fields];

$campaignmanageform = null;

if (!$submissionuser) {
    $campaignmanageform = new \auth_magic\form\campaigns_manageform(null, $params);
    // Process the submitted items form data.
    if ($user = $campaignmanageform->get_data()) {
        $parentuser = null;
        $userenrolmentkey = isset($user->enrolpassword) ? $user->enrolpassword : '';
        if ($DB->record_exists('user', ['email' => $user->email])) {
            $redirectstr = get_string('campaignassignapplied', 'auth_magic');
            $newuser = $DB->get_record('user', ['email' => $user->email]);
            $customfieldvalues = auth_magic_managerole_assignments_customvalues($user, $campaignrecord->approvalroles);
            $campaigninstance->assign_user($user, $newuser->id, $coupon);
            $assignmentobj = \auth_magic\roleassignment::create($newuser->id);
            $parentuser = $assignmentobj->manage_role_assignments([], $customfieldvalues);

        } else {
            $redirectstr = get_string('signupsuccess', 'auth_magic');
            // Add missing required fields.
            $user = campaign_helper::get_campaign_fields_instance($campaignid)->reset_placeholder_values($user);
            if (trim($user->username) === '') {
                $user->username = $user->email;
            }
            $user = campaign_helper::signup_setup_new_user($user);
            // Plugins can perform post sign up actions once data has been validated.
            core_login_post_signup_requests($user);
            $authplugin = get_auth_plugin($user->auth);
            $user->password = isset($user->password) && $authplugin->is_internal() ? hash_internal_user_password($user->password) : '';
            // Prints notice and link to login/index.php.
            if ($userid = user_create_user($user, false, false)) {
                $user->id = $userid;
                $newuser = $DB->get_record('user', ['id' => $userid]);
                // Sent the confirmation link.
                if ($campaignrecord->emailconfirm == campaign::ENABLE && $campaignrecord->approvaltype != 'optionalin') {
                    auth_magic_send_confirmation_email($newuser, new moodle_url('/auth/magic/confirm.php'));
                }

                $usercontext = context_user::instance($newuser->id);
                // Update preferences.
                useredit_update_user_preference($newuser);
                // Save custom profile fields data.
                profile_save_data($user);

                if ($authplugin->is_internal() && empty($user->password) && $campaignrecord->approvaltype != 'optionalin') {
                    setnew_password_and_mail($newuser);
                    unset_user_preference('create_password', $newuser);
                    set_user_preference('auth_forcepasswordchange', 1, $newuser);
                }

                $campaigninstance->assign_user($user, $newuser->id, $coupon);

                \core\event\user_created::create_from_userid($newuser->id)->trigger();
                // Login user automatically.
                if ($campaignrecord->emailconfirm != campaign::ENABLE && $campaignrecord->approvaltype != 'optionalin') {
                    complete_user_login($newuser);
                }
                // After user complete login then Set the user unconfirmed.
                if ($campaignrecord->approvaltype == 'optionalin') {
                    $DB->set_field("user", "confirmed", 0, ["id" => $newuser->id]);
                } else if ($campaignrecord->emailconfirm == campaign::PARTIAL) {
                    $DB->set_field("user", "confirmed", 0, ["id" => $newuser->id]);
                    if ($campaignrecord->approvaltype != 'optionalin') {
                        auth_magic_send_confirmation_email($newuser, new moodle_url('/auth/magic/confirm.php'));
                    }
                }
            }
        }

        if (!auth_magic_is_paid_campaign($campaign, $userenrolmentkey) && ($campaignrecord->emailconfirm != campaign::ENABLE
            || isloggedin()) && $campaignrecord->approvaltype != 'optionalin') {
            // Assign to the campaign cohorts, roles, parent.
            $campaignhelper->process_campaign_assignments($newuser, false, $parentuser);
        }
        $campaigninstance->campaign_after_submission($newuser, $redirectstr);

    } else if ($campaignmanageform->is_cancelled()) {
        redirect(get_login_url());
    }
}

$content = $campaigninstance->buildform($campaignmanageform);
// TODO: Not available handler.

// Page content display started.
echo $OUTPUT->header();

echo $content;

// Footer.
echo $OUTPUT->footer();
