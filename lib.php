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
 * Authentication Plugin: Magic Authentication lib functions.
 *
 *
 * @package     auth_magic
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_user\output\myprofile\tree;
use auth_magic\campaign;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

define("MAGICCAMPAIGNSREQUIRED", 10);
define("MAGICCAMPAIGNSOPTIONAL", 20);
define("MAGICCAMPAIGNSHIDDENPROVIDEVALUE", 30);
define("MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT", 40);
define("MAGICCAMPAIGNSREQUIREDHIDDENOTHERFIELD", 50);
define("MAGICCAMPAIGNSTANDARDFIELD", 1);
define("MAGICCAMPAIGNPROFILEFIELD", 2);
define("REQUIREDONCE", 60);
define("REQUIREDTWICE", 70);
define("HIDDEN", 80);


/**
 * Set a magic login link expiry form.
 */
class linkexpirytime_form extends moodleform {

    /**
     * Add elements to form.
     *
     */
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $manualexpiry = !empty($this->_customdata['manualexpiry']) ? $this->_customdata['manualexpiry'] : 0;
        $mform->addElement('duration', 'linkexpirytime', get_string('linkexpirytime', 'auth_magic'));
        $mform->setDefault('linkexpirytime', $manualexpiry);
    }
}



/**
 * Get user login link
 * @param int $userid
 * @return string url.
 */
function auth_magic_get_user_login_link($userid) {
    global $DB;
    return $DB->get_field('auth_magic_loginlinks', 'magiclogin', ['userid' => $userid], 'loginurl');
}



/**
 * Send message to user using message api.
 *
 * @param  mixed $userto
 * @param  mixed $subject
 * @param  mixed $messageplain
 * @param  mixed $messagehtml
 * @param  mixed $courseid
 * @return bool message status
 */
function auth_magic_messagetouser($userto, $subject, $messageplain, $messagehtml, $courseid = null) {
    $eventdata = new \core\message\message();
    $eventdata->name = 'instantmessage';
    $eventdata->component = 'moodle';
    $eventdata->courseid = empty($courseid) ? SITEID : $courseid;
    $eventdata->userfrom = core_user::get_support_user();
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
 * Sent the invitation link to the user.
 *
 * @param int $userid
 *
 * @return bool message status
 */
function auth_magic_sent_invitation_user($userid) {
    $site = get_site();
    $user = \core_user::get_user($userid);
    $invitationlink = auth_magic_get_user_invitation_link($userid);
    $auth = get_auth_plugin('magic');

    if (empty($invitationlink)) {
        $auth->create_magic_instance($user, false);
        $invitationlink = auth_magic_get_user_invitation_link($user->id);
    }
    $subject = get_string('magicloginlink', 'auth_magic', format_string($site->fullname));
    // Lang data.
    $data = new stdClass();
    $data->sitename = format_string($site->fullname);
    $data->admin = generate_email_signoff();
    $data->fullname = fullname($user);
    $data->link = $invitationlink;
    $data->expiry = auth_magic_get_user_magic_link_expires($userid, 'invitationexpiry');
    $messageplain = get_string('invitationmessage', 'auth_magic', $data); // Plain text.
    $messagehtml = text_to_html($messageplain, false, false, true);
    $user->mailformat = 1;  // Always send HTML version as well.
    return auth_magic_messagetouser($user, $subject, $messageplain, $messagehtml);
}

/**
 * Sent the login link to the user.
 * @param int $userid
 * @param bool $otherauth
 * @param bool $expired
 * @return bool message status
 */
function auth_magic_sent_loginlink_touser($userid, $otherauth = false, $expired = false) {
    global $DB;
    $site = get_site();
    $user = \core_user::get_user($userid);
    $auth = get_auth_plugin('magic');
    if ($otherauth) {
        $auth->create_magic_instance($user, false);
    }
    $loginlink = auth_magic_get_user_login_link($userid);
    $subject = get_string('loginsubject', 'auth_magic', format_string($site->fullname));
    $data = new stdClass();
    $data->sitename = format_string($site->fullname);
    $data->admin = generate_email_signoff();
    $data->fullname = fullname($user);
    if (empty($loginlink)) {
        $auth->create_magic_instance($user, false);
        $loginlink = auth_magic_get_user_login_link($user->id);
    }
    $data->link = $loginlink;
    $data->expiry = auth_magic_get_user_magic_link_expires($userid, 'loginexpiry');
    if ($expired) {
        $messageplain = get_string('expiredloginlinkmsg', 'auth_magic', $data);
    } else {
        // Check link is expiry and more type.
        $instance = $DB->get_record('auth_magic_loginlinks', ['userid' => $user->id]);
        if ($instance->loginexpiry < time()) {
            $auth->update_new_loginkey($user, $instance);
            auth_magic_sent_loginlink_touser($user->id, $otherauth);
            return;
        }
        $messageplain = get_string('loginlinkmessage', 'auth_magic', $data);
    }
    $messagehtml = text_to_html($messageplain, false, false, true);
    $user->mailformat = 1;  // Always send HTML version as well.
    auth_magic_messagetouser($user, $subject, $messageplain, $messagehtml);
    return true;

}

/**
 * Sent the information for non magic auth users.
 * @param int $userid
 * @return void
 */
function auth_magic_requiredmail_magic_authentication($userid) {
    $site = get_site();
    $user = \core_user::get_user($userid);
    $forgothtml = html_writer::link(new moodle_url('/login/forgot_password.php'), get_string('forgotten'));
    $subject = get_string('loginsubject', 'auth_magic', format_string($site->fullname));
    $data = new stdClass();
    $data->sitename = format_string($site->fullname);
    $data->admin = generate_email_signoff();
    $data->fullname = fullname($user);
    $data->forgothtml = $forgothtml;
    $messageplain = get_string('preventmagicauthmessage', 'auth_magic', $data);
    $messagehtml = text_to_html($messageplain, false, false, true);
    $user->mailformat = 1;
    return auth_magic_messagetouser($user, $subject, $messageplain, $messagehtml);
}

/**
 * Get magic email user.
 * @param string $email
 * @return stdclass user
 */
function auth_magic_get_email_user($email) {
    global $DB;
    $user = $DB->get_record('user', ['email' => $email]);
    if (!$user && get_config('auth_magic', 'loginoption')) {
        $user = $DB->get_record('user', ['username' => $email]);
    }
    return !empty($user) ? $user : null;
}



/**
 * Get available manual enrolment courses.
 *
 * @return array courses.
 */
function auth_magic_get_courses_for_registration() {
    global $PAGE, $DB;
    $courses = [];
    $coursesinfo = $DB->get_records('course', null, 'id DESC');
    if (!empty($coursesinfo)) {
        foreach ($coursesinfo as $info) {
            $instances = enrol_get_instances($info->id, true);
            // Make sure manual enrolments instance exists.
            foreach ($instances as $instance) {
                if ($instance->enrol == 'manual') {
                    $courselist = new core_course_list_element($info);
                    $courses[$info->id] = $courselist->get_formatted_fullname();
                }
            }
        }
    }
    return $courses;
}

/**
 * Get available manual enrolment given course.
 *
 * @param int $courseid
 *
 * @return bool status.
 */
function auth_magic_is_course_manual_enrollment($courseid) {
    $instances = enrol_get_instances($courseid, true);
    // Make sure manual enrolments instance exists.
    foreach ($instances as $instance) {
        if ($instance->enrol == 'manual') {
            return true;
        }
    }
    return false;
}

/**
 * Display user courses.
 *
 * @param int $userid
 *
 * @return string
 */
function auth_magic_user_courses($userid) {
    $courses = enrol_get_all_users_courses($userid, true, ['fullname'], 'fullname');
    $fullnames = array_column($courses, 'fullname');
    $content = '';
    if (count($fullnames) > 3) {
        $show = array_slice($fullnames, 0, 3);
        $more = array_slice($fullnames, 3);
        $content .= implode(',', $show);
        $content .= "<details><summary>" . get_string('more', 'auth_magic', count($more)) . "</summary>";
        $content .= implode(',', $more). "</details>";
    } else {
        $content .= implode(',', $fullnames);
    }
    return $content;

}

/**
 * Enroll user into the course.
 *
 * @param int $courseid courseid
 * @param object $user user
 * @param int $enrolmentduration duration
 *
 * @return bool enrol or not
 */
function auth_magic_enroll_course_user($courseid, $user, $enrolmentduration = 0) {
    global $DB;
    $context = context_course::instance($courseid);
    $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
    $enrolmanual = enrol_get_plugin('manual');
    if ($enrolmanual &&  !empty($instance)) {
        if ($enrolmanual->allow_enrol($instance)) {
            $timeend = 0;
            if ($enrolmentduration) {
                $timeend = time() + $enrolmentduration;
            }
            $roleid = get_config('auth_magic', 'enrolmentrole');
            $enrolmanual->enrol_user($instance, $user->id, $roleid, time(), $timeend);
            return true;
        }
    }
    return false;
}

/**
 * Display user created confim box
 *
 * @param array $args
 *
 * @return string
 */
function auth_magic_output_fragment_display_box_content($args) {
    global $DB, $OUTPUT;
    $templatecontext = [];
    $user = $DB->get_record('user', ['id' => $args['user']]);
    $profileurl = new moodle_url('/user/profile.php', ['id' => $user->id]);
    if ($args['course']) {
        $course = get_course($args['course']);
        $courseelement = new core_course_list_element($course);
        $templatecontext['coursename'] = $courseelement->get_formatted_fullname();
        $profileurl = new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $course->id]);
    }
    $userkeyinfo = auth_magic_get_user_userkeyinfo($user->id);
    $templatecontext['username'] = fullname($user);
    $templatecontext['magicinvitation'] = $userkeyinfo->magicinvitation;
    $templatecontext['profileurl'] = $profileurl->out(false);
    $status = '';
    if (!$args['userexist'] && $args['course']) {
        $status = get_string('createuserenrolcourse', 'auth_magic', $templatecontext['coursename']);
    } else if ($args['userexist'] && $args['course']) {
        $status = get_string('existuserenrolcourse', 'auth_magic', $templatecontext['coursename']);
    } else if (!$args['userexist'] && !$args['course']) {
        $status = get_string('statuscreateuser', 'auth_magic');
    }
    $templatecontext['status'] = $status;
    return $OUTPUT->render_from_template('auth_magic/modalbox', $templatecontext);
}

/**
 * Get the logininfo for given user.
 *
 * @param int $userid
 *
 * @return object
 */
function auth_magic_get_user_userkeyinfo($userid) {
    global $DB;
    return $DB->get_record('auth_magic_loginlinks', ['userid' => $userid]);
}

/**
 * Get user invitation link
 *
 * @param int $userid
 *
 * @return string url.
 */
function auth_magic_get_user_invitation_link($userid) {
    global $DB;
    return $DB->get_field('auth_magic_loginlinks', 'magicinvitation', ['userid' => $userid], 'loginurl');
}

/**
 * Get the child users for the parent.
 *
 * @param int $parentid
 * @param bool $checkparent
 * @param bool $includemagic
 * @return array child users.
 */
function auth_magic_get_parent_child_users($parentid, $checkparent = false, $includemagic = true) {
    global $DB;
    $isparent = false;
    $users = [];
    $sql1 = '';
    $sql2 = '';
    if ($includemagic) {
        $sql1 = ', {auth_magic_loginlinks} mc';
        $sql2 = ' AND u.id = mc.userid';
    }
    if ($usercontexts = $DB->get_records_sql("SELECT c.instanceid
                                                    FROM {role_assignments} ra, {context} c, {user} u $sql1
                                                   WHERE ra.userid = ?
                                                         AND ra.contextid = c.id
                                                         $sql2
                                                         AND c.instanceid = u.id
                                                         AND c.contextlevel = ".CONTEXT_USER, [$parentid])) {
        $users = array_keys($usercontexts);
        $isparent = true;
    }
    if ($checkparent) {
        return $isparent;
    }
    return $users;
}

/**
 * Parent can see the child user keys
 *
 * @return bool status
 */
function auth_magic_is_parent_see_child_magiclinks() {
    global $USER;
    $status = false;
    $users = auth_magic_get_parent_child_users($USER->id);
    if (!empty($users)) {
        $user = current($users);
        $usercontext = context_user::instance($user);
        if (has_capability('auth/magic:viewchildloginlinks', $usercontext)) {
            $status = true;
        }
    }
    return $status;
}


/**
 * Parent can see the child campaignlist
 *
 * @return bool status
 */
function auth_magic_is_campaignowner_see_campaignlist() {
    global $USER;
    $status = false;
    $users = auth_magic_get_parent_child_users($USER->id, false, false);
    if (!empty($users)) {
        $user = current($users);
        $usercontext = context_user::instance($user);
        if (has_capability('auth/magic:viewcampaignownerlists', $usercontext)) {
            $status = true;
        }
    }
    return $status;
}


/**
 * Get user login link expires.
 * @param int $userid
 * @param string $type
 * @return string time.
 */
function auth_magic_get_user_magic_link_expires($userid, $type) {
    global $DB;
    $expiry = $DB->get_field('auth_magic_loginlinks', $type,  ['userid' => $userid]);
    return $expiry ? auth_magic_expirytime_convert_datestring($expiry) : '';
}

/**
 * Convert expirytime to date.
 * @param int $expiry
 * @return string value.
 */
function auth_magic_expirytime_convert_datestring($expiry) {
    if ($expiry && $expiry > time()) {
        $t = $expiry - time();
        $hours = floor($t / 3600);
        $minutes = floor(($t % 3600) / 60);
        $seconds = $t % 60;
        $strhours = get_string('hours');
        $strmins = get_string('minutes');
        $strseconds = get_string('seconds');
        return sprintf("%02d $strhours %02d $strmins %02d $strseconds", $hours, $minutes, $seconds);
    }
    return get_string('currentlylinkexpiry', 'auth_magic');
}



/**
 * Defines learningtools nodes for my profile navigation tree.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser is the user viewing profile, current user ?
 * @param stdClass $course course object
 *
 * @return bool
 */
function auth_magic_myprofile_navigation(tree $tree, $user, $iscurrentuser, $course) {
    global $USER;
    if (!is_enabled_auth('magic')) {
        return;
    }
    // Get the learningtools category.
    if (!array_key_exists('magicauth', $tree->__get('categories'))) {
        // Create the category.
        $categoryname = get_string('configtitle', 'auth_magic');
        $category = new core_user\output\myprofile\category('magicauth', $categoryname, 'privacyandpolicies');
        $tree->add_category($category);
    } else {
        // Get the existing category.
        $category = $tree->__get('categories')['magicauth'];
    }
    $systemcontext = context_system::instance();
    if ($iscurrentuser) {
        // Quick registration.
        if (!empty($course)) {
            $coursecontext = context_course::instance($course->id);
            if (has_capability('auth/magic:cancoursequickregistration', $coursecontext) &&
                auth_magic_is_course_manual_enrollment($course->id)) {
                $registrationurl = new moodle_url('/auth/magic/registration.php', ['courseid' => $course->id]);
                $registersnode = new core_user\output\myprofile\node('magicauth', 'quickregistration',
                    get_string('quickregistration', 'auth_magic'), null, $registrationurl);
                $tree->add_node($registersnode);
            }
        } else {
            $usercontext = context_user::instance($USER->id);
            if (has_capability('auth/magic:viewloginlinks', $systemcontext)) {
                $magickeysurl = new moodle_url('/auth/magic/listusers.php');
                $magickeysnode = new core_user\output\myprofile\node('magicauth', 'magickeys',
                    get_string('userkeyslist', 'auth_magic'), null, $magickeysurl);
                $tree->add_node($magickeysnode);
            } else if (auth_magic_is_parent_see_child_magiclinks()) {
                $magickeysurl = new moodle_url('/auth/magic/listusers.php', ['userid' => $USER->id]);
                $magickeysnode = new core_user\output\myprofile\node('magicauth', 'magickeys',
                    get_string('userkeyslist', 'auth_magic'), null, $magickeysurl);
                $tree->add_node($magickeysnode);
            }

            if (auth_magic_is_campaignowner_see_campaignlist()) {
                $magickeysurl = new moodle_url('/auth/magic/campaigns/manage.php');
                $magickeysnode = new core_user\output\myprofile\node('magicauth', 'campaignlist',
                    get_string('managecampaign', 'auth_magic'), null, $magickeysurl);
                $tree->add_node($magickeysnode);
            }

            if (has_capability('auth/magic:cansitequickregistration', $systemcontext)) {
                $registrationurl = new moodle_url('/auth/magic/registration.php');
                $registersnode = new core_user\output\myprofile\node('magicauth', 'quickregistration',
                    get_string('quickregistration', 'auth_magic'), null, $registrationurl);
                $tree->add_node($registersnode);
            }
        }
    }
}

/**
 * Display link expiry time form.
 *
 * @param array $args
 *
 * @return string $expriytimeform display magic login link expiry form.
 */
function auth_magic_output_fragment_link_expiration_form($args) {
    global $DB;
    $expirytimeform = html_writer::start_tag('div', ['id' => 'linkexpirytime']);
    $params['manualexpiry'] = $DB->get_field('auth_magic_loginlinks', 'manualexpiry', ['userid' => $args['userid']]);
    $mform = new linkexpirytime_form(null, $params);
    $expirytimeform .= $mform->render();
    $expirytimeform .= html_writer::end_tag('div');
    return $expirytimeform;
}

/**
 * Assign to parent role to user.
 * @param int $userid
 * @param int $contextid
 */
function auth_magic_parent_role_assign($userid, $contextid) {
    // If check the parent role assign or not.
    if ($roleid = get_config('auth_magic', 'owneraccountrole')) {
        role_assign($roleid, $userid, $contextid);
    }
}


/**
 * Serve the files from the auth magic file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function auth_magic_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=[]) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }
    // Get extended plugins fileareas.
    $availablefiles = [
        'description', 'comments',
        'consentstatement', 'welcomemessagecontent',
        'followupmessagecontent', 'submissioncontent', 'logo',
        'headerimage', 'backgroundimage',
    ];
    // Make sure the filearea is one of those used by the plugin.
    if (!in_array($filearea, $availablefiles)) {
        return false;
    }
    // Item id is 0.
    $itemid = array_shift($args);

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // ...$args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // ...$args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'auth_magic', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}

/**
 * Signup user request controller.
 * @param string $email
 */
function auth_magic_user_signup_request($email) {
    $signup = new \auth_magic\signup_controller($email);
    if (!PHPUNIT_TEST) {
        $signup->sendemail();
        redirect(get_login_url(), get_string('sentregisterlinktouser', 'auth_magic'),
        null, \core\output\notification::NOTIFY_SUCCESS);
    }
}


/**
 * Signup use into site.
 * @param string $key
 */
function auth_magic_signup_user($key) {
    return \auth_magic\signup_controller::user_signup($key);
}

/**
 * Import the auth details.
 * @param mform $loginform
 * @param string $email
 * @param string $errormessage
 * @param bool $passwordupdate
 */
function auth_magic_import_auth_details($loginform, $email, $errormessage, $passwordupdate) {
    global $DB, $PAGE, $OUTPUT, $SITE, $SESSION, $CFG;
    $template = [];
    $template['loginformhtml'] = $loginform->render();
    $classname = 'core\\output\\language_menu';
    if (class_exists($classname)) {
        $languagedata = new \core\output\language_menu($PAGE);
        $languagemenu = $languagedata->export_for_action_menu($OUTPUT);
        $template['languagemenu'] = $languagemenu;
    }
    $template['logo_url'] = $OUTPUT->get_compact_logo_url();
    $template['sitename'] = $SITE->fullname;
    // Added the password when update the email.
    if ($email) {
        $user = auth_magic_get_email_user($email);
        $wantsurl = (isset($SESSION->wantsurl)) ? $SESSION->wantsurl : '/';
        if ($user) {
            $auth = get_auth_plugin($user->auth);
            if ($user->auth == 'oauth2') {
                $identityproviders = [];
                $issuers = $DB->get_records('auth_oauth2_linked_login', ['userid' => $user->id]);
                foreach ($issuers as $issuer) {
                    $idp = \core\oauth2\api::get_issuer($issuer->issuerid);
                    $params = ['id' => $idp->get('id'), 'sesskey' => sesskey(), 'wantsurl' => $wantsurl];
                    $url = new moodle_url('/auth/oauth2/login.php', $params);
                    $icon = $idp->get('image');
                    $identityproviders[] = ['url' => $url->out(false), 'iconurl' => $icon, 'name' => $idp->get_display_name()];
                }
                $template['identityproviders'] = $identityproviders;
            } else if ($user->auth == 'magic' && (!auth_magic_is_user_privilege($user) || empty($user->password))) {
                // Show the idp list when user not privileged user.
                $template['identityproviders'] = $auth->loginpage_idp_list($wantsurl);
            } else if ($user->auth == 'magic' && auth_magic_is_user_privilege($user)) {
                // Check magic auth previlege user or not.
                $template['identityproviders'] = $auth->loginpage_idp_list($wantsurl);
                $template['privilege'] = true;
                $template['passwordupdate'] = $passwordupdate;
                if (!$passwordupdate) {
                    $errormsg = get_string("invalidlogin");
                }
            } else if ($user->auth != 'magic') {
                // Other auth idp list.
                $template['identityproviders'] = $auth->loginpage_idp_list($wantsurl);
            }
            $template['ipmagic'] = ($user->auth == 'magic') ? true : false;
        } else {
            // Using the auth magic registration.
            if (get_config('auth_magic', 'autocreateusers') && validate_email($email)) {
                $auth = get_auth_plugin('magic');
                $template['identityproviders'] = $auth->loginpage_idp_list(null);
                $template['ipmagic'] = true;
            }
        }
    }
    $template['error'] = $errormessage;
    $template['customclass'] = ($CFG->version < 2022030300) ? true : false;
    return $OUTPUT->render_from_template('auth_magic/login', $template);
}


/**
 * Generate footer links.
 * @param string $menuname Footer block link name.
 * @return string The Footer links are return.
 */
function auth_magic_generate_footer_links($menuname = '') {
    global $CFG, $PAGE;
    $htmlstr = '';
    $htmlstr .= html_writer::start_tag('div', ['class' => 'magic-login-footer']);
    $htmlstr .= html_writer::start_tag('ul');
    $menustr = get_config('auth_magic', $menuname);
    $menusettings = explode("\n", $menustr);
    foreach ($menusettings as $menukey => $menuval) {
        $expset = explode("|", $menuval);
        if (!empty($expset) && isset($expset[0]) && isset($expset[1])) {
            list($ltxt, $lurl) = $expset;
            $ltxt = trim($ltxt);
            $lurl = trim($lurl);
            if (empty($ltxt)) {
                continue;
            }
            if (empty($lurl)) {
                $lurl = 'javascript:void(0);';
            }
            $pos = strpos($lurl, 'http');
            if ($pos === false) {
                $lurl = new moodle_url($lurl);
            }
            $htmlstr .= html_writer::start_tag('li');
            $htmlstr .= html_writer::link($lurl, $ltxt);
            $htmlstr .= html_writer::end_tag('li') . "\n";
        }
    }
    $htmlstr .= html_writer::end_tag('ul');
    $htmlstr .= html_writer::end_tag('div');
    return $htmlstr;
}

/**
 * Check the user is privileged or not.
 * @param object $user
 * @return bool
 */
function auth_magic_is_user_privilege($user) {
    global $DB;
    if (!get_config('auth_magic', 'supportpassword')) {
        return false;
    }
    $privilegeroles = get_config('auth_magic', 'privilegedrole');
    $privilegeroles = explode(",", $privilegeroles);
    foreach ($privilegeroles as $privilegerole) {
        if ($DB->record_exists('role_assignments', ['roleid' => $privilegerole, 'userid' => $user->id])) {
            return true;
        }
    }
    return false;
}


/**
 * Get the enable auth plugins.
 * @return array choices.
 */
function auth_magic_get_enabled_auth() {
    $choices = [];
    $authsenabled = get_enabled_auth_plugins();
    foreach ($authsenabled as $auth) {
        $authplugin = get_auth_plugin($auth);
        // Get the auth title (from core or own auth lang files).
        $authtitle = $authplugin->get_title();
        $choices[$auth] = $authtitle;
    }
    return $choices;
}


/**
 * Get the available payment.
 */
function auth_magic_get_payment_accounts() {
    $choices = [];
    $accounts = \core_payment\helper::get_payment_accounts_to_manage(\context_system::instance());
    foreach ($accounts as $account) {
        if ($account->is_available()) {
            $choices[$account->get('id')] = $account->get_formatted_name();
        }
    }
    return $choices;
}


/**
 * Navigation hook to add to preferences page.
 *
 * @param navigation_node $useraccount
 * @param stdClass $user
 * @param context_user $context
 * @param stdClass $course
 * @param context_course $coursecontext
 */
function auth_magic_extend_navigation_user_settings(navigation_node $useraccount,
                                                     stdClass $user,
                                                     context_user $context,
                                                     stdClass $course,
                                                     context_course $coursecontext) {
    global $USER, $CFG, $DB, $PAGE;

    $pagetypes = [
        'campaigns-payment-page',
        'login-change_password',
        'admin-tool-policy-view',
        'admin-tool-policy-index',
        'payment-gateway-bank-pay',
        'user-edit',
    ];

    if (is_enabled_auth('magic') && !\core\session\manager::is_loggedinas() && $user->id == $USER->id
    && !in_array($PAGE->pagetype, $pagetypes) && $PAGE->pagetype != 'login-change_password') {

        require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_helper.php');
        if ($campaignuser = $DB->get_record('auth_magic_campaigns_users', ['userid' => $user->id])) {
            $campaigndata  = \campaign_helper::get_campaign($campaignuser->campaignid);
            if (auth_magic_is_campaign_signup_user($user->id, $campaignuser->campaignid)) {
                $campaigninfo = new campaign($campaignuser->campaignid);
                $campaign = $campaigninfo->get_campaign();
                if (auth_magic_is_paid_campaign($campaign) && !$campaigninfo->is_coupon_user()) {
                    $returnurl = new moodle_url('/auth/magic/campaigns/payment.php',
                        ['campaignid' => $campaignuser->campaignid, 'userid' => $user->id, 'sesskey' => sesskey()]);
                    if ($paymentstatus = $DB->get_record('auth_magic_payment_logs', ['userid' => $user->id,
                        'campaignid' => $campaignuser->campaignid])) {
                        if ($paymentstatus->status != 'completed') {
                            // Implemented the make payment Workflow.
                            return redirect($returnurl);
                        }
                    } else {
                        return redirect($returnurl);
                    }
                }
            }
        }
        return true;
    }
}


/**
 * Check campaign signup user.
 * @param int $userid
 * @param int $campaignid
 */
function auth_magic_is_campaign_signup_user($userid, $campaignid) {
    global $DB;
    return $DB->record_exists('auth_magic_campaigns_users', ['userid' => $userid, 'campaignid' => $campaignid]);
}

/**
 * Get user names using select box.
 * @param array $users
 * @param int $noneid
 */
function auth_magic_get_usernames_choices($users, $noneid = campaign::NONE) {
    $list = [$noneid => get_string('none')];
    foreach ($users as $user) {
        $fullname = fullname($user);
        if (empty(trim($fullname))) {
            $list[$user->id] = $user->email;
        } else {
            $list[$user->id] = $fullname;
        }
    }
    return $list;
}

/**
 * Change the user formfield based on other users.
 * @param array $args
 */
function auth_magic_output_fragment_get_user_formfield($args) {
    global $DB, $PAGE;
    $PAGE->requires->js_call_amd('auth_magic/magic', 'init', [['campaignformfield' => true,
        'contextid' => $PAGE->context->id]]);
    $currentcampaignid = $args['currentcampaignid'];
    $relateduser = $args['relateduser'];

    $campaignform = new \auth_magic\form\campaigns_form(new moodle_url("/auth/magic/campaigns/edit.php"),
        ['id' => $currentcampaignid]);
    if ($relateduser) {
        $campaignuser = $DB->get_record('auth_magic_campaigns_users', ['userid' => $relateduser]);
    }

    if ($campaignuser) {
        $relatedcampaign = $DB->get_record('auth_magic_campaigns', ['id' => $campaignuser->campaignid]);
    } else {
        $relatedcampaign = $DB->get_record('auth_magic_campaigns', ['id' => $currentcampaignid]);
    }

    $record = new stdClass();
    $record->auth = $relatedcampaign->auth;
    campaign_helper::get_campaign_formfied_values($record, $relatedcampaign->id);
    $campaignform->set_data($record);
    return $campaignform->render();
}

/**
 * Check the campaign paid or not.
 * @param int $campaign
 * @param string $coupon
 * @return bool
 */
function auth_magic_is_paid_campaign($campaign, $coupon = '') {
    if (isset($campaign->paymentinfo->fee) && $campaign->paymentinfo->type != 'free') {
        if (!empty($coupon) && $campaign->is_valid_coupon_campaign()) {
            return false;
        }
        return true;
    }
    return false;
}


/**
 * Get the roles with disabled options in select box.
 * @param [array] $includecontexts
 * @param [bool] $disableoption
 * @return array
 */
function auth_magic_get_roles_options($includecontexts, $disableoption = false) {
    global $DB;
    $roles = [];
    $list = [];
    foreach ($includecontexts as $context) {
        $roles += get_roles_for_contextlevels($context);
    }

    list($insql, $inparams) = $DB->get_in_or_equal(array_values($roles));
    $roles = $DB->get_records_sql("SELECT * FROM {role} WHERE id $insql", $inparams);
    if ($disableoption) {
        $list[0] = get_string('disabled', 'auth_magic');
    }
    $list += role_fix_names($roles, null, ROLENAME_ALIAS, true);
    return $list;
}

/**
 * Get all groupings.
 */
function auth_magic_get_all_groupings() {
    global $DB;
    return $DB->get_records_menu('groupings', null, '', 'id,name');
}


/**
 * Count the group user for grade book roles.
 * @param [int] $groupid
 * @return void
 */
function auth_magic_count_gradebook_role_groupusers($groupid) {
    global $DB, $CFG;
    $group = $DB->get_record('groups', ['id' => $groupid]);
    $count = 0;
    if ($group) {
        $coursecontext = context_course::instance($group->courseid);
        // We want to query both the current context and parent contexts.
        list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($coursecontext->get_parent_context_ids(true),
            SQL_PARAMS_NAMED, 'relatedctx');

        $sql = "SELECT count(gm.id) FROM {groups_members} gm
                JOIN {role_assignments} ra ON ra.userid = gm.userid AND ra.roleid IN ($CFG->gradebookroles)
                    AND ra.contextid $relatedctxsql
                WHERE gm.groupid = :groupid";
        $params = [
            'groupid' => $groupid,
        ];
        $params += $relatedctxparams;

        $count = $DB->count_records_sql($sql, $params);
    }
    return $count;
}

/**
 * Send email to specified user with confirmation text and activation link.
 *
 * @param stdClass $user object
 * @param string $confirmationurl user confirmation URL
 * @param int $childuser
 * @return bool Returns true if mail was sent OK and false if there was an error.
 */
function auth_magic_send_confirmation_email($user, $confirmationurl = null, $childuser = 0) {
    global $CFG;

    $site = get_site();
    $supportuser = core_user::get_support_user();

    $data = new stdClass();
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

    $subject = get_string('emailconfirmationsubject', '', format_string($site->fullname));

    if (empty($confirmationurl)) {
        $confirmationurl = '/login/confirm.php';
    }

    $confirmationurl = new moodle_url($confirmationurl);
    // Remove data parameter just in case it was included in the confirmation so we can add it manually later.
    $confirmationurl->remove_params('data');
    $confirmationpath = $confirmationurl->out(false);

    // We need to custom encode the username to include trailing dots in the link.
    // Because of this custom encoding we can't use moodle_url directly.
    // Determine if a query string is present in the confirmation url.
    $hasquerystring = strpos($confirmationpath, '?') !== false;
    // Perform normal url encoding of the username first.
    $username = urlencode($user->username);
    // Prevent problems with trailing dots not being included as part of link in some mail clients.
    $username = str_replace('.', '%2E', $username);

    $link = $confirmationpath . ( $hasquerystring ? '&' : '?') . 'data='. $user->secret .'/'. $username;
    if ($childuser) {
        $link .= '&childuser='. $childuser;
    }
    $data->link = $link;

    $message     = get_string('emailconfirmation', '', $data);
    $messagehtml = text_to_html(get_string('emailconfirmation', '', $data), false, false, true);

    // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
    return email_to_user($user, $supportuser, $subject, $message, $messagehtml);
}


function auth_magic_send_revocationlink_email($campaignid, $user, $parentuser) {
    $site = get_site();
    $supportuser = core_user::get_support_user();

    $data = new stdClass();
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

    $subject = get_string('emailrevocationsubject', 'auth_magic', format_string($site->fullname));

    $revocationurl = new moodle_url('/auth/magic/campaigns/revocation.php',
        ['campaignid' => $campaignid, 'userid' => $user->id]);
    $data->link = $revocationurl->out(false);
    $data->user = fullname($user);

    $message     = get_string('emailrevocation', 'auth_magic', $data);
    $messagehtml = text_to_html(get_string('emailrevocation', 'auth_magic', $data), false, false, true);

    // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
    return email_to_user($parentuser, $supportuser, $subject, $message, $messagehtml);
}

/**
 * Confirm the child user.
 * @param object $childuser
 * @return void
 */
function auth_magic_confirm_childuser($childuser) {
    global $DB;
    $authplugin = get_auth_plugin('magic');
    $childuser = $DB->get_record('user', ['id' => $childuser]);
    if ($childuser) {
        $confirmation = $authplugin->user_confirm($childuser->username, $childuser->secret);
        auth_magic_user_confirmation_campaign_assignments($childuser->id);
        return $confirmation;
    }
    return false;
}


/**
 * Confirm the parent user.
 * @param string $username
 * @return void
 */
function auth_magic_confirm_parentuser($username) {
    global $DB;
    $authplugin = get_auth_plugin('magic');
    $user = get_complete_user_data('username', $username);
    if ($user && $record = $DB->get_record('auth_magic_approval', ['userid' => $user->id])) {
        $parentuser = get_complete_user_data('id', $record->parent);
        $authplugin->user_confirm($parentuser->username, $parentuser->secret);
    }
}

/**
 * Campaign assigement update after confirm the user.
 *
 * @param [int] $userid
 * @return void
 */
function auth_magic_user_confirmation_campaign_assignments($userid) {
    global $DB;
    if ($campaignuser = $DB->get_record('auth_magic_campaigns_users', ['userid' => $userid])) {
        $campaigninstance = campaign::instance($campaignuser->campaignid);
        $campaignhelper = new \campaign_helper($campaigninstance->id);
        $user = $DB->get_record('user', ['id' => $userid]);
        if ($campaigninstance->get_campaign()->emailconfirm == campaign::PARTIAL
            && $user->confirmed == 0 && $user->lastaccess > 0) {
            $DB->set_field("user", "confirmed", 1, ["id" => $user->id]);
        }

        if ($campaigninstance->get_campaign()->emailconfirm == campaign::ENABLE ||
                $campaigninstance->get_campaign()->approvaltype != 'disabled') {
            // Assign to the campaign cohorts, roles, parent.
            $campaignhelper->process_campaign_assignments($user);
        }
    }
}


function auth_magic_managerole_assignments_customvalues($data, $roles) {
    $customfieldvalues = [];
    if (!empty($roles)) {
        $roles = json_decode($roles);
        foreach ($roles as $roleid) {
            $field = 'roleassignment_' . $roleid;
            $customvalue = 'profile_field_'.get_config('auth_magic', $field);
            if (isset($data->{$customvalue})) {
                $customfieldvalues[$customvalue] = $data->{$customvalue};
            }
        }
    }
    return $customfieldvalues;
}