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
 * Privacy implementation for Magic authentication.
 *
 * @package   auth_magic
 * @copyright bdecent GmbH 2023
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic\privacy;

use stdClass;
use context;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * The Magic authentication data export and deletion options.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Get list of the data privacy summary meta strings.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $magicmetadata = [
            'userid' => 'privacy:metadata:auth_magic:userid',
            'parent' => 'privacy:metadata:auth_magic:parent',
            'magicauth' => 'privacy:metadata:auth_magic:magicauth',
            'parentrole' => 'privacy:metadata:auth_magic:parentrole',
            'loginuserkey' => 'privacy:metadata:auth_magic:loginuserkey',
            'invitationuserkey' => 'privacy:metadata:auth_magic:invitationuserkey',
            'magiclogin' => 'privacy:metadata:auth_magic:magiclogin',
            'magicinvitation' => 'privacy:metadata:auth_magic:magicinvitation',
            'loginexpiry' => 'privacy:metadata:auth_magic:loginexpiry',
            'invitationexpiry' => 'privacy:metadata:auth_magic:invitationexpiry',
            'manualexpiry' => 'privacy:metadata:auth_magic:manualexpiry',
            'timecreated' => 'privacy:metadata:auth_magic:timecreated',
            'timemodified' => 'privacy:metadata:auth_magic:timemodified',
        ];

        $campaignpaymentlogsdata = [
            'campaignid' => 'privacy:metadata:auth_magic:campaignid',
            'userid' => 'privacy:metadata:auth_magic:userid',
            'paymentid' => 'privacy:metadata:auth_magic:paymentid',
            'status' => 'privacy:metadata:auth_magic:paymentstatus',
            'timecreated' => 'privacy:metadata:auth_magic:timecreated',
            'timemodified' => 'privacy:metadata:auth_magic:timemodified',
        ];

        $campaignusers = [
            'campaignid' => 'privacy:metadata:auth_magic:campaignid',
            'userid' => 'privacy:metadata:auth_magic:userid',
            'enrolpassword' => 'privacy:metadata:auth_magic:enrolpassword',
            'passenrolmentkey' => 'privacy:metadata:auth_magic:passenrolmentkey',
        ];

        $roleassignments = [
            'userid' => 'privacy:metadata:auth_magic:userid',
            'parent_userid' => 'privacy:metadata:auth_magic:parent',
            'roleid' => 'privacy:metadata:auth_magic:roleid',
            'field' => 'privacy:metadata:auth_magic:field',
            'status' => 'privacy:metadata:auth_magic:roleassignstatus',
        ];

        $approvals = [
            'userid' => 'privacy:metadata:auth_magic:userid',
            'parent' => 'privacy:metadata:auth_magic:parent',
            'campaignid' => 'privacy:metadata:auth_magic:campaignid',
        ];

        $collection->add_database_table('auth_magic_loginlinks', $magicmetadata, 'privacy:metadata:auth_magic_loginlinks');
        $collection->add_database_table('auth_magic_payment_logs', $campaignpaymentlogsdata,
        'privacy:metadata:campaignpaymentlogs');
        $collection->add_database_table('auth_magic_campaigns_users', $campaignusers,
        'privacy:metadata:campaignusers');
        $collection->add_database_table('auth_magic_roleassignments', $roleassignments, 'privacy:metadata:roleassignments');
        $collection->add_database_table('auth_magic_approval', $approvals, 'privacy:metadata:approval');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $params = ['amluserid' => $userid,
                    'contextlevel' => CONTEXT_USER,
                    'apluserid' => $userid,
                    'acuuserid' => $userid,
                    'amruserid' => $userid,
                    'amcguserid' => $userid,
                    'ampuserid' => $userid,
                ];

        $sql = "SELECT ctx.id
                  FROM {auth_magic_loginlinks} aml
                  JOIN {context} ctx ON ctx.instanceid = aml.userid AND ctx.contextlevel = :contextlevel
                 WHERE aml.userid = :amluserid";

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        $sql = "SELECT ctx.id
            FROM {auth_magic_payment_logs} apl
            JOIN {context} ctx ON ctx.instanceid = apl.userid AND ctx.contextlevel = :contextlevel
            WHERE apl.userid = :apluserid";
        $contextlist->add_from_sql($sql, $params);

        $sql = "SELECT ctx.id
        FROM {auth_magic_campaigns_users} acu
        JOIN {context} ctx ON ctx.instanceid = acu.userid AND ctx.contextlevel = :contextlevel
        WHERE acu.userid = :acuuserid";
        $contextlist->add_from_sql($sql, $params);

        $sql = "SELECT ctx.id
        FROM {auth_magic_roleassignments} amr
        JOIN {context} ctx ON ctx.instanceid = amr.userid AND ctx.contextlevel = :contextlevel
        WHERE amr.userid = :amruserid";
        $contextlist->add_from_sql($sql, $params);

        $sql = "SELECT ctx.id
        FROM {auth_magic_approval} amp
        JOIN {context} ctx ON ctx.instanceid = amp.userid AND ctx.contextlevel = :contextlevel
        WHERE amp.userid = :ampuserid";
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }
        $params = [$context->instanceid];
        $sql = "SELECT userid
                    FROM {auth_magic_loginlinks}
                    WHERE userid = ?";
        $userlist->add_from_sql('userid', $sql, $params);

        $sql = "SELECT userid
        FROM {auth_magic_payment_logs}
        WHERE userid = ?";
        $userlist->add_from_sql('userid', $sql, $params);

        $sql = "SELECT userid
        FROM {auth_magic_campaigns_users}
        WHERE userid = ?";
        $userlist->add_from_sql('userid', $sql, $params);

        $sql = "SELECT userid
        FROM {auth_magic_roleassignments}
        WHERE userid = ?";
        $userlist->add_from_sql('userid', $sql, $params);

        $sql = "SELECT userid
        FROM {auth_magic_approval}
        WHERE userid = ?";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if ($context instanceof \context_user) {
            list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
            if (!empty($userinparams)) {
                $sql = "userid {$userinsql}";
                $DB->delete_records_select('auth_magic_loginlinks', $sql, $userinparams);
                $DB->delete_records_select('auth_magic_payment_logs', $sql, $userinparams);
                $DB->delete_records_select('auth_magic_campaigns_users', $sql, $userinparams);
                $DB->delete_records_select('auth_magic_roleassignments', $sql, $userinparams);
                $DB->delete_records_select('auth_magic_approval', $sql, $userinparams);
            }
        }
    }

    /**
     * Delete user notes data for multiple context.
     *
     * @param approved_contextlist $contextlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_USER) {
                continue;
            }
            if ($context->instanceid == $userid) {
                // Delete stored user notes.
                self::delete_user_magicdata($context->instanceid);
                self::delete_user_paymentlogs($context->instanceid);
                self::delete_user_campaignsdata($context->instanceid);
                self::delete_user_roleassignment_data($context->instanceid);
                self::delete_user_approval_data($context->instanceid);
            }
        }
    }

    /**
     * Delete all notes data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context->contextlevel == CONTEXT_USER) {
            // Delete all users notes.
            self::delete_user_magicdata($context->instanceid);
            self::delete_user_paymentlogs($context->instanceid);
            self::delete_user_campaignsdata($context->instanceid);
            self::delete_user_roleassignment_data($context->instanceid);
            self::delete_user_approval_data($context->instanceid);
        }
    }

    /**
     * This does the deletion of user notes data given a userid.
     *
     * @param int $userid The user ID
     */
    private static function delete_user_paymentlogs(int $userid) {
        global $DB;
        if ($DB->delete_records('auth_magic_payment_logs', ['userid' => $userid])) {
            return true;
        }
        return false;
    }

    /**
     * This does the deletion of user notes data given a userid.
     *
     * @param int $userid The user ID
     */
    private static function delete_user_magicdata(int $userid) {
        global $DB;
        if ($DB->delete_records('auth_magic_loginlinks', ['userid' => $userid])) {
            return true;
        }
        return false;
    }


    /**
     * This does the deletion of user notes data given a userid.
     *
     * @param int $userid The user ID
     */
    private static function delete_user_campaignsdata(int $userid) {
        global $DB;
        if ($DB->delete_records('auth_magic_campaigns_users', ['userid' => $userid])) {
            return true;
        }
        return false;
    }

    /**
     * This does the deletion of user notes data given a userid.
     *
     * @param int $userid The user ID
     */
    private static function delete_user_roleassignment_data(int $userid) {
        global $DB;
        if ($DB->delete_records('auth_magic_roleassignments', ['userid' => $userid])) {
            return true;
        }
        return false;
    }


    /**
     * This does the deletion of user notes data given a userid.
     *
     * @param int $userid The user ID
     */
    private static function delete_user_approval_data(int $userid) {
        global $DB;
        if ($DB->delete_records('auth_magic_approval', ['userid' => $userid])) {
            return true;
        }
        return false;
    }


    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }
        // Context user.
        $user = $contextlist->get_user();

        // List of user loginlinks stored in table.
        $loginlinks = $DB->get_records('auth_magic_loginlinks', ['userid' => $user->id]);

        if (!empty($loginlinks)) {
            // Generate the loginlinks list to export.
            $exportdata['MagicLogins'] = array_map(function($record) {
                return [
                    'userid' => transform::user($record->userid),
                    'parent' => $record->parent ? transform::user($record->parent) : get_string('none'),
                    'magicauth' => $record->magicauth,
                    'parentrole' => ($record->parentrole) ? $record->parentrole : get_string('none'),
                    'loginuserkey' => $record->loginuserkey,
                    'invitationuserkey' => $record->invitationuserkey,
                    'magiclogin' => $record->magiclogin,
                    'magicinvitation' => $record->magicinvitation,
                    'loginexpiry' => ($record->loginexpiry) ? transform::datetime($record->loginexpiry) : '-',
                    'invitationexpiry' => ($record->invitationexpiry) ? transform::datetime($record->invitationexpiry) : '-',
                    'timecreated' => ($record->timecreated) ? transform::datetime($record->timecreated) : '-',
                    'timemodified' => ($record->timemodified) ? transform::datetime($record->timemodified) : '-',
                ];
            }, $loginlinks);
        }

        // List of the campaign users stored in table.
        $campaignusers = $DB->get_records('auth_magic_campaigns_users', ['userid' => $user->id]);

        if (!empty($campaignusers)) {
            // Generate the campaign users list to export.
            $exportdata['Campaignusersinfo'] = array_map(function($record) {
                return [
                    'userid' => transform::user($record->userid),
                    'campaignid' => $record->campaignid ? $record->campaignid : get_string('none'),
                    'timecreated' => ($record->timecreated) ? transform::datetime($record->timecreated) : '-',
                ];
            }, $campaignusers);
        }

        // List of the campaign users stored in table.
        $campaignpaymentlogs = $DB->get_records('auth_magic_payment_logs', ['userid' => $user->id]);

        if (!empty($campaignpaymentlogs)) {
            // Generate the campaign users list to export.
            $exportdata['Campaignpaymentlogs'] = array_map(function($record) {
                return [
                    'userid' => transform::user($record->userid),
                    'campaignid' => $record->campaignid ? $record->campaignid : get_string('none'),
                    'paymentid' => $record->paymentid,
                    'status' => $record->status,
                    'timecreated' => ($record->timecreated) ? transform::datetime($record->timecreated) : '-',
                    'timemodified' => ($record->timemodified) ? transform::datetime($record->timemodified) : '-',
                ];
            }, $campaignpaymentlogs);
        }
        // List of the role assignments stored in table.
        $roleassignments = $DB->get_records('auth_magic_roleassignments', ['userid' => $user->id]);

        if (!empty($roleassignments)) {
            // Generate the campaign users list to export.
            $exportdata['Relative_roleassignments'] = array_map(function($record) {
                return [
                    'userid' => transform::user($record->userid),
                    'parent' => $record->parent_userid ? transform::user($record->parent_userid) : get_string('none'),
                    'roleid' => $record->roleid,
                    'field' => $record->field,
                    'status' => $record->status,
                    'timecreated' => ($record->timecreated) ? transform::datetime($record->timecreated) : '-',
                ];
            }, $roleassignments);
        }

        // List of the role assignments stored in table.
        $approvals = $DB->get_records('auth_magic_approval', ['userid' => $user->id]);

        if (!empty($approvals)) {
            // Generate the campaign users list to export.
            $exportdata['approval_roles'] = array_map(function($record) {
                return [
                    'userid' => transform::user($record->userid),
                    'parent' => $record->parent ? transform::user($record->parent) : get_string('none'),
                    'campaignid' => $record->campaignid ? $record->campaignid : get_string('none'),
                    'timecreated' => ($record->timecreated) ? transform::datetime($record->timecreated) : '-',
                ];
            }, $approvals);
        }

        if (!empty($exportdata)) {
            $context = \context_user::instance($user->id);
            // Fetch the generic module data for the note.
            $contextdata = helper::get_context_data($context, $user);
            $contextdata = (object)array_merge((array)$contextdata, $exportdata);
            writer::with_context($context)->export_data([get_string('privacy:metadata:auth_magic', 'auth_magic').' '.$user->id],
            $contextdata);
        }

    }

}
