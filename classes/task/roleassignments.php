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
 * Auth Magic - Adhoc task handler to perform the relative role assignments.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_magic\task;

use core\task\adhoc_task;
use auth_magic\campaign;
use core_user;

/**
 * Adhoc task to create user relative role assignments, fields mapping are updated in global config.
 */
class roleassignments extends adhoc_task {

    /**
     * Cron users list limit
     *
     * @var int
     */
    public static $userlimit = 500;

    /**
     * Get instance of the roleassignments adhoc tasks.
     *
     * @param int $roleid
     * @param string $fieldname
     * @param string $oldfield
     * @return self
     */
    public static function instance(int $roleid, string $fieldname, string $oldfield): self {
        $task = new self();

        $task->set_custom_data((object) [
            'roleid' => $roleid,
            'field' => $fieldname,
            'oldfield' => $oldfield,
        ]);

        return $task;
    }

    /**
     * Execute the role assignment tasks, Fetchs the list of users to update the role assignments.
     * Create roleassignment instance for each user, create/update the role assignments, removes the previous role assignments.
     *
     * Still the users are need to updated and the limit is reached, creates another task to udpate the assignments.
     *
     * @return void
     */
    public function execute() {
        global $DB;

        $data = $this->get_custom_data();

        $role = $DB->get_record('role', ['id' => $data->roleid]);

        $roles = [$data->roleid => $role->shortname];

        $currentfield = !empty($data->field) ? $data->field : $data->oldfield;

        list($sql, $params) = self::get_role_assignment_users($data->roleid, $currentfield);
        // Params for query.
        $users = $DB->get_records_sql($sql, $params, 0, self::$userlimit);

        foreach ($users as $userid => $user) {
            $assignmentobj = \auth_magic\roleassignment::create($user->id);
            $assignmentobj->manage_role_assignments($roles);
            $assignmentobj->remove_user_field_assignment($data->roleid, $data->oldfield);
        }

        // Generate sql for count.
        list($sql, $params) = self::get_role_assignment_users($data->roleid, $currentfield, true);
        if ($DB->count_records_sql($sql, $params)) {
            $task = self::instance($data->roleid, $data->field, $data->oldfield);
            \core\task\manager::reschedule_or_queue_adhoc_task($task);
        } else {
            // After the assignments are updated for the changes.
            // Remove all users previous role assignments.
            \auth_magic\roleassignment::remove_allusers_assignment($data->roleid, $data->oldfield);
        }

        return true;
    }

    /**
     * Get the query and params to fetch the list of users to update the role assignments.
     *
     * @param int $roleid
     * @param string $field
     * @param bool $count
     * @return array
     */
    public static function get_role_assignment_users(int $roleid, string $field, bool $count=false): array {

        $select = $count ? ' COUNT(u.id) ' : ' u.*, ind.data ';

        $sql = "SELECT $select FROM {user} u
            JOIN {user_info_field} inf ON inf.shortname=:fieldname
            JOIN {user_info_data} ind ON ind.fieldid = inf.id AND ind.userid = u.id
            WHERE ind.data != '' AND u.deleted = 0 AND u.suspended = 0
            AND u.id NOT IN (
                SELECT userid FROM {auth_magic_roleassignments} mra
                WHERE mra.field = :fieldname2 AND mra.roleid = :roleid AND mra.status = :current
            )";

        $params = [
            'fieldname' => $field, 'fieldname2' => $field, 'roleid' => $roleid,
            'current' => \auth_magic\roleassignment::STATUSCURRENT,
        ];

        return [$sql, $params];
    }
}
