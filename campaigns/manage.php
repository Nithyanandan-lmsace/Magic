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
 * Display list of campaigns.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_courseformat\external\get_state;

require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_helper.php');
require_once($CFG->dirroot.'/auth/magic/classes/table/campaign_table.php');

require_login();

if (!is_enabled_auth('magic')) {
    throw new moodle_exception(get_string('pluginisdisabled', 'auth_magic'));
}

$context = context_system::instance();
if (!auth_magic_is_campaignowner_see_campaignlist() && !has_capability("auth/magic:viewcampaignlists", $context)) {
    throw new moodle_exception(get_string('campaigns:notaccess', 'auth_magic'));
}

// Campaign action.
$id = optional_param('id', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHAEXT);

// Page values.
$url = new moodle_url('/auth/magic/campaigns/manage.php');

$strcampaigns = get_string('strcampaigns', 'auth_magic');
// Setup page values.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->navbar->add($strcampaigns, $url);
$PAGE->set_title("$SITE->fullname: ". $strcampaigns);
$PAGE->set_heading($SITE->fullname);

if (is_siteadmin()) {
    $PAGE->set_pagelayout('admin');
}

$table = new auth_magic\table\campaigns_table($context->id);
$table->define_baseurl($PAGE->url);

// Verfiy the user session.
if ($action !== null && confirm_sesskey()) {
    // Every action is based on campaign, campaign id param should exist.
    $id = required_param('id', PARAM_INT);
    // Create campaign instance. Actions are performed in campaign instance.
    $campaign = \campaign_helper::instance($id);

    $transaction = $DB->start_delegated_transaction();
    // Perform the requested action.
    switch ($action) :
        // Triggered action is delete, then init the deletion of campaign.
        case 'delete':
            // Delete the campaign.
            if ($campaign->delete_campaign()) {
                // Notification to user for campaign deleted success.
                \core\notification::success(get_string('campaigns:campaigndeleted', 'auth_magic'));
            }
            break;
        case "hidecampaign":
            // Disable the campaign visibility.
            $campaign->update_visible(false);
            break;
        case "showcampaign":
            // Enable the campaign.
            $campaign->update_visible(true);
            break;
    endswitch;

    // Allow to update the changes to database.
    $transaction->allow_commit();
    // End of any action redirect to overview page for clear the params from url.
    redirect($url);
}

// Set copy lnk.
$params = [
    'copycampaignlink' => true,
    'campaigntitle' => get_string('campaignlink', 'auth_magic'),
];
$PAGE->requires->js_call_amd('auth_magic/magic', 'init', [$params]);
// Page content display started.
echo $OUTPUT->header();

// Campaign heading.
echo $OUTPUT->heading(get_string('managecampaign', 'auth_magic'));

// Add the create item and create campaign buttons.
echo \campaign_helper::create_campaign_button();

$table->out(25, false);
// Footer.
echo $OUTPUT->footer();
