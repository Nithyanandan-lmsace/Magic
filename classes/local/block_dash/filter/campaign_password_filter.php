<?php
// This file is part of The Bootstrap Moodle theme
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
 * Available enrolment status based field.
 * @package    auth_magic
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic\local\block_dash\filter;

use block_dash\local\data_grid\filter\select_filter;

/**
 * Available enrolment status based field.
 */
class campaign_password_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     * If overridden call parent.
     */
    public function init() {
        $this->add_option('yes', get_string('yes'));
        $this->add_option('no', get_string('no'));
        parent::init();
    }


    /**
     * Get the enrolment status filter label.
     * @return string
     */
    public function get_label() {
        return get_string('campaigns:password', 'auth_magic');
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @throws \coding_exception|\dml_exception
     */
    public function get_sql_and_params() {
        global $USER, $DB;

        list($sql, $params) = parent::get_sql_and_params();

        $values = $this->get_values();
        $sql = "";

        // Check the yes and no options are select then return false.
        if (array_search('yes', $values) !== false && array_search('no', $values) !== false) {
            return false;
        }

        if (array_search('yes', $values) !== false) {
            $sql .= ' (amc.password != "") ';
        }

        if (array_search('no', $values) !== false) {
            $sql .= ' (amc.password = "")';
        }
        return [$sql, $params];
    }
}
