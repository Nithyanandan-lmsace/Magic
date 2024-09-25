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
class campaign_owner_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     * If overridden call parent.
     */
    public function init() {
        global $DB, $CFG;
        require_once($CFG->dirroot. "/auth/magic/lib.php");
        $users = $DB->get_records_sql("SELECT *
                                    FROM {user}
                                    WHERE confirmed = 1 AND deleted = 0 AND id <> ?", [$CFG->siteguest]);
        $options = auth_magic_get_usernames_choices($users);
        $this->add_options($options);
        //$this->add_option(2, 'Admin User');
        parent::init();
    }

    /**
     * Get the enrolment status filter label.
     * @return string
     */
    public function get_label() {
        return get_string('campaigns:campaignowner', 'auth_magic');
    }

    /**
     * Return where SQL and params for placeholders.
     *
     * @return array
     * @throws \coding_exception|\dml_exception
     */
   /*  public function get_sql_and_params() {
        global $USER, $DB;

        $userids = $this->get_values();
        list($sql, $params) = parent::get_sql_and_params();

        if ($sql) {
            list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'amc', true, true);
            $sql = ' amc.campaignowner ' . $insql;
            $params += $inparams;
        }
        return [$sql, $params];
    } */
}
