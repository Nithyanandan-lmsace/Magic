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

/**
 * Filters results to current course only.
 *
 * @package block_dash
 */
class campaign_hide_my_campaign_condition extends condition {


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
        return get_string('hidemycampaign', 'auth_magic');
    }

    public function get_sql_and_params() {
        global $USER;

        list($sql, $params) = parent::get_sql_and_params();
        $sql = "amc.id NOT IN (
            SELECT campaignid FROM {auth_magic_campaigns_users} amcu WHERE amcu.userid = :currentuser
        )";
        $params = ['currentuser' => $USER->id];
        return [$sql, $params];
    }
}

