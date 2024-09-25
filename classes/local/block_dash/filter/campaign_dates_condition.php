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
 * @package    block_dash
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
class campaign_dates_condition extends condition {

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

        return get_string('campaigndates', 'auth_magic');
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
            'past' => get_string('strpast', 'auth_magic'),
            'present' => get_string('strpresent', 'auth_magic'),
            'future' => get_string('strfuture', 'auth_magic'),
        ];
        $select = $mform->addElement('select', $fieldname . '[campaigndates]', '', $options, ['class' => 'select2-form']);
        $mform->hideIf($fieldname . '[campaigndates]', $fieldname . '[enabled]');
        $select->setMultiple(true);
    }


    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $USER, $DB;

        if (isset($this->get_preferences()['campaigndates']) && is_array($this->get_preferences()['campaigndates'])) {
            $dates = $this->get_preferences()['campaigndates'];
            $sql = [];
            $params = [];
            foreach ($dates as $key => $date) {
                switch ($date) {
                    case 'past':
                        $sql[] = "(amc.enddate <> 0 AND amc.enddate < :cdc_now_$key)";
                        $params += ['cdc_now_'.$key => time()];
                        break;
                    case 'present':
                        $sql[] = "( ( amc.startdate < :cdc_startdate_$key OR amc.startdate = 0) AND ( amc.enddate = 0 OR amc.enddate > :cdc_enddate_$key) )";
                        $params += ['cdc_enddate_'.$key => time(), 'cdc_startdate_'.$key => time()];
                        break;
                    case 'future':
                        $sql[] = "(amc.startdate > :cdc_now_$key)";
                        $params += ['cdc_now_'.$key => time()];
                        break;
                }
            }

            return ['('.implode(' OR ', $sql).')', $params];
        }

    }
}
