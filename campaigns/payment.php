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
require_once($CFG->dirroot."/auth/magic/lib.php");

use auth_magic\campaign;

if (!is_enabled_auth('magic')) {
    throw new moodle_exception(get_string('pluginisdisabled', 'auth_magic'));
}

require_login();
require_sesskey();

// PAGE URL.
$userid = optional_param('userid', null, PARAM_INT);
$campaignid = optional_param('campaignid', null, PARAM_INT);

$url = new moodle_url('/auth/magic/campaigns/payment.php', ['userid' => $userid, 'campaignid' => $campaignid,
    'sesskey' => sesskey()]);

$context = context_system::instance();
$strviewcampaign = get_string('strpaymentcampaign', 'auth_magic');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title("$SITE->fullname: ". $strviewcampaign);

$PAGE->set_pagetype('campaigns-payment-page');

if (!auth_magic_is_campaign_signup_user($userid, $campaignid)) {
    // Throw error to non access the.
    throw new moodle_exception('invalidrequest');
}

$campaigninfo = campaign::instance($campaignid);
$campaign = $campaigninfo->get_campaign();


if (!auth_magic_is_paid_campaign($campaign) && !$campaigninfo->is_coupon_user()) {
    return new moodle_url('/my');
}

$PAGE->set_heading($campaign->title);

// Page content display started.
echo $OUTPUT->header();

$template = [
    'cost' => \core_payment\helper::get_cost_as_string($campaign->paymentinfo->fee, $campaign->paymentinfo->currency),
    'instanceid' => $campaign->id,
    'successurl' => \auth_magic\payment\service_provider::get_success_url('campaign', $campaign->id)->out(false),
    'description' => get_string('purchasecampaigndescription', 'auth_magic', $campaign->title),
];

echo $OUTPUT->render_from_template('auth_magic/campaign_payment', $template);

// Footer.
echo $OUTPUT->footer();
