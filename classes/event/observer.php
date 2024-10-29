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
 * Event observer for Magic authentication.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_magic\event;

defined('MOODLE_INTERNAL') || die();

use auth_magic\auth_magic_test;
use auth_magic\campaign;
use moodle_url;

require_once($CFG->dirroot. "/auth/magic/auth.php");
require_once($CFG->dirroot. "/auth/magic/lib.php");
require_once($CFG->dirroot. "/auth/magic/campaigns/campaign_helper.php");

/**
 * Event observer for auth_magic.
 */
class observer {

    /**
     * Create user data creation request when the user is created.
     *
     * @param \core\event\user_created $event
     */
    public static function created_user_data_request(\core\event\user_created $event) {
        global $USER, $CFG;
        $userid = $event->objectid;
        $usercontext = \context_user::instance($userid);
        $user = \core_user::get_user($userid);
        if ($user->auth == 'magic') {
            if (isloggedin()) {
                require_once($CFG->dirroot."/auth/magic/lib.php");
                auth_magic_parent_role_assign($USER->id, $usercontext->id);
            }
            $auth = get_auth_plugin('magic');
            // Request login url.
            $auth->create_magic_instance($user);
        }
        // Trigger the relative assignment to assign users.
        \auth_magic\roleassignment::user_updated($event);
        return true;
    }

    /**
     * Create user data deletion request when the user is deleted.
     *
     * @param \core\event\user_deleted $event
     *
     * @return bool
     */
    public static function create_delete_data_request(\core\event\user_deleted $event) {
        global $DB;
        $userid = $event->objectid;
        $DB->delete_records('auth_magic_loginlinks', ['userid' => $userid]);
        $DB->delete_records('auth_magic_campaigns_users', ['userid' => $userid]);
        $DB->delete_records('auth_magic_payment_logs', ['userid' => $userid]);
        $DB->delete_records('auth_magic_roleassignments', ['userid' => $userid]);
        delete_user_key('auth/magic', $userid);
        return true;
    }

    /**
     * Create user data update request when the user is updated.
     *
     * @param \core\event\user_updated $event
     *
     * @return bool
     */
    public static function create_update_data_request(\core\event\user_updated $event) {
        global $DB, $USER, $CFG;
        $userid = $event->objectid;
        $usercontext = \context_user::instance($userid);
        $user = \core_user::get_user($userid);
        if ($user->auth == 'magic') {
            auth_magic_parent_role_assign($USER->id, $usercontext->id);
            $auth = get_auth_plugin('magic');
            // Request login url.
            $auth->create_magic_instance($user);
        }

        // Trigger the relative assignment to assign users.
        \auth_magic\roleassignment::user_updated($event);
        return true;
    }

    /**
     * Create magic authentication user list viewed request.
     *
     * @param \core\event\user_list_viewed $event
     */
    public static function create_user_list_viewed_request(\core\event\user_list_viewed $event) {
        global $PAGE;
        $data = $event->get_data();
        if (isset($data['objecttable']) && isset($data['courseid']) && $data['contextlevel'] == CONTEXT_COURSE) {
            $courseid = $data['courseid'];
            $coursecontext = \context_course::instance($courseid);
            $params['hascourseregister'] = has_capability("auth/magic:cancoursequickregistration", $coursecontext)
                && auth_magic_is_course_manual_enrollment($courseid) && is_enabled_auth('magic');
            $url = new \moodle_url('/auth/magic/registration.php');
            $params['url'] = $url->out(false);
            $params['courseid'] = $courseid;
            $params['strquickregister'] = get_string('quickregistration', 'auth_magic');
            $PAGE->requires->js_call_amd('auth_magic/magic', 'init', [$params]);
        }
    }


    /**
     * Create user logged in request.
     *
     * @param \core\event\user_loggedin $event
     */
    /* public static function create_user_loggedin_request(\core\event\user_loggedin $event) {
        global $PAGE, $DB;
        $data = $event->get_data();
        $userid = $data['userid'];
        auth_magic_user_confirmation_campaign_assignments($userid);
    } */


    /**
     * Create group member added request.
     *
     * @param \core\event\group_member_added $event
     */
    public static function create_group_member_request(\core\event\group_member_added $event) {
        global $DB;
        $groupid = $event->objectid;
        if ($record = $DB->get_record('auth_magic_campaign_groups', ['groupid' => $groupid])) {
            $campaign = $DB->get_record('auth_magic_campaigns', ['id' => $record->campaignid]);
            // Remove the group enrolment key based on campaign group capacity.
            if ($campaign->groupcapacity && $campaign->groupcapacity <= auth_magic_count_gradebook_role_groupusers($groupid)) {
                $DB->set_field('groups', 'enrolmentkey', '', ['id' => $groupid]);
            }
        }
    }


    /**
     * Overserve the config log create event, check is the relative roles allocation changes.
     * Then trigger the role assignments to update the roles and users accordinly.
     *
     * @param \core\event\config_log_created $event
     * @return void
     */
    public static function config_log_created(\core\event\config_log_created $event) {

        $data = $event->get_data();
        if (stripos($data['other']['name'], 'roleassignment_') !== false && $data['other']['plugin'] == 'auth_magic') {
            \auth_magic\roleassignment::role_allocation_field_updated($data['other']);
        }
    }

}
