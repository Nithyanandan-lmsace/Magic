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
 * campaign info.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic;

use html_writer;
use moodle_url;
use stdclass;
use context_system;
use core\event\user_loggedin;
use moodle_exception;
use auth_magic\payment\service_provider;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/auth/magic/campaigns/campaign_helper.php');
require_once($CFG->dirroot. '/group/lib.php');

/**
 * Magic campaign info.
 */
class campaign {


    /**
     * Roles based on Any context
     * @var int
     */
    public const ANYCONTEXT = 1;

    /**
     * Roles based on System context
     * @var int
     */
    public const SYSTEMCONTEXT = 2;

    /**
     * Campaign status available.
     */
    public const STATUS_AVAILABLE = 0;

    /**
     * Campaign status available.
     */
    public const STATUS_ARCHIVED = 1;

    /**
     * Campaign visibility hidden.
     */
    public const HIDDEN = 0;

    /**
     * Campaign visibility visible.
     */
    public const VISIBLE = 1;

    /**
     * Enable value.
     */
    public const ENABLE = 1;

    /**
     * Disable value.
     */
    public const DISABLE = 0;

    /**
     * partial value.
     */
    public const PARTIAL = 2;


    /**
     * Campaign capacity.
     */
    public const CAPACITY_UNLIMITED = 0;

    /**
     * Campaign Center alignment.
     */
    public const FORM_POSITION_CENTER = 0;

    /**
     * Campaign leftoverlay position.
     */
    public const FORM_POSITION_LEFTOVERLAY = 1;

    /**
     * Campaign rightoverlay position.
     */
    public const FORM_POSITION_RIGHTOVERLAY = 2;

    /**
     * Campaign leftfull position.
     */
    public const FORM_POSITION_LEFTFULL = 3;

    /**
     * Campaign rightfull position.
     */
    public const FORM_POSITION_RIGHTFULL = 4;

    /**
     * None.
     */
    public const NONE = 0;

    /**
     * Constants for access rules all matching
     * @var int
     */
    public const ALL = 1;

    /**
     * Constants for access rule any of matching
     * @var int
     */
    public const ANY = 2;

    /**
     * Compaign ID.
     *
     * @var int
     */
    public $id;

    /**
     * Compaign data.
     *
     * @var stdclass
     */
    public $campaign;

    /**
     * Create instance of the campaign.
     *
     * @param int $id
     * @return \auth_magic\campaign
     */
    public static function instance($id) {

        static $instance;

        if (!isset($instance)) {
            $instance = new self($id);
        }

        return $instance;
    }

    /**
     * Campaign Constructor.
     * @param int $id
     */
    public function __construct($id) {

        $this->id = $id;
        $this->campaign = $this->get_campaign();

    }

    /**
     * Get the campaign record from DB. Load its fields to the record.
     *
     * @return void
     */
    public function get_campaign() {
        global $DB;

        $campaign = $DB->get_record('auth_magic_campaigns', ['id' => $this->id]);

        if (!$campaign) {
            throw new moodle_exception('campaignnotfound', 'auth_magic_campaigns');
        }

        $campaign->description = file_rewrite_pluginfile_urls(
            $campaign->description, 'pluginfile.php', \context_system::instance()->id,
            'auth_magic', 'description', $this->id
        );

        $campaign->formfields = $DB->get_records('auth_magic_campaigns_fields', ['campaignid' => $this->id]);

        $campaign->paymentinfo = $DB->get_record('auth_magic_campaigns_payment', ['campaignid' => $this->id]);

        return $campaign;
    }

    /**
     * Verfiy the campaign is available for the usrs to signup.
     *
     * @return bool True Campaign visibility is vvisible, not archeived, and its not capacity completed, Otherwise it is FALSE,
     */
    public function is_campaign_available() {
        // Check the campaign visiblity and status and start and end dates.
        if (!$this->campaign->visibility || $this->campaign->status == self::STATUS_ARCHIVED || !$this->restriction_bydate()) {
            return false;
        }
        // Check the capacity is reached.
        $capacity = $this->get_capacity();
        // Capacity is unlimited then campaign is available.
        if ($this->campaign->capacity != self::CAPACITY_UNLIMITED) {
            // Check if availalbe count is leass than 1 then hide from users.
            if ($capacity->available <= 0) {
                return false;
            }
        }

        if (!empty($this->campaign->restrictcohorts) && !$this->restriction_bycohorts()) {
            return false;
        }

        if (!empty($this->campaign->restrictroles) && !$this->restriction_byroles()) {
            return false;
        }

        return true;
    }

    /**
     * Is the campagin vaild to check restriction by cohorts.
     * @return void
     */
    public function restriction_byroles() {
        global $DB, $USER;

        $roles = $this->campaign->restrictroles;
        // Roles not mentioned then stop the role check.
        if ($roles == '' || empty($roles)) {
            return true;
        }

        $roles = json_decode($this->campaign->restrictroles);

        list($insql, $inparam) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'rl');

        $contextsql = ($this->campaign->restrictrolecontext == campaign::SYSTEMCONTEXT)
            ? ' AND contextid=:systemcontext ' : '';

        $sql = "SELECT userid FROM {role_assignments} WHERE roleid $insql AND userid=:rluserid $contextsql";
        $params = [
            'rluserid' => $USER->id,
            'systemcontext' => context_system::instance()->id,
        ];

        return $DB->record_exists_sql($sql, array_merge($params, $inparam));

    }


    /**
     * Is the campagin vaild to check restriction by cohorts.
     * @return void
     */
    public function restriction_bycohorts() {
        global $DB, $USER;

        $cohorts = $this->campaign->restrictcohorts;

        if ($cohorts == '' || empty($cohorts)) {
            return true;
        }

        $cohorts = json_decode($this->campaign->restrictcohorts);
        // Build insql to confirm the user cohort is available in the configured cohort.
        list($insql, $inparam) = $DB->get_in_or_equal($cohorts, SQL_PARAMS_NAMED, 'ch');

        // If operator is all then check the count of user assigned cohorts,
        // Confirm the count is same as configured items cohorts count.
        $condition = ($this->campaign->restrictcohortoperator == campaign::ALL) ? " GROUP BY cm.userid HAVING COUNT(DISTINCT c.id) = :chcount" :
        ' HAVING COUNT(DISTINCT c.id) != 0';

        $sql = "SELECT count(*) AS member FROM {cohort_members} cm
            JOIN {cohort} c ON cm.cohortid = c.id
            WHERE c.id $insql AND cm.userid=:chuserid $condition";
        $params = ['chuserid' => $USER->id, 'chcount' => count($cohorts)] + $inparam;
        return $DB->record_exists_sql($sql, $params);
    }

    /**
     * Include the privacy policy statement in the campaign form. Make this checkbox as required.
     *
     * @param \form $mform
     * @return void
     */
    public function include_privacy_policy(&$mform) {

        if (!$this->campaign->privacypolicy) {
            return false;
        }

        $content = file_rewrite_pluginfile_urls(
            $this->campaign->consentstatement, 'pluginfile.php', \context_system::instance()->id,
            'auth_magic', 'consentstatement', $this->id
        );

        $mform->addElement('checkbox', 'privacypolicy', $content);
        $mform->addRule('privacypolicy', get_string('required'), 'required', '', 'client');

    }

    /**
     * Include the privacy policy statement in the campaign form. Make this checkbox as required.
     *
     * @param \form $mform
     * @param string $coupon
     * @return void
     */
    public function include_enrolmentkey(&$mform, $coupon = '') {
        if (!$this->campaign->courseenrolmentkey || $this->campaign->courseenrolmentkey == 'disabled') {
            return false;
        }

        if ($coupon && $coupon == md5($this->campaign->coupon)) {
            return false;
        }

        $mform->addElement('text', 'enrolpassword', get_string('campaigns:courseenrolmentkey', 'auth_magic'));
        $mform->setType('enrolpassword', PARAM_RAW);
        if ($this->campaign->courseenrolmentkey != 'optional') {
            $mform->addRule('enrolpassword', get_string('required'), 'required', '', 'client');
        }

    }


    /**
     * Campaign captcha enabled or not.
     */
    public function campaign_captcha_enabled() {
        global $CFG;
        if (!$this->campaign->recaptcha || empty($CFG->recaptchaprivatekey) || empty($CFG->recaptchapublickey)) {
            return false;
        }
        return true;
    }



    /**
     * Validation for enrolment key.
     *
     * @param string $enrolpassword
     * @param bool $checkoptional
     * @return void
     */
    public function enrolment_key_validation($enrolpassword, $checkoptional = true) {
        global $CFG;
        if (file_exists($CFG->dirroot. "/enrol/self/locallib.php")) {
            require_once($CFG->dirroot. "/enrol/self/locallib.php");
        }
        $errors = [];
        if (empty($this->campaign->courseenrolmentkey)
            || $this->campaign->courseenrolmentkey == 'disabled') {
            return $errors;
        }

        if ($checkoptional && $this->campaign->courseenrolmentkey == 'optional') {
            return $errors;
        }
        // If enabled the campaign course then check the match enrolment key.
        if ($this->campaign->campaigncourse) {
            $keytype = $this->campaign->courseenrolmentkey;
            $instances = enrol_get_instances($this->campaign->campaigncourse, true);
            foreach ($instances as $instance) {
                if (!empty($instance->password) && $instance->password == $enrolpassword) {
                    return $errors;
                }
            }
            if ($this->campaign->courseenrolmentkey != 'strict' &&
                enrol_self_check_group_enrolment_key($this->campaign->campaigncourse, $enrolpassword)) {
                return $errors;
            }
        }

        // If check the campaign coupon.
        if ($this->campaign->courseenrolmentkey != 'strict' && $this->campaign->coupon) {
            if ($this->campaign->coupon == $enrolpassword) {
                return $errors;
            }
        }
        $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_self');
        return $errors;
    }


    /**
     * Include the privacy policy statement in the campaign form. Make this checkbox as required.
     *
     * @param \form $mform
     * @return void
     */
    public function include_captcha(&$mform) {
        if ($this->campaign_captcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('security_question', 'auth'));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
            $mform->closeHeaderBefore('recaptcha_element');
        }
    }


    /**
     * Accept the privacy and policy documents available for the user.
     *
     * @param int $userid
     * @return void
     */
    public function accept_user_privacy_policies($userid) {
        // This campaign is not privacy constant support, user should accept privacy manually.
        if (!$this->campaign->privacypolicy) {
            return false;
        }
        $versions = \tool_policy\api::list_current_versions();
        $policies = array_unique(array_values(array_column($versions, 'id')));
        if (!empty($policies)) {
            $lang = current_language();
            \tool_policy\api::accept_policies($policies, $userid, null, $lang);
        }
    }

    /**
     * Camapaign course.
     * @param object $user
     */
    public function process_campaign_course($user, $removed) {
        if (!$this->campaign->campaigncourse) {
            return false;
        }
        $this->enroll_user_to_campaigncourse($user, $removed);
        return $this->assign_campaign_course_group($user, $removed);
    }

    /**
     * Process the approval roles.
     * @param [object] $user
     * @return bool
     */
    public function process_approval_roles($user, $removed, $parentuser = null) {
        global $DB, $USER;
        // Check the user has approval user or not.
        $usercontextroles = get_roles_for_contextlevels(CONTEXT_USER);
        $systemcontextroles = get_roles_for_contextlevels(CONTEXT_SYSTEM);
        if (is_null($parentuser)) {
            $record = $DB->get_record('auth_magic_approval', ['userid' => $user->id, 'campaignid' => $this->campaign->id]);
            $parentuser = !empty($record) ? $DB->get_record('user', ['id' => $record->parent]) : null;
        }
        if ($parentuser) {
            $usercontext = \context_user::instance($user->id);
            if ($this->campaign->approvalroles) {
                $approvalroles = json_decode($this->campaign->approvalroles);
                foreach ($approvalroles as $roleid) {
                    if (in_array($roleid, $usercontextroles)) {
                        if ($removed) {
                            role_unassign($roleid, $parentuser->id, $usercontext->id);
                        } else {
                            role_assign($roleid, $parentuser->id, $usercontext->id);
                        }
                    }
                    if (in_array($roleid, $systemcontextroles)) {
                        if ($removed) {
                            role_unassign($roleid, $parentuser->id, \context_system::instance()->id);
                        } else {
                            role_assign($roleid, $parentuser->id, \context_system::instance()->id);
                        }
                    }
                }
            }

            $instances = $DB->get_records_sql("SELECT * FROM {enrol} WHERE courseid =:courseid AND status = 0 AND enrol != 'guest'",
                ['courseid' => $this->campaign->campaigncourse]);
            if (!empty($instances)) {
                $instance = current($instances);
                $enrolmanual = enrol_get_plugin($instance->enrol);
                if ($removed) {
                    $enrolmanual->unenrol_user($instance, $parentuser->id, $this->campaign->courseparentrole);
                } else {
                    $enrolmanual->enrol_user($instance, $parentuser->id, $this->campaign->courseparentrole);
                }
                return true;
            }
        }
    }

    /**
     * Process Campaign course group.
     *
     * @param [type] $user
     * @return void
     */
    public function assign_campaign_course_group($user, $removed) {
        if ($this->campaign->campaigngroups == 'disabled') {
            return false;
        }

        // Check the assign group based on the Campaign or User.
        if ($this->campaign->campaigngroups == 'campaign') {
            return $this->assign_campaign_group($user, $removed);
        } else {
            \campaign_helper::create_campaign_group($this->campaign->id, 'peruser', $user->id);
            return $this->assign_user_group($user, $removed);
        }
    }

    /**
     * Assigned the user into the campaign named group.
     *
     * @param [object] $user
     * @return int|bool
     */
    public function assign_campaign_group($user, $removed) {
        global $DB;
        $idnumber = "campaign_". $this->campaign->id;
        $group = groups_get_group_by_idnumber($this->campaign->campaigncourse, $idnumber);
        if ($group) {
            if ($removed) {
                groups_remove_member($group->id, $user->id);
                return false;
            } else {
                $adduser = true;
                if (isset($this->campaign->groupcapacity) && $this->campaign->groupcapacity) {
                    if ($this->campaign->groupcapacity <= $DB->count_records('groups_members', ['groupid' => $group->id])) {
                        $adduser = false;
                    }
                }
                if ($adduser) {
                    groups_add_member($group->id, $user->id);
                }
                return $group->id;
            }

        }
        return false;
    }

    /**
     * Assigned the user into the user named group.
     *
     * @param [object] $user
     * @return int|bool
     */
    public function assign_user_group($user, $removed) {
        global $USER;
        $idnumber = "campaign_". $this->campaign->id . "_user_". $user->id;
        $group = groups_get_group_by_idnumber($this->campaign->campaigncourse, $idnumber);
        if ($group) {
            if ($removed) {
                groups_remove_member($group->id, $user->id);
                return false;
            } else {
                groups_add_member($group->id, $user->id);
                return $group->id;
            }
        }
        return false;
    }

    /**
     * Enrol the user into campaign course.
     * @param [object] $user
     */
    public function enroll_user_to_campaigncourse($user, $removed) {
        global $DB;
        $context = \context_course::instance($this->campaign->campaigncourse);
        $record = $DB->get_record('auth_magic_campaigns_users', ['userid' => $user->id]);
        $instance = [];
        // If user vaild the enrolment key user will enrol the related.
        if (isset($record->enrolpassword) && !empty($record->enrolpassword)) {
            $instance = $DB->get_record('enrol', ['courseid' => $this->campaign->campaigncourse,
                'enrol' => 'self', 'password' => $record->enrolpassword]);
        }

        if (empty($instance)) {
            $instances = $DB->get_records('enrol', ['courseid' => $this->campaign->campaigncourse, 'enrol' => 'self']);
            if (!$instances) {
                $instances = $DB->get_records('enrol', ['courseid' => $this->campaign->campaigncourse, 'enrol' => 'manual']);
            }
            $instance = current($instances);
        }

        if (!empty($instance)) {
            $enrolmanual = enrol_get_plugin($instance->enrol);
            if ($removed) {
                $enrolmanual->unenrol_user($instance, $user->id, $this->campaign->coursestudentrole);
            } else {
                $enrolmanual->enrol_user($instance, $user->id, $this->campaign->coursestudentrole);
            }
            return true;
        }
    }

    /**
     * It checks the current item or menu data contained access rules based on start or end date.
     *
     * Start date is configured and the date is reached user has access otherwise it hide the node.
     * Or the end date is cconfigured and the date is passed it will hide the node from menu.
     *
     * @return bool True if the date is reached and not passed if configured, otherwise it false.
     */
    public function restriction_bydate() {

        $startdate = $this->campaign->startdate;
        $enddate = $this->campaign->enddate;
        // Check any of the start date or end date is configured.
        if (empty($startdate) && empty($enddate)) {
            return true;
        }

        $date = new \DateTime("now", \core_date::get_user_timezone_object());
        $today = $date->getTimestamp();

        // Verify the started date is reached.
        if (!empty($startdate) && $startdate > $today) {
            return false;
        }

        // If the campaign startdate reached or no start date, then check the enddate is reached.
        if (!empty($enddate) && $enddate < $today) {
            return false;
        }
        // Campaign is configured between the start and end date.
        return true;
    }

    /**
     * Add the capacity info to the campaign form.
     *
     * @param \moodle_form $mform
     * @return void
     */
    public function show_capacity_info(&$mform) {

        $capacity = $this->get_capacity();

        $used = $capacity->used;
        $available = $capacity->available;

        $capacityinfo = get_string('campaigns:capacity_info', 'auth_magic', ['used' => $used, 'available' => $available]);
        $mform->addElement('html', "<div class='form-group row fitem'> <div class='col-md-3'></div>
        <div class='col-md-9'><div class='capacity-info'>");
        $mform->addElement('html', $capacityinfo);
        $mform->addElement('html', "</div></div></div>");
    }

    /**
     * Assign the user to the campaign after the user signup using the campaign, helps to maintain the capacity of campaign.
     *
     * @param object $formdata
     * @param int $userid
     * @param string $coupon
     * @return bool|int Inserted id or False.
     */
    public function assign_user($formdata, $userid, $coupon) {
        global $DB;

        $record = ['campaignid' => $this->id, 'userid' => $userid];

        // Check the user enrolment key is vaild then update the user passenrolment key field.
        if (isset($formdata->enrolpassword) && !empty($formdata->enrolpassword)) {
            $record['enrolpassword'] = $formdata->enrolpassword;
            $errors = $this->enrolment_key_validation($formdata->enrolpassword, false);
            if (empty($errors)) {
                $record['passenrolmentkey'] = true;
            }
        } else if ($coupon && md5($this->campaign->coupon) == $coupon) {
            $record['passenrolmentkey'] = true;
        }

        if (!$DB->record_exists('auth_magic_campaigns_users', $record)) {

            $record['timecreated'] = time();
            return $DB->insert_record('auth_magic_campaigns_users', $record);
        }
        return false;

    }


    /**
     * After submission to access the page.
     * @param object $user
     */
    public function campaign_after_submission($user, $redirectstr) {
        global $PAGE;
        $redirect = '';
        if ($this->campaign->redirectaftersubmisson == 'redirectsummary') { // Redirect summary page.
            $redirect = new moodle_url('/auth/magic/campaigns/summary.php', ['code' => $this->campaign->code,
                'submissionuser' => $user->id]);
        } else if ($this->campaign->redirectaftersubmisson == 'redirecturl') { // Redirect to custom URL.
            if ($this->campaign->submissonredirecturl) {
                $redirect = $this->campaign->submissonredirecturl;
            } else {
                $redirect = new moodle_url('/auth/magic/campaigns/summary.php', ['code' => $this->campaign->code,
                    'submissionuser' => $user->id]);
            }
        } else {
            $redirect = $PAGE->url;
        }
        if ($redirect) {
            return redirect($redirect, $redirectstr, null, \core\output\notification::NOTIFY_SUCCESS);
        }
    }

    /**
     * Send the welcome message to user.
     *
     * @param int $userid
     * @param string $password
     * @param int $groupid
     * @return void
     */
    public function send_welcome_message($userid, $password, $groupid) {
        global $DB;

        if (!$this->campaign->welcomemessage) {
            return false;
        }

        $filearea = 'welcomemessagecontent';
        // System context.
        $context = \context_system::instance();

        $subject = get_string('welcomemessagesubject', 'auth_magic');
        $template = $this->campaign->welcomemessagecontent;

        $user = \core_user::get_user($userid);
        $user->password = $password;
        // Assign the owner as sender if owener set for this campaign.
        // Otherwise assign the support user as sender.
        $sender = ($this->campaign->campaignowner) ? \core_user::get_user($this->campaign->campaignowner)
            : \core_user::get_support_user();
        $sender->fullname = fullname($sender);
        // Check sender is campaign owener.
        $owner = $this->campaign->campaignowner == $sender->id ? $sender : [];

        $usergroup  = ($groupid) ? groups_get_group($groupid) : null;
        // Replace the email text placeholders with data.
        list($subject, $messagehtml) = self::update_emailvars($template, $subject, $user, $this->campaign, $owner, $usergroup);
        // Rewrite the plugin file placeholders in the email text.
        $messagehtml = file_rewrite_pluginfile_urls($messagehtml, 'pluginfile.php',
            $context->id, 'auth_magic', $filearea, 0);

        $messageplain = html_to_text($messagehtml);

        $send = \campaign_helper::campaign_messagetouser($user, $subject, $messageplain, $messagehtml, $sender);
        // Send a copy of the campaign welcome message to user, if configured to owner should receive.
        if ($send && $this->campaign->campaignowner && $this->campaign->welcomemessageowner) {
            \campaign_helper::campaign_messagetouser($sender, $subject, $messageplain, $messagehtml);
        }
    }

    /**
     * Send followup message to users after some delays.
     *
     * @param stdclass $user
     * @param stdclass $campaign
     * @param stdclass $sender
     * @return void
     */
    public static function send_followup_message($user, $campaign, $sender) {
        global $DB;

        $filearea = 'followupmessagecontent';
        // System context.
        $context = \context_system::instance();

        $subject = get_string('followupmessagesubject', 'auth_magic');
        $template = $campaign->followupmessagecontent;

        $sender->fullname = fullname($sender);
        // Check sender is campaign owener.
        $owner = $campaign->campaignowner == $sender->id ? $sender : [];
        // Replace the email text placeholders with data.
        list($subject, $messagehtml) = self::update_emailvars($template, $subject, $user, $campaign, $owner, null);
        // Rewrite the plugin file placeholders in the email text.
        $messagehtml = file_rewrite_pluginfile_urls($messagehtml, 'pluginfile.php',
            $context->id, 'auth_magic', $filearea, 0);
        $messageplain = html_to_text($messagehtml);
        // Send the followup message to user.
        $send = \campaign_helper::campaign_messagetouser($user, $subject, $messageplain, $messagehtml, $sender);
        if (!PHPUNIT_TEST) {
            mtrace('Follow up message send to user' . fullname($user) .'('.$user->id.')');
        }
        // Update the followup notified flag to prevt sending again.
        $DB->set_field('auth_magic_campaigns_users', 'followup', time(), ['userid' => $user->id, 'campaignid' => $campaign->id]);
        // Send a copy of the campaign welcome message to user, if configured to owner should receive.
        if ($send && $campaign->campaignowner && $campaign->followupmessageowner) {
            \campaign_helper::campaign_messagetouser($sender, $subject, $messageplain, $messagehtml);
        }
    }


    /**
     * Get capacity avialble for this campaign.
     *
     * @return stdclass
     */
    public function get_capacity() {
        global $DB;

        $sql = 'SELECT * FROM {auth_magic_campaigns_users} cu
        JOIN {user} u ON u.id = cu.userid
        WHERE cu.campaignid = :campaignid AND u.deleted <> 1';

        $list = $DB->get_records_sql($sql, ['campaignid' => $this->id]);

        $used = count($list);

        $available = ($this->campaign->capacity > 0) ? ($this->campaign->capacity - $used) : get_string('unlimited', 'auth_magic');

        $capacity = (object) ['used' => $used, 'available' => $available];
        return $capacity;

    }

    /**
     * Get images for campaign.
     *
     * @return void
     */
    public function get_campaign_images() {
        $fileareas = ['logo', 'headerimage', 'backgroundimage'];

        $images = [];
        $fs = get_file_storage();
        $contextid = \context_system::instance()->id;
        // Fetch the file url.
        foreach ($fileareas as $image) {
            $files = $fs->get_area_files($contextid, 'auth_magic', $image, $this->id, '', false);
            if (!empty($files)) {
                // Get the first file.
                $file = reset($files);

                // Conver the file to url.
                $images[$image] = \moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename(),
                    false
                );
            }
        }

        return $images;
    }

    /**
     * Confirm the campaign has token or user entered correct password.
     *
     * @return bool True if token avialble or user verified otherwise false.
     */
    public function password_verified() {
        global $SESSION, $USER;

        if (empty($this->campaign->password)) {
            return true;
        }

        $token = optional_param('token', null, PARAM_ALPHANUMEXT);
        // Verify the campaign hased password is same as token.
        if ($token && $this->campaign->token == $token) {
            return true;
        } else {
            // Check the user already verified and have the access to the campaign.
            return isset($SESSION->passwordverifiedcampaign) && isset($SESSION->passwordverifiedcampaign[$this->id]) ? true : false;
        }

        return false;
    }

    /**
     * Verify the password for campaign.
     * @param object $data
     * @return bool
     */
    public function verify_password($data) {
        global $SESSION;

        if (empty($data->campaignpassword)) {
            \core\notification::error(get_string('campaigns:emptypassword', 'auth_magic'));
            return false;
        }

        $password = base64_encode($data->campaignpassword);
        if ($password == $this->campaign->password) {
            $SESSION->passwordverifiedcampaign[$this->id] = true;
            // Display the success page.
            \core\notification::success(get_string('campaigns:verifiedsuccess', 'auth_magic'));
            return true;
        } else {
            // Display the error message for user.
            \core\notification::error(get_string('campaigns:emptypassword', 'auth_magic'));
        }
        return false;
    }

    /**
     * Get posistion classes.
     * @return array
     */
    public function form_position_class() {
        $formclass = [
            self::FORM_POSITION_CENTER  => 'form-position-center',
            self::FORM_POSITION_LEFTOVERLAY => 'form-position-leftoverlay',
            self::FORM_POSITION_RIGHTOVERLAY => 'form-position-rightoverlay',
            self::FORM_POSITION_LEFTFULL  => 'form-position-leftfull',
            self::FORM_POSITION_RIGHTFULL  => 'form-position-rightfull',
        ];
        return $formclass[$this->campaign->formposition] ?? '';
    }

    /**
     * Build form.
     * @param mform $campaignmanageform
     * @param object $user
     */
    public function buildform($campaignmanageform = null, $campaignselfform = null, $campaignteamform = null) {
        global $OUTPUT, $CFG, $USER, $DB;

        if (!$this->is_campaign_available() && !(is_siteadmin() || (isloggedin()
            && $USER->id == $this->campaign->campaignowner))) {
            $notavailable = get_string('campaigns:notavailable', 'auth_magic');
            \core\notification::info($notavailable); // Info to user.
            return false;
        }

        // Allow direct access to the users.
        $isallowed = (is_siteadmin() || (isloggedin() && $USER->id == $this->campaign->campaignowner));

        // Password verified.
        if (!$isallowed && !$this->password_verified()) {
            require_once($CFG->dirroot. '/auth/magic/campaigns/locallib.php');

            $passwordform = new \campaign_rule_form(null, ['code' => $this->campaign->code]);
            $passwordtemplate = [
                'confirm' => true,
                'passwordform' => $passwordform->render(),
            ];
            return $OUTPUT->render_from_template('auth_magic/signup', $passwordtemplate);
        }

        // Campaign heading.
        $campaigndata  = \campaign_helper::get_campaign($this->id);

        // Form position classes.
        $classes[] = $this->form_position_class();
        // Transparent  classes.
        $classes[] = $this->campaign->transparentform ? 'form-transparent' : '';
        // Genereate owener data.
        if ($this->campaign->displayowerprofile) {
            $classes[] = 'form-owner-profile';
            $owner = $this->campaign->campaignowner ? \core_user::get_user($this->campaign->campaignowner) : get_admin();
            $ownername = fullname($owner);
            if (!empty($owner->picture)) {
                $ownerpicture = $OUTPUT->user_picture($owner, ['size' => 32]);
            }
        }

        $cost = '';
        $campaigncoupon = optional_param('coupon', null, PARAM_ALPHANUMEXT);
        if (auth_magic_is_paid_campaign($campaigndata) && $campaigncoupon != md5($this->campaign->coupon)) {
            $cost = \core_payment\helper::get_cost_as_string($campaigndata->paymentfee, $campaigndata->paymentcurrency);
        }

        $summarycontent = '';
        if ($submissonuserid = optional_param('submissionuser', null, PARAM_INT)) {
            if ($this->is_coupon_user()) {
                $cost = '';
            }
            if ($this->campaign->submissioncontent
                && $DB->record_exists('auth_magic_campaigns_users', ['userid' => $submissonuserid,
                'campaignid' => $this->id])) {
                $submissionuser = $DB->get_record('user', ['id' => $submissonuserid]);
                $summarycontent = self::update_submission_summaryvars($this->campaign->submissioncontent, $submissionuser);
                $summarycontent = file_rewrite_pluginfile_urls(
                    $summarycontent, 'pluginfile.php', \context_system::instance()->id,
                    'auth_magic', 'submissioncontent', $this->id
                );
                $summarycontent = format_text($summarycontent, FORMAT_HTML);
            }
        }

        $tabs = [
            'myself' => [
                'capability' => 'auth/magic:campaignself',
                'context' => \context_system::instance(),
                'string' => get_string('myself', 'auth_magic'),
                'id' => "my-tab",
                'id2' => "myselftab",
                'contentform' => $campaignselfform,
            ],
            'team' => [
                'capability' => 'auth/magic:campaignteam',
                'context' => \context_system::instance(),
                'string' => get_string('teamtab', 'auth_magic'),
                'id' => "team-tab",
                'id2' => "teamtab",
                'contentform' => $campaignteamform,
            ],
            'new' => [
                'capability' => 'auth/magic:campaignnew',
                'context' => \context_system::instance(),
                'string' => get_string('contacttab', 'auth_magic'),
                'id' => "user-tab",
                'id2' => "usertab",
                'contentform' => $campaignmanageform,
            ],
        ];

        $tabactive = true;
        $tabcontentactive = true;
        $content = html_writer::start_div('campaign-signup-block');

        if (isloggedin()) {
            // Tab html.
            $tabhead = '';
            $tabhead .= html_writer::tag("div", get_string('signupyourself', 'auth_magic'), ['class' => 'campaign-info-head']);
            $tabhead .= html_writer::start_tag('ul', ["class" => "nav nav-tabs", "id" => "myTab",  "role" => "tablist"]);
                foreach ($tabs as $tab) {
                    if (has_capability($tab['capability'], $tab['context'])) {
                        $navadditionalclasses = ($tabactive === true) ? " active" : "";
                        $tabhead .= html_writer::start_tag('li', ['class' => "nav-item" . $navadditionalclasses,  "role" => "presentation"]);
                        $tabhead .= html_writer::tag('a', $tab['string'], ["class" => "nav-link" . $navadditionalclasses,
                            "id" => $tab['id'], "data-toggle" => "tab", "href" => "#". $tab['id2'], "role" => "tab",
                            "aria-selected" => "true"]);
                        $tabhead .= html_writer::end_tag('li');
                        $tabactive = false;
                    }
                }
            $tabhead .= html_writer::end_tag('ul');

            $tabbody = html_writer::start_tag('div', ["class" => "tab-content", "id" => "myTabContent"]);
                foreach ($tabs as $tab) {
                    if (has_capability($tab['capability'], $tab['context'])) {
                        $tabadditionalclasses = ($tabcontentactive === true) ? " show active" : "";
                        $tabbody .= html_writer::start_tag('div', ["class" => "tab-pane fade" . $tabadditionalclasses, "id" => $tab['id2'],
                            "role" => "tabpanel", "aria-labelledby" => $tab['id']]);
                        $tabbody .= ($tab['contentform'] != null) ? $tab['contentform']->render() : '';
                        $tabbody .= html_writer::end_tag('div');
                        $tabcontentactive = false;
                    }
                }
            $tabhead .= html_writer::end_tag('div');
            $content .= $tabhead . $tabbody;
        } else {
            $content .= ($campaignmanageform != null) ? $campaignmanageform->render() : '';
        }
        $content .= html_writer::end_div();

        $template = [
            'campaign' => $campaigndata,
            'cost' => $cost,
            'images' => $this->get_campaign_images(),
            // Display the campaigns form for create or edit.
            'content' => $content,
            'classes' => implode(" ", $classes),
            'ownerpicture' => $ownerpicture ?? false,
            'ownername' => $ownername ?? false,
            'title' => format_string($campaigndata->title),
            'description' => format_text($this->campaign->description, FORMAT_HTML),
            'summary' => !empty($summarycontent) ? $summarycontent : '',
            'istab' => isloggedin() ? true : false,
        ];
        return $OUTPUT->render_from_template('auth_magic/signup', $template);
    }

    /**
     * Check the vaild coupon or not.
     *
     * @return boolean
     */
    public function is_valid_coupon_campaign() {
        if (empty($this->campaign->courseenrolmentkey) || !$this->campaign->campaigncourse
            || $this->campaign->courseenrolmentkey == 'disabled') {
            return true;
        }
        $keytype = $this->campaign->courseenrolmentkey;
        $instances = enrol_get_instances($this->campaign->campaigncourse, true);
        foreach ($instances as $instance) {
            if (!empty($instance->password) && $instance->password == $enrolpassword) {
                return true;
            }
        }
        if ($keytype != 'strict' && enrol_self_check_group_enrolment_key($this->campaign->campaigncourse, $enrolpassword)) {
            return true;
        }
        return false;
    }

    /**
     * Check the user used to the coupon.
     *
     * @return boolean
     */
    public function is_coupon_user() {
        global $DB, $USER;
        if ($DB->record_exists('auth_magic_campaigns_users', ['campaignid' => $this->campaign->id,
            'userid' => $USER->id, 'passenrolmentkey' => true])) {
                return true;
        }
        return false;
    }

    /**
     * Get campaign through code.
     * @param string $code
     */
    public static function get_campaign_fromcode($code) {
        global $DB;
        return $DB->get_record('auth_magic_campaigns', ['code' => $code]);
    }

    /**
     * Replace email template placeholders with dynamic datas.
     *
     * @param  mixed $templatetext Email Body content with placeholders
     * @param  mixed $subject Mail subject with placeholders.
     * @param  mixed $user User data object.
     * @param  mixed $campaign Campaign data object.
     * @param  mixed $campaignowner Campaign owner user data.
     * @param  mixed $usergroup Campaign group data.
     * @return array Updated subject and message body content.
     */
    public static function update_emailvars($templatetext, $subject, $user, $campaign, $campaignowner, $usergroup) {
        global $DB, $CFG;

        require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_message_vars.php');
        $amethods = \campaign_message_vars::vars(); // List of available placeholders.
        $vars = new \campaign_message_vars($user, $campaign, $campaignowner, $usergroup);

        foreach ($amethods as $funcname) {
            $replacement = "{" . $funcname . "}";
            // Message text placeholder update.
            if (stripos($templatetext, $replacement) !== false) {
                $val = $vars->$funcname;
                // Placeholder found on the text, then replace with data.
                $templatetext = str_replace($replacement, $val, $templatetext);
            }
            // Replace message subject placeholder.
            if (stripos($subject, $replacement) !== false) {
                $val = $vars->$funcname;
                $subject = str_replace($replacement, $val, $subject);
            }
        }
        return [$subject, $templatetext];
    }

    /**
     * Replace message placeholders with dynamic datas.
     *
     * @param  mixed $message content with placeholders
     * @param  mixed $user User data object.
     * @return array Updated subject and message body content.
     */
    public static function update_submission_summaryvars($message, $user) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_formfield_vars.php');
        require_once($CFG->dirroot. "/user/profile/lib.php");
        profile_load_data($user);
        $userfieldsdata = \campaign_helper::get_magic_profile_fileds_data();
        $fields = array_keys($userfieldsdata['profilefields']);
        $amethods = \campaign_formfield_vars::vars($fields); // List of available placeholders.
        $vars = new \campaign_formfield_vars($user);
        foreach ($amethods as $funcname) {
            $replacement = "{" . $funcname . "}";
            // Message text placeholder update.
            if (stripos($message, $replacement) !== false) {
                $position = strpos($funcname, 'standard_');
                if ($position !== false) {
                    $funcname = substr_replace($funcname, '', $position, strlen('standard_'));
                }
                $val = isset($vars->user->$funcname) ? $vars->user->$funcname : '';
                // Placeholder found on the text, then replace with data.
                $message = str_replace($replacement, $val, $message);
            }
        }
        return $message;
    }

}
