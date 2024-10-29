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
    $managereturnurl = $PAGE->url . "#usertab";
    $campaignmanageform = new \auth_magic\form\campaigns_manageform($managereturnurl, $params);
    // Process the submitted items form data.
    if ($user = $campaignmanageform->get_data()) {
        $campaignhelper->update_campaign_manageform($user, $params);

    } else if ($campaignmanageform->is_cancelled()) {
        redirect(get_login_url());
    }

    $selfreturnurl = $PAGE->url . "#myseltab";
    $campaignselfform = new \auth_magic\form\campaigns_selfform($selfreturnurl, $params);

    if ($selfformdata = $campaignselfform->get_data()) {
        // Need to update self form data.
        $campaignhelper->update_campaign_selfform($selfformdata);
    }

    $teamreturnurl = $PAGE->url . "#teamtab";
    $campaignteamform = new \auth_magic\form\campaigns_teamform($teamreturnurl, $params);

    if ($teamformdata = $campaignteamform->get_data()) {
        // Need to update team form data.
        $campaignhelper->update_campaign_teamform($teamformdata);
    }
}

$content = $campaigninstance->buildform($campaignmanageform, $campaignselfform, $campaignteamform);
// TODO: Not available handler.

// Page content display started.
echo $OUTPUT->header();

echo $content;

// Footer.
echo $OUTPUT->footer();
