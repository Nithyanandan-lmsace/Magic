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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. "/auth/magic/campaigns/campaign_helper.php");

/**
 * Simple task to send follow up message for user after some days.
 */
class campaign_expiry_actions extends scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('campaignexpirycheck', 'auth_magic');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $DB;
        mtrace("running the magic campaign expiry task");
        // Fetch campaigns that are expired but haven't had their post-expiry actions triggered yet.
        $time = time();
        $sql = "SELECT * FROM {auth_magic_campaigns} WHERE expirydate >= :time AND expirytime != 0";
        $campaigns = $DB->get_records_sql($sql, ['time' => $time]);

        foreach ($campaigns as $campaign) {
            mtrace("running the campaign expiry task: " . $campaign->title);
            $campaign = \campaign_helper::get_campaign($campaign->id);
            // Handle pre-expiry notifications.
            $this->handle_campaign_notifications($campaign);
            // Handle post-expiry actions.
            if ($time >= $campaign->expirydate) {
                $this->handle_post_expiry_actions($campaign);
            }
        }
    }

    /**
     * Handler for post expiry actions.
     * @param object $campaign.
     */
    private function handle_post_expiry_actions($campaign) {
        mtrace("running handle_post_expiry_actions: " . $campaign->title);
        $campaignhelper = new \campaign_helper($campaign->id);
        return $campaignhelper->process_expiry_campaign_actions();
    }

    /**
     * Handle campaign notification.
     * @param object $campaign
     */
    public function handle_campaign_notifications($campaign) {
        global $DB;
        mtrace("running handle_campaign_notifications: " . $campaign->title);
        $campaignhelper = new \campaign_helper($campaign->id);
        $campaignhelper->send_expiry_notification($campaign);
    }
}
