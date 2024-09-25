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
 * Authentication Plugin: Magic Authentication external functions.
 *
 *
 * @package     auth_magic
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/externallib.php');
require_once($CFG->dirroot. "/auth/magic/lib.php");

/**
 * Define external class.
 */
class external extends \external_api {


    /**
     * Parameters define to the get magic link.
     *
     * @return array list of option parameters
     */
    public static function get_magiclink_passcheck_parameters() {
        return new external_function_parameters(
            [
                'email' => new external_value(PARAM_RAW_TRIMMED, 'User email'),
                'password' => new external_value(PARAM_RAW, 'User password'),
            ],
        );
    }

    /**
     * Check auth login.
     *
     * @param array $email email
     * @param string $password password
     *
     * @return bool status
     */
    public static function get_magiclink_passcheck($email, $password) {
        global $CFG;
        $status = false;
        $params = self::validate_parameters(self::get_magiclink_passcheck_parameters(),
            ['email' => $email, 'password' => $password]);
        $userrecord = auth_magic_get_email_user($email);
        $user = authenticate_user_login($userrecord->username, $params['password']);
        $status = ($user) ? true : false;
        return ["status" => $status];
    }

    /**
     * Returns magic links and expiration time.
     *
     * @return array magic link and expiration time.
     */
    public static function get_magiclink_passcheck_returns() {
        return new external_single_structure(
            [
                'status' => new \external_value(PARAM_BOOL, 'Return status'),
            ],
        );
    }

    /**
     * Parameters define to the get magic link.
     *
     * @return array list of option parameters
     */
    public static function get_magic_links_parameters() {
        return new external_function_parameters(
            [
                'user' => new external_single_structure(
                    [
                        'id' => new external_value(PARAM_INT, 'Get the user id', VALUE_OPTIONAL),
                        'idnumber' => new external_value(PARAM_TEXT, 'Get the user idnumber', VALUE_OPTIONAL, null),
                        'username' => new external_value(PARAM_TEXT, 'Get the user username', VALUE_OPTIONAL),
                        'email' => new external_value(PARAM_RAW_TRIMMED, 'Get the user email', VALUE_OPTIONAL),
                    ], 'Users data', VALUE_REQUIRED
                ),
                'linktype' => new external_value(PARAM_TEXT, 'Get magic link type', VALUE_REQUIRED, 'login'),
            ],
        );
    }

    /**
     * Get the magic links.
     *
     * @param array $user user data
     * @param string $linktype magic link type
     *
     * @return array magiclink and expirytime.
     */
    public static function get_magic_links($user, $linktype) {
        global $DB;

        $params = self::validate_parameters(self::get_magic_links_parameters(), ['user' => $user, 'linktype' => $linktype]);

        if (array_key_exists('id', $params['user'])) {
            $id = $params['user']['id'];
            $userdata = $DB->get_record('user', ['id' => $id]);
        }

        if (array_key_exists('idnumber', $params['user'])) {
            $idnumber = $params['user']['idnumber'];
            $userdata = $DB->get_record('user', ['idnumber' => $idnumber]);

        }

        if (array_key_exists('username', $params['user'])) {
            $username = $params['user']['username'];
            $userdata = $DB->get_record('user', ['username' => $username]);
        }

        if (array_key_exists('email', $params['user'])) {
            $comparedmail = $DB->sql_compare_text('email');
            $email = $DB->sql_compare_text($params['user']['email']);
            if (!validate_email($email)) {
                throw new moodle_exception(get_string('invalidemail'));
            } else {
                $userdata = $DB->get_record('user', [$comparedmail => $email]);
            }
        }

        if ($params['linktype'] != 'invitation' && $params['linktype'] != 'login') {
            throw new moodle_exception(get_string('instructionsforlinktype', 'auth_magic'));
        }
        $result = [];
        if (empty($userdata)) {
            throw new moodle_exception(get_string('invailduser', 'auth_magic'));
        }

        $accessauthtoall = get_config('auth_magic', 'authmethod');

        if ($userdata->auth == 'magic' || $accessauthtoall) {
            if (!$DB->record_exists('auth_magic_loginlinks', ['userid' => $userdata->id]) && $accessauthtoall) {
                // Create a new loginlinks for the user.
                $auth = get_auth_plugin('magic');
                // Request login url.
                $auth->create_magic_instance($userdata);
            }

            $data = $DB->get_record('auth_magic_loginlinks', ['userid' => $userdata->id]);

            // Check the magic link type is not empty.
            if (!empty($data)) {
                if (!empty($params['linktype']) ) {
                    if ($params['linktype'] == 'invitation') {
                        $link = $data->magicinvitation;
                        $invitationexpiry = $data->invitationexpiry;
                        $expiry = userdate($invitationexpiry, '%d/%m/%Y, %I:%M %p');
                    } else if ($params['linktype'] == 'login') {
                        $link = $data->magiclogin;
                        $loginexpiry = $data->loginexpiry;
                        $expiry = userdate($loginexpiry, '%d/%m/%Y, %I:%M %p');
                    }
                }
            }

        } else {
            throw new moodle_exception(get_string('quickregisterfornonauth', 'auth_magic'));
        }
        $status = empty($data) ? get_string("userhavenotlinks", "auth_magic", $params['linktype']) : get_string('success');
        $result[] = [
            'magiclink' => $link,
            'magiclinkexpiry' => $expiry,
            'status' => $status,
        ];
        return $result;
    }

    /**
     * Returns magic links and expiration time.
     *
     * @return array magic link and expiration time.
     */
    public static function get_magic_links_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'magiclink' => new \external_value(PARAM_URL, 'Return magic link'),
                    'magiclinkexpiry' => new \external_value(PARAM_TEXT, 'Return magic link expiration time'),
                    'status' => new \external_value(PARAM_TEXT, 'Return magic link expiration time'),
                ],
            )
        );
    }

    /**
     * Parameters defintion to update link expiry time.
     *
     * @return array list of option parameters.
     */
    public static function update_link_expiry_time_parameters() {
        return new external_function_parameters (
            [
                'userid' => new external_value(PARAM_INT, 'User id'),
                'formdata' => new external_value(PARAM_RAW, 'The data from the link expiry time'),
            ],
        );
    }

    /**
     * Update the magic link expiration time.
     *
     * @param int $userid user id
     * @param array $formdata get a user data
     *
     * @return array $message
     */
    public static function update_link_expiry_time($userid, $formdata) {
        global $DB, $CFG;
        $vaildparams = self::validate_parameters(self::update_link_expiry_time_parameters(),
         ['userid' => $userid, 'formdata' => $formdata]);
        $sitecontext = context_system::instance();
        require_capability('auth/magic:usersetlinkexpirytime', $sitecontext);
        parse_str($vaildparams['formdata'], $data);
        $linksmanualexpiry = $data['linkexpirytime']['number'] * $data['linkexpirytime']['timeunit'];
        $authinstance = $DB->get_record('auth_magic_loginlinks', ['userid' => $vaildparams['userid']]);
        $instance = $DB->get_record('user_private_key', ['value' => $authinstance->loginuserkey]);
        $invitationinstance = $DB->get_record('user_private_key', ['value' => $authinstance->invitationuserkey]);
        $message = get_string('error', 'auth_magic');
        if ($instance && $invitationinstance ) {
            $instance->validuntil = time() + $linksmanualexpiry;
            $instance->timecreated = time();
            $DB->update_record('user_private_key', $instance);
            $invitationinstance->validuntil = time() + $linksmanualexpiry;
            $invitationinstance->timecreated = time();
            $DB->update_record('user_private_key', $invitationinstance);
            $authinstance->loginexpiry = time() + $linksmanualexpiry;
            $authinstance->timemodified = time();
            $authinstance->manualexpiry = $linksmanualexpiry;
            $authinstance->invitationexpiry = time() + $linksmanualexpiry;
            $DB->update_record('auth_magic_loginlinks', $authinstance);
            $message = get_string('success', 'auth_magic');
        }
        return [
            'message' => $message,
        ];
    }

    /**
     * Return a message.
     * @return array message.
     */
    public static function update_link_expiry_time_returns() {
        return new external_single_structure(
            [
            'message' => new \external_value(PARAM_TEXT, 'Return status message'),
            ],
        );
    }

    /**
     * Get the course groupings.
     */
    public static function get_course_groupings_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'Id of course'),
                'campaignid' => new external_value(PARAM_INT, 'Id of the campaign'),
            ],
        );
    }

    /**
     * Get course groupings.
     *
     * @param [int] $courseid
     * @param [int] $campaignid
     * @return void
     */
    public static function get_course_groupings($courseid, $campaignid) {
        global $OUTPUT, $DB;
        $params = self::validate_parameters(self::get_course_groupings_parameters(),
            ['courseid' => $courseid, 'campaignid' => $campaignid]);
        // Now security checks.
        $context = context_course::instance($params['courseid']);

        try {
            self::validate_context($context);
        } catch (Exception $e) {
                $exceptionparam = new stdClass();
                $exceptionparam->message = $e->getMessage();
                $exceptionparam->courseid = $params['courseid'];
                throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
        }
        require_capability('moodle/course:managegroups', $context);
        $gs = groups_get_all_groupings($params['courseid']);
        $course = get_course($courseid);
        $groupings = [];
        foreach ($gs as $grouping) {
            $list = [];
            $list['id'] = $grouping->id;
            $list['name'] = $grouping->name;
            $groupings[] = $list;
        }
        $selfenrol = false;
        $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'self']);
        $enrolplugin = enrol_get_plugin('self');
        if ($enrolplugin && !empty($instance) && $enrolplugin->can_self_enrol($instance) === true) {
            $selfenrol = true;
        }
        $courseinfo['is_available_selfenrol'] = $selfenrol;
        $courseinfo['is_separtegroup'] = ($course->groupmode == SEPARATEGROUPS) ? true : false;
        $campaigngrouping = false;
        if ($campaignid) {
            $campaigngrouping = $DB->get_field('auth_magic_campaigns', 'campaigngrouping', ['id' => $campaignid]);
        }
        $courseinfo['campaign_grouping'] = $campaigngrouping ? $campaigngrouping : 0;
        $courselink = html_writer::link(new moodle_url('/course/edit.php', ['id' => $course->id]),
            get_string('reviewcoursesettings', 'auth_magic'));
        $enrolinstancelink = html_writer::link(new moodle_url('/enrol/instances.php', ['id' => $course->id]),
        get_string('reviewcourseenrolmentsettings', 'auth_magic'));
        $separtegroupinfostr = get_string("separtegroupinstructions", "auth_magic",
            ["coursename" => format_string($course->fullname), "courselink" => $courselink]);
        $selfcourseinfostr = get_string("selfcourseinstructions", "auth_magic", ["coursename" => format_string($course->fullname),
        "courselink" => $enrolinstancelink]);
        $courseinfo['separtegroupinfo'] = $OUTPUT->notification($separtegroupinfostr, \core\output\notification::NOTIFY_INFO);
        $courseinfo['selfenrolinfo'] = $OUTPUT->notification($selfcourseinfostr, \core\output\notification::NOTIFY_INFO);
        return ['groupings' => $groupings, 'courseinfo' => $courseinfo];
    }

    /**
     * Return a groupings data.
     *
     * @return void
     */
    public static function get_course_groupings_returns() {
        return new external_function_parameters(
            [
                'groupings' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'grouping record id'),
                            'name' => new external_value(PARAM_TEXT, 'multilang compatible name, course unique'),
                        ]
                    )
                ),
                'courseinfo' => new external_single_structure(
                    [
                        'is_available_selfenrol' => new external_value(PARAM_BOOL, 'Check the self enrolment exist or not'),
                        'is_separtegroup' => new external_value(PARAM_BOOL, 'Check the group separte or not'),
                        'campaign_grouping' => new external_value(PARAM_INT, 'Campaign grouping'),
                        'separtegroupinfo' => new external_value(PARAM_RAW, 'Separte group Info'),
                        'selfenrolinfo' => new external_value(PARAM_RAW, 'Self enrol Info'),
                    ]
                ),
            ],
        );
    }

}
