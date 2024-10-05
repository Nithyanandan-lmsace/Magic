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
 * Define campaign helper.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot."/auth/magic/lib.php");
require_once($CFG->dirroot."/cohort/lib.php");

use auth_magic\campaign;

/**
 * Class Campaign helper.
 */
class campaign_helper {

    /**
     * The unique identifier for the campaign.
     *
     * @var int
     */
    public $campaignid;

    /**
     * The campaign object
     */
    public $campaign;

    /**
     * campaign constructor.
     *
     * @param int $campaignid campaign data.
     * @throws moodle_exception If campaign format is not correct.
     */
    public function __construct($campaignid) {
        global $DB;

        $this->campaignid = $campaignid;
        $this->campaign = self::get_campaign($this->campaignid);
    }

    /**
     * Generate the button which is displayed on top of the manage campaigns table. Helps to create campaigns.
     *
     * @return string The HTML contents to display the create campaigns button.
     */
    public static function create_campaign_button() {
        global $OUTPUT;
        if (!has_capability('auth/magic:createcampaign', context_system::instance())) {
            return "";
        }

        // Setup create campaign button on page.
        $caption = get_string('createcampaign', 'auth_magic');
        $editurl = new moodle_url('/auth/magic/campaigns/edit.php', ['sesskey' => sesskey()]);

        // IN Moodle 4.2, primary button param depreceted.
        $button = new single_button($editurl, $caption, 'get');
        $button = $OUTPUT->render($button);

        return $button;
    }

    public function update_campaign_selfform($data) {
        global $DB;
        $campaigninstance = campaign::instance($this->campaignid);
        if (isset($data->userid) && !auth_magic_is_paid_campaign($this->campaign, $data->coupon)) {
            $user = $DB->get_record('user', ['id' => $data->userid]);
            // Assign to the campaign cohorts, roles, parent.
            $this->process_campaign_assignments($user);
            $campaigninstance->campaign_after_submission($user, get_string('campaignassignmentapply', 'auth_magic'));
        } else {
            $returnurl = new moodle_url('/auth/magic/campaigns/payment.php',
                        ['campaignid' => $this->campaignid, 'userid' => $data->userid, 'sesskey' => sesskey()]);
            return redirect($returnurl);
        }
    }


    public function update_campaign_teamform($data) {
        global $DB, $PAGE;
        if (!empty($data->teammembers)) {
            if (!auth_magic_is_paid_campaign($this->campaign, $data->coupon)) {
                foreach ($data->teammembers as $member) {
                    $user = $DB->get_record('user', ['id' => $member]);
                    // Assign to the campaign cohorts, roles, parent.
                    $this->process_campaign_assignments($user);
                }
                return redirect($PAGE->url, get_string('campaignassignmentapply', 'auth_magic'),
                    null, \core\output\notification::NOTIFY_SUCCESS);
            } else {
                $returnurl = new moodle_url('/auth/magic/campaigns/payment.php',
                ['campaignid' => $this->campaignid, 'sesskey' => sesskey()]);
                $userparams = [];
                foreach ($data->teammembers as $member) {
                    $userparams[] = 'users[]=' . $member;
                }
                $usersquery = implode('&', $userparams);
                $returnurl = $returnurl->out(false) . "&" . $usersquery;
                return redirect($returnurl);
            }
        }
    }

    public function update_campaign_manageform($user, $params) {
        global $DB;
        $campaigninstance = campaign::instance($this->campaignid);
        $parentuser = null;
        $userenrolmentkey = isset($user->enrolpassword) ? $user->enrolpassword : '';
        if ($DB->record_exists('user', ['email' => $user->email])) {
            $redirectstr = get_string('campaignassignapplied', 'auth_magic');
            $newuser = $DB->get_record('user', ['email' => $user->email]);
            $customfieldvalues = auth_magic_managerole_assignments_customvalues($user, $this->campaign->approvalroles);
            $campaigninstance->assign_user($user, $newuser->id, $params['coupon']);
            $assignmentobj = \auth_magic\roleassignment::create($newuser->id);
            $parentuser = $assignmentobj->manage_role_assignments([], $customfieldvalues);

        } else {
            $redirectstr = get_string('signupsuccess', 'auth_magic');
            // Add missing required fields.
            $user = campaign_helper::get_campaign_fields_instance($this->campaignid)->reset_placeholder_values($user);
            if (trim($user->username) === '') {
                $user->username = $user->email;
            }
            $user = campaign_helper::signup_setup_new_user($user);
            // Plugins can perform post sign up actions once data has been validated.
            core_login_post_signup_requests($user);
            $authplugin = get_auth_plugin($user->auth);
            $user->password = isset($user->password) && $authplugin->is_internal() ? hash_internal_user_password($user->password) : '';
            // Prints notice and link to login/index.php.
            if ($userid = user_create_user($user, false, false)) {
                $user->id = $userid;
                $newuser = $DB->get_record('user', ['id' => $userid]);
                // Sent the confirmation link.
                if ($this->campaign->emailconfirm == campaign::ENABLE && $this->campaign->approvaltype != 'optionalin') {
                    auth_magic_send_confirmation_email($newuser, new moodle_url('/auth/magic/confirm.php'));
                }

                $usercontext = context_user::instance($newuser->id);
                // Update preferences.
                useredit_update_user_preference($newuser);
                // Save custom profile fields data.
                profile_save_data($user);

                if ($authplugin->is_internal() && empty($user->password) && $this->campaign->approvaltype != 'optionalin') {
                    setnew_password_and_mail($newuser);
                    unset_user_preference('create_password', $newuser);
                    set_user_preference('auth_forcepasswordchange', 1, $newuser);
                }

                $campaigninstance->assign_user($user, $newuser->id, $params['coupon']);

                \core\event\user_created::create_from_userid($newuser->id)->trigger();
                // Login user automatically.
                if ($this->campaign->emailconfirm != campaign::ENABLE && $this->campaign->approvaltype != 'optionalin') {
                    complete_user_login($newuser);
                }
                // After user complete login then Set the user unconfirmed.
                if ($this->campaign->approvaltype == 'optionalin') {
                    $DB->set_field("user", "confirmed", 0, ["id" => $newuser->id]);
                } else if ($this->campaign->emailconfirm == campaign::PARTIAL) {
                    $DB->set_field("user", "confirmed", 0, ["id" => $newuser->id]);
                    if ($this->campaign->approvaltype != 'optionalin') {
                        auth_magic_send_confirmation_email($newuser, new moodle_url('/auth/magic/confirm.php'));
                    }
                }
            }
        }

        if (!auth_magic_is_paid_campaign($this->campaign, $userenrolmentkey) && ($this->campaign->emailconfirm != campaign::ENABLE
            || isloggedin()) && $this->campaign->approvaltype != 'optionalin') {
            // Assign to the campaign cohorts, roles, parent.
            $this->process_campaign_assignments($newuser, false, $parentuser);
        }
        $campaigninstance->campaign_after_submission($newuser, $redirectstr);

    }

    /**
     * Get file options for selecting a single web image file.
     *
     * @return array An array of file options.
     */
    public static function image_fileoptions() {

        return [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => 'web_image',
        ];
    }

    /**
     * Get a profile fields data for magic campaigns settings.
     *
     * @return array $data An array of profile field data.
     */
    public static function get_magic_profile_fileds_data() {
        $data = [
            'profilefields' => [
                'standard_firstname' => get_string('campaigns:standard_firstname', 'auth_magic'),
                'standard_lastname' => get_string('campaigns:standard_lastname', 'auth_magic'),
                'standard_username' => get_string('campaigns:standard_username', 'auth_magic'),
                'standard_password' => get_string('campaigns:standard_password', 'auth_magic'),
                'standard_email' => get_string('campaigns:standard_email', 'auth_magic'),
                'standard_country' => get_string('campaigns:standard_country', 'auth_magic'),
                'standard_lang' => get_string('campaigns:standard_lang', 'auth_magic'),
                'standard_city' => get_string('campaigns:standard_city', 'auth_magic'),
                'standard_idnumber' => get_string('campaigns:standard_idnumber', 'auth_magic'),
                'standard_alternatename' => get_string('campaigns:standard_alternatename', 'auth_magic'),
                'standard_department' => get_string('campaigns:standard_department', 'auth_magic'),
                'standard_institution' => get_string('campaigns:standard_institution', 'auth_magic'),
                'standard_address' => get_string('campaigns:standard_address', 'auth_magic'),
            ],
            'fieldoptions' => [
                MAGICCAMPAIGNSREQUIRED => get_string('campaigns:required', 'auth_magic'),
                MAGICCAMPAIGNSOPTIONAL => get_string('campaigns:optional', 'auth_magic'),
                MAGICCAMPAIGNSHIDDENPROVIDEVALUE => get_string('campaigns:hiddentype1', 'auth_magic'),
                MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT => get_string('campaigns:hiddentype2', 'auth_magic'),
                MAGICCAMPAIGNSREQUIREDHIDDENOTHERFIELD => get_string('campaigns:hiddentype3', 'auth_magic'),
            ],
            'customfieldoptions' => [
                'standard_password' => [
                    REQUIREDONCE => get_string('campaigns:requiredonce', 'auth_magic'),
                    REQUIREDTWICE => get_string('campaigns:requiredtwice', 'auth_magic'),
                    HIDDEN => get_string('campaigns:hidden', 'auth_magic'),
                ],
            ],
            'defaultvalues' => [
                'standard_firstname' => MAGICCAMPAIGNSREQUIRED,
                'standard_lastname' => MAGICCAMPAIGNSREQUIRED,
                'standard_username' => MAGICCAMPAIGNSREQUIRED,
                'standard_password' => REQUIREDONCE,
                'standard_email' => MAGICCAMPAIGNSREQUIRED,
                'standard_country' => MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT,
                'standard_lang' => MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT,
                'standard_city' => MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT,
                'standard_idnumber' => MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT,
                'standard_alternatename' => MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT,
                'standard_department' => MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT,
                'standard_institution' => MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT,
                'standard_address' => MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT,
                'profile_field' => MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT,
            ],
        ];
        return $data;
    }

    /**
     * Send the campaign expiry notification.
     * @param mixed $campaign
     * @param mixed $notifytime
     * @return bool
     */
    public function send_expiry_notification($campaign) {
        global $DB;
        $campaignusers = $DB->get_records("auth_magic_campaigns_users", ['campaignid' => $campaign->id]);
        if (!empty($campaignusers)) {
            foreach ($campaignusers as $campaignuser) {
                // Parse selected notification schedule
                $notifyschedule = $campaign->expirybeforenotify;
                $notifiedstatus = !empty($campaignuser->expirybeforenotifystatus) ? json_decode($campaignuser->expirybeforenotifystatus) : [];
                $time_now = time();
                $notificationstosend = [];
                // Define notification intervals in seconds
                $intervals = [
                    '3month' => 3 * 30 * 24 * 60 * 60,
                    '1month' => 1 * 30 * 24 * 60 * 60,
                    '3week' => 3 * 7 * 24 * 60 * 60,
                    '2week' => 2 * 7 * 24 * 60 * 60,
                    '1week' => 1 * 7 * 24 * 60 * 60,
                    '3day' => 3 * 24 * 60 * 60,
                    '1day' => 1 * 24 * 60 * 60,
                    'upon' => 0,
                ];

                foreach ($notifyschedule as $notifytime) {
                    // Calculate when the notification should be sent
                    $notifytime_seconds = $intervals[$notifytime];
                    $notify_date = $campaign->expirydate - $notifytime_seconds;
                    // Check if it's time to send the notification and if it hasn't been sent yet
                    if ($time_now >= $notify_date && !in_array($notifytime, $notifiedstatus)) {
                        $notificationstosend[] = $notifytime;
                    }
                }


                if (!empty($notificationstosend)) {
                    foreach ($notificationstosend as $notifytime) {
                        mtrace("running send_expiry_notification: " . $campaign->title . "notifytime: ". $notifytime);
                        $user = $DB->get_record('user', ['id' => $campaignuser->userid]);
                        mtrace("running send_expiry_notification: " . $campaign->title . "notifytime: ". $notifytime . "User" . fullname($user));
                        $subject = get_string("subjectcampaignexpirynotify", 'auth_magic', $campaign->title);
                        $message = get_string('messagecampaignexpirynotify', 'auth_magic', ['campaignname' => $campaign->title, 'notifytime' => $notifytime]);
                        self::campaign_messagetouser($user, $subject, $message, $message);
                    }

                    // Update the notifiedstatus field to mark these notifications as sent
                    $notifiedstatus = array_merge($notifiedstatus, $notificationstosend);
                    $record = $DB->get_record('auth_magic_campaigns_users', ['id' => $campaignuser->id]);
                    $record->expirybeforenotifystatus = json_encode($notifiedstatus);
                    mtrace("expirybeforenotifystatus: " .  json_encode($notifiedstatus));
                    $DB->update_record('auth_magic_campaigns_users', $record);
                }
            }
        }
        return true;
    }

    /**
     * Expiry campaign workflow.
     */
    public function process_expiry_campaign_actions() {
        global $DB, $CFG;
        $campaignusers = $DB->get_records('auth_magic_campaigns_users', ['campaignid' => $this->campaignid]);
        if (!empty($campaignusers)) {
            foreach ($campaignusers as $campaignuser) {
                if ($user = $DB->get_record('user', array('id'=> $campaignuser->userid, 'mnethostid'=> $CFG->mnet_localhost_id, 'deleted' => 0))) {
                    if ($this->campaign->expirysuspenduser && $user->suspended != 1) {
                        $user->suspended = 1;
                        // Force logout.
                        \core\session\manager::kill_user_sessions($user->id);
                        user_update_user($user, false);
                    }

                    if ($this->campaign->expirydeleteduser) {
                        delete_user($user);
                        \core\session\manager::gc(); // Remove stale sessions.
                        $user = null;
                    }

                    if ($user) {
                        if ($this->campaign->expiryassigncohorts) {
                            $this->assign_cohorts([$this->campaign->expiryassigncohorts], $user->id, false);
                        }

                        if ($this->campaign->expiryremovecohorts) {
                            $this->assign_cohorts([$this->campaign->expiryremovecohorts], $user->id, true);
                        }

                        if ($this->campaign->expiryunassignglobalrole) {
                            $this->assign_globalrole($this->campaign->expiryunassignglobalrole, $user->id, true);
                        }
                    }
                }
            }
        }
    }




    /**
     * Implement the campaign assignments workflow.
     * @param object $user
     */
    public function process_campaign_assignments($user, $removed = false, $parentuser = null) {
        global $USER;
        // Assign to the cohort.
        $this->assign_cohorts($this->campaign->cohorts, $user->id, $removed);
        $this->assign_globalrole($this->campaign->globalrole, $user->id, $removed);
        // Campaign owner role id.
        $ownerroleid = get_config('auth_magic', 'campaignownerrole');
        $this->assign_parentuser($ownerroleid, $this->campaign->campaignowner, $user->id, $removed);

        $campaigninstance = campaign::instance($this->campaignid);

        $usergroupid = $campaigninstance->process_campaign_course($user, $removed);

        $campaigninstance->process_approval_roles($user, $removed, $parentuser);

        // Accept user.
        $campaigninstance->accept_user_privacy_policies($USER->id);
        // Insert the user to the campaign for maintain the capacity.
        // Send a welcome message to the user.
        if (!$removed) {
            $campaigninstance->send_welcome_message($user->id, $user->password ?? '', $usergroupid);
        }
    }

    /**
     * Insert or update the campaigns instance to DB.
     *
     * @param object $formdata form data from campaigns form.
     * @return $campaignid.
     */
    public static function manage_instance($formdata) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        if (isset($formdata->id) && $DB->record_exists('auth_magic_campaigns', ['id' => $formdata->id])) {
            $campaignid = self::update_campaign($formdata);
            self::create_campaign_group($campaignid, 'campaign');
            self::get_campaign_fields_instance($campaignid)->update_fields($formdata);
            // Show the edited success notification.
            \core\notification::success(get_string('campaigns:updatesuccess', 'auth_magic'));
        } else {
            $campaignid = self::create_campaign($formdata);
            self::create_campaign_group($campaignid, 'campaign');
            self::get_campaign_fields_instance($campaignid)->create_fields($formdata);
            // Show the campaign inserted success notification.
            \core\notification::success(get_string('campaigns:insertsuccess', 'auth_magic'));
        }
        // Allow to update the DB changes to Database.
        $transaction->allow_commit();
        return $campaignid;
    }

    /**
     * Create the campaign group.
     *
     * @param [int] $campaignid
     * @param [string] $grouptype
     * @param int|null $groupuser
     * @return void
     */
    public static function create_campaign_group($campaignid, $grouptype, $groupuser = null) {
        global $USER, $DB;
        $groupid = 0;
        $campaign = campaign::instance($campaignid)->get_campaign();
        if ($grouptype == 'campaign' && $campaign->campaigngroups == 'campaign') { // Create campaign group.
            $idnumber = "campaign_". $campaign->id;
            if (!$group = groups_get_group_by_idnumber($campaign->campaigncourse, $idnumber)) {
                $groupdata = new \stdClass();
                $groupdata->courseid = $campaign->campaigncourse;
                $groupdata->name = $campaign->title;
                $groupdata->enablemessaging = $campaign->groupmessaging;
                $groupdata->idnumber = $idnumber;
                if ($campaign->groupenrolmentkey) {
                    $groupdata->enrolmentkey = base64_encode(random_string(6));
                }
                $groupid = groups_create_group($groupdata);
            } else {
                $group->enablemessaging = $campaign->groupmessaging;
                if ($campaign->groupenrolmentkey) {
                    $group->enrolmentkey = base64_encode(random_string(6));
                } else {
                    $group->enrolmentkey = "";
                }
                groups_update_group($group);
            }
        } else if ($grouptype == 'peruser' && $campaign->campaigngroups == 'peruser') { // Create the user group.
            $idnumber = "campaign_". $campaign->id . "_user_". $groupuser;
            if (!$group = groups_get_group_by_idnumber($campaign->campaigncourse, $idnumber)) {
                $groupdata = new \stdClass();
                $groupdata->courseid = $campaign->campaigncourse;
                $groupdata->name = fullname($USER) . " " . $campaign->title;
                $groupdata->enablemessaging = $campaign->groupmessaging;
                $groupdata->idnumber = $idnumber;
                if ($campaign->groupenrolmentkey) {
                    $groupdata->enrolmentkey = base64_encode(random_string(6));
                }
                $groupid = groups_create_group($groupdata);
            } else {
                $group->enablemessaging = $campaign->groupmessaging;
                if ($campaign->groupenrolmentkey) {
                    $group->enrolmentkey = base64_encode(random_string(6));
                } else {
                    $group->enrolmentkey = "";
                }
                groups_update_group($group);
            }
        }

        if ($groupid) {
            if ($campaign->campaigngrouping) {
                // Assign campaign course group into the grouping.
                groups_assign_grouping($campaign->campaigngrouping, $groupid);
            }

            // Store the campaign groups.
            if (!$DB->record_exists('auth_magic_campaign_groups', ['groupid' => $groupid,
                'campaignid' => $campaign->id])) {
                $record = new stdClass;
                $record->campaignid = $campaign->id;
                $record->groupid = $groupid;
                $record->timecreated = time();
                $DB->insert_record('auth_magic_campaign_groups', $record);
            }
        }
    }

    /**
     * Get campaign instance.
     * @param int $campaignid
     * @return object instance
     */
    public static function get_campaign_fields_instance($campaignid) {
        return \auth_magic\campaign_fields::instance($campaignid);
    }

    /**
     * Get the data fiels in the auth magic campaigns table.
     *
     * @param object $instance get the instance of table.
     * @param object $data get the form data.
     * @return object $instance.
     */
    public static function get_data_fields($instance, $data) {
        global $USER;
        // Form fields.
        $instance->title = (!empty($data->title)) ? $data->title : '';
        $context = context_system::instance();
        $instance->description_editor = $data->description_editor;
        $instance->description = $data->description_editor['text'];
        $instance->descriptionformat = $data->description_editor['format'];

        $instance->logo_filemanager = $data->logo_filemanager;
        $instance->headerimage_filemanager = $data->headerimage_filemanager;
        $instance->backgroundimage_filemanager = $data->backgroundimage_filemanager;

        $instance->comments_editor = $data->comments_editor;
        $instance->comments = $data->comments_editor['text'];
        $instance->commentsformat = $data->comments_editor['format'];

        $instance->capacity = (!empty($data->capacity)) ? $data->capacity : '';
        $instance->status = (!empty($data->status)) ? $data->status : '';
        $instance->visibility = (!empty($data->visibility)) ? $data->visibility : '';
        $instance->startdate = (!empty($data->startdate)) ? $data->startdate : '';
        $instance->enddate = (!empty($data->enddate)) ? $data->enddate : '';
        $instance->password = (!empty($data->password)) ? base64_encode($data->password) : '';

        $instance->transparentform = (!empty($data->transparentform)) ? $data->transparentform : '';
        $instance->displayowerprofile = (!empty($data->displayowerprofile)) ? $data->displayowerprofile : '';
        $instance->formposition = (!empty($data->formposition)) ? $data->formposition : '';
        $instance->recaptcha = (!empty($data->recaptcha)) ? $data->recaptcha : '';
        $instance->emailconfirm = (!empty($data->emailconfirm)) ? $data->emailconfirm : '';

        $instance->redirectaftersubmisson = (!empty($data->redirectaftersubmisson)) ? $data->redirectaftersubmisson : '';
        $instance->submissonredirecturl = isset($data->submissonredirecturl) ? $data->submissonredirecturl : '';
        $instance->submissioncontent_editor = $data->submissioncontent_editor;
        $instance->submissioncontent = $data->submissioncontent_editor['text'];
        $instance->submissioncontentformat = $data->submissioncontent_editor['format'];

        if (!isset($instance->id) || ($instance->expirytime != $data->expirytime)) {
            $instance->expirytime = isset($data->expirytime) ? $data->expirytime : 0;
            $instance->expirydate = !empty($data->expirytime) ? time() + $instance->expirytime : 0;
        }


        $instance->expirysuspenduser = ($data->expirysuspenduser) ? $data->expirysuspenduser : 0;
        $instance->expirydeleteduser = $data->expirydeleteduser ? $data->expirydeleteduser : 0;
        $instance->expiryassigncohorts = $data->expiryassigncohorts;
        $instance->expiryremovecohorts = $data->expiryremovecohorts;
        $instance->expiryunassignglobalrole = $data->expiryunassignglobalrole;
        $instance->expirybeforenotify = !empty($data->expirybeforenotify) ? json_encode($data->expirybeforenotify) : '';

        $instance->relativeuser = $USER->id;
        $instance->approvaltype = isset($data->approvaltype) ? $data->approvaltype : '';
        $instance->approvalroles = !empty($data->approvalroles) ? json_encode($data->approvalroles) : '';
        $instance->campaigncourse = isset($data->campaigncourse) ? $data->campaigncourse : 0;
        $instance->courseenrolmentkey = isset($data->courseenrolmentkey) ? $data->courseenrolmentkey : '';
        $instance->coursestudentrole = isset($data->coursestudentrole) ? $data->coursestudentrole : 0;
        $instance->courseparentrole = isset($data->courseparentrole) ? $data->courseparentrole : 0;
        $instance->campaigngroups = isset($data->campaigngroups) ? $data->campaigngroups : '';
        $instance->campaigngrouping = isset($data->campaigngrouping) ? $data->campaigngrouping : '';
        $instance->groupmessaging = isset($data->groupmessaging) ? $data->groupmessaging : 0;
        $instance->groupenrolmentkey = isset($data->groupenrolmentkey) ? $data->groupenrolmentkey : 0;
        $instance->groupcapacity = isset($data->groupcapacity) ? $data->groupcapacity : '';

        $instance->restrictroles = (!empty($data->restrictroles)) ? json_encode($data->restrictroles) : '';
        $instance->restrictcohorts = (!empty($data->restrictcohorts)) ? json_encode($data->restrictcohorts) : '';
        $instance->restrictrolecontext = $data->restrictrolecontext;
        $instance->restrictcohortoperator = $data->restrictcohortoperator;

        $instance->auth = (!empty($data->auth)) ? $data->auth : '';
        $instance->globalrole = (!empty($data->globalrole)) ? $data->globalrole : "";
        $instance->cohorts = (!empty($data->cohorts)) ? json_encode($data->cohorts) : "";
        $instance->campaignowner = (!empty($data->campaignowner)) ? $data->campaignowner : "";
        $instance->privacypolicy = (!empty($data->privacypolicy)) ? $data->privacypolicy : '';

        $instance->consentstatement_editor = $data->consentstatement_editor;
        $instance->consentstatement = $data->consentstatement_editor['text'];
        $instance->consentstatementformat = $data->consentstatement_editor['format'];

        $instance->welcomemessage = (!empty($data->welcomemessage)) ? $data->welcomemessage : '';
        $instance->welcomemessagecontent_editor = $data->welcomemessagecontent_editor;
        $instance->welcomemessagecontent = $data->welcomemessagecontent_editor['text'];
        $instance->welcomemessagecontentformat = $data->welcomemessagecontent_editor['format'];

        $instance->welcomemessageowner = (!empty($data->welcomemessageowner)) ? $data->welcomemessageowner : '';
        $instance->followupmessage = (!empty($data->followupmessage)) ? $data->followupmessage : '';

        $instance->followupmessagecontent_editor = $data->followupmessagecontent_editor;
        $instance->followupmessagecontent = $data->followupmessagecontent_editor['text'];
        $instance->followupmessagecontentformat = $data->followupmessagecontent_editor['format'];

        $instance->followupmessagedelay = (!empty($data->followupmessagedelay)) ? $data->followupmessagedelay : '';
        $instance->followupmessageowner = (!empty($data->followupmessageowner)) ? $data->followupmessageowner : '';
        return $instance;
    }

    /**
     * Insert the new campaign settings in table auth_magic_campaigns.
     *
     * @param object $formdata get the data from form fields.
     * @return stdClass $insertrecord Returns the object of inserted campaign data.
     */
    public static function create_campaign($formdata) {
        global $DB;
        $insertinstance = new stdClass();
        $campaign = self::get_data_fields($insertinstance, $formdata);
        // Add code and token.
        self::create_code($campaign);
        self::create_coupon($campaign);
        $campaign->timecreated = time();
        $campaign->id = $DB->insert_record('auth_magic_campaigns', $campaign);
        self::update_campaign_payment($campaign->id, $formdata);
        self::postupdate_editor_files($campaign);
        self::postupdate_filemanager_files($campaign);
        return $campaign->id;
    }

    /**
     * Update the campaign settings in table auth_magic_campaigns.
     *
     * @param object $formdata get the data from form fields.
     * @return int $updaterecord Returns the object of updated campaign data.
     */
    public static function update_campaign($formdata) {
        global $DB;
        $campaignid = $formdata->id;
        $updateinstance = $DB->get_record('auth_magic_campaigns', ['id' => $campaignid]);
        $campaign = self::get_data_fields($updateinstance, $formdata);
        // Token and code is not added to campaign create this on update.
        if (empty($updateinstance->code) || empty($updateinstance->token)) {
            // Add code and token.
            self::create_code($campaign);
        }
        $campaign->timemodified = time();
        $DB->update_record('auth_magic_campaigns', $campaign);
        self::update_campaign_payment($campaignid, $formdata);
        self::postupdate_editor_files($campaign);
        self::postupdate_filemanager_files($campaign);
        return $campaign->id;

    }

    /**
     * Create the campaign code.
     * @param object $campaign
     */
    public static function create_code(&$campaign) {
        global $DB;
        // Something long and unique.
        $token = md5(time().random_string(40));
        while ($DB->record_exists('auth_magic_campaigns', ['token' => $token])) {
            // Must be unique.
            $token = md5(time().random_string(40));
        }
        // Something short and unique.
        $code = base64_encode(random_string(6));
        while ($DB->record_exists('auth_magic_campaigns', ['code' => $code])) {
            // Must be unique.
            $code = base64_encode(random_string(6));
        }
        // Add code to copy and send campaign links, using campaign id is not good.
        $campaign->code = $code;
        $campaign->token = $token;
    }


    /**
     * Create the campaign code.
     * @param object $campaign
     */
    public static function create_coupon(&$campaign) {
        global $DB;
        // Something long and unique.
        $coupon = base64_encode(random_string(6));
        while ($DB->record_exists('auth_magic_campaigns', ['coupon' => $coupon])) {
            // Must be unique.
            $coupon = base64_encode(random_string(6));
        }
        $campaign->coupon = $coupon;
    }

    /**
     * Get the form fieldname.
     * @param int $data
     * @param string $alias
     * @return string field.
     */
    public static function get_formfield_fieldname($data, $alias = '') {
        $fieldtype = 'standard_';
        if ($data->fieldtype == MAGICCAMPAIGNPROFILEFIELD) {
            $fieldtype = 'profile_field_';
        }
        $fieldname = $fieldtype . $data->field;
        if ($alias) {
            $fieldname = $fieldname . $alias;
        }
        return $fieldname;
    }

    /**
     * Get campaign formfields.
     * @param stdClass $record
     * @param int $id
     */
    public static function get_campaign_formfied_values(&$record, $id) {
        $fields = self::get_campaign_fields_instance($id)->get_fields();
        foreach ($fields as $field) {
            $fieldname = self::get_formfield_fieldname($field);
            if ($field->fieldoption == MAGICCAMPAIGNSHIDDENPROVIDEVALUE) {
                $record->{$fieldname} = $field->customvalue;
            } else if ($field->fieldoption == MAGICCAMPAIGNSREQUIREDHIDDENOTHERFIELD) {
                $record->{$fieldname . '_otherfield'} = $field->otherfieldvalue;
            }
            $record->{$fieldname . '_option'} = $field->fieldoption;
        }
    }

    /**
     * Fetches a campaigns record from the database by ID and return values.
     *
     * @param int $id The ID of the campaign to fetch.
     * @param bool $includeformfields
     */
    public static function get_campaign($id, $includeformfields = true) {
        global $DB;
        // Verfiy and Fetch campaign record from DB.
        $record = $DB->get_record('auth_magic_campaigns', ['id' => $id]);
        if (!empty($record)) {
            $record->transparentform = $record->transparentform;
            $record->displayowerprofile = $record->displayowerprofile;
            $record->formposition = $record->formposition;
            $record->globalrole = $record->globalrole ?: "";
            $record->cohorts = !empty($record->cohorts) ? json_decode($record->cohorts) : "";
            $record->restrictroles =  !empty($record->restrictroles) ? json_decode($record->restrictroles) : "";
            $record->restrictcohorts =  !empty($record->restrictcohorts) ? json_decode($record->restrictcohorts) : "";
            $record->password = base64_decode($record->password) ?: "";
            $record->campaignowner = $record->campaignowner ?: "";
            $record->approvalroles =  !empty($record->approvalroles) ? json_decode($record->approvalroles) : "";
            $record->expirybeforenotify =  !empty($record->expirybeforenotify) ? json_decode($record->expirybeforenotify) : "";
            // Set the campaign form fields elements.
            if ($includeformfields) {
                self::get_campaign_formfied_values($record, $id);
            }
            $campaignpayment = $DB->get_record('auth_magic_campaigns_payment', ['campaignid' => $id]);
            if ($campaignpayment) {
                $record->paymentinfo = $campaignpayment;
                $record->paymenttype = $campaignpayment->type;
                $record->paymentfee = $campaignpayment->fee;
                $record->paymentcurrency = $campaignpayment->currency;
                $record->paymentaccount = $campaignpayment->paymentaccount;
            }
            return $record;
        } else {
            // TODO: string for campaign not found.
            throw new moodle_exception('campaignsnotfound', 'auth_magic');
        }
        return false;
    }

    /**
     * Update the campaign payment.
     * @param int $campaignid
     * @param array $formdata
     */
    public static function update_campaign_payment($campaignid, $formdata) {
        global $DB;
        // Update the record.
        if ($existrecord = $DB->get_record('auth_magic_campaigns_payment', ['campaignid' => $campaignid])) {
            $existrecord->type = $formdata->paymenttype;
            if ($formdata->paymenttype != 'free') {
                $existrecord->fee = unformat_float($formdata->paymentfee);
                $existrecord->currency = $formdata->paymentcurrency;
                $existrecord->paymentaccount = $formdata->paymentaccount;
                $existrecord->timemodified = time();
            }
            $DB->update_record('auth_magic_campaigns_payment', $existrecord);
        } else {
            $record = new \stdClass;
            $record->campaignid = $campaignid;
            $record->type = $formdata->paymenttype;
            if ($formdata->paymenttype != 'free') {
                $record->fee = unformat_float($formdata->paymentfee);
                $record->currency = $formdata->paymentcurrency;
                $record->paymentaccount = $formdata->paymentaccount;
            }
            $record->timecreated = time();
            $DB->insert_record('auth_magic_campaigns_payment', $record);
        }
    }

    /**
     * Create an instance of the campaign class from the given campaign ID or campaign object/array.
     *
     * @param int|stdclass $campaign
     * @return campaign
     */
    public static function instance($campaign) {

        if (is_scalar($campaign)) {
            $campaign = self::get_campaign($campaign);
        }

        if (!is_array($campaign) && !is_object($campaign)) {
            throw new moodle_exception('campaignformatnotcorrect', 'auth_magic');
        }
        return new self($campaign->id);
    }
    /**
     * Updates the "visible" field of the current campaign.
     *
     * @param bool $visibility The new value for the "visible" field.
     * @return bool True if the update was successful, false otherwise.
     */
    public function update_visible(bool $visibility) {
        return $this->update_field('visibility', $visibility, ['id' => $this->campaignid]);
    }

    /**
     * Updates a field of the current campaign with the given key and value.
     *
     * @param string $key The key of the field to update.
     * @param mixed $value The new value of the field.
     * @return bool|int Returns true on success, or false on failure.
     */
    public function update_field($key, $value) {
        global $DB;

        $result = $DB->set_field('auth_magic_campaigns', $key, $value, ['id' => $this->campaignid]);

        return $result;
    }

    /**
     * Delete the current campaign and all its associated items from the database.
     *
     * @return bool True if the deletion is successful, false otherwise.
     */
    public function delete_campaign() {
        global $DB;
        if ($DB->delete_records('auth_magic_campaigns', ['id' => $this->campaignid])) {
            $DB->delete_records('auth_magic_campaigns_fields', ['campaignid' => $this->campaignid]);
            $DB->delete_records('auth_magic_campaigns_users', ['campaignid' => $this->campaignid]);
            $DB->delete_records('auth_magic_campaigns_payment', ['campaignid' => $this->campaignid]);
            $DB->delete_records('auth_magic_payment_logs', ['campaignid' => $this->campaignid]);
            return true;
        }
        return false;
    }

    /**
     * Assgin user in to the cohorts.
     *
     * @param int $cohortids Cohort id.
     * @param int $userid User id.
     * @return bool True if the cohorts assign successfull, false otherwise.
     */
    public function assign_cohorts($cohortids, $userid, $removed) {
        if ($cohortids) {
            foreach ($cohortids as $cohortid) {
                if ($removed) {
                    cohort_remove_member($cohortid, $userid);
                } else {
                    cohort_add_member($cohortid, $userid);
                }
            }
        }
    }

    /**
     * Assgin global role context.
     *
     * @param int $roleid Role id.
     * @param int $userid User id.
     * @return bool True if the role assign successfull, false otherwise.
     */
    public function assign_globalrole($roleid, $userid, $removed) {
        if ($roleid) {
            if ($removed) {
                role_unassign($roleid, $userid, context_system::instance()->id);
            } else {
                role_assign($roleid, $userid, context_system::instance()->id);
            }
            return false;
        }
    }


    /**
     * Assgin user in to the parent.
     *
     * @param int $roleid .
     * @param int $parentid parent id.
     * @param int $userid User id.
     * @return bool True if the parent assign successfull, false otherwise.
     */
    public function assign_parentuser($roleid, $parentid, $userid, $removed) {
        // Need to update roleid.
        if ($parentid && $roleid) {
            $usercontext = context_user::instance($userid);
            if ($removed) {
                role_unassign($roleid, $parentid, $usercontext->id);
            } else {
                role_assign($roleid, $parentid, $usercontext->id);
            }
            return false;
        }
    }

    /**
     * Send message to user using message api.
     *
     * @param  mixed $userto
     * @param  mixed $subject
     * @param  mixed $messageplain
     * @param  mixed $messagehtml
     * @param  mixed $sender
     * @return bool message status
     */
    public static function campaign_messagetouser($userto, $subject, $messageplain, $messagehtml, $sender = null) {

        $eventdata = new \core\message\message();
        $eventdata->name = 'instantmessage';
        $eventdata->component = 'moodle';
        $eventdata->courseid = SITEID;
        $eventdata->userfrom = $sender ?: core_user::get_support_user();
        $eventdata->userto = $userto;
        $eventdata->subject = $subject;
        $eventdata->fullmessage = $messageplain;
        $eventdata->fullmessageformat = FORMAT_HTML;
        $eventdata->fullmessagehtml = $messagehtml;
        $eventdata->smallmessage = $subject;

        if (message_send($eventdata)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * campaign form editor element options.
     *
     * @return array
     */
    public static function get_editor_options() {
        global $CFG;
        return [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $CFG->maxbytes,
            'trust' => false,
            'context' => context_system::instance(),
            'noclean' => true,
        ];
    }

    /**
     * campaign form editor element options.
     *
     * @return array
     */
    public static function get_filemanager_options() {
        global $CFG;
        return [
            'maxfiles' => 1,
            'maxbytes' => $CFG->maxbytes,
            'context' => context_system::instance(),
            'noclean' => true,
        ];
    }

    /**
     * Postupdate the editor files.
     * @param object $campaign
     */
    public static function postupdate_editor_files($campaign) {
        global $DB;
        $itemid = isset($campaign->id) ? $campaign->id : null;
        $editors = ['description', 'comments', 'consentstatement', 'welcomemessagecontent', 'followupmessagecontent',
            'submissioncontent'];
        $upd = new stdClass();
        $upd->id = $campaign->id;
        foreach ($editors as $editor) {
            $editorformat = $editor . "format";
            $campaign = file_postupdate_standard_editor($campaign, $editor, self::get_editor_options(),
                context_system::instance(), 'auth_magic', $editor, $itemid);
            $upd->{$editor}       = $campaign->{$editor};
            $upd->{$editorformat} = $campaign->{$editorformat};
        }
        $DB->update_record('auth_magic_campaigns', $upd);
    }

    /**
     * Postupdate the filemanager files.
     * @param object $campaign
     */
    public static function postupdate_filemanager_files($campaign) {
        global $DB;
        $itemid = isset($campaign->id) ? $campaign->id : null;
        $filemanagers = ['logo', 'headerimage', 'backgroundimage'];
        $upd = new stdClass();
        $upd->id = $campaign->id;
        foreach ($filemanagers as $filemanager) {
            $campaign = file_postupdate_standard_filemanager($campaign, $filemanager, self::get_filemanager_options(),
                context_system::instance(), 'auth_magic', $filemanager, $itemid);
            $upd->{$filemanager} = $campaign->{$filemanager};
        }
        $DB->update_record('auth_magic_campaigns', $upd);
    }

    /**
     * Loads the prepare editor files.
     * @param object $campaign
     */
    public static function prepare_editor_files($campaign) {
        $itemid = isset($campaign->id) ? $campaign->id : null;
        $editors = ['description', 'comments', 'consentstatement', 'welcomemessagecontent',
            'followupmessagecontent', 'submissioncontent'];
        foreach ($editors as $editor) {
            $campaign = file_prepare_standard_editor($campaign, $editor, self::get_editor_options(),
                context_system::instance(), 'auth_magic', $editor, $itemid);
        }
        return $campaign;
    }

    /**
     * Loads the prepare editor files.
     * @param object $campaign
     */
    public static function prepare_filemanger_files($campaign) {
        $itemid = isset($campaign->id) ? $campaign->id : null;
        $filemanagers = ['logo', 'headerimage', 'backgroundimage'];
        foreach ($filemanagers as $filemanager) {
            $campaign = file_prepare_standard_filemanager($campaign, $filemanager, self::get_filemanager_options(),
                context_system::instance(), 'auth_magic', $filemanager, $itemid);
        }
        return $campaign;
    }

    /**
     * Signup user default data.
     * @param object $user
     * @return object user.
     */
    public static function signup_setup_new_user($user) {
        global $CFG;
        $user->firstaccess = 0;
        $user->timecreated = time();
        $user->mnethostid  = $CFG->mnet_localhost_id;
        $user->secret      = random_string(15);
        if (isset($user->campaignid)) {
            $user->auth = self::get_campaign($user->campaignid)->auth;
            $campaign = campaign::instance($user->campaignid);
            if (self::get_campaign($user->campaignid)->emailconfirm == campaign::ENABLE) {
                $user->confirmed = 0;
                // Send_confirmation_email.
            } else {
                $user->confirmed = 1;
            }
        }
        // Initialize alternate name fields to empty strings.
        $namefields = array_diff(\core_user\fields::get_name_fields(), useredit_get_required_name_fields());
        foreach ($namefields as $namefield) {
            $user->$namefield = '';
        }
        return $user;
    }

    /**
     * Custom change type parameter.
     *
     */
    public static function get_custom_field_types() {
        return [
            'username' => PARAM_RAW,
            'country' => PARAM_TEXT,
        ];
    }

    /**
     * Get profile field object.
     * @param string $type
     * @param int $fieldid
     * @param int $userid
     * @param stdClass $fielddata
     * @return object
     */
    public static function profile_get_user_field(string $type, int $fieldid = 0,
        int $userid = 0, stdClass $fielddata = null): profile_field_base {
        global $CFG;
        require_once("{$CFG->dirroot}/user/profile/field/{$type}/field.class.php");

        // Return instance of profile field type.
        $profilefieldtype = "profile_field_{$type}";
        return new $profilefieldtype($fieldid, $userid, $fielddata);
    }

    /**
     * public static function profile_field using otherfields.
     * @param string $datatype
     */
    public static function get_profile_otherfields($datatype) {
        global $DB;
        $records = $DB->get_records_menu('user_info_field', ['datatype' => $datatype],
            '', 'shortname, name');
        $otherfields = [];
        array_walk($records, function(&$value, $key) use (&$otherfields) {
            $otherfields["profile_field_" . $key] = get_string('strprofilefield:', 'auth_magic', $value);
        }
        );
        return $otherfields;
    }
}
