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
 * Create campaign Verify the password form.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die('No direct access');

require_once($CFG->dirroot.'/lib/formslib.php');
/**
 * Handler form to Protect campaign using password.
 */
class campaign_rule_form extends moodleform {

    /**
     * Define the password fields content.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'code');
        $mform->setType('code', PARAM_ALPHANUMEXT);
        if (isset($this->_customdata['code'])) {
            $mform->setDefault('code', $this->_customdata['code']);
        }

        $mform->addElement('static', 'passwordmessage', '',
                get_string('campaigns:requirepasswordmessage', 'auth_magic'));

        // Don't use the 'proper' field name of 'password' since that get's
        // Firefox's password auto-complete over-excited.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('password', 'campaignpassword',
            get_string('campaigns:password', 'auth_magic'), ['autofocus' => 'true']);
        $mform->addRule('campaignpassword', get_string('error'), 'required');

        $this->add_action_buttons(true, get_string('verify', 'auth_magic'));
    }
}
