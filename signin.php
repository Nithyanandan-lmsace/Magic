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
 * Main login page.
 *
 * @package    auth_magic
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once($CFG->dirroot. "/login/lib.php");
require_once($CFG->dirroot. "/auth/magic/lib.php");

if (!is_enabled_auth('magic')) {
    throw new moodle_exception(get_string('pluginisdisabled', 'auth_magic'));
}

redirect_if_major_upgrade_required();
$email = optional_param('email', '', PARAM_EMAIL);
$testsession = optional_param('testsession', 0, PARAM_INT); // Test session works properly.

$context = context_system::instance();

$PAGE->set_url("$CFG->wwwroot/auth/magic/signin.php");
$PAGE->set_context($context);
$PAGE->set_pagelayout('login');
$loginsite = get_string("loginsite");

// Define variables used in page.
$site = get_site();
$PAGE->set_title("$site->fullname: $loginsite");
$PAGE->set_heading("$site->fullname");

$params = [
    'customloginhook' => true,
    'strbutton' => get_string('getmagiclinkviagmail', 'auth_magic'),
];
$PAGE->requires->js_call_amd('auth_magic/authmagic', 'init', [$params]);

$urlparams['email'] = !empty($email) ? $email : "";
$user = auth_magic_get_email_user($email);
$urlparams['user'] = $user;

if (isloggedin() && !isguestuser()) {
    echo $OUTPUT->header();
    // Prevent logging when already logged in, we do not want them to relogin by accident because sesskey would be changed.
    echo $OUTPUT->box_start();
    $logout = new single_button(new moodle_url('/login/logout.php', ['sesskey' => sesskey(),
        'loginpage' => 1]), get_string('logout'), 'post');
    $continue = new single_button(new moodle_url('/'), get_string('cancel'), 'get');
    echo $OUTPUT->confirm(get_string('alreadyloggedin', 'error', fullname($USER)), $logout, $continue);
    echo $OUTPUT->box_end();
    exit;
}

$errormsg = '';
$errorcode = 0;

// Check for timed out sessions.
if (!empty($SESSION->has_timed_out)) {
    $sessionhastimedout = true;
    unset($SESSION->has_timed_out);
} else {
    $sessionhastimedout = false;
}

// Detect problems with timedout sessions.
if ($sessionhastimedout && !data_submitted()) {
    $errormsg = get_string('sessionerroruser', 'error');
    $errorcode = 4;
}

if (!empty($SESSION->loginerrormsg)) {
    // We had some errors before redirect, show them now.
    $errormsg = $SESSION->loginerrormsg;
    unset($SESSION->loginerrormsg);

}

// Update the magic auth identidy provider status.
$passwordupdate = false;

$loginform = new auth_magic\form\login_form(null, $urlparams, 'post', '',
    ['class' => 'login-form', 'id' => 'login']);

if ($data = $loginform->get_data()) {
    if (isset($data->password)) {
        // Submit password form.
        $userrecord = auth_magic_get_email_user($data->email);
        $data->username = $userrecord->username;
        $user = authenticate_user_login($data->username, $data->password);
        $passwordupdate = (!$user) ? false : true;
        if (!$user && $data && is_restored_user($data->username)) {
            $PAGE->set_title(get_string('restoredaccount'));
            $PAGE->set_heading($site->fullname);
            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string('restoredaccount'));
            echo $OUTPUT->box(get_string('restoredaccountinfo'), 'generalbox boxaligncenter');
            require_once('restored_password_form.php'); // Use our "supplanter" login_forgot_password_form. MDL-20846.
            $form = new login_forgot_password_form('forgot_password.php',
                ['username' => $data->username]);
            $form->display();
            echo $OUTPUT->footer();
            die;
        }
        // Check user object and user auth not magic.
        // TODO: Doesn't support magic auth login via username password when user account privileged user.
        if ($user && $user->auth != 'magic') {
            if (!empty($user)) {
                // Language setup.
                if (isguestuser($user)) {
                    // No predefined language for guests - use existing session or default site lang.
                    unset($user->lang);

                } else if (!empty($user->lang)) {
                    // Unset previous session language - use user preference instead.
                    unset($SESSION->lang);
                }

                if (empty($user->confirmed)) {       // This account was never confirmed.
                    $PAGE->set_title(get_string("mustconfirm"));
                    $PAGE->set_heading($site->fullname);
                    echo $OUTPUT->header();
                    echo $OUTPUT->heading(get_string("mustconfirm"));
                    echo $OUTPUT->box(get_string("emailconfirmsent", "", s($user->email)), "generalbox boxaligncenter");
                    $resendconfirmurl = new moodle_url('/login/index.php',
                        [
                            'username' => $data->username,
                            'password' => $data->password,
                            'resendconfirmemail' => true,
                            'logintoken' => \core\session\manager::get_login_token(),
                        ]
                    );
                    echo $OUTPUT->single_button($resendconfirmurl, get_string('emailconfirmationresend'));
                    echo $OUTPUT->footer();
                    die;
                }

                // Let's get them all set up.
                complete_user_login($user);
                \core\session\manager::apply_concurrent_login_limit($user->id, session_id());
                // Sets the username cookie.
                if (empty($CFG->rememberusername)) {
                    // No permanent cookies, delete old one if exists.
                    set_moodle_cookie('');
                } else {
                    set_moodle_cookie($USER->username);
                }
                $urltogo = core_login_get_return_url();
                // Check if user password has expired.
                // Currently supported only for ldap-authentication module.
                $userauth = get_auth_plugin($USER->auth);
                if (!isguestuser() && !empty($userauth->config->expiration) && $userauth->config->expiration == 1) {
                    $externalchangepassword = false;
                    if ($userauth->can_change_password()) {
                        $passwordchangeurl = $userauth->change_password_url();
                        if (!$passwordchangeurl) {
                            $passwordchangeurl = $CFG->wwwroot.'/login/change_password.php';
                        } else {
                            $externalchangepassword = true;
                        }
                    } else {
                        $passwordchangeurl = $CFG->wwwroot.'/login/change_password.php';
                    }
                    $days2expire = $userauth->password_expire($USER->username);
                    $PAGE->set_title("$site->fullname: $loginsite");
                    $PAGE->set_heading("$site->fullname");
                    if (intval($days2expire) > 0 && intval($days2expire) < intval($userauth->config->expiration_warning)) {
                        echo $OUTPUT->header();
                        echo $OUTPUT->confirm(get_string('auth_passwordwillexpire', 'auth', $days2expire),
                            $passwordchangeurl, $urltogo);
                        echo $OUTPUT->footer();
                        exit;
                    } else if (intval($days2expire) < 0 ) {
                        if ($externalchangepassword) {
                            // We end the session if the change password form is external. This prevents access to the site.
                            // Until the password is correctly changed.
                            require_logout();
                        } else {
                            // If we use the standard change password form, this user preference will be reset when the password.
                            // Is changed. Until then it will prevent access to the site.
                            set_user_preference('auth_forcepasswordchange', 1, $USER);
                        }
                        echo $OUTPUT->header();
                        echo $OUTPUT->confirm(get_string('auth_passwordisexpired', 'auth'), $passwordchangeurl, $urltogo);
                        echo $OUTPUT->footer();
                        exit;
                    }
                }
                // Discard any errors before the last redirect.
                unset($SESSION->loginerrormsg);
                // Test the session actually works by redirecting to self.
                $SESSION->wantsurl = $urltogo;
                redirect(new moodle_url(get_login_url(), ['testsession' => $USER->id]));
            } else {
                if (empty($errormsg)) {
                    if ($errorcode == AUTH_LOGIN_UNAUTHORISED) {
                        $errormsg = get_string("unauthorisedlogin", "", $data->username);
                    } else {
                        $errormsg = get_string("invalidlogin");
                        $errorcode = 3;
                    }
                }
            }
        } else {
            if (!$user) {
                $errormsg = get_string("invalidlogin");
                $errorcode = 3;
            }
        }
    } else {
        // Email record not exists.
        if (!$DB->record_exists('user', ['email' => $data->email]) && !get_config('auth_magic', 'autocreateusers')) {
            $errormsg = get_string('emailnotexists', 'auth_magic');
        }
    }
} else if ($loginform->is_cancelled()) {
    redirect($PAGE->url);
}
if ($CFG->version < 2022030300) {
    $PAGE->add_body_class('magic-login-form');
}
echo $OUTPUT->header();
echo auth_magic_import_auth_details($loginform, $email, $errormsg, $passwordupdate);
echo $OUTPUT->footer();
echo auth_magic_generate_footer_links('loginfooter');




