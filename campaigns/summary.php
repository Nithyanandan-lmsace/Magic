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
use auth_magic\campaign;


if (!is_enabled_auth('magic')) {
    throw new moodle_exception(get_string('pluginisdisabled', 'auth_magic'));
}

// PAGE URL.
$url = new moodle_url('/auth/magic/campaigns/summary.php');

$code = optional_param('code', null, PARAM_ALPHANUMEXT);
$token = optional_param('token', null, PARAM_ALPHANUMEXT);

if ($code) {
    $campaign = \auth_magic\campaign::get_campaign_fromcode($code);
    if (!$campaign || empty($campaign)) {
        throw new moodle_exception('campaigncodenotfound', 'auth_magic');
    }
    $campaignid = $campaign->id;
    $url->param('code', $code);
} else {
    $campaignid = required_param('campaignid', PARAM_INT);
    $url->param('campaignid', $id);
}


$context = context_system::instance();
$strvcampaignsummary = get_string('strvcampaignsummary', 'auth_magic');

// Setup page values.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title("$SITE->fullname: ". $strvcampaignsummary);

$campaigninstance = campaign::instance($campaignid);

// For Style purpose.
$PAGE->add_body_class('magic-campaign-signup');


// Page content display started.
echo $OUTPUT->header();

echo $campaigninstance->buildform(null);

// Footer.
echo $OUTPUT->footer();




