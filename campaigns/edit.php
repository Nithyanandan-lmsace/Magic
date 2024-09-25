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

// Require admin library.
require_once($CFG->libdir.'/adminlib.php');

require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_helper.php');

require_login();
require_sesskey();

if (!is_enabled_auth('magic')) {
    throw new moodle_exception(get_string('pluginisdisabled', 'auth_magic'));
}

$context = context_system::instance();


$id = optional_param('id', 0, PARAM_INT);
if ($id) {
    if (!$DB->get_records('auth_magic_campaigns', ['campaignowner' => $USER->id, 'id' => $id])
        && !has_capability("auth/magic:viewcampaignlists", $context)) {
        throw new moodle_exception(get_string('campaigns:notaccess', 'auth_magic'));
    }
} else {
    if ((!has_capability("auth/magic:createcampaign", $context))) {
        throw new moodle_exception(get_string('campaigns:notaccess', 'auth_magic'));
    }
}

// Prepare filearea for campaign records.
if ($id) {
    $campaign = \campaign_helper::get_campaign($id);
    $campaign->id = $id;
    $campaign = \campaign_helper::prepare_editor_files($campaign);
    $campaign = \campaign_helper::prepare_filemanger_files($campaign);

} else {
    $campaign = new stdClass();
    $campaign = \campaign_helper::prepare_editor_files($campaign);
    $campaign = \campaign_helper::prepare_filemanger_files($campaign);
}
// Page values.
$url = new moodle_url('/auth/magic/campaigns/edit.php', ['id' => $id, 'sesskey' => sesskey()]);
// Setup page values.
$strcreatecampaign = get_string('createcampaign', 'auth_magic');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title("$SITE->fullname: ". $strcreatecampaign);
$PAGE->set_heading($SITE->fullname);
// Setup the breadcrums for qick access.
$PAGE->navbar->add(get_string('pluginname', 'auth_magic'), new moodle_url('/admin/category.php',
    ['category' => 'authmagicsettings'])
);
$PAGE->navbar->add(get_string('strcampaigns', 'auth_magic'), new moodle_url('/auth/magic/campaigns/manage.php'));
$PAGE->navbar->add(get_string('createcampaign', 'auth_magic'), new moodle_url('/auth/magic/campaigns/edit.php'));

if (is_siteadmin()) {
    $PAGE->set_pagelayout('admin');
}

$PAGE->requires->js_call_amd('auth_magic/magic', 'init', [['campaignformfield' => true,
    'contextid' => $context->id, 'currentcampaignid' => $id]]);
// Edit create campaigns form.
$campaignform = new \auth_magic\form\campaigns_form(null, ['id' => $id]);
$campaignviewurl = new moodle_url('/auth/magic/campaigns/manage.php');

// Process the submitted items form data.
if ($formdata = $campaignform->get_data()) {
    $result = \campaign_helper::manage_instance($formdata);
    if ($result) {
        redirect($campaignviewurl);
    }
} else if ($campaignform->is_cancelled()) {
    redirect($campaignviewurl);
}

// Setup the campaign to the form, if the form id param available.
if ($id !== null && $id > 0) {
    // Set the campaign data to the campaign edit form.
    $campaignform->set_data($campaign);
} else {
    $campaignform->set_data($campaign);
}

// Page content display started.
echo $OUTPUT->header();

// Campaign heading.
echo $OUTPUT->heading(get_string('createcampaign', 'auth_magic'));

// Display the campaigns form for create or edit.
echo $campaignform->display();

// Footer.
echo $OUTPUT->footer();
