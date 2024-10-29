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

use core_courseformat\external\get_state;
use auth_magic\campaign;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_helper.php');
require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_message_vars.php');
require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_formfield_vars.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/editlib.php');

/**
 * Create new campaigns form.
 *
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class campaigns_form extends \moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        global $USER, $CFG, $COURSE, $PAGE, $DB, $OUTPUT, $SITE;
        $mform = $this->_form;

        $id = (isset($this->_customdata['id'])) ? $this->_customdata['id'] : 0;

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'campaignid', $id);
        $mform->setType('campaignid', PARAM_INT);

            // General section.
        $mform->addElement('header', 'generalsection', get_string('campaigns:generalsection', 'auth_magic'));

        // Add the Title field (required).
        $mform->addElement('text', 'title', get_string('campaigns:title', 'auth_magic'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('error'), 'required');
        $mform->addHelpButton('title', 'campaigns:title', 'auth_magic');

        // Add the Description field (optional).
        $editoroptions = \campaign_helper::get_editor_options();
        $mform->addElement('editor', 'description_editor', get_string('campaigns:description', 'auth_magic'), '', $editoroptions);
        $mform->setType('description_editor', PARAM_RAW);
        $mform->addHelpButton('description_editor', 'campaigns:description', 'auth_magic');

        // Add the internal comments field (optional).
        $mform->addElement('editor', 'comments_editor', get_string('campaigns:comments', 'auth_magic'), '', $editoroptions);
        $mform->setType('comments_editor', PARAM_RAW);
        $mform->addHelpButton('comments_editor', 'campaigns:comments', 'auth_magic');

        // Availability section.
        $mform->addElement('header', 'availabilitysection', get_string('campaigns:availabilitysection', 'auth_magic'));

        // Add the capacity field.
        $mform->addElement('text', 'capacity', get_string('campaigns:capacity', 'auth_magic'));
        $mform->addRule('capacity', get_string('error'), 'numeric');
        $mform->addHelpButton('capacity', 'campaigns:capacity', 'auth_magic');
        // Add capacity info.
        if ($id) {
            \auth_magic\campaign::instance($id)->show_capacity_info($mform);
        }

        $mform->setType('capacity', PARAM_INT);

        // Add the status field.
        $status = [
            campaign::STATUS_AVAILABLE => get_string('campaigns:available', 'auth_magic'),
            campaign::STATUS_ARCHIVED => get_string('campaigns:archived', 'auth_magic'),
        ];
        $mform->addElement('select', 'status', get_string('campaigns:status', 'auth_magic'), $status);
        $mform->addHelpButton('status', 'campaigns:status', 'auth_magic');

        // Add the visibility field.
        $visibility = [
            campaign::HIDDEN => get_string('campaigns:hidden', 'auth_magic'),
            campaign::VISIBLE => get_string('visible'),
        ];
        $mform->addElement('select', 'visibility', get_string('campaigns:visibility', 'auth_magic'), $visibility);
        $mform->setDefault('visibility', campaign::HIDDEN);
        $mform->addHelpButton('visibility', 'campaigns:visibility', 'auth_magic');

        // Add the campaign open date field.
        $mform->addElement('date_time_selector', 'startdate',
            get_string('campaigns:start_from', 'auth_magic'), ['optional' => true]);
        $mform->addHelpButton('startdate', 'campaigns:start_from', 'auth_magic');
        // Add the campaign close date field.
        $mform->addElement('date_time_selector', 'enddate',
            get_string('campaigns:end_from', 'auth_magic'), ['optional' => true]);
        $mform->addHelpButton('enddate', 'campaigns:end_from', 'auth_magic');

        // Add the campaign password field.
        $mform->addElement('passwordunmask', 'password', get_string('campaigns:password', 'auth_magic'));
        $mform->addHelpButton('password', 'campaigns:password', 'auth_magic');

        // Appearance section.
        $mform->addElement('header', 'appearancesection',
            get_string('campaigns:appearancesection', 'auth_magic'));

        // Add logo as filemanager element.
        $options = \campaign_helper::image_fileoptions();
        $mform->addElement('filemanager', 'logo_filemanager',
            get_string('campaigns:logo', 'auth_magic'), null, $options);
        $mform->addHelpButton('logo_filemanager', 'campaigns:logo', 'auth_magic');

        // Add header image field.
        $mform->addElement('filemanager', 'headerimage_filemanager',
            get_string('campaigns:headerimg', 'auth_magic'), null, $options);
        $mform->addHelpButton('headerimage_filemanager', 'campaigns:headerimg', 'auth_magic');

        // Add background image field.
        $mform->addElement('filemanager', 'backgroundimage_filemanager',
            get_string('campaigns:backgroundimg', 'auth_magic'), null, $options);
        $mform->addHelpButton('backgroundimage_filemanager', 'campaigns:backgroundimg', 'auth_magic');

        // Add Transparent form field.
        $mform->addElement('advcheckbox', 'transparentform',
            get_string('campaigns:transform', 'auth_magic'), 'Default: false', null, [0, 1]);
        $mform->addHelpButton('transparentform', 'campaigns:transform', 'auth_magic');

        // Add Show campaign owner profile picture field.
        $mform->addElement('advcheckbox', 'displayowerprofile',
            get_string('campaigns:ownerprofile', 'auth_magic'), 'Default: false', null, [0, 1]);
        $mform->addHelpButton('displayowerprofile', 'campaigns:ownerprofile', 'auth_magic');

        // Add the Form position field.
        $positions = [
            campaign::FORM_POSITION_CENTER => get_string('campaigns:center', 'auth_magic'),
            campaign::FORM_POSITION_LEFTOVERLAY => get_string('campaigns:leftoverlay', 'auth_magic'),
            campaign::FORM_POSITION_RIGHTOVERLAY => get_string('campaigns:rightoverlay', 'auth_magic'),
            campaign::FORM_POSITION_LEFTFULL => get_string('campaigns:leftfull', 'auth_magic'),
            campaign::FORM_POSITION_RIGHTFULL => get_string('campaigns:rightfull', 'auth_magic'),
        ];
        $mform->addElement('select', 'formposition', get_string('campaigns:formposition', 'auth_magic'), $positions);
        $mform->setDefault('formposition', campaign::FORM_POSITION_CENTER);
        $mform->addHelpButton('formposition', 'campaigns:formposition', 'auth_magic');

        // Restrict access.
        $mform->addElement('header', 'restrictaccesssection', get_string('campaigns:restrictaccesssection', 'auth_magic'));

        // Add by roles as autocomplete element.
        $rolelist = role_get_names(\context_system::instance());
        $roleoptions = [];
        foreach ($rolelist as $role) {
            $roleoptions[$role->id] = $role->localname;
        }
        $byroleswidget = $mform->addElement('autocomplete', 'restrictroles', get_string('campaignbyrole', 'auth_magic'),
                $roleoptions);
        $byroleswidget->setMultiple(true);
        $mform->addHelpButton('restrictroles', 'campaignbyrole', 'auth_magic');

        // Add context as select element.
        $rolecontext = [
            campaign::ANYCONTEXT => get_string('any'),
            campaign::SYSTEMCONTEXT => get_string('coresystem'),
        ];
        $mform->addElement('select', 'restrictrolecontext', get_string('campaignrolecontext', 'auth_magic'), $rolecontext);
        $mform->setDefault('restrictrolecontext', campaign::ANYCONTEXT);
        $mform->setType('restrictrolecontext', PARAM_INT);
        $mform->addHelpButton('restrictrolecontext', 'campaignrolecontext', 'auth_magic');

        // Add by cohorts as autocomplete element.
        $cohortslist = cohort_get_all_cohorts();
        $cohortoptions = $cohortslist['cohorts'];
        if ($cohortoptions) {
            array_walk($cohortoptions, function(&$value) {
                $value = $value->name;
            });
        }
        $bycohortswidget = $mform->addElement('autocomplete', 'restrictcohorts', get_string('campaignbycohort', 'auth_magic'),
                $cohortoptions);
        $bycohortswidget->setMultiple(true);
        $mform->addHelpButton('restrictcohorts', 'campaignbycohort', 'auth_magic');

        // Add operator as select element.
        $operatoroptions = [
                campaign::ANY => get_string('any'),
                campaign::ALL => get_string('all'),
        ];
        $mform->addElement('select', 'restrictcohortoperator', get_string('campaignoperator', 'auth_magic'), $operatoroptions);
        $mform->setDefault('restrictcohortoperator', campaign::ANY);
        $mform->setType('restrictcohortoperator', PARAM_INT);
        $mform->addHelpButton('restrictcohortoperator', 'campaignoperator', 'auth_magic');

        // Security section.
        $mform->addElement('header', 'securitysection', get_string('campaigns:securitysection', 'auth_magic'));

        // Add the reCAPTCHA element.
        $options = [
            campaign::DISABLE => get_string('no'),
        ];

        if (!empty($CFG->recaptchaprivatekey) && !empty($CFG->recaptchapublickey)) {
            $options += [
                campaign::ENABLE => get_string('yes'),
            ];
        }

        if (empty($CFG->recaptchaprivatekey) || empty($CFG->recaptchapublickey)) {
            $captchaconfig = new \moodle_url('/admin/settings.php', ['section' => 'manageauths']);
            $mform->addElement('static', 'customint2_text', '',
                \html_writer::span(get_string('nocaptchaavilable', 'auth_magic',
                    $captchaconfig->out(false) . "#admin-recaptchapublickey"), 'alert alert-info'));
        }
        $mform->addElement('select', 'recaptcha', get_string('campaigns:recaptcha', 'auth_magic'), $options);
        $mform->setDefault('recaptcha', campaign::DISABLE);
        $mform->addHelpButton('recaptcha', 'campaigns:recaptcha', 'auth_magic');

        // Add the require email confirmation element.
        $options = [
            campaign::DISABLE => get_string('no'),
            campaign::ENABLE => get_string('yes'),
            campaign::PARTIAL => get_string('campaigns:partial', 'auth_magic'),
        ];
        $mform->addElement('select', 'emailconfirm', get_string('campaigns:emailconfirm', 'auth_magic'), $options);
        $mform->setDefault('emailconfirm', campaign::PARTIAL);
        $mform->addHelpButton('emailconfirm', 'campaigns:emailconfirm', 'auth_magic');

        // Payment method.
        $mform->addElement('header', 'paymentsection', get_string('campaigns:payment', 'auth_magic'));

        $options = [
            'free' => get_string('campaigns:strfree', 'auth_magic'),
        ];

        if (auth_magic_get_payment_accounts()) {
            $options += [
                'paid' => get_string('campaigns:strpaid', 'auth_magic'),
            ];
        }

        // Payment type.
        $mform->addElement('select', 'paymenttype', get_string('campaigns:paymenttype', 'auth_magic'), $options);
        $mform->addHelpButton('paymenttype', 'campaigns:paymenttype', 'auth_magic');

        // Fee.
        $mform->addElement('text', 'paymentfee', get_string('campaigns:paymentfee', 'auth_magic'));
        $mform->addHelpButton('paymentfee', 'campaigns:paymentfee', 'auth_magic');
        $mform->setType('paymentfee', PARAM_FLOAT);
        $mform->hideIf('paymentfee', 'paymenttype', 'eq', 'free');

        // Currency.
        $paypalcurrencies = enrol_get_plugin('paypal')->get_currencies();
        $mform->addElement('select', 'paymentcurrency', get_string('campaigns:currency', 'auth_magic'), $paypalcurrencies);
        $mform->addHelpButton('paymentcurrency', 'campaigns:currency', 'auth_magic');
        $mform->setDefault('paymentcurrency', 'USD');
        $mform->hideIf('paymentcurrency', 'paymenttype', 'eq', 'free');

        // Account.
        if (auth_magic_get_payment_accounts()) {
            $mform->addElement('select', 'paymentaccount', get_string('campaigns:paymentaccount', 'auth_magic'),
                auth_magic_get_payment_accounts());
            $mform->addHelpButton('paymentaccount', 'campaigns:paymentaccount', 'auth_magic');
            $mform->hideIf('paymentaccount', 'paymenttype', 'eq', 'free');
        } else {
            $mform->addElement('static', 'customint1_text', '',
                \html_writer::span(get_string('noaccountsavilable', 'payment'), 'alert alert-danger'));
            $mform->addElement('hidden', 'customint1');
            $mform->setType('customint1', PARAM_INT);
            $mform->hideIf('customint1', 'paymenttype', 'eq', 'free');
        }

        // Get cohorts.
        $cohortslist = \cohort_get_all_cohorts();
        $cohorts = $cohortslist['cohorts'];
        if ($cohorts) {
            array_walk($cohorts, function(&$value) {
                $value = $value->name;
            });
        }

        // Get global roles.
        $roles = get_roles_for_contextlevels(CONTEXT_SYSTEM);
        list($insql, $inparams) = $DB->get_in_or_equal(array_values($roles));
        $roles = $DB->get_records_sql("SELECT * FROM {role} WHERE id $insql", $inparams);
        $systemroles[0] = get_string('disabled', 'auth_magic');
        $systemroles += role_fix_names($roles, null, ROLENAME_ALIAS, true);

        // Expiry section.
        $mform->addElement('header', 'expirysection', get_string('campaigns:expirysection', 'auth_magic'));

        // Expiry time.
        $mform->addElement('duration', 'expirytime', get_string('campaigns:expirytiondate', 'auth_magic'),
            ['units' => [HOURSECS, DAYSECS, WEEKSECS], 'optional' => true]);
        $mform->setType('expirytime', PARAM_INT);

        $mform->addElement('advcheckbox', 'expirysuspenduser', get_string('campaign:suspenduser',
            'auth_magic'), 'Default: false', null, [0, 1]);

        $mform->addElement('advcheckbox', 'expirydeleteduser', get_string('campaign:deleteuser',
            'auth_magic'), 'Default: false', null, [0, 1]);

        $selectcohorts = array_merge([0 => get_string('disabled', 'auth_magic')], $cohorts);
        $mform->addElement('select', 'expiryassigncohorts', get_string('campaign:expiryassigncohorts',
            'auth_magic'), $selectcohorts);
        $mform->setType('expiryassigncohorts', PARAM_INT);

        $mform->addElement('select', 'expiryremovecohorts', get_string('campaign:expiryremovecohorts',
            'auth_magic'), $selectcohorts);
        $mform->setType('expiryremovecohorts', PARAM_INT);

        $mform->addElement('select', 'expiryunassignglobalrole', get_string('campaign:unassignglobalrole',
            'auth_magic'), $systemroles);
        $mform->setType('expiryunassignglobalrole', PARAM_INT);

        $expirybefore = [
            '3month' => get_string('monthbefore3', 'auth_magic'),
            '1month' => get_string('monthbefore1', 'auth_magic'),
            '3week' => get_string('weekbefore3', 'auth_magic'),
            '2week' => get_string('weekbefore2', 'auth_magic'),
            '1week' => get_string('weekbefore1', 'auth_magic'),
            '3day' => get_string('daybefore3', 'auth_magic'),
            '1day' => get_string('daybefore1', 'auth_magic'),
            'upon' => get_string('uponbefore', 'auth_magic'),
        ];

        $select = $mform->addElement('select', 'expirybeforenotify', get_string('campaign:expirybeforenotify',
            'auth_magic'), $expirybefore);
        $select->setMultiple(true);

        // After submission setion.
        $mform->addElement('header', 'aftersubmisson', get_string('campaigns:redirectaftersubmisson',
            'auth_magic'));
        $options = [
            'noredirect' => get_string('campaigns:noredirect', 'auth_magic'),
            'redirectsummary' => get_string('campaigns:redirectsummary', 'auth_magic'),
            'redirecturl' => get_string('campaigns:redirecturl', 'auth_magic'),
        ];

        // Redirect after submisson.
        $mform->addElement('select', 'redirectaftersubmisson', get_string('campaigns:redirectaftersubmisson',
            'auth_magic'), $options);
        $mform->addHelpButton('redirectaftersubmisson', 'campaigns:redirectaftersubmisson',
            'auth_magic');
        $mform->setDefault('redirectaftersubmisson', 'noredirect');

        $mform->addElement('text', 'submissonredirecturl', get_string('campaigns:redirecturl', 'auth_magic'));
        $mform->addHelpButton('submissonredirecturl', 'campaigns:redirecturl', 'auth_magic');
        $mform->setType('submissonredirecturl', PARAM_URL);
        $mform->hideIf('submissonredirecturl', 'emailconfirm', 'eq', campaign::ENABLE);
        $mform->hideIf('submissonredirecturl', 'redirectaftersubmisson', 'neq', 'redirecturl');

        // Summary page content.
        $mform->addElement('editor', 'submissioncontent_editor', get_string('campaigns:summarypagecontent', 'auth_magic'),
            '', $editoroptions);
        $mform->addHelpButton('submissioncontent_editor', 'campaigns:summarypagecontent', 'auth_magic');
        $mform->setType('submissioncontent_editor', PARAM_RAW);
        $this->formfield_placeholders($mform);

        $options = [
            'disabled' => get_string('disabled', 'auth_magic'),
            'information' => get_string('information', 'auth_magic'),
            'optionalin' => get_string('optional_in', 'auth_magic'),
            'optionalout' => get_string('optional_out', 'auth_magic'),
            'fulloptionout' => get_string('full_option_out', 'auth_magic'),
        ];

        // Approval section.
        $mform->addElement('header', 'approval', get_string('approval', 'auth_magic'));
        $mform->addElement('select', 'approvaltype', get_string('campaigns:approvaltype', 'auth_magic'),
        $options);
        $mform->addHelpButton('approvaltype', 'campaigns:approvaltype', 'auth_magic');
        $mform->setDefault('approvaltype', 'disabled');

        $mform->addElement('autocomplete', 'approvalroles', get_string('campaigns:approvalroles', 'auth_magic'),
            auth_magic_get_roles_options([CONTEXT_SYSTEM, CONTEXT_USER] , false), ['multiple' => true]);
        $mform->addHelpButton('approvalroles', 'campaigns:approvalroles', 'auth_magic');

        // Campaign course.
        $courses = ['0' => get_string('disabled', 'auth_magic')];
        $records = get_user_capability_course("moodle/course:update", $USER->id, true, 'fullname');
        if ($records) {
            foreach ($records as $record) {
                if ($record->id != $SITE->id) {
                    $course = get_course($record->id);
                    $courses[$record->id] = format_string($course->fullname);
                }
            }
        }

        $mform->addElement('header', 'campaigncourseheading', get_string('campaigns:campaigncourse', 'auth_magic'));

        $mform->addElement('select', 'campaigncourse', get_string('campaigns:campaigncourse', 'auth_magic'), $courses,
            ['noselectionstring' => get_string('noselection', 'form')]);
        $mform->addHelpButton('campaigncourse', 'campaigns:campaigncourse', 'auth_magic');

        $mform->addElement('select', 'coursestudentrole', get_string('campaigns:coursestudentrole', 'auth_magic'),
            auth_magic_get_roles_options([CONTEXT_COURSE] , true));
        $mform->addHelpButton('coursestudentrole', 'campaigns:coursestudentrole', 'auth_magic');
        $mform->hideIf('coursestudentrole', 'campaigncourse', 'eq', '0');

        $mform->addElement('select', 'courseparentrole', get_string('campaigns:courseparentrole', 'auth_magic'),
            auth_magic_get_roles_options([CONTEXT_COURSE] , true));
        $mform->addHelpButton('courseparentrole', 'campaigns:courseparentrole', 'auth_magic');
        $mform->hideIf('courseparentrole', 'campaigncourse', 'eq', '0');

        $options = [
            'disabled' => get_string('disabled', 'auth_magic'),
            'campaign' => get_string('strcampaign', 'auth_magic'),
            'peruser' => get_string('peruser', 'auth_magic'),
        ];
        $mform->addElement('select', 'campaigngroups', get_string('campaigns:groups', 'auth_magic'), $options);
        $mform->addHelpButton('campaigngroups', 'campaigns:groups', 'auth_magic');
        $mform->hideIf('campaigngroups', 'campaigncourse', 'eq', '0');

        $mform->addElement('checkbox', 'groupmessaging', get_string('campaigns:groupmessaging', 'auth_magic'));
        $mform->addHelpButton('groupmessaging', 'campaigns:groupmessaging', 'auth_magic');
        $mform->setDefault('checkbox', false);
        $mform->hideIf('groupmessaging', 'campaigngroups', 'eq', 'disabled');

        $mform->addElement('checkbox', 'groupenrolmentkey', get_string('campaigns:groupenrolmentkey', 'auth_magic'));
        $mform->addHelpButton('groupenrolmentkey', 'campaigns:groupenrolmentkey', 'auth_magic');
        $mform->setDefault('checkbox', false);
        $mform->hideIf('groupenrolmentkey', 'campaigngroups', 'eq', 'disabled');

        $mform->addElement('text', 'groupcapacity', get_string('campaigns:groupcapacity', 'auth_magic'));
        $mform->setType('groupcapacity', PARAM_INT);
        $mform->addHelpButton('groupcapacity', 'campaigns:groupcapacity', 'auth_magic');
        $mform->addRule('groupcapacity', get_string('error'), 'numeric');
        $mform->hideIf('groupcapacity', 'campaigngroups', 'eq', 'disabled');

        $groupings = [
            '0' => get_string('disabled', 'auth_magic'),
        ];
        $groupings += auth_magic_get_all_groupings();
        $mform->addElement('select', 'campaigngrouping', get_string('campaigns:grouping', 'auth_magic'), $groupings);
        $mform->addHelpButton('campaigngrouping', 'campaigns:grouping', 'auth_magic');
        $mform->hideIf('campaigngrouping', 'campaigngroups', 'eq', 'disabled');

        // Form field section.
        $mform->addElement('header', 'formfieldsection', get_string('campaigns:formfieldsection', 'auth_magic'));
        $childusers = auth_magic_get_parent_child_users($USER->id, false, false);
        if ($childusers) {
            list($usersql, $userparams) = $DB->get_in_or_equal($childusers, SQL_PARAMS_NAMED);
            $records = $DB->get_records_sql("SELECT userid FROM {auth_magic_campaigns_users} WHERE userid $usersql", $userparams);
            if ($records) {
                $childusers = user_get_users_by_id(array_keys($records));
                $mform->addElement('select', 'linkfields', get_string('campaigns:child_formfield', 'auth_magic'),
                    auth_magic_get_usernames_choices($childusers));
                $mform->addHelpButton('linkfields', 'campaigns:child_formfield', 'auth_magic');
            }
        }

        // Authentication method.
        $mform->addElement('select', 'auth', get_string('campaigns:type_auth', 'auth_magic'), auth_magic_get_enabled_auth());
        $mform->addHelpButton('auth', 'chooseauthmethod', 'auth');

        $options = [
            'disabled' => get_string('disabled', 'auth_magic'),
            'required' => get_string('campaigns:required', 'auth_magic'),
            'optional' => get_string('campaigns:optional', 'auth_magic'),
            'strict' => get_string('campaigns:strict', 'auth_magic'),
        ];
        $mform->addElement('select', 'courseenrolmentkey', get_string('campaigns:courseenrolmentkey', 'auth_magic'),
            $options);
        $mform->addHelpButton('courseenrolmentkey', 'campaigns:courseenrolmentkey', 'auth_magic');

        // Standard fields.
        $userfieldsdata = \campaign_helper::get_magic_profile_fileds_data();
        foreach ($userfieldsdata['profilefields'] as $field => $fieldname) {
            $standardfield = [];
            $userfieldoptions = $userfieldsdata['fieldoptions'];
            if ($field == 'standard_email') {
                $userfieldoptions = [
                    MAGICCAMPAIGNSREQUIRED => get_string('campaigns:required', 'auth_magic'),
                ];
            }
            // Field option.
            if ($field == 'standard_password') {
                $standardfield[] = &$mform->createElement('select', $field . '_option',
                get_string('campaigns:'.$field, 'auth_magic'), $userfieldsdata['customfieldoptions'][$field],
                    ["class" => "campaign_fieldoption_fields", 'data-field' => $field]);
            } else {
                $standardfield[] = &$mform->createElement('select', $field . '_option',
                get_string('campaigns:'.$field, 'auth_magic'), $userfieldoptions, ["class" => "campaign_fieldoption_fields",
                    'data-field' => $field]);
            }

            // Other field and other values.
            if ($field == 'standard_lang') {
                $purpose = user_edit_map_field_purpose(-1, 'lang');
                $translations = get_string_manager()->get_list_of_translations();
                $standardfield[] = &$mform->createElement('select', $field, '', $translations, $purpose);
                $mform->setDefault($field, $CFG->lang);
                $otherfields = \campaign_helper::get_profile_otherfields('menu');
                if ($otherfields) {
                    $standardfield[] = &$mform->createElement('select', $field.'_otherfield', '',
                        $otherfields, ["class" => "campaign_otherform_fields", 'data-field' => $field]);
                    $standardfield[] = &$mform->createElement('hidden', $field.'_otherfield_prevalue', "");
                    $mform->setType($field.'_otherfield_prevalue', PARAM_ALPHANUMEXT);
                    foreach ($otherfields as $otherfield => $value) {
                        $standardfield[] = &$mform->createElement('hidden', $field.'_otherfield_value[]',
                            $otherfield, ['data-value' => $value]);
                        $mform->setType($field.'_otherfield_value[]', PARAM_ALPHANUMEXT);
                    }
                }
            } else if ($field == 'standard_country') {
                $country = get_string_manager()->get_list_of_countries();
                $defaultcountry[''] = get_string('selectacountry');
                $country = array_merge($defaultcountry, $country);
                $standardfield[] = &$mform->createElement('select', $field, '', $country);
                $standardfield[] = &$mform->createElement('hidden', $field.'_otherfield_prevalue', "");
                $mform->setType($field.'_otherfield_prevalue', PARAM_ALPHANUMEXT);
                if (!empty($CFG->country)) {
                    $mform->setDefault($field, $CFG->country);
                } else {
                    $mform->setDefault($field, '');
                }
                $otherfields = \campaign_helper::get_profile_otherfields('menu');
                if ($otherfields) {
                    $standardfield[] = &$mform->createElement('select', $field.'_otherfield', '',
                        $otherfields,  ["class" => "campaign_otherform_fields", 'data-field' => $field]);
                    foreach ($otherfields as $otherfield => $value) {
                        $standardfield[] = &$mform->createElement('hidden', $field.'_otherfield_value[]',
                            $otherfield, ['data-value' => $value]);
                        $mform->setType($field.'_otherfield_value[]', PARAM_ALPHANUMEXT);
                    }
                }
            } else if ($field != 'standard_password') {
                $standardfield[] = &$mform->createElement('text', $field);
                $mform->setType($field, PARAM_ALPHANUMEXT);
                $otherfields = $userfieldsdata['profilefields'] + \campaign_helper::get_profile_otherfields('text');
                unset($otherfields[$field]);
                $standardfield[] = &$mform->createElement('select', $field.'_otherfield', '', $otherfields,
                    ["class" => "campaign_otherform_fields", 'data-field' => $field]);
                $standardfield[] = &$mform->createElement('hidden', $field.'_otherfield_prevalue', "");
                $mform->setType($field.'_otherfield_prevalue', PARAM_ALPHANUMEXT);
                foreach ($otherfields as $otherfield => $value) {
                    $standardfield[] = &$mform->createElement('hidden', $field.'_otherfield_value[]',
                        $otherfield, ['data-value' => $value]);
                    $mform->setType($field.'_otherfield_value[]', PARAM_ALPHANUMEXT);
                }
            }
            $mform->setDefault($field . '_option', $userfieldsdata['defaultvalues'][$field]);
            $mform->disabledIf($field, $field . '_option', 'neq', MAGICCAMPAIGNSHIDDENPROVIDEVALUE);
            $mform->disabledIf($field.'_otherfield', $field. "_option", 'neq', MAGICCAMPAIGNSREQUIREDHIDDENOTHERFIELD);
            $mform->addGroup($standardfield, $field . 'standardfield', get_string('campaigns:'.$field, 'auth_magic'),
                [' '], false);
            $mform->addHelpButton($field . 'standardfield', 'strstandardprofilefield', 'auth_magic');
            $mform->closeHeaderBefore('formfieldsection');
        }
        // Custom profile fields.
        foreach (profile_get_custom_fields() as $customfield) {
            if ($customfield->visible != PROFILE_VISIBLE_NONE) {
                $field = 'profile_field_' . $customfield->shortname;
                $mform->addElement('html', '<div class=custom-field-group form-group fitem>');
                // Option.
                $userfieldoptions = $userfieldsdata['fieldoptions'];
                if ($customfield->required) {
                    $userfieldoptions = [
                        MAGICCAMPAIGNSREQUIRED => get_string('campaigns:required', 'auth_magic'),
                    ];
                }

                if ($customfield->forceunique) {
                    $userfieldoptions = [
                        MAGICCAMPAIGNSREQUIRED => get_string('campaigns:required', 'auth_magic'),
                        MAGICCAMPAIGNSOPTIONAL => get_string('campaigns:optional', 'auth_magic'),
                    ];
                }
                $mform->addElement('select', $field . '_option', $customfield->name, $userfieldoptions,
                    ['id' => $customfield->id]);
                $mform->addHelpButton($field . '_option', 'strcustomprofilefield', 'auth_magic');
                $mform->setDefault($field . '_option', $userfieldsdata['defaultvalues']['profile_field']);
                // Provide value.
                $fileddata = \campaign_helper::profile_get_user_field($customfield->datatype, $customfield->id);
                $fileddata->edit_field($mform);
                $mform->disabledIf('profile_field_' . $customfield->shortname, $field. '_option',
                    'neq', MAGICCAMPAIGNSHIDDENPROVIDEVALUE);
                // Relate to the other field.
                $otherfieldslist = \campaign_helper::get_profile_otherfields($customfield->datatype);
                unset($otherfieldslist['profile_field_'. $customfield->shortname]);
                $mform->addElement('select', 'profile_field_' . $customfield->shortname. '_otherfield', '', $otherfieldslist,
                    ['class' => 'campaign_otherform_fields', 'data-field' => 'profile_field_' . $customfield->shortname]);
                $mform->disabledIf('profile_field_' . $customfield->shortname. '_otherfield', $field. '_option',
                    'neq', MAGICCAMPAIGNSREQUIREDHIDDENOTHERFIELD);
                $mform->addHelpButton($field.'_option', 'strcustomprofilefield', 'auth_magic');
                $mform->addElement('html', '</div>');
            }
        }

        // Assignments section.
        $mform->addElement('header', 'assignmentssection', get_string('campaigns:assignmentssection', 'auth_magic'));

        $cohort = $mform->addElement('autocomplete', 'cohorts', get_string('campaigns:cohorts', 'auth_magic'), $cohorts);
        $cohort->setMultiple(true);
        $mform->addHelpButton('cohorts', 'campaigns:cohorts', 'auth_magic');

        // Global role based on system context.
        $globalrole = $mform->addElement('select', 'globalrole',
            get_string('campaigns:globalrole', 'auth_magic'), $systemroles);
        $mform->setType('globalrole', PARAM_INT);
        $globalrole->setMultiple(false);
        $mform->addHelpButton('globalrole', 'campaigns:globalrole', 'auth_magic');

        // Campaign owner account.
        $users = $DB->get_records_sql("SELECT *
                                    FROM {user}
                                    WHERE confirmed = 1 AND deleted = 0 AND id <> ?", [$CFG->siteguest]);
        $mform->addElement('autocomplete', 'campaignowner', get_string('campaigns:owneraccount', 'auth_magic'),
            auth_magic_get_usernames_choices($users));
        $mform->addHelpButton('campaignowner', 'campaigns:campaignowner', 'auth_magic');

        // Privacy policy section.
        $mform->addElement('header', 'privacypolicysection', get_string('campaigns:privacypolicysection', 'auth_magic'));

        // Add display consent option field.
        $mform->addElement('advcheckbox', 'privacypolicy', get_string('campaigns:privacypolicy', 'auth_magic'),
            'Default: false', null, [0, 1]);
        $mform->addHelpButton('privacypolicy', 'campaigns:privacypolicy', 'auth_magic');

        // Add consent statement field.
        $mform->addElement('editor', 'consentstatement_editor',
            get_string('campaigns:consentstatement', 'auth_magic'), '', $editoroptions);
        $mform->addHelpButton('consentstatement_editor', 'campaigns:consentstatement', 'auth_magic');

        // ... Welcome message section.
        $mform->addElement('header', 'welcomemessagesection_editor', get_string('campaigns:welcomemessagesection', 'auth_magic'));

        // Add option for send welcome message to new accounts.
        $mform->addElement('advcheckbox', 'welcomemessage', get_string('campaigns:welcomemessage', 'auth_magic'),
            'Default: false', null, [0, 1]);
        $mform->addHelpButton('welcomemessage', 'campaigns:welcomemessage', 'auth_magic');

        // Add welcome message content field.
        $mform->addElement('editor', 'welcomemessagecontent_editor', get_string('campaigns:welcomemessagecontent', 'auth_magic'),
            '', $editoroptions);
        $mform->addHelpButton('welcomemessagecontent_editor', 'campaigns:welcomemessagecontent', 'auth_magic');
        $this->message_placeholders($mform);

        // Add option for message send to the campaign owner also.
        $mform->addElement('advcheckbox', 'welcomemessageowner', get_string('campaigns:welcomemessageowner', 'auth_magic'),
            'Default: false', null, [0, 1]);
        $mform->addHelpButton('welcomemessageowner', 'campaigns:welcomemessageowner', 'auth_magic');

        // Follow up message section.
        $mform->addElement('header', 'followupmessagesection', get_string('campaigns:followupmessagesection', 'auth_magic'));

        // Add option for send follow up message to new accounts.
        $mform->addElement('advcheckbox', 'followupmessage', get_string('campaigns:followupmessage', 'auth_magic'),
            'Default: false', null, [0, 1]);
        $mform->addHelpButton('followupmessage', 'campaigns:followupmessage', 'auth_magic');

        // Add duration of follow up messages.
        $mform->addElement('text', 'followupmessagedelay', get_string('campaigns:messagedelay', 'auth_magic'),
            ['optional' => true]);
        $mform->setType('followupmessagedelay', PARAM_INT);
        $mform->addRule('followupmessagedelay', get_string('error'), 'numeric', '', 'client');
        $mform->addHelpButton('followupmessagedelay', 'campaigns:messagedelay', 'auth_magic');

        // Add Follow up message content field.
        $mform->addElement('editor', 'followupmessagecontent_editor', get_string('campaigns:followupmessagecontent', 'auth_magic'),
        '', $editoroptions);
        $mform->addHelpButton('followupmessagecontent_editor', 'campaigns:followupmessagecontent', 'auth_magic');
        $this->message_placeholders($mform);

        // Add option for message send to the campaign owner also.
        $mform->addElement('advcheckbox', 'followupmessageowner', get_string('campaigns:followupmessageowner', 'auth_magic'),
            'Default: false', null, [0, 1]);
        $mform->addHelpButton('followupmessageowner', 'campaigns:followupmessageowner', 'auth_magic');

        // Welcome and follow up message placholders.
        $PAGE->requires->js_call_amd('auth_magic/messagevars', 'init');
        $this->add_action_buttons();
    }

    /**
     * Add email placeholder fields in form fields.
     *
     * @param  \form $mform
     * @return void
     */
    public function message_placeholders(&$mform) {
        global $OUTPUT;

        $vars = \campaign_message_vars::vars();
        $templatecontext['messagevars'] = $vars;
        $mform->addElement('html', $OUTPUT->render_from_template('auth_magic/messagevars', $templatecontext));
    }


    /**
     * Add email placeholder fields in form fields.
     *
     * @param  \form $mform
     * @return void
     */
    public function formfield_placeholders(&$mform) {
        global $OUTPUT;
        $userfieldsdata = \campaign_helper::get_magic_profile_fileds_data();
        $fields = array_keys($userfieldsdata['profilefields']);;
        $vars = \campaign_formfield_vars::vars($fields);
        $templatecontext['messagevars'] = $vars;
        $mform->addElement('html', $OUTPUT->render_from_template('auth_magic/messagevars', $templatecontext));
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
        if ($data['paymenttype'] == 'paid' && isset($data['customint1']) &&
                (!$data['customint1']
                    || !array_key_exists($data['customint1'], \core_payment\helper::get_payment_accounts_menu($context)))) {
            $errors['paymenttype'] = 'Enrolments can not be enabled without specifying the payment account';
        }
        return $errors;
    }
}
