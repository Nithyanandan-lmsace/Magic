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
 * Form for campaign user access the campaigns teamform.
 *
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package auth_magic
 */
namespace auth_magic\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');


/**
 * Create new campaigns teams form.
 *
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class campaigns_selfform extends \moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        global $USER;
        $mform = $this->_form;

        $campaignid = $this->_customdata['campaignid'];
        $code = $this->_customdata['code'];
        $token = $this->_customdata['token'];
        $coupon = $this->_customdata['coupon'];

        $mform->addElement('hidden', 'campaignid', $campaignid);
        $mform->setType('campaignid', PARAM_INT);

        $mform->addElement('hidden', 'userid', $USER->id);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'code', $code);
        $mform->setType('code', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'coupon', $coupon);
        $mform->setType('coupon', PARAM_ALPHANUMEXT);

        $mform->addElement('static', 'myselfinfo', get_string('campaign:myselfinfo', 'auth_magic'));
        $this->add_action_buttons(true, get_string('strapply', 'auth_magic'));
    }
}