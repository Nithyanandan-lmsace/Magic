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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot . "/user/profile/lib.php");
require_once($CFG->dirroot. '/auth/magic/lib.php');


use moodle_url;
use html_writer;
use auth_magic\campaign;

/**
 * Create new campaigns form.
 *
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class campaigns_manageform extends \moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        global $CFG;
        $mform = $this->_form;

        $fields = $this->_customdata['fields'];
        $campaignid = $this->_customdata['campaignid'];
        $code = $this->_customdata['code'];
        $token = $this->_customdata['token'];
        $coupon = $this->_customdata['coupon'];

        $campaigninfo = campaign::instance($campaignid);

        $mform->addElement('hidden', 'campaignid', $campaignid);
        $mform->setType('campaignid', PARAM_INT);
        $mform->addElement('hidden', 'code', $code);
        $mform->setType('code', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'token', $token);
        $mform->setType('token', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'coupon', $coupon);
        $mform->setType('coupon', PARAM_ALPHANUMEXT);

        $customfieldtypes = \campaign_helper::get_custom_field_types();

        foreach ($fields as $field) {
            // Implemented the standard field element.
            $fieldname = $field->field;
            if ($field->fieldtype == MAGICCAMPAIGNSTANDARDFIELD) {
                // Get the field id.
                $fieldstringid = $fieldname;
                if (!get_string_manager()->string_exists($fieldstringid, 'moodle')) {
                    $fieldstringid = get_string($fieldname, 'auth_magic');
                } else {
                    $fieldstringid = get_string($fieldname, 'moodle');
                }
                // Check the campaign field required or optional.
                if ($field->fieldoption == MAGICCAMPAIGNSREQUIRED || $field->fieldoption == MAGICCAMPAIGNSOPTIONAL) {

                    if ($field->field == 'lang') {
                        $purpose = user_edit_map_field_purpose(-1, 'lang');
                        $translations = get_string_manager()->get_list_of_translations();
                        $mform->addElement('select', $fieldname, $fieldstringid, $translations, $purpose);
                        $mform->setDefault($fieldname, $CFG->lang);
                    } else if ($field->field == 'country') {
                        $country = get_string_manager()->get_list_of_countries();
                        $defaultcountry[''] = get_string('selectacountry');
                        $country = array_merge($defaultcountry, $country);
                        $mform->addElement('select', $fieldname, $fieldstringid, $country);

                        if (!empty($CFG->country) ) {
                            $mform->setDefault($fieldname, $CFG->country);
                        } else {
                            $mform->setDefault($fieldname, '');
                        }
                    } else {
                        $mform->addElement('text', $fieldname, $fieldstringid, 'maxlength="100" size="30"');
                        // Set custom setType filed.
                        if (isset($customfieldtypes[$fieldname])) {
                            $mform->setType($fieldname, $customfieldtypes[$fieldname]);
                        } else {
                            $mform->setType($fieldname, \core_user::get_property_type($fieldname));
                        }
                    }
                    $stringid = 'missing' . $fieldname;
                    if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                        $stringid = 'required';
                    }
                    if ($field->fieldoption == MAGICCAMPAIGNSREQUIRED) {
                        $mform->addRule($fieldname, get_string($stringid), 'required', null, 'client');
                    }
                    // Implemented the confirm email field.
                    if ($field->field == 'email' && $campaigninfo->get_campaign()->emailconfirm == campaign::DISABLE) {
                        $this->confirm_email_field($mform);
                    }
                } else if ($field->field == 'password') { // Password field.
                    if ($field->fieldoption == REQUIREDONCE || $field->fieldoption == REQUIREDTWICE) {
                        $mform->addElement('password', 'password', get_string('newpassword'), [
                            'maxlength' => 32,
                            'size' => 12,
                            'autocomplete' => 'new-password',
                        ]);
                        $mform->setType('password', \core_user::get_property_type('password'));
                        $mform->addRule('password', get_string('required'), 'required', null, 'client');

                    }
                    if ($field->fieldoption == REQUIREDTWICE) {
                        $strpasswordagain = get_string('newpassword') . ' (' . get_string('again') . ')';
                        $mform->addElement('password', 'password2', $strpasswordagain, [
                            'maxlength' => 32,
                            'size' => 12,
                            'autocomplete' => 'new-password',
                        ]);
                        $mform->setType('password2', \core_user::get_property_type('password'));
                        $mform->addRule('password2', get_string('required'), 'required', null, 'client');
                    }
                } else {
                    // Check the campaign field hidden or hidden other type.
                    if ($field->fieldoption == MAGICCAMPAIGNSHIDDENPROVIDEVALUE ||
                        $field->fieldoption == MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT
                        || $field->fieldoption == MAGICCAMPAIGNSREQUIREDHIDDENOTHERFIELD) {
                        $fieldvalue = $field->customvalue;
                        if ($field->fieldoption == MAGICCAMPAIGNSREQUIREDHIDDENOTHERFIELD) {
                            $fieldvalue = $field->otherfieldvalue;
                        }
                        $mform->addElement('hidden', $fieldname, $fieldvalue);
                        // Set custom setType filed.
                        if (isset($customfieldtypes[$fieldname])) {
                            $mform->setType($fieldname, $customfieldtypes[$fieldname]);
                        } else {
                            $mform->setType($fieldname, \core_user::get_property_type($fieldname));
                        }
                    }
                }
            } else if ($field->fieldtype == MAGICCAMPAIGNPROFILEFIELD) {
                // Implemented the profile field element.
                if ($field->fieldoption == MAGICCAMPAIGNSREQUIRED || $field->fieldoption == MAGICCAMPAIGNSOPTIONAL) {
                    $customfield = profile_get_custom_field_data_by_shortname($field->field, false);
                    if ($customfield) {
                        $fileddata = \campaign_helper::profile_get_user_field($customfield->datatype, $customfield->id);
                        $fileddata->edit_field_add($mform);
                        $fileddata->edit_field_set_default($mform);
                        $fileddata->edit_field_set_required($mform);
                        $profilefield = 'profile_field_'. $fieldname;
                        if ($field->fieldoption == MAGICCAMPAIGNSREQUIRED && $mform->elementExists($profilefield)) {
                            $mform->addRule($profilefield, get_string('required'), 'required', null, 'client');
                        }
                    }
                } else {
                    // Check the campaign field hidden or hidden other type.
                    if ($field->fieldoption == MAGICCAMPAIGNSHIDDENPROVIDEVALUE ||
                        $field->fieldoption == MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT
                        || $field->fieldoption == MAGICCAMPAIGNSREQUIREDHIDDENOTHERFIELD) {
                        $customfield = profile_get_custom_field_data_by_shortname($field->field, false);
                        if ($customfield) {
                            $fileddata = \campaign_helper::profile_get_user_field($customfield->datatype, $customfield->id);
                            $fieldtypeproperties = $fileddata->get_field_properties();
                            $fieldvalue = $field->customvalue;
                            if ($field->fieldoption == MAGICCAMPAIGNSREQUIREDHIDDENOTHERFIELD) {
                                $fieldvalue = $field->customvalue;
                            }
                            $profilefield = 'profile_field_'. $fieldname;
                            $mform->addElement('hidden', $fieldname, $fieldvalue);
                            if (isset($fieldtypeproperties[0])) {
                                $mform->setType($fieldname, $fieldtypeproperties[0]);
                            } else {
                                $mform->setType($fieldname, PARAM_RAW);
                            }
                        }
                    }
                }
            }
        }

        // Include the campaign privacy statements to this campagin form.
        $campaigninfo->include_enrolmentkey($mform, $coupon);

        // Include the campaign privacy statements to this campagin form.
        $campaigninfo->include_privacy_policy($mform);
        // Include the campaign recaptcha.
        $campaigninfo->include_captcha($mform);
        $this->add_action_buttons(true, get_string('strsignup', 'auth_magic'));
    }

    /**
     * Include the email confirmation.
     * @param \form $mform
     */
    public function confirm_email_field(&$mform) {
        $mform->addElement('text', 'email2', get_string('emailagain'), 'maxlength="100" size="25"');
        $mform->setType('email2', \core_user::get_property_type('email'));
        $mform->addRule('email2', get_string('missingemail'), 'required', null, 'client');
    }

    /**
     * Validate user supplied data on the signup form.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Extend validation for any form extensions from plugins.
        $errors = array_merge($errors, core_login_validate_extend_signup_form($data));
        $errors += $this->signup_validate_data($data, $files);
        return $errors;
    }

    /**
     * Validates the campaign sign-up data (except recaptcha that is validated by the form element).
     *
     * @param  array $data  the sign-up data
     * @param  array $files files among the data
     * @return array list of errors, being the key the data element name and the value the error itself
     * @since Moodle 3.2
     */
    public function signup_validate_data($data, $files) {
        global $CFG, $DB, $USER;
        $errors = [];
        $authplugin = get_auth_plugin('magic');
        $campaigninfo = campaign::instance($data['campaignid']);
        if ($DB->record_exists('user', ['username' => $data['username'], 'mnethostid' => $CFG->mnet_localhost_id])) {
            $errors['username'] = get_string('usernameexists');
        } else {
            // Check allowed characters.
            if ($data['username'] !== \core_text::strtolower($data['username'])) {
                $errors['username'] = get_string('usernamelowercase');
            } else {
                if ($data['username'] !== \core_user::clean_field($data['username'], 'username')) {
                    $errors['username'] = get_string('invalidusername');
                }

            }
        }

        // Check if user exists in external db.
        // TODO: maybe we should check all enabled plugins instead.
        if ($authplugin->user_exists($data['username'])) {
            $errors['username'] = get_string('usernameexists');
        }

        if (! validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail');

        } else if (empty($CFG->allowaccountssameemail)) {
            // Emails in Moodle as case-insensitive and accents-sensitive. Such a combination can lead to very slow queries
            // on some DBs such as MySQL. So we first get the list of candidate users in a subselect via more effective
            // accent-insensitive query that can make use of the index and only then we search within that limited subset.
            $sql = "SELECT 'x'
                      FROM {user}
                     WHERE " . $DB->sql_equal('email', ':email1', false, true) . "
                       AND id IN (SELECT id
                                    FROM {user}
                                   WHERE " . $DB->sql_equal('email', ':email2', false, false) . "
                                     AND mnethostid = :mnethostid)";
            $params = [
                'email1' => $data['email'],
                'email2' => $data['email'],
                'mnethostid' => $CFG->mnet_localhost_id,
            ];
            // If there are other user(s) that already have the same email, show an error.
            if ($DB->record_exists_sql($sql, $params)) {
                $forgotpasswordurl = new moodle_url('/login/forgot_password.php');
                $forgotpasswordlink = html_writer::link($forgotpasswordurl,
                    get_string('emailexistshintlink'));
                if (!isloggedin() || $USER->email != $data['email']) {
                    $errors['email'] = get_string('emailexists') . ' ' .
                        get_string('emailexistssignuphint', 'moodle', $forgotpasswordlink);
                }
            }
        }

        // Email confirm vaildation.
        if ($campaigninfo->get_campaign()->emailconfirm == campaign::DISABLE) {
            if (empty($data['email2'])) {
                $errors['email2'] = get_string('missingemail');
            } else if (\core_text::strtolower($data['email2']) != \core_text::strtolower($data['email'])) {
                $errors['email2'] = get_string('invalidemail');
            }
        }

        if (!isset($errors['email'])) {
            if ($err = email_is_not_allowed($data['email'])) {
                $errors['email'] = $err;
            }
        }

        if ($campaigninfo->campaign_captcha_enabled()) {
            $recaptchaelement = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['g-recaptcha-response'])) {
                $response = $this->_form->_submitValues['g-recaptcha-response'];
                if (!$recaptchaelement->verify($response)) {
                    $errors['recaptcha_element'] = get_string('incorrectpleasetryagain', 'auth');
                }
            } else {
                $errors['recaptcha_element'] = get_string('missingrecaptchachallengefield');
            }
        }

        if (isset($data['enrolpassword'])) {
            $errors += $campaigninfo->enrolment_key_validation($data['enrolpassword']);
        }

        $fieldinstance = \campaign_helper::get_campaign_fields_instance($data['campaignid'])->get_field('password');
        if ($fieldinstance->fieldoption == REQUIREDONCE || $fieldinstance->fieldoption == REQUIREDTWICE) {
            // Ignore submitted username.
            if ($fieldinstance->fieldoption == REQUIREDTWICE && $data['password'] !== $data['password2']) {
                $errors['password'] = get_string('passwordsdiffer');
                $errors['password2'] = get_string('passwordsdiffer');
                return $errors;
            }

            $errmsg = ''; // Prevents eclipse warnings.
            if (!check_password_policy($data['password'], $errmsg)) {
                $errors['password'] = $errmsg;
                if ($fieldinstance->fieldoption == REQUIREDTWICE) {
                    $errors['password2'] = $errmsg;
                }
                return $errors;
            }
        }

        // Validate customisable profile fields. (profile_validation expects an object as the parameter with userid set).
        $dataobject = (object)$data;
        $dataobject->id = 0;
        $errors += profile_validation($dataobject, $files);
        return $errors;
    }
}
