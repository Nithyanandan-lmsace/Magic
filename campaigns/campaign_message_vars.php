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
 * Campaign welcome message and follow up message settings placeholder definition.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Filter for notification content placeholders.
 */
class campaign_message_vars {

    // Objects the vars refer to.

    /**
     * User data record.
     *
     * @var object
     */
    protected $user = null;

    /**
     * Campaign record.
     *
     * @var object
     */
    protected $campaign = null;

    /**
     * Campaign owner record.
     *
     * @var object
     */
    protected $campaignowner = null;

    /**
     * Site record.
     *
     * @var object
     */
    protected $site = null;

    /**
     * Site url.
     *
     * @var string
     */
    protected $url = null;

    /**
     * Placeholder doesn't have dynamic filter then it will replaced with blank value.
     *
     * @var string
     */
    protected $blank = "[blank]";

    /**
     * @var int
     */
    protected $group;

    /**
     * Sets up and retrieves the API objects.
     *
     * @param  mixed $user User data record
     * @param  mixed $campaign Course data object
     * @param  mixed $campaignowner Sender user record data.
     * @param  mixed $usergroup
     * @return void
     */
    public function __construct($user, $campaign, $campaignowner, $usergroup) {
        global $CFG;

        $this->user =& $user;
        $this->campaign =& $campaign;
        $this->campaignowner = $campaignowner;
        $this->group = $usergroup;

        $wwwroot = $CFG->wwwroot;
        if (!empty($user->id)) {
            $this->url = new moodle_url($wwwroot .'/user/profile.php', ['id' => $this->user->id]);
        }
        $this->site = get_site();
    }

    /**
     * Check whether it is ok to call certain methods of this class as a substitution var
     *
     * @param string $methodname = text;
     * @return string
     **/
    private static function ok2call($methodname) {
        return ($methodname != "vars" && $methodname != "__construct" && $methodname != "__get" && $methodname != "ok2call");
    }

    /**
     * Set up all the methods that can be called and used for substitution var in email templates.
     *
     * @return array $result.
     **/
    public static function vars() {
        $reflection = new ReflectionClass("campaign_message_vars");
        $amethods = $reflection->getMethods();
        // These fields refer to the objects declared at the top of this class. User_ -> $this->user, etc.
        $result = [
            // User fields.
            'User_FirstName', 'User_LastName', 'User_Email', 'User_Username', 'User_Password',
            'User_Institution', 'User_Department',
            'User_Address', 'User_City', 'User_Country',
            // Campaign information fields .
            'CampaignOwner_fullname', 'CampaignOwner_email',
            // Campaign informations.
            'Campaign_title', 'Campaign_description', 'Group_enrolmentkey',
            // Site fields.
            'Site_FullName', 'Site_ShortName', 'Site_Summary',
        ];

        // Add all methods of this class that are ok2call to the $result array as well.
        // This means you can add extra methods to this class to cope with values that don't fit in objects mentioned above.
        // Or to create methods with specific formatting of the values (just don't give those methods names starting with
        // 'User_', 'Course_', etc).
        foreach ($amethods as $method) {
            if (self::ok2call($method->name) && !in_array($method->name, $result) ) {
                $strings = explode('_', $method->name);
                $result[] = implode('_', array_map('ucwords', $strings));
            }
        }

        return $result;
    }

    /**
     * Trap calls to non-existent methods of this class, that can then be routed to the appropriate objects.
     * @param string $name Placeholder used on the template.
     */
    public function __get($name) {
        if (isset($name)) {
            if (property_exists($this, $name)) {
                return $this->$name;
            }
            preg_match('/^(.*)_(.*)$/', $name, $matches);
            if (isset($matches[1])) {
                $object = strtolower($matches[1]);
                $property = strtolower($matches[2]);

                if (isset($this->$object->$property)) {
                    return $this->$object->$property;
                } else if (method_exists($this->$object, '__get')) {
                    return $this->$object->__get($property);
                } else if (method_exists($this->$object, 'get')) {
                    return $this->$object->get($property);
                } else {
                    return $this->blank;
                }
            } else if (self::ok2call($name)) {
                return $this->$name();
            }
        }
    }

    /**
     * Provide the SiteURL method for templates.
     *
     * returns text;
     *
     **/
    public function siteurl() {
        global $CFG;

        $wwwroot = $CFG->wwwroot;
        return $wwwroot;
    }

    /**
     * Date of user registration.
     *
     * @return string
     */
    public function registrationdate() {
        return userdate($this->user->timecreated, get_string('strftimedatetimeshort', 'core_langconfig'));
    }

}
