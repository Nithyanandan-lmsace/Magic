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
 * Auth Magic - Relative role assignment handler.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic;

use auth_magic\task\roleassignments;
use moodle_exception;
use stdClass;
use core_user;

/**
 * Relative role assignment handler to perform the users role assignments and unassignments.
 */
class roleassignment {


    /**
     * User data object.
     *
     * @var stdClass
     */
    protected $user;

    /**
     * Field is mapped to used as identifier of the assignment role user.
     *
     * @var string
     */
    protected $identityfield;

    /**
     * Field is mapped to used as identifier of the assignment role user.
     *
     * @var \context_user
     */
    protected $usercontext;

    /**
     * Field is mapped to used as identifier of the assignment role user.
     *
     * @var bool
     */
    protected $autocreate;

    /**
     * Represet the account idetentifier as email.
     *
     * @var int
     */
    public const IDENTITY_EMAIL = 0;

    /**
     * Represet the account idetentifier as username.
     *
     * @var int
     */
    public const IDENTITY_USERNAME = 1;

    /**
     * Represet the account idetentifier as idnumber.
     *
     * @var int
     */
    public const IDENTITY_IDNUMBER = 2;

    /**
     * Represet the account idetentifier as fullname.
     *
     * @var int
     */
    public const IDENTITY_FULLNAME = 3;

    /**
     * Represents the role assignment record status is current and updated.
     */
    public const STATUSCURRENT = 1;

    /**
     * Constructor, setup the config of relative role asisgnments.
     */
    protected function __construct() {

        $this->identityfield = get_config('auth_magic', 'roleaccountidentifier');

        $this->autocreate = get_config('auth_magic', 'autocreate_relativeusers');
    }

    /**
     * Create the magic relative role assignments instance.
     *
     * @param int $userid ID of the user.
     *
     * @return \auth_magic\roleassignment
     */
    public static function create(int $userid) {

        $instance = new self();
        $instance->set_user($userid); // Set the user for this instance.
        return $instance;
    }

    /**
     * Set the user for this instance to manage the role assignments for the user.
     *
     * @param int $userid
     * @return void
     */
    public function set_user(int $userid) : void {
        global $CFG;

        require_once($CFG->dirroot. "/user/profile/lib.php");

        // Get the user data of this userid.
        $user = \core_user::get_user($userid, '*', MUST_EXIST);
        profile_load_data($user); // Load the custom profile fields data to the user.

        $this->user = $user; // Set the class user.

        $this->usercontext = \context_user::instance($this->user->id);
    }

    /**
     * Manage the role assignments and remove the users from assigned roles.
     *
     * @param array $roles List of roles to create user assignments.
     * @return void
     */
    public function manage_role_assignments(array $roles=[], array $customfieldvalue = []) {

        global $DB;
        // Generate role assignments for this user.
        $assignments = $this->generate_role_assignments($roles, $customfieldvalue);
        $parentuser = null;
        foreach ($assignments as $data) {

            // Find the user related to the identifier value.
            $parentuser = $this->find_identifier_useraccount($data->identifiervalue, $data->rolename);

            // Get the role assignments for the user based on the field value.
            $assignment = $this->get_user_role_assignment_byfield($data->fieldname, $data->roleid);
            // This role assignment already created with this same user. No changes are made.
            if (!empty($assignment) && $assignment->parent_userid == $parentuser->id) {
                continue;
            }

            // Assign the relative user to the allocated role.
            if (isset($parentuser->id) && role_assign($data->roleid, $parentuser->id, $this->usercontext->id)) {
                $this->create_role_assignment_log($data->roleid, $parentuser->id, $data->fieldname, $assignment);
            }

            // This role assigned already for different user, need to unassign before assign new user.
            if (!empty($assignment) && $assignment->parent_userid !== $parentuser->id) {

                role_unassign($data->roleid, $assignment->parent_userid, $this->usercontext->id);
                // Remove the assignment.
                $this->remove_role_assignment_log($data->roleid, $assignment->parent_userid, $data->fieldname, $assignment);
            }
        }
        return $parentuser;

    }

    /**
     * Find the relative role user using the identifier method configured in the admin settings.
     *
     * @param mixed $value
     * @param string $rolename
     *
     * @return stdclass
     */
    protected function find_identifier_useraccount($value, string $rolename='') {
        global $DB;
        $usercreate = false;
        // Identityfy the relative user using the email of the user.
        if ($this->identityfield == self::IDENTITY_EMAIL) {
            $user = core_user::get_user_by_email($value);
            // User not found, autocreate is enabled then creates a new user with this email id.
            if (empty($user) && $this->autocreate) {
                $user = $this->create_new_parentuser($value, $rolename);
                $usercreate = true;
            }
            // Identityfy the relative user using the email of the user.
        } else if ($this->identityfield == self::IDENTITY_USERNAME) {
            $user = core_user::get_user_by_username($value);
        } else if ($this->identityfield == self::IDENTITY_IDNUMBER) {
            $user = $DB->get_record('user', ['idnumber' => $value]);
        } else if ($this->identityfield == self::IDENTITY_FULLNAME) {
            $users = core_user::search($value);
            $user = !empty($users) ? current($users) : [];
        }
        // User is exist then update the approval process.
        if ($user) {
            $this->update_campaign_approval_process($user, $usercreate);
        }

        return (object) $user ?? [];
    }

    /**
     * Update the campaign approval data.
     * @param object $parentuser
     * @param bool $usercreate
     * @return void
     */
    protected function update_campaign_approval_process($parentuser, $usercreate = false) {
        global $DB, $USER;
        $record = $DB->get_record('auth_magic_campaigns_users', ['userid' => $this->user->id]);
        if ($parentuser && $record = $DB->get_record('auth_magic_campaigns_users', ['userid' => $this->user->id])) {
            if (!$DB->record_exists('auth_magic_approval', ['userid' => $this->user->id,
                'parent' => $parentuser->id, 'campaignid' => $record->campaignid]))  {
                $data = new stdClass;
                $data->userid = $this->user->id;
                $data->parent = $parentuser->id;
                $data->campaignid = $record->campaignid;
                $DB->insert_record('auth_magic_approval', $data);
            }
            // Get the campaign info.
            $campaign = campaign::instance($record->campaignid)->get_campaign();
            if ($usercreate) {
                $DB->set_field('user', 'confirmed', 0, ['id' => $parentuser->id]);
            }

            // Send email to the user based on the approval type.
            if ($campaign->approvaltype != 'disabled') {
                auth_magic_send_confirmation_email($parentuser, new \moodle_url('/auth/magic/confirm.php'), $this->user->id);
            }

            if ($campaign->approvaltype == 'optionalout' || $campaign->approvaltype == 'fulloptionout' ) {
                auth_magic_send_revocationlink_email($campaign->id, $this->user, $parentuser);
            }
        }
    }

    /**
     * Create the new parent user.
     *
     * @param string $email
     * @param string $rolename
     * @return void
     */
    protected function create_new_parentuser(string $email, string $rolename) {
        global $CFG;

        // Include the user libaray file.
        require_once($CFG->dirroot.'/user/lib.php');

        // Auto create roles disabled, no need to continue further.
        if (!$this->autocreate) {
            return [];
        }

        // Email is valid, Generate a user names.
        if (validate_email($email)) {

            $username = explode('@', $email);
            $user = new stdClass();
            $user->email = $email;
            $user->firstname = get_string('relativeuserfirstname', 'auth_magic') ?: fullname($this->user);
            $user->lastname = get_string('relativeuserlastname', 'auth_magic') ?: $rolename;
            $user->username = get_string('relativeusername', 'auth_magic') ?: current($username);
            $user->mnethostid = $CFG->mnet_localhost_id; // Always local user.
            $user->confirmed = 1;
            $user->secret      = random_string(15);
            $user->timecreated = time();

            // Create a new parent user.
            $id = user_create_user($user);
            $newuser = core_user::get_user($id);
            setnew_password_and_mail($newuser);
            unset_user_preference('create_password', $newuser);
            set_user_preference('auth_forcepasswordchange', 1, $newuser);
            return $newuser;
        }
    }

    /**
     * Fetch the list of role assignments created for the user in this role with mapped field name.
     *
     * @param string $fieldname
     * @param int $roleid
     * @return stdclass
     */
    protected function get_user_role_assignment_byfield(string $fieldname, int $roleid) {
        global $DB;

        $assignment = $DB->get_record('auth_magic_roleassignments',
            ['roleid' => $roleid, 'userid' => $this->user->id, 'field' => $fieldname]);

        return $assignment;
    }

    /**
     * Generate the assignments to assign the role.
     *
     * @param array $roles
     * @return array
     */
    protected function generate_role_assignments(array $roles=[], array $customfieldvalues = []) : array {
        // Get user context roles.
        $roles = $roles ?: self::get_user_context_roles();
        $assignments = [];
        foreach ($roles as $roleid => $rolename) {
            // Get the mapped field for this role.
            $fieldname = get_config('auth_magic', "roleassignment_$roleid");
            // Name of the user profield field for the mapped role field.
            $name = 'profile_field_'.$fieldname;

            $userfieldvalue = '';

            if (!empty($customfieldvalues)) {
                $userfieldvalue = isset($customfieldvalues[$name]) ? $customfieldvalues[$name] : '';
            } else if (isset($this->user->$name) && !empty($this->user->$name)) {
                $userfieldvalue = $this->user->$name;
            }


            if (!empty($fieldname) && !empty($userfieldvalue)) {
                // Identifier value of the parent user data.
                // Build assignments with role and field data.
                $assignments[] = (object) [
                    'fieldname' => $fieldname,
                    'roleid' => $roleid,
                    'identifiervalue' => $userfieldvalue,
                    'rolename' => $rolename,
                ];

                $mappings[$roleid] = $fieldname; // Name of the field mapped with this role.
            }

            // When the field is empty, check is any role assignments are created for this field before.
            if (!empty($userfieldvalue)) {
                // Remove the assignment.
                $this->remove_user_field_assignment($roleid, $fieldname);
            }

            // Remove the previous role assignments.
            $this->remove_roleassignment_previous($roleid, $fieldname);

        }

        return $assignments ?? [];
    }

    /**
     * Remove the user field assignment.
     *
     * @param int $roleid
     * @param string $fieldname
     * @return void
     */
    public function remove_user_field_assignment(int $roleid, string $fieldname) {
        global $DB;

        $condition = ['roleid' => $roleid, 'field' => $fieldname, 'userid' => $this->user->id];
        if ($assignment = $DB->get_record('auth_magic_roleassignments', $condition)) {
            // Unassign the previous user role assignments.
            role_unassign($assignment->roleid, $assignment->parent_userid, $this->usercontext->id);
            // Delete the role assignment log.
            $DB->delete_records('auth_magic_roleassignments', $condition);
        }
    }

    /**
     * Remove all the users role assignments using the given field mapped with the roleid.
     *
     * @param int $roleid
     * @param string $fieldname
     * @return void
     */
    public static function remove_allusers_assignment(int $roleid, string $fieldname) {
        global $DB;
        $records = $DB->get_records('auth_magic_roleassignments', ['roleid' => $roleid, 'field' => $fieldname]);
        foreach ($records as $record) {
            // Remove all the previous role assignments.
            self::create($record->userid)->remove_roleassignment_previous($roleid, $fieldname);
        }
    }

    /**
     * Remove the relative role assignments to the user using the previous field mapping data.
     *
     * @param int $roleid
     * @param string $fieldname
     * @return void
     */
    public function remove_roleassignment_previous(int $roleid, string $fieldname) {
        global $DB;

        $condition = ['roleid' => $roleid, 'userid' => $this->user->id];
        if ($assignments = $DB->get_records('auth_magic_roleassignments', $condition)) {
            foreach ($assignments as $id => $assignment) {
                // Prevent deletion of new role.
                if ($assignment->field == $fieldname) {
                    continue;
                }
                // Unassign the previous user role assignments.
                role_unassign($assignment->roleid, $assignment->parent_userid, $this->usercontext->id);
                // Delete the role assignment log.
                $DB->delete_records('auth_magic_roleassignments', $condition);
            }
        }
    }

    /**
     * Remove the role assignment.
     *
     * @param int $roleid
     * @param int $parentuserid
     * @param string $fieldname
     * @param stdClass $assignment
     * @return void
     */
    protected function remove_role_assignment_log(int $roleid, int $parentuserid, string $fieldname, $assignment = null) {
        global $DB;

        $record = new stdClass();
        $record->roleid = $roleid;
        $record->parent_userid = $parentuserid; // Assignment parent id, fetched before create new assignment.
        $record->field = $fieldname;
        $record->userid = $this->user->id;

        $DB->delete_records('auth_magic_roleassignments', (array) $record);
    }

    /**
     * Create a log in auth magic table to maintain the relative role assignments, and profile field value changes.
     *
     * @param int $roleid
     * @param int $parentuserid
     * @param string $fieldname
     * @param stdClass|null $assignment
     * @return void
     */
    protected function create_role_assignment_log(int $roleid, int $parentuserid, string $fieldname, $assignment = null) {
        global $DB;

        $record = new stdClass();
        $record->roleid = $roleid;
        $record->parent_userid = $parentuserid;
        $record->field = $fieldname;
        $record->timecreated = time();
        $record->userid = $this->user->id;
        $record->status = self::STATUSCURRENT;
        if (!empty($assignment)) {
            $record->id = $assignment->id;
            $DB->update_record('auth_magic_roleassignments', $record);
        } else {
            $DB->insert_record('auth_magic_roleassignments', $record);
        }
    }

    /**
     * Fetch the list of user context roles and fix the role names.
     *
     * @return array
     */
    protected static function get_user_context_roles() {
        global $DB;

        // List of user context roles.
        $roles = get_roles_for_contextlevels(CONTEXT_USER);
        // No roles are created for user context.
        if (empty($roles)) {
            return [];
        }
        list($insql, $inparams) = $DB->get_in_or_equal(array_values($roles));
        $roles = $DB->get_records_sql("SELECT * FROM {role} WHERE id $insql", $inparams);
        $roles = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);

        return $roles;
    }

    /**
     * Get the list of user profile fields in text type to mapping with roles.
     *
     * @return array
     */
    protected static function get_user_profile_fields() {
        // List of user profield custom fields.
        $fieldobjects = profile_get_user_fields_with_data(0);
        // Fetch the list of text type fields.
        $fields = ['' => get_string('none')];
        foreach ($fieldobjects as $fieldobject) {
            if ($fieldobject->field->datatype !== 'text') {
                continue;
            }
            $field = (object) $fieldobject->get_field_config_for_external();
            $fields[$field->shortname] = $field->name;
        }

        return $fields;
    }

    /**
     * Trigger the profile field is changed for the role allocation.
     *
     * @param array $configdata
     * @return void
     */
    public static function role_allocation_field_updated(array $configdata) {
        global $DB;

        $name = $configdata['name']; // Role field config name (roleassignment_parent).
        $oldvalue = $configdata['oldvalue'] ?? ''; // Name of the previously mapped field.
        $value = $configdata['value']; // Name of the current mapped field.

        $roleid = (int) str_replace('roleassignment_', '', $name);
        $currentfield = !empty($value) ? $value : $oldvalue;

        // Disable the status for all role assignments using the old profile field.
        $DB->set_field('auth_magic_roleassignments', 'status', !self::STATUSCURRENT, ['roleid' => $roleid, 'field' => $oldvalue]);

        // Get the list of role assignments for the users.
        list($sql, $params) = \auth_magic\task\roleassignments::get_role_assignment_users($roleid, $currentfield, true);
        // List of users to manage the role change is not more than the limit. Do the changes here.
        // Otherwise setup the adhoc task to preform the changes in background.
        if ($DB->count_records_sql($sql, $params) <= roleassignments::$userlimit) {

            $role = $DB->get_record('role', ['id' => $roleid]);
            $roles = [$roleid => $role->shortname];

            // Params for query.
            list($sql, $params) = \auth_magic\task\roleassignments::get_role_assignment_users($roleid, $currentfield);

            $users = $DB->get_records_sql($sql, $params);
            // Manage the role assignments.
            foreach ($users as $userid => $user) {
                $assignmentobj = self::create($user->id);
                $assignmentobj->manage_role_assignments($roles);
                $assignmentobj->remove_user_field_assignment($roleid, $oldvalue);
            }

            // After the assignments are updated for the changes.
            // Remove all users previous role assignments.
            self::remove_allusers_assignment($roleid, $oldvalue);

        } else {
            // Initiate the adhoc task to assignments.
            $task = \auth_magic\task\roleassignments::instance($roleid, $value, $oldvalue);
            \core\task\manager::queue_adhoc_task($task);
        }
    }

    /**
     * Observe the user updated event, then create the role assignments for the user if not already created.
     *
     * @param \core\event\user_updated $event
     * @return void
     */
    public static function user_updated($event) {
        $userid = $event->objectid;

        $instance = self::create($userid); // Create a new instance of role assignments.
        // $instance->set_user($userid); // Set the user for this instance.
        $instance->manage_role_assignments();
    }

    /**
     * Include the role assignment settings and account identifier settings to the admin settings section.
     *
     * @param \admin_settingpage $settings
     * @return void
     */
    public static function include_admin_setting(\admin_settingpage &$settings) {
        global $DB;

        $setting = '';

        // Get user context roles.
        $roles = self::get_user_context_roles();

        // Get the user fields.
        $fields = self::get_user_profile_fields();

        // Create heading for the relative role assignments.
        $name = 'auth_magic/relativeroleassignment';
        $title = get_string('relativeroleassignment', 'auth_magic', null, true);
        $setting = new \admin_setting_heading($name, $title, null);
        $settings->add($setting);

        foreach ($roles as $roleid => $rolename) {
            $name = "auth_magic/roleassignment_$roleid";
            $title = get_string("fieldroleassignment", "auth_magic", $rolename);
            $desc = get_string("fieldroleassignment_desc", "auth_magic");
            $setting = new \admin_setting_configselect($name, $title, $desc, 0, $fields);

            $settings->add($setting);
        }

        // Setting: Account identifier for the role profile field.
        $name = 'auth_magic/roleaccountidentifier';
        $title = get_string('accountidentifier', 'auth_magic');
        $desc = get_string('accountidentifier_desc', 'auth_magic');

        $fields = [
            self::IDENTITY_EMAIL => get_string('email'),
            self::IDENTITY_USERNAME => get_string('username'),
            self::IDENTITY_IDNUMBER => get_string('idnumber'),
            self::IDENTITY_FULLNAME => get_string('fullname'),
        ];
        $setting = new \admin_setting_configselect($name, $title, $desc, 0, $fields);
        $settings->add($setting);

        // Setting: Auto create the relative role users.
        $name = "auth_magic/autocreate_relativeusers";
        $title = get_string("autocreaterelativeroles", "auth_magic");
        $desc = get_string("autocreaterelativeroles_desc", "auth_magic");
        $setting = new \admin_setting_configcheckbox($name, $title, $desc, 0);
        $settings->add($setting);
    }
}
