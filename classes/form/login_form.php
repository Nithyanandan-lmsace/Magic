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
 * Form for editing a quick registration
 *
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package auth_magic
 */
namespace auth_magic\form;

use context_system;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/auth/magic/lib.php');

/**
 * Create new login form.
 *
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class login_form extends \moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        global $CFG, $PAGE;
        $mform = $this->_form;

        $email = $this->_customdata['email'];
        $user = $this->_customdata['user'];
        $mform->addElement('text', 'email', get_string('email'),
            ['placeholder' => get_string('strenteryouremail', 'auth_magic')]);
        $mform->setType('email', PARAM_RAW_TRIMMED);
        $mform->addRule('email', null, 'required', null, 'client');
        $mform->setDefault('email', $email);
        if ($user) {
            $auth = get_auth_plugin($user->auth);
            if (($user->auth == 'magic' && !empty($user->password) && auth_magic_is_user_privilege($user))) {
                $params = [
                    'customloginhook' => true,
                    'passcheck' => true,
                    'strbutton' => get_string('getmagiclinkviagmail', 'auth_magic'),
                    'contextid' => \context_system::instance()->id,
                ];
                $PAGE->requires->js_call_amd('auth_magic/authmagic', 'init', [$params]);
                $mform->addElement('password', 'password', get_string('password'),
                    ['placeholder' => get_string('password')]);
                $mform->setType('password', PARAM_RAW);
                $mform->addRule('password', null, 'required', null, 'client');
            } else if ($user->auth != 'magic' && $auth->can_reset_password()) {
                $mform->addElement('password', 'password', get_string('password'),
                    ['placeholder' => get_string('password')]);
                $mform->setType('password', PARAM_RAW);
                $mform->addElement('submit', 'submitbutton', get_string('strsignin', 'auth_magic'),
                    ['class' => 'magic-signin']);
            }
        }
        $mform->addElement('submit', 'submitbutton', "&#xf18e;", ['class' => 'magic-submit-action']);
    }

    /**
     * Validate the form data.
     * @param array $data
     * @param array $files
     * @return array|bool
     */
    public function validation($data, $files) {
        global $DB, $CFG;
        $data = (object)$data;
        $err = [];

        if (get_config('auth_magic', 'loginoption')) {
            if (!$DB->record_exists('user', ['username' => $data->email]) && !validate_email($data->email)) {
                $err['email'] = get_string('invalidemail');
            }
        } else {
            if (!validate_email($data->email)) {
                $err['email'] = get_string('invalidemail');
            }
        }

        if (count($err) == 0) {
            return true;
        } else {
            return $err;
        }
    }
}
