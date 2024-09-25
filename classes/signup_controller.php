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
 * User signup.
 *
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package auth_magic
 */
namespace auth_magic;
use stdClass;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. "/auth/magic/lib.php");
require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_helper.php');
require_once($CFG->dirroot. '/user/editlib.php');

/**
 * Define Magic Signup.
 */
class signup_controller {

    /**
     * User email.
     * @var string
     */
    private $email;

    /**
     * Signup key info.
     * @var object
     */
    private $record;

    /**
     * Expiry time.
     * @var int
     */
    private $expiryperiod;

    /**
     * Signup constructor
     * @param string $email
     */
    public function __construct($email) {
        global $DB;
        $this->email = $email;
        $this->expiryperiod = get_config("auth_magic", "invitationexpiry");
        $this->create_key();
        $this->record = $DB->get_record('auth_magic_signupkey', ['email' => $this->email]);
    }

    /**
     * Check signup key expiry or not.
     */
    public function is_expiry() {
        if ($this->record->validuntil < time()) {
            return true;
        }
        return false;
    }

    /**
     * Sent the registration link to the user.
     */
    public function sendemail() {
        global $DB;
        $site = get_site();
        $subject = get_string('registrationsubject', 'auth_magic', format_string($site->fullname));
        $data = new stdClass();
        $data->emailplaceholder = substr($this->email, 0, strrpos($this->email, '@'));
        $signupurl = new moodle_url("/auth/magic/login.php", ['key' => $this->record->value, 'register' => true]);
        $data->link = $signupurl->out(false);
        $data->admin = generate_email_signoff();
        $data->sitename = $site->fullname;
        $data->expiry = auth_magic_expirytime_convert_datestring($this->record->validuntil);
        if ($this->is_expiry()) {
            $messageplain = get_string('expiredregistrationmessage', 'auth_magic', $data);
        } else {
            $messageplain = get_string('registrationmessage', 'auth_magic', $data);
        }
        $messagehtml = text_to_html($messageplain, false, false, true);
        if (!$user = $DB->get_record('user', ['email' => $this->email])) {
            $emailuser = new stdClass();
            $emailuser->email = $this->record->email;
            $emailuser->id = -99;
            return email_to_user($emailuser, \core_user::get_support_user(), $subject, $messagehtml);
        }
        $user->mailformat = 1;  // Always send HTML version as well.
        return auth_magic_messagetouser($user, $subject, $messageplain, $messagehtml);
    }

    /**
     * Delete the signup key.
     */
    private function delete_key() {
        global $DB;
        return $DB->delete_records('auth_magic_signupkey', ['email' => $this->email]);
    }

    /**
     * Creates a new signup access key.
     */
    private function create_key() {
        global $DB;
        if (!$DB->record_exists('auth_magic_signupkey', ['email' => $this->email])) {
            $key = new stdClass();
            $key->email         = $this->email;
            $key->validuntil    = time() + $this->expiryperiod;
            $key->timecreated   = time();
            // Something long and unique.
            $key->value         = md5(time().random_string(40));
            while ($DB->record_exists('auth_magic_signupkey', ['value' => $key->value])) {
                // Must be unique.
                $key->value     = md5(time().random_string(40));
            }
            $DB->insert_record('auth_magic_signupkey', $key);
        }
    }

    /**
     * Create the signup user.
     */
    private function create_user() {
        $user = new stdClass();
        $user->email = $this->email;
        $user->username = $this->email;
        $user = \campaign_helper::signup_setup_new_user($user);
        $user->confirmed = 1;
        $authplugin = get_auth_plugin('magic');
        $userid = $authplugin->user_signup($user, true);
        $this->delete_key();
        return $userid;
    }

    /**
     * Check the signup key.
     * @param string $key
     */
    public static function check_signup_key($key) {
        global $DB;
        if ($record = $DB->get_record('auth_magic_signupkey', ['value' => $key])) {
            return $record;
        }
        return false;
    }

    /**
     * Update the registration line expiry time.
     */
    public function update_expiry_time() {
        global $DB;
        $this->record->validuntil = time() + $this->expiryperiod;
        $DB->update_record('auth_magic_signupkey', $this->record);
    }

    /**
     * User signup.
     * @param string $key
     */
    public static function user_signup($key) {
        global $DB, $CFG;
        // Check the key is exist or not.
        if (isloggedin()) {
            require_logout();
        }
        if ($record = self::check_signup_key($key)) {
            $instance = new self($record->email);
            if ($instance->is_expiry()) {
                $messagestr = get_string('registrationexpirylink', 'auth_magic');
                if (get_config('auth_magic', 'loginkeytype') == 'more') {
                    $instance->sendemail();
                    $instance->update_expiry_time();
                    $messagestr = get_string('registrationexpirylinkwithupdate', 'auth_magic');
                }
                return redirect(new moodle_url('/login/index.php'), $messagestr,
                    null, \core\output\notification::NOTIFY_INFO);
            }
            if ($userid = $instance->create_user()) {
                // User signup success fully.
                if (!PHPUNIT_TEST) {
                    $user = $DB->get_record('user', ['id' => $userid]);
                    complete_user_login($user);
                    return redirect($CFG->wwwroot);
                }
            }
        }
        if (!PHPUNIT_TEST) {
            // Doesn't match the signup key.
            return redirect(new moodle_url('/login/index.php'), get_string('invalidkey', 'error'),
                null, \core\output\notification::NOTIFY_ERROR);
        }
    }
}
