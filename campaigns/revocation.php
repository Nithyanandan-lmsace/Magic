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
require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_helper.php');

use auth_magic\campaign;

if (!is_enabled_auth('magic')) {
    throw new moodle_exception(get_string('pluginisdisabled', 'auth_magic'));
}


// PAGE URL.
$userid = required_param('userid',  PARAM_INT);
$campaignid = required_param('campaignid',  PARAM_INT);
$data = required_param('data', PARAM_RAW);

$redirecturl = optional_param('redirecturl', null, PARAM_RAW);

$url = new \moodle_url('/auth/magic/campaigns/revocation.php', ['userid' => $userid, 'campaignid' => $campaignid]);

$context = context_system::instance();
$strrevocationcampaign = get_string('strrevocationcampaign', 'auth_magic');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title("$SITE->fullname: ". $strrevocationcampaign);

if (!auth_magic_is_campaign_signup_user($userid, $campaignid)) {
    // Throw error to non access the.
    throw new moodle_exception('invalidrequest');
}


$dataelements = explode('/', $data, 2); // Stop after 1st slash. Rest is username. MDL-7647.
$usersecret = $dataelements[0];
$username   = $dataelements[1];


if (!$relateduser = get_complete_user_data('username', $username)) {
    throw new \moodle_exception('cannotfinduser', '', '', s($username));
}

complete_user_login($relateduser);
\core\session\manager::apply_concurrent_login_limit($relateduser->id, session_id());


require_login();

$user = $DB->get_record('user', ['id' => $userid]);
$campaignhelper = new campaign_helper($campaignid);
$campaignhelper->process_campaign_assignments($user, true);

$campaigninstance = campaign::instance($campaignid);
$campaignrecord = $campaigninstance->get_campaign();

// Check the campaign approval type and change to user unconfirmed.
if ($campaignrecord->approvaltype == 'fulloptionout') {
    // Set the user unconfirmed and set the auth to nologin.
    $DB->set_field("user", "confirmed", 0, ["id" => $user->id]);
    $DB->set_field("user", "auth", 'nologin', ["id" => $user->id]);
}


if (!$DB->record_exists('auth_magic_revocation_logs', ['userid' => $user->id, 'campaignid' => $campaignid])) {
    $record = new stdClass;
    $record->userid = $user->id;
    $record->campaignid = $campaignid;
    $record->timecreated = time();
    $DB->insert_record('auth_magic_revocation_logs', $record);
}

if ($redirecturl != null) {
    redirect(urldecode($redirecturl));
}

$PAGE->navbar->add(get_string("revoked", 'auth_magic'));
$PAGE->set_title(get_string("revoked", 'auth_magic'));
$PAGE->set_heading($COURSE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
echo "<h3>".get_string("thanks").", ". fullname($user) . "</h3>\n";
echo "<p>".get_string("revoked", 'auth_magic')."</p>\n";
echo $OUTPUT->single_button(new \moodle_url('/my'), get_string('continue'));
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
exit;

