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
 * Filters results to current course only.
 *
 * @package    auth_magic
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic\local\block_dash\filter;

use block_dash\local\data_grid\filter\condition;
use coding_exception;
use dml_exception;
use moodleform;
use MoodleQuickForm;

/**
 * Filters results to current course only.
 *
 * @package block_dash
 */
class campaign_approval_types_condition extends condition {

    /**
     * Get filter SQL operation.
     *
     * @return string
     */
    public function get_operation() {
        return self::OPERATION_IN_OR_EQUAL;
    }

    /**
     * Get condition label.
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_label() {
        if ($label = parent::get_label()) {
            return $label;
        }

        return get_string('approvaltypes', 'auth_magic');
    }

    /**
     * Add form fields for this filter (and any settings related to this filter.)
     *
     * @param moodleform $moodleform
     * @param MoodleQuickForm $mform
     * @param string $fieldnameformat
     */
    public function build_settings_form_fields(
        moodleform $moodleform, MoodleQuickForm $mform, $fieldnameformat = 'filters[%s]'): void {

        global $DB;

        parent::build_settings_form_fields($moodleform, $mform, $fieldnameformat); // Always call parent.

        $fieldname = sprintf($fieldnameformat, $this->get_name());

        $options = [
            'disabled' => get_string('disabled', 'auth_magic'),
            'information' => get_string('information', 'auth_magic'),
            'optionalin' => get_string('optional_in', 'auth_magic'),
            'optionalout' => get_string('optional_out', 'auth_magic'),
            'fulloptionout' => get_string('full_option_out', 'auth_magic'),
        ];
        $select = $mform->addElement('select', $fieldname . '[approvaltypes]', '', $options, ['class' => 'select2-form']);
        $mform->hideIf($fieldname . '[approvaltypes]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }


    /**
     * Get values from filter based on user selection. All filters must return an array of values.
     *
     * Override in child class to add more values.
     *
     * @return array
     */
    public function get_values() {
        if (isset($this->get_preferences()['approvaltypes']) && is_array($this->get_preferences()['approvaltypes'])) {
            $status = $this->get_preferences()['approvaltypes'];
            return $status;
        }
        return [];
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $DB;

        list($sql, $params) = parent::get_sql_and_params();

        if ($sql) {
            list($insql, $inparams) = $DB->get_in_or_equal($this->get_preferences()['approvaltypes'],
                SQL_PARAMS_NAMED, 'amc', true, true);
            $sql = ' amc.approvaltype ' . $insql;
            $params = array_merge($params, $inparams);
        }
        return [$sql, $params];
    }
}
