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
 * Upgrade function.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Magic authentication upgrade method.
 * @param int $oldversion
 * @return bool
 */
function xmldb_auth_magic_upgrade($oldversion) {
    global $CFG, $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2023032000) {
        // Define table auth_magic_loginlinks to be created.
        $table = new xmldb_table('auth_magic_loginlinks');
        $field = new xmldb_field('manualexpiry', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'loginexpiry');

        // Conditionally launch add field description_format.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Magic savepoint reached.
        upgrade_plugin_savepoint(true, 2023032000, 'auth', 'magic');
    }

    if ($oldversion < 2023052203) {

        // Define table auth_magic_campaigns to be created.
        $table = new xmldb_table('auth_magic_campaigns');

        // Adding fields to table auth_magic_campaigns.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('comments', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('commentsformat', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('capacity', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('visibility', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('startdate', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('enddate', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'startdate');
        $table->add_field('password', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('code', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_field('token', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('logo', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('headerimage', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('backgroundimage', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('transparentform', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('displayowerprofile', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('formposition', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('recaptcha', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('emailconfirm', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('redirectaftersubmisson', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('submissioncontent', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('submissioncontentformat', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('submissonredirecturl', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('auth', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('cohorts', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('globalrole', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('campaignowner', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('privacypolicy', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('consentstatement', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('consentstatementformat', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('welcomemessage', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('welcomemessagecontent', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('welcomemessagecontentformat', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('welcomemessageowner', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('followupmessage', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('followupmessagedelay', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('followupmessagecontent', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('followupmessagecontentformat', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('followupmessageowner', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '11', null, null, null, null);

        // Adding keys to table auth_magic_campaigns.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for auth_magic_campaigns.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table auth_magic_campaigns_fields to be created.
        $table = new xmldb_table('auth_magic_campaigns_fields');

        // Adding fields to table auth_magic_campaigns_fields.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('campaignid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('field', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('fieldoption', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('customvalue', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('otherfieldvalue', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('fieldtype', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '11', null, null, null, null);

        // Adding keys to table auth_magic_campaigns_fields.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for auth_magic_campaigns_fields.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table auth_magic_campaigns_users to be created.
        $table = new xmldb_table('auth_magic_campaigns_users');

        // Adding fields to table auth_magic_campaigns_users.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('campaignid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('deleted', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('followup', XMLDB_TYPE_INTEGER, '11', null, null, null, 0);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '11', null, null, null, null);

        // Adding keys to table auth_magic_campaigns_users.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for auth_magic_campaigns_users.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Magic savepoint reached.
        upgrade_plugin_savepoint(true, 2023052203, 'auth', 'magic');
    }

    if ($oldversion < 2023052600) {
        // Define table auth_magic_signupkey to be created.
        $table = new xmldb_table('auth_magic_signupkey');
        // Adding fields to table auth_magic_signupkey.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_CHAR, '128', null, XMLDB_NOTNULL, null, null);
        $table->add_field('validuntil', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '11', null, null, null, null);

        // Adding keys to table auth_magic_signupkey.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for auth_magic_signupkey.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Magic savepoint reached.
        upgrade_plugin_savepoint(true, 2023052600, 'auth', 'magic');
    }

    if ($oldversion < 2023101900) {

        // Define table auth_magic_campaigns_fields to be created.
        $table = new xmldb_table('auth_magic_campaigns_payment');

        // Adding fields to table auth_magic_campaigns_payment.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('campaignid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('fee', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        $table->add_field('currency', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('paymentaccount', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '11', null, null, null, null);

        // Adding keys to table auth_magic_campaigns_fields.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for auth_magic_campaigns_fields.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Magic savepoint reached.
        upgrade_plugin_savepoint(true, 2023101900, 'auth', 'magic');
    }

    if ($oldversion < 2023102101) {

        // Define table auth_magic_campaigns_fields to be created.
        $table = new xmldb_table('auth_magic_payment_logs');

        // Adding fields to table auth_magic_payment_logs.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('campaignid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('paymentid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '11', null, null, null, null);

        // Adding keys to table auth_magic_campaigns_fields.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for auth_magic_campaigns_fields.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Magic savepoint reached.
        upgrade_plugin_savepoint(true, 2023102101, 'auth', 'magic');
    }

    if ($oldversion < 2023102601) {
        // Define table auth_magic_loginlinks to be created.
        $table = new xmldb_table('auth_magic_campaigns');
        $field = new xmldb_field('auth', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'formposition');

        // Conditionally launch add field description_format.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Magic savepoint reached.
        upgrade_plugin_savepoint(true, 2023102601, 'auth', 'magic');
    }

    if ($oldversion < 2023103000) {
        $table = new xmldb_table('auth_magic_campaigns');
        $field = new xmldb_field('recaptcha', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'formposition');

        // Conditionally launch add field description_format.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('emailconfirm', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'formposition');
        // Conditionally launch add field description_format.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Magic savepoint reached.
        upgrade_plugin_savepoint(true, 2023103000, 'auth', 'magic');
    }

    if ($oldversion < 2023110703) {

        $table = new xmldb_table('auth_magic_campaigns');
        $field = new xmldb_field('redirectaftersubmisson', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'emailconfirm');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('submissioncontent', XMLDB_TYPE_TEXT, '255', null, null, null, null, 'redirectaftersubmisson');
        // Conditionally launch add field submissioncontent.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('submissioncontentformat', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'submissioncontent');
        // Conditionally launch add field submissioncontent.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('submissonredirecturl', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'submissioncontentformat');
        // Conditionally launch add field submissioncontent.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define table auth_magic_roleassignments to be created.
        $table = new xmldb_table('auth_magic_roleassignments');

        // Adding fields to table auth_magic_roleassignments.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('parent_userid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('roleid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('field', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, null, null, 1);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '18', null, null, null, null);

        // Adding keys to table auth_magic_roleassignments.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for auth_magic_roleassignments.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Magic savepoint reached.
        upgrade_plugin_savepoint(true, 2023110703, 'auth', 'magic');
    }

    if ($oldversion < 2023112504) {

        // Magic savepoint reached.
        $table = new xmldb_table('auth_magic_payment_logs');
        $field = new xmldb_field('paymentid', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'userid');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2023112504, 'auth', 'magic');
    }

    if ($oldversion < 2023112505) {

        // Magic savepoint reached.
        $table = new xmldb_table('auth_magic_campaigns');
        // Approval type.
        $field = new xmldb_field('approvaltype', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'submissonredirecturl');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Approval Roles.
        $field = new xmldb_field('approvalroles', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'approvaltype');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Campaign course.
        $field = new xmldb_field('campaigncourse', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'approvalroles');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Course Enrolment key.
        $field = new xmldb_field('courseenrolmentkey', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'campaigncourse');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Course Studentrole.
        $field = new xmldb_field('coursestudentrole', XMLDB_TYPE_INTEGER, 11, null, null, null, null, 'courseenrolmentkey');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Course Parentrole.
        $field = new xmldb_field('courseparentrole', XMLDB_TYPE_INTEGER, 11, null, null, null, null, 'coursestudentrole');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Groups.
        $field = new xmldb_field('campaigngroups', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'courseparentrole');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Grouping.
        $field = new xmldb_field('campaigngrouping', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'campaigngroups');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Group Messaging.
        $field = new xmldb_field('groupmessaging', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'campaigngrouping');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Group Enrolment key.
        $field = new xmldb_field('groupenrolmentkey', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'groupmessaging');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Group capacity.
        $field = new xmldb_field('groupcapacity', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'groupenrolmentkey');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2023112505, 'auth', 'magic');
    }

    if ($oldversion < 2023112506) {

        // Magic savepoint reached.
        $table = new xmldb_table('auth_magic_campaigns');
        // Course Enrolment key.
        $field = new xmldb_field('courseenrolmentkey', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'campaigncourse');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2023112506, 'auth', 'magic');
    }

    if ($oldversion < 2023112507) {

        // Magic savepoint reached.
        $table = new xmldb_table('auth_magic_campaigns');
        // Course Enrolment key.
        $field = new xmldb_field('relativeuser', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'submissonredirecturl');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2023112507, 'auth', 'magic');
    }

    if ($oldversion < 2023112508) {

        // Define table auth_magic_campaigns_fields to be created.
        $table = new xmldb_table('auth_magic_campaign_groups');

        // Adding fields to table auth_magic_campaigns_fields.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('campaignid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '11', null, null, null, null);

        // Adding keys to table auth_magic_campaigns_fields.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for auth_magic_campaigns_fields.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2023112508, 'auth', 'magic');
    }

    if ($oldversion < 2023122301) {

        // Magic savepoint reached.
        $table = new xmldb_table('auth_magic_campaigns_users');
        // Course Enrolment key.
        $field = new xmldb_field('passenrolmentkey', XMLDB_TYPE_INTEGER, '11', null, null, null, 0, 'followup');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2023122301, 'auth', 'magic');
    }

    if ($oldversion < 2023122302) {

        // Define table auth_magic_campaigns_fields to be created.
        $table = new xmldb_table('auth_magic_approval');

        // Adding fields to table auth_magic_campaigns_fields.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('parent', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('campaignid', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '11', null, null, null, null);

        // Adding keys to table auth_magic_campaigns_fields.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for auth_magic_campaigns_fields.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2023122302, 'auth', 'magic');
    }

    if ($oldversion < 2023122307) {

        // Magic savepoint reached.
        $table = new xmldb_table('auth_magic_campaigns_users');
        // Course Enrolment key.
        $field = new xmldb_field('enrolpassword', XMLDB_TYPE_CHAR, '55', null, null, null, 0, 'passenrolmentkey');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2023122307, 'auth', 'magic');
    }

    if ($oldversion < 2023122308) {

        // Magic savepoint reached.
        $table = new xmldb_table('auth_magic_payment_logs');
        // Course Enrolment key.
        $field = new xmldb_field('paiduser', XMLDB_TYPE_INTEGER, '11', null, null, null, 0, 'userid');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2023122308, 'auth', 'magic');
    }

    if ($oldversion < 2024010201) {
        // Magic savepoint reached.
        $table = new xmldb_table('auth_magic_campaigns');
        // Course Enrolment key.
        $field = new xmldb_field('coupon', XMLDB_TYPE_CHAR, '50', null, null, null, 0, 'groupcapacity');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2024010201, 'auth', 'magic');
    }

    if ($oldversion < 2024081400) {
        // Magic savepoint reached.
        $table = new xmldb_table('auth_magic_campaigns');
        // Course Enrolment key.
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '11', null, null, null, 0, 'timecreated');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2024081400, 'auth', 'magic');
    }




    if ($oldversion < 2024090901) {

        // Magic savepoint reached.
        $table = new xmldb_table('auth_magic_campaigns');
        // restrict roles.
        $field = new xmldb_field('restrictroles', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'coupon');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('restrictrolecontext', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'restrictroles');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // restrict cohorts.
        $field = new xmldb_field('restrictcohorts', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'restrictrolecontext');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        $field = new xmldb_field('restrictcohortoperator', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'restrictcohorts');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2024090901, 'auth', 'magic');
    }



    if ($oldversion < 2024090905) {

        // Magic savepoint reached.
        $table = new xmldb_table('auth_magic_campaigns');

         // Expiry time.
        $field = new xmldb_field('expirytime', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'restrictcohorts');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Expiry time.
        $field = new xmldb_field('expirydate', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'expirytime');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Expiry suspenduser.
        $field = new xmldb_field('expirysuspenduser', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'expirydate');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Expiry deleteduser.
        $field = new xmldb_field('expirydeleteduser', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'expirysuspenduser');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('expiryassigncohorts', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'expirydeleteduser');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('expiryremovecohorts', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'expiryassigncohorts');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // restrict cohorts.
        $field = new xmldb_field('expiryunassignglobalrole', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'expiryremovecohorts');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }


    if ($oldversion < 2024091801) {

        // Magic savepoint reached.
        $table = new xmldb_table('auth_magic_campaigns_users');
        // Expiry suspenduser.
        $field = new xmldb_field('expirybeforenotifystatus', XMLDB_TYPE_TEXT, null, null, null, null, null, 'passenrolmentkey');
        // Conditionally launch add field redirectaftersubmisson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2024091801, 'auth', 'magic');
    }

    return true;
}
