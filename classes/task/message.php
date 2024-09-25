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
 * Magic Message - scheduled task handler.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_magic\task;

use core\task\scheduled_task;
use auth_magic\campaign;
use core_user;

/**
 * Simple task to send follow up message for user after some days.
 */
class message extends scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('sendmessage', 'auth_magic');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        $campaigns = $this->fetch_followup_campaigns();
    }

    /**
     * Send followup message.
     */
    public function fetch_followup_campaigns() {
        global $DB;

        $sql = "SELECT u.*, cp.id as campaignid, cp.campaignowner, cu.id as usercampignrecordid, cp.followupmessagedelay
        FROM {auth_magic_campaigns_users} cu
        JOIN {user} u ON cu.userid = u.id
        JOIN {auth_magic_campaigns} cp ON cp.id = cu.campaignid AND cp.followupmessage <> 0
        WHERE u.deleted  <> 1 AND cu.followup <= 0 AND cp.visibility = :cpvisible AND cp.status = :cpavailable";
        // List of users to notify.
        $users = $DB->get_records_sql($sql, [
            'cpvisible' => 1, 'cpavailable' => campaign::STATUS_AVAILABLE,
        ], 0, 100);

        // No records to send.
        if (empty($users)) {
            return true;
        }

        $today = new \DateTime('Now', \core_date::get_user_timezone_object());
        $now = strtotime($today->format('Y-m-d'));

        $campaignowners = []; // Store campaignowner once they fetched.
        $supportuser = \core_user::get_support_user();

        // TODO: Fetch List of campaigns in single query.
        $campaigns = [];

        foreach ($users as $user) {

            $usercreated = date('Y-m-d', $user->timecreated);
            $date = new \DateTime($usercreated, \core_date::get_user_timezone_object());
            $delay = $user->followupmessagedelay; // Once the delay reached.
            $date->modify("+$delay days");

            if (strtotime($date->format('Y-m-d')) <= $now) {

                if ($user->campaignowner) {
                    if (array_key_exists($user->campaignowner, $campaignowners)) {
                        $sender = $campaignowners[$user->campignowner];
                    } else {
                        $sender = \core_user::get_user($user->campaignowner);
                        $campaignowners[$sender->id] = $sender;
                    }
                } else {
                    $sender = $supportuser;
                }
                // Find the campaign.
                if (array_key_exists($user->campaignid, $campaigns)) {
                    $campaign = $campaigns[$user->campaignid];
                } else {
                    $campaign = $DB->get_record('auth_magic_campaigns', ['id' => $user->campaignid]);
                    $campaigns[$campaign->id] = $campaign;
                }

                \auth_magic\campaign::send_followup_message($user, $campaign, $sender);
            }
        }

        return true;
    }
}
