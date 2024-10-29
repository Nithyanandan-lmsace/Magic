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
 * Payment subsystem callback implementation for auth_magic.
 *
 * @package    auth_magic
 * @category   payment
 * @copyright  2020 Shamim Rezaie <shamim@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic\payment;

/**
 * Payment subsystem callback implementation for auth_magic.
 *
 * @copyright  2020 Shamim Rezaie <shamim@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class service_provider implements \core_payment\local\callback\service_provider {

    /**
     * Callback function that returns the enrolment cost and the accountid
     * for the course that $instanceid enrolment instance belongs to.
     *
     * @param string $paymentarea Payment area
     * @param int $instanceid The enrolment instance id
     * @return \core_payment\local\entities\payable
     */
    public static function get_payable(string $paymentarea, int $instanceid): \core_payment\local\entities\payable {
        global $DB, $SESSION;
        $instance = $DB->get_record('auth_magic_campaigns_payment', ['campaignid' => $instanceid], '*', MUST_EXIST);
        $payfee = $instance->fee;
        if (isset($SESSION->auth_magic_teamusers)) {
            $payfee = $SESSION->auth_magic_teamusers * $payfee;
        }
        return new \core_payment\local\entities\payable($payfee, $instance->currency, $instance->paymentaccount);
    }

    /**
     * Callback function that returns the URL of the page the user should be redirected to in the case of a successful payment.
     *
     * @param string $paymentarea Payment area
     * @param int $instanceid The enrolment instance id
     * @return \moodle_url
     */
    public static function get_success_url(string $paymentarea, int $instanceid): \moodle_url {
        global $DB;
        $instance = $DB->get_record('auth_magic_campaigns', ['id' => $instanceid], '*', MUST_EXIST);
        return new \moodle_url('/my');
    }

    /**
     * Callback function that delivers what the user paid for to them.
     *
     * @param string $paymentarea
     * @param int $instanceid The enrolment instance id
     * @param int $paymentid payment id as inserted into the 'payments' table, if needed for reference
     * @param int $userid The userid the order is going to deliver to
     * @return bool Whether successful or not
     */
    public static function deliver_order(string $paymentarea, int $instanceid, int $paymentid, int $userid): bool {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_helper.php');
        if (!$user = $DB->get_record('user', ['id' => $userid, 'mnethostid' => $CFG->mnet_localhost_id])) {
            throw new \moodle_exception('nousers');
        }

        $record = new \stdClass;
        $record->campaignid = $instanceid;
        $record->userid = $userid;
        $record->paiduser = $userid;
        $record->paymentid = $paymentid;
        $record->status = 'completed';
        $record->timecreated = time();
        $record->timemodified = time();
        $DB->insert_record('auth_magic_payment_logs', $record);

        $campaignhelper = new \campaign_helper($instanceid);
        // Assign the campaign assignments.
        $campaignhelper->process_campaign_assignments($user);
        return true;
    }
}
