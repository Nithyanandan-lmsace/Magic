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
 * campaign field Info.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic;
use stdClass;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot."/user/profile/lib.php");

/**
 * Magic Campaign fields.
 */
class campaign_fields {

    /**
     * Indicator for recursive function.
     * @var int
     */
    public $callrecursivefunction;

    /**
     * Create instance of the campaign.
     *
     * @param int $id
     * @return \auth_magic\campaign
     */
    public static function instance($id) {
        static $instance;
        if (!isset($instance)) {
            $instance = new self($id);
        }
        return $instance;
    }

    /**
     * Campaign field constructor.
     * @param int $id
     */
    public function __construct($id) {

        $this->id = $id;
        $this->campaignfields = $this->get_fields();
        $this->callrecursivefunction1 = 0;
        $this->callrecursivefunction2 = 0;
    }

    /**
     * Get campaign.
     * @return object campaign object
     */
    public function get_campaign() {
        return \auth_magic\campaign::instance($id)->get_campaign();
    }

    /**
     * Get profile require and optional fields.
     * @return array fields
     */
    public function get_profile_change_fields() {
        global $DB;
        $params = [
            'campaignid' => $this->id,
            'req' => MAGICCAMPAIGNSREQUIRED,
            'opt' => MAGICCAMPAIGNSOPTIONAL,
            'fieldtype' => MAGICCAMPAIGNPROFILEFIELD,
        ];
        $changefields = $DB->get_records_sql_menu("SELECT field FROM {auth_magic_campaigns_fields} WHERE
            campaignid = :campaignid AND fieldtype = :fieldtype AND (fieldoption = :req OR fieldoption = :opt)", $params);
        return array_keys($changefields);
    }

    /**
     * Update the pre placeholder data.
     * @param object $data
     */
    public function pre_placeholder_profile_data($data) {
        global $DB;
        $changefields = $this->get_profile_change_fields();
        foreach ($changefields as $field) {
            $var = "profile_field_" . $field;
            if (isset($data->{$var})) {
                $data->{$field} = $data->{$var};
            }
        }
        return $data;
    }

    /**
     * Update the post placeholder data.
     * @param object $data
     */
    public function post_placeholder_profile_data($data) {
        global $DB;
        $profilefields = $DB->get_records_sql_menu("SELECT field FROM {auth_magic_campaigns_fields} WHERE
        campaignid = :campaignid AND fieldtype = :fieldtype",
        ['campaignid' => $this->id, 'fieldtype' => MAGICCAMPAIGNPROFILEFIELD]);
        $profilefields = array_keys($profilefields);
        foreach ($profilefields as $field) {
            $var = "profile_field_" . $field;
            if (isset($data->{$field})) {
                $data->{$var} = $data->{$field};
            }
        }
        return $data;
    }

    /**
     * Changed the placeholder values.
     * @param object $data
     */
    public function reset_placeholder_values($data) {
        $data = $this->pre_placeholder_profile_data($data);
        foreach ($this->campaignfields as $fieldinstance) {
            $data->{$fieldinstance->field} = $this->get_field_placeholder_data($fieldinstance, $data);
        }
        $data = $this->post_placeholder_profile_data($data);
        return $data;
    }

    /**
     * Replace the placholder for field intstance to user submit the data.
     * @param object $fieldinstance
     * @param object $data
     */
    public function get_field_placeholder_data($fieldinstance, $data) {
        if (isset($data->{$fieldinstance->field})) {
            $field = $fieldinstance->field;
            $userfieldvalue = $data->{$field};
            // Check the field option and replace the data to placeholder.
            if ($fieldinstance->fieldoption == MAGICCAMPAIGNSOPTIONAL) { // Optional.
                // Check the optional value isset or not, If not set to empty.
                if (!isset($userfieldvalue)) {
                    $userfieldvalue = "";
                }
            } else if ($fieldinstance->fieldoption == MAGICCAMPAIGNSHIDDENPROVIDEVALUE) { // Hidden provide value.
                if (!isset($userfieldvalue) || empty($userfieldvalue)) {
                    $userfieldvalue = $fieldinstance->customvalue;
                } else if ($userfieldvalue != $fieldinstance->customvalue) {
                    $userfieldvalue = $fieldinstance->customvalue;
                }
            } else if ($fieldinstance->fieldoption == MAGICCAMPAIGNSREQUIREDHIDDENDEFAULT) { // Hidden default.
                if ($this->is_profile_field($field)) {
                    $customfield = profile_get_custom_field_data_by_shortname($field, false);
                    $userfieldvalue = $customfield->defaultdata;
                } else if (!isset($userfieldvalue)) {
                    $userfieldvalue = "";
                }
            } else if ($fieldinstance->fieldoption == MAGICCAMPAIGNSREQUIREDHIDDENOTHERFIELD) { // Hidden using other field.
                $userfieldvalue = $this->replace_otherfield_value($fieldinstance, $data);
            }
            return $userfieldvalue;
        }
    }

    /**
     * replace the otherfield option field.
     * @param object $fieldinstance
     * @param object $formdata
     */
    public function replace_otherfield_value($fieldinstance, $formdata) {
        $instance = $fieldinstance;
        $this->callrecursivefunction++;
        // TODO Stop the recursive function over 20 times.
        // TODO Sometimes User give wrong form fields data.To stop the recursive function.
        if ($this->callrecursivefunction > 20) {
            return "";
        }
        list($userfieldvalue, $modifiyfield) = $this->call_recursive_otherfield_value($instance, $formdata);
        if (empty($modifiyfield)) {
            return "";
        }
        // Check the modified the field is other one so call the function again.
        if ($modifiyfield != $fieldinstance->field) {
            if (isset($formdata->{$modifiyfield}) && self::is_field_placholder_type($formdata->{$modifiyfield})) {
                $formdata->{$modifiyfield} = $userfieldvalue;
            }
            return $this->replace_otherfield_value($instance, $formdata);
        } else {
            return $userfieldvalue;
        }
    }

    /**
     * recursive function to modifiy the otherfield option field.
     * @param object $fieldinstance
     * @param object $formdata
     */
    public function call_recursive_otherfield_value($fieldinstance, $formdata) {
        $this->callrecursivefunction1++;
        // TODO Stop the recursive function over 20 times.
        // TODO Sometimes User give wrong form fields data.To stop the recursive function.
        if ($this->callrecursivefunction1 > 15) {
            return ["", ""];
        }
        $otherfieldvalue = $fieldinstance->otherfieldvalue;
        $modifiyfield = $fieldinstance->field;
        $userfieldvalue = "";
        if ($otherfieldvalue) {
            $replacefield = self::get_orignial_field_name($otherfieldvalue);
            // Using recurie function to change the value.
            $fieldinstance = $this->get_field($replacefield);
            if ($fieldinstance) {
                if (isset($formdata->{$fieldinstance->field}) &&
                    !self::is_field_placholder_type($formdata->{$fieldinstance->field})) {
                    $userfieldvalue = $formdata->{$fieldinstance->field};
                } else if ($fieldinstance->fieldoption != MAGICCAMPAIGNSREQUIREDHIDDENOTHERFIELD &&
                    !self::is_field_placholder_type($fieldinstance->otherfieldvalue)) {
                    $userfieldvalue = $formdata->{$fieldinstance->field};
                } else {
                    // Check the replace the field is base on other placeholder field.
                    // In this case all parent placholder change the orignal vaule.
                    $modifiyfield = $fieldinstance->field;
                    return $this->call_recursive_otherfield_value($fieldinstance, $formdata);
                }
            }
        }
        return [$userfieldvalue, $modifiyfield];
    }

    /**
     * Create the campaign form fields.
     * @param object $data
     */
    public function create_fields($data) {
        global $DB;
        $formfields = array_filter((array) $data, function ($key) {
            return (
                (strpos($key, 'standard_') === 0 || strpos($key, 'profile_field_') === 0)
                && (!preg_match('/^standard_[A-Za-z]+_otherfield_prevalue$/', $key)
                && !preg_match('/^profile_field_[A-Za-z]+_otherfield_prevalue$/', $key)
                && !preg_match('/^standard_[A-Za-z]+_otherfield_value$/', $key)
                && !preg_match('/^profile_field_[A-Za-z]+_otherfield_value$/', $key))
            );
        }, ARRAY_FILTER_USE_KEY);
        foreach ($formfields as $fieldname => $value) {
            $this->insert_field($formfields, $fieldname);
        }
    }

    /**
     * Insert the form field
     * @param array $formfields
     * @param string $fieldname
     * @return void
     */
    public function insert_field($formfields, $fieldname) {
        global $DB;
        $field = self::get_orignial_field_name($fieldname);
        if (!$this->is_field($field)) {
            // Insert record.
            $fieldtype = self::get_field_type($fieldname);
            $instance = new stdClass;
            $instance->campaignid = $this->id;
            $instance->field = $field;
            $fieldoption = $fieldtype . $field. '_option';
            $instance->fieldoption = isset($formfields[$fieldoption]) ? $formfields[$fieldoption] : '';
            $fieldcustomvalue = $fieldtype . $field;
            $instance->customvalue = isset($formfields[$fieldcustomvalue]) ? $formfields[$fieldcustomvalue] : '';
            $fieldotherfield = $fieldtype . $field .'_otherfield';
            $instance->otherfieldvalue = isset($formfields[$fieldotherfield]) ? $formfields[$fieldotherfield] : '';
            $instance->fieldtype = (strpos($fieldtype, 'profile_field_') === 0)
                ? MAGICCAMPAIGNPROFILEFIELD : MAGICCAMPAIGNSTANDARDFIELD;
            $instance->timecreated = time();
            $DB->insert_record('auth_magic_campaigns_fields', $instance);
        }
    }

    /**
     * Update the campaign form fields.
     * @param object $data
     */
    public function update_fields($data) {
        global $DB;

        $formfields = array_filter((array) $data, function ($key) {
            return (
                (strpos($key, 'standard_') === 0 || strpos($key, 'profile_field_') === 0)
                && (!preg_match('/^standard_[A-Za-z]+_otherfield_prevalue$/', $key)
                && !preg_match('/^profile_field_[A-Za-z]+_otherfield_prevalue$/', $key)
                && !preg_match('/^standard_[A-Za-z]+_otherfield_value$/', $key)
                && !preg_match('/^profile_field_[A-Za-z]+_otherfield_value$/', $key))
            );
        }, ARRAY_FILTER_USE_KEY);
        foreach ($formfields as $fieldname => $value) {
            $field = self::get_orignial_field_name($fieldname);
            $fieldtype = self::get_field_type($fieldname);
            // Check the field exist or not.
            if (!$this->is_field($field)) {
                $this->insert_field($formfields, $fieldname);
            } else {
                // Update the field.
                $fieldrecord = $this->get_field($field);
                $fieldoption = isset($formfields[$fieldtype . $field. '_option'])
                    ? $formfields[$fieldtype . $field. '_option'] : '';
                $fieldcustomvalue = isset($formfields[$fieldtype . $field]) ? $formfields[$fieldtype . $field] : '';
                $fieldotherfieldvalue = isset($formfields[$fieldtype . $field. '_otherfield'])
                    ? $formfields[$fieldtype . $field. '_otherfield'] : '';
                if ($fieldrecord->fieldoption != $fieldoption || $fieldrecord->customvalue != $fieldcustomvalue ||
                $fieldrecord->otherfieldvalue != $fieldotherfieldvalue) {
                    $fieldrecord->fieldoption = $fieldoption;
                    $fieldrecord->customvalue = $fieldcustomvalue;
                    $fieldrecord->otherfieldvalue = $fieldotherfieldvalue;
                    $fieldrecord->timemodified = time();
                    $DB->update_record('auth_magic_campaigns_fields', $fieldrecord);
                }
            }
        }
    }

    /**
     * Get campaign form field.
     * @param string $field
     */
    public function get_field($field) {
        global $DB;
        return $DB->get_record('auth_magic_campaigns_fields', ['campaignid' => $this->id, 'field' => $field]);
    }

    /**
     * Get campaign form fields.
     */
    public function get_fields() {
        global $DB;
        return $DB->get_records('auth_magic_campaigns_fields', ['campaignid' => $this->id]);
    }

    /**
     * Check the campaign form field exist or not.
     * @param string $field
     */
    public function is_field($field) {
        global $DB;
        return $DB->record_exists('auth_magic_campaigns_fields', ['campaignid' => $this->id, 'field' => $field]);
    }

    /**
     * Check the form field profile or not.
     * @param string $field
     */
    public function is_profile_field($field) {
        if ($fieldinstance = $this->get_field($field)) {
            if ($fieldinstance->fieldtype == MAGICCAMPAIGNPROFILEFIELD) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check the field starndard or profile.
     * @param string $field
     */
    public static function get_field_type($field) {
        if (strpos($field, 'standard_') === 0) {
            return "standard_";
        } else if (strpos($field, 'profile_field_') === 0) {
            return "profile_field_";
        } else {
            return '';
        }
    }

    /**
     * Get the field identifiy name.
     * @param string $field
     */
    public static function get_orignial_field_name($field) {
        $field = str_replace('standard_', '', $field);
        $field = str_replace('profile_field_', '', $field);
        $field = str_replace('_option', '', $field);
        $field = str_replace('_otherfield', '', $field);
        return $field;
    }

    /**
     * Check the field valid or not.
     * @param string $field
     * @return bool
     */
    public static function is_field_placholder_type($field) {
        if (strpos($field, 'standard_') === 0 || strpos($field, 'profile_field_') === 0) {
            return true;
        }
        return false;
    }
}
