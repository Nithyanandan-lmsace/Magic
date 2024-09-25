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
 * Campaign summary content settings placeholder definition.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Filter for notification content placeholders.
 */
class campaign_formfield_vars {

    // Objects the vars refer to.

    /**
     * User data record.
     *
     * @var object
     */
    protected $user = null;


    /**
     * Sets up and retrieves the API objects.
     *
     * @param  mixed $user User data record
     * @return void
     */
    public function __construct($user) {
        global $CFG;

        $this->user =& $user;
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
     * @param array $fields
     * @return array $result.
     **/
    public static function vars($fields) {
        $reflection = new ReflectionClass("campaign_formfield_vars");
        $amethods = $reflection->getMethods();
        // These fields refer to the objects declared at the top of this class. User_ -> $this->user, etc.
        $result = $fields;
        foreach (profile_get_custom_fields() as $customfield) {
            if ($customfield->visible != PROFILE_VISIBLE_NONE) {
                $field = 'profile_field_' . $customfield->shortname;
                $result[] = $field;
            }
        }

        return $result;
    }

    /**
     * Trap calls to non-existent methods of this class, that can then be routed to the appropriate objects.
     * @param string $value Placeholder used on the template.
     */
    public function __get($value) {
        if (isset($value)) {
            if (property_exists($this, $value)) {
                return $this->$value;
            }
            preg_match('/^(.*)_(.*)$/', $value, $matches);
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
            } else if (self::ok2call($value)) {
                return $this->$value();
            }
        }
    }

}
