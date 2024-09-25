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
 * Admin settings and defaults
 *
 * @package auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot."/auth/magic/lib.php");
require_once($CFG->dirroot. "/auth/magic/classes/roleassignment.php");

    $ADMIN->add('accounts', new admin_externalpage('auth_magic_quickregistration',
            get_string('quickregistration', 'auth_magic'),
            new moodle_url('/auth/magic/registration.php'), ['auth/magic:cansitequickregistration']));

    $ADMIN->add('accounts', new admin_externalpage('auth_magic_loginlinks',
        get_string('listofmagiclink', 'auth_magic'),
        new moodle_url('/auth/magic/listusers.php')));

    // The 'Publish as LTI tool' node is a category.
    $ADMIN->add('authsettings', new admin_category('authmagicsettings', new lang_string('pluginname', 'auth_magic'),
        $this->is_enabled() === false));

    // General settings.
    $settings = new admin_settingpage($section, get_string('generalsettings', 'auth_magic'),
        'moodle/site:config', $this->is_enabled() === false);

    // Support password.
    $name = "auth_magic/supportpassword";
    $title = get_string("strsupportpassword", "auth_magic");
    $setting = new admin_setting_configcheckbox($name, $title, "", 0);
    $settings->add($setting);

    // Magic login link expiry.
    $name = "auth_magic/loginexpiry";
    $title = get_string("loginexpiry", "auth_magic");
    $desc = "";
    $setting = new admin_setting_configduration($name, $title, $desc, 10 * MINSECS);
    $settings->add($setting);

    // Magic invitation link expiry.
    $name = "auth_magic/invitationexpiry";
    $title = get_string("invitationexpiry", "auth_magic");
    $desc = "";
    $setting = new admin_setting_configduration($name, $title, $desc, 1 * HOURSECS);
    $settings->add($setting);


    // Magic invitation link expiry.
    $name = "auth_magic/loginkeytype";
    $title = get_string("loginkeytype", "auth_magic");
    $desc = get_string("loginkeytype_desc", "auth_magic");
    $options = [
        'once' => get_string('keyuseonce', 'auth_magic'),
        'more' => get_string('keyusemultiple', 'auth_magic'),
    ];
    $setting = new admin_setting_configselect($name, $title, $desc, 'once', $options);
    $settings->add($setting);


    // Allow user to use username to login option.
    $name = "auth_magic/loginoption";
    $title = get_string("loginoption", "auth_magic");
    $desc = get_string("loginoptiondesc", "auth_magic");
    $setting = new admin_setting_configcheckbox($name, $title, $desc, 0);
    $settings->add($setting);

    // Supported authentication method.
    $options = [
        0 => get_string('magiconly', 'auth_magic'),
        1 => get_string('anymethod', 'auth_magic'),
    ];
    $name = "auth_magic/authmethod";
    $title = get_string("strsupportauth", "auth_magic");
    $desc = "";
    $setting = new admin_setting_configselect($name, $title, $desc, 0, $options);
    $settings->add($setting);

    // Enrolment duration.
    $name = "auth_magic/enrolmentduration";
    $title = get_string("defaultenrolmentduration", "auth_magic");
    $desc = "";
    $setting = new admin_setting_configduration($name, $title, $desc, 0);
    $settings->add($setting);

    // Enrollment role.
    $options = get_default_enrol_roles(context_system::instance());
    $student = get_archetype_roles('student');
    $student = reset($student);
    $name = "auth_magic/enrolmentrole";
    $title = get_string("defaultenrolmentrole", "auth_magic");
    $desc = "";
    $setting = new admin_setting_configselect($name, $title, $desc, $student->id ?? null, $options);
    $settings->add($setting);


    // Owner account role.
    $options = [];
    $options[0] = get_string('none');
    $usercontextroles = get_roles_for_contextlevels(CONTEXT_USER);
    if ($usercontextroles) {
        list($rolesql, $roleparams) = $DB->get_in_or_equal($usercontextroles);
        $sql = "SELECT id, name FROM {role} WHERE id $rolesql";
        $roles = $DB->get_records_sql($sql, $roleparams);
        $options += array_column($roles, 'name', 'id');
    }
    $name = "auth_magic/owneraccountrole";
    $title = get_string("strowneraccountrole", "auth_magic");
    $desc = "";
    $setting = new admin_setting_configselect($name, $title, $desc, 0, $options);
    $settings->add($setting);


    // Magic login link button position.
    $name = "auth_magic/loginlinkbtnpostion";
    $title = get_string("loginlinkbtnpostion", "auth_magic");
    $desc = "";
    $options = [
        0 => get_string('belowusername', 'auth_magic'),
        1 => get_string('belowpassword', 'auth_magic'),
        2 => get_string('normal', 'auth_magic'),
    ];
    $setting = new admin_setting_configselect($name, $title, $desc, 2, $options);
    $settings->add($setting);


    // Fetch roles that have the capability to manage data requests.
    $capableroles = get_roles_with_capability("auth/magic:privilegeaccount");
    $capableroles = role_fix_names($capableroles);
    // Role(s) that map to the Data Protection Officer role. These are assignable roles with the capability to
    // manage data requests.
    $roles = [];
    foreach ($capableroles as $id => $role) {
            $roles[$id] = $role->localname;
    }

    // Magic login link button position.
    $name = "auth_magic/privilegedrole";
    $title = get_string("privilegedrole", "auth_magic");
    $desc = "";
    $default = ['value' => key($roles)];
    $setting = new admin_setting_configmultiselect($name, $title, $desc, $default, $roles);
    $settings->add($setting);

    $PAGE->requires->js_amd_inline("
        require(['core/form-autocomplete'], function(module) {
        module.enhance('#id_s_auth_magic_privilegedrole');
        }); ");

    // Footer link.
    $name = "auth_magic/loginfooter";
    $title = get_string("loginfooter", "auth_magic");
    $description = get_string('loginfooter_desc', 'auth_magic');
    $default = get_string('loginfooterdefault', 'auth_magic');
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $settings->add($setting);

    // Campaign owner account role.
    $options = [];
    $options[0] = get_string('none', 'auth_magic');
    $usercontextroles = get_roles_for_contextlevels(CONTEXT_USER);
    if ($usercontextroles) {
        list($rolesql, $roleparams) = $DB->get_in_or_equal($usercontextroles);
        $sql = "SELECT id, name, shortname FROM {role} WHERE id $rolesql";
        $roles = $DB->get_records_sql($sql, $roleparams);
        $options += array_column($roles, 'shortname', 'id');
    }

    // Campaign owner.
    $name = "auth_magic/campaignownerrole";
    $title = get_string("strcampaignownerrole", "auth_magic");
    $desc = get_string("strcampaignownerrole_desc", "auth_magic");
    $setting = new admin_setting_configselect($name, $title, $desc, 0, $options);
    $settings->add($setting);

    \auth_magic\roleassignment::include_admin_setting($settings);

    $ADMIN->add('authmagicsettings', $settings);

    // Campaign settings.
    $campaignsettings = new admin_settingpage('authmagiccampaign', get_string('managecampaign', 'auth_magic'),
        'moodle/site:config', $this->is_enabled() === false);

    $ADMIN->add('authmagicsettings', new admin_externalpage(
        'auth_magic_campaigns',
        get_string('managecampaign', 'auth_magic'),
        new moodle_url('/auth/magic/campaigns/manage.php')));


    // Signup settings.
    $signupsettings = new admin_settingpage('authmagicsignup', get_string('strmagicsignup', 'auth_magic'),
        'moodle/site:config', $this->is_enabled() === false);

    $name = "auth_magic/autocreateusers";
    $title = get_string("strautocreateusers", "auth_magic");
    $desc = get_string("strautocreateusers_desc", "auth_magic");
    $setting = new admin_setting_configcheckbox($name, $title, $desc, 0);
    $signupsettings->add($setting);

    $ADMIN->add('authmagicsettings', $signupsettings);

    $settings = null;
