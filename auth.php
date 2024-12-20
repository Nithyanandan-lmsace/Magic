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
 * Magic authentication login - Create magic links to login user.
 *
 * @package auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot."/auth/magic/lib.php");

/**
 * Magic authentication login.
 */
class auth_plugin_magic extends auth_plugin_base {


    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'magic';
    }

    /**
     * Don't allow login using login form.
     *
     * @param string $username The username
     * @param string $password The password
     */
    public function user_login($username, $password) {
        global $DB, $CFG;
        if (!get_config('auth_magic', 'supportpassword')) {
            return false;
        }
        if (!$user = $DB->get_record('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id])) {
            return false;
        }
        if (!validate_internal_user_password($user, $password)) {
            return false;
        }
        return true;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    public function can_signup() {
        // Override if needed.
        return true;
    }

    /**
     * Sign up a new user ready for confirmation.
     * Password is passed in plaintext.
     *
     * @param object $user new user object
     * @param boolean $notify print notice with link and terminate
     */
    public function user_signup($user, $notify=true) {
        // Standard signup, without custom confirmatinurl.
        return $this->magic_user_signup($user, $notify);
    }

    /**
     * Confirm the new user as registered.
     *
     * @param string $username
     * @param string $confirmsecret
     */
    public function user_confirm($username, $confirmsecret) {
        global $DB, $SESSION;
        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->secret === $confirmsecret && $user->confirmed) {
                return AUTH_CONFIRM_ALREADY;

            } else if ($user->secret === $confirmsecret) {   // They have provided the secret key to get in.
                $DB->set_field("user", "confirmed", 1, ["id" => $user->id]);

                return AUTH_CONFIRM_OK;
            }
        } else {
            return AUTH_CONFIRM_ERROR;
        }
    }

    /**
     * Sign up a new user ready for confirmation.
     *
     * Password is passed in plaintext.
     * A custom confirmationurl could be used.
     *
     * @param object $user new user object
     * @param boolean $notify print notice with link and terminate
     * @return boolean true if everything well ok and $notify is set to true
     * @throws moodle_exception
     * @since Moodle 3.2
     */
    public function magic_user_signup($user, $notify=true) {
        global $CFG, $DB, $SESSION, $PAGE, $OUTPUT;
        require_once($CFG->dirroot.'/user/profile/lib.php');
        require_once($CFG->dirroot.'/user/lib.php');
        $plainpassword = null;
        if (get_config('auth_magic', 'supportpassword') && isset($user->password)) {
            $plainpassword = $user->password;
            $user->password = hash_internal_user_password($user->password);
        }

        $user->id = user_create_user($user, false, false);
        if (get_config('auth_magic', 'supportpassword') && $plainpassword) {
            user_add_password_history($user->id, $plainpassword);
        }

        // Save any custom profile field information.
        profile_save_data($user);

        // Save wantsurl against user's profile, so we can return them there upon confirmation.
        if (!empty($SESSION->wantsurl)) {
            set_user_preference('auth_magic_wantsurl', $SESSION->wantsurl, $user);
        }
        // Trigger event.
        \core\event\user_created::create_from_userid($user->id)->trigger();

        return $user->id;
    }

    /**
     * Returns true if plugin allows confirming of new users.
     *
     * @return bool
     */
    public function can_confirm() {
        return true;
    }


    /**
     * No password updates.
     *
     * @param string $user The username
     * @param string $newpassword The password
     */
    public function user_update_password($user, $newpassword) {
        if (get_config('auth_magic', 'supportpassword')) {
            $user = get_complete_user_data('id', $user->id);
            // This will also update the stored hash to the latest algorithm
            // if the existing hash is using an out-of-date algorithm (or the
            // legacy md5 algorithm).
            return update_internal_user_password($user, $newpassword);
        }
        return false;
    }

    /**
     * Don't store local passwords.
     *
     * @return bool True.
     */
    public function prevent_local_passwords() {
        // Just in case, we do not want to loose the passwords.
        return !get_config('auth_magic', 'supportpassword');
    }

    /**
     * No external data sync.
     *
     * @return bool
     */
    public function is_internal() {
        // We do not know if it was internal or external originally.
        return get_config('auth_magic', 'supportpassword');
    }

    /**
     * No changing of password.
     *
     * @return bool
     */
    public function can_change_password() {
        return get_config('auth_magic', 'supportpassword');
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    public function change_password_url() {
        return null;
    }

    /**
     * No password resetting.
     */
    public function can_reset_password() {
        return get_config('auth_magic', 'supportpassword');
    }

    /**
     * Returns true if plugin can be manually set.
     *
     * @return bool
     */
    public function can_be_manually_set() {
        return true;
    }


    /**
     * Create key for a specific user.
     *
     * @param int $userid User ID.
     * @param int $validuntil
     */
    public function create_user_key($userid, $validuntil) {
        $config = $this->get_config_data();
        return create_user_key(
            'auth/magic',
            $userid,
            null,
            null,
            $validuntil
        );
    }

    /**
     * Delete all keys for a specific user.
     *
     * @param int $userid User ID.
     */
    public function delete_keys($userid) {
        delete_user_key('auth/magic', $userid);
    }

    /**
     * Get plugin config data.
     * @return stdClass data
     */
    public function get_config_data() {
        return get_config('auth_magic');
    }

    /**
     * Returns a list of potential IdPs that this authentication plugin supports. Used to provide links on the login page.
     *
     * @param string $wantsurl The relative url fragment the user wants to get to.
     * @return array Array of idps.
     */
    public function loginpage_idp_list($wantsurl) {
        global $PAGE;
        return [
            [
                'url' => new \moodle_url('/auth/magic/login.php'),
                'iconurl' => '',
                'name' => get_string('getmagiclinkviagmail', 'auth_magic'),
            ],
        ];
    }

    /**
     * Hook for overriding behaviour of login page.
     * This method is called from login/index.php page for all enabled auth plugins.
     */
    public function loginpage_hook() {
        global $CFG, $PAGE;
        $CFG->authloginviaemail = true;
        $linkbtnpos = get_config('auth_magic', 'loginlinkbtnpostion');
        $params = [
            'loginhook' => true,
            'strbutton' => get_string('getmagiclinkviagmail', 'auth_magic'),
            'linkbtnpos' => $linkbtnpos,
        ];
        $PAGE->requires->js_call_amd('auth_magic/authmagic', 'init', [$params]);
    }


    /**
     * Create key for users.
     * @param object $user
     * @param bool $checkparent
     * @return void
     */
    public function create_magic_instance($user, $checkparent = true) {
        global $CFG, $DB, $USER;
        if (!$DB->record_exists('auth_magic_loginlinks', ['userid' => $user->id])) {

            $config = $this->get_config_data();
            $loginexpiry = !empty($config->loginexpiry) ? time() + $config->loginexpiry : 0;
            $invitationexpiry = !empty($config->invitationexpiry) ? time() + $config->invitationexpiry : 0;
            $loginuserkey = $this->create_user_key($user->id, $loginexpiry);
            $invitationuserkey = $this->create_user_key($user->id, $invitationexpiry);
            $loginurl = $CFG->wwwroot . '/auth/magic/login.php?key=' . $loginuserkey;
            $invitationurl = $CFG->wwwroot . '/auth/magic/login.php?key=' . $invitationuserkey;
            $parent = 0;
            $parentrole = null;
            if ($checkparent) {
                $parentrole = get_config('auth_magic', 'owneraccountrole');
                if ($parentrole) {
                    $parent = $USER->id;
                }
            }

            // Insert record.
            $record = new stdClass;
            $record->userid = $user->id;
            $record->parent = $parent;
            $record->magicauth = ($checkparent) ? 1 : 0;
            $record->parentrole = ($checkparent) ? $parentrole : 0;
            $record->loginuserkey = $loginuserkey;
            $record->invitationuserkey = $invitationuserkey;
            $record->magiclogin = $loginurl;
            $record->magicinvitation = $invitationurl;
            $record->loginexpiry = $loginexpiry;
            $record->invitationexpiry = $invitationexpiry;
            $record->timecreated = time();
            $DB->insert_record('auth_magic_loginlinks', $record);
        }

    }

    /**
     * Update the loginkey.
     * @param core_user $user
     * @param stdClass $keyinstance
     */
    public function update_new_loginkey($user, $keyinstance) {
        global $DB, $CFG;
        $config = $this->get_config_data();
        $loginexpiry = !empty($config->loginexpiry) ? time() + $config->loginexpiry : 0;
        if (!empty($keyinstance)) {
            // Delete the previous loginkey.
            $DB->delete_records('user_private_key', ['value' => $keyinstance->loginuserkey,
                'userid' => $keyinstance->userid]);
            $loginuserkey = $this->create_user_key($user->id, $loginexpiry);
            $keyinstance->loginuserkey = $loginuserkey;
            $keyinstance->magiclogin = $CFG->wwwroot . '/auth/magic/login.php?key=' . $loginuserkey;
            $keyinstance->loginexpiry = $loginexpiry;
            $keyinstance->timemodified = time();
            $DB->update_record('auth_magic_loginlinks', $keyinstance);
        }
    }

    /**
     * Wheather key is invitation or login.
     * @param mixed $key
     * @return bool
     */
    public function check_userkey_type($key) {
        global $DB;
        $options = [
            'script' => 'auth/magic',
            'value' => $key,
        ];

        $accessauthtoall = get_config('auth_magic', 'authmethod');
        if ($instance = $DB->get_record('auth_magic_loginlinks', ['loginuserkey' => $key])) {
            // Key as login.
            if (!empty($instance->loginexpiry) && $instance->loginexpiry < time()) {
                // Resend login and indicate to the click the expiry key.
                $relateduser = \core_user::get_user($instance->userid);
                if (!$relateduser->suspended && !$relateduser->deleted) {
                    if ($relateduser->auth == 'magic' || $accessauthtoall) {
                        $messagestr = get_string('loginexpiryloginlinkwithupdate', 'auth_magic');
                        $this->update_new_loginkey($relateduser, $instance);
                        auth_magic_sent_loginlink_touser($relateduser->id, false, true);
                        // Give the response only for non-login user logged in user
                        // show the prompt to display the access different account.
                        if (!isloggedin()) {
                            redirect(new moodle_url('/login/index.php'), $messagestr,
                                null, \core\output\notification::NOTIFY_INFO);
                        }
                    }
                }
            }
        } else if ($instance = $DB->get_record('auth_magic_loginlinks', ['invitationuserkey' => $key])) {
            // Key as invitation.
            if (!empty($instance->invitationexpiry) && $instance->invitationexpiry < time()) {
                // Resend login.
                $relateduser = \core_user::get_user($instance->userid);
                if (!$relateduser->suspended && !$relateduser->deleted) {
                    if ($relateduser->auth == 'magic' || $accessauthtoall) {
                        // Exist login is expiry or not.
                        if (!empty($instance->loginexpiry) && $instance->loginexpiry < time()) {
                            $this->update_new_loginkey($relateduser, $instance);
                        }
                        $messagestr = get_string('invitationexpiryloginlinkwithupdate', 'auth_magic');
                        auth_magic_sent_loginlink_touser($relateduser->id);
                        // Give the response only for non-login user logged in user show the prompt to
                        // Display the access different account.
                        if (!isloggedin()) {
                            redirect(new moodle_url('/login/index.php'), $messagestr,
                                null, \core\output\notification::NOTIFY_INFO);
                        }
                    }
                }
            }
        }
        return true;
    }

}
