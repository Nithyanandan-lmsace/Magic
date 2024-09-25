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
 * Transform course data into dash dashboard description.
 *
 * @package    auth_magic
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic\local\block_dash\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;

/**
 * Transforms data to formatted dash dashboard description.
 *
 * @package auth_magic
 */
class campaign_capacitystatus extends abstract_field_attribute {

    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param \stdClass $data
     * @param \stdClass $record Entire row
     * @return mixed
     * @throws \moodle_exception
     */
    public function transform_data($data, \stdClass $record) {
        global $DB;
        $capacity = $data;
        $campaignusers = $DB->count_records('auth_magic_campaigns_users', ['campaignid' => $record->amc_id]);

        if ($capacity == 0) {
            return get_string("capacity:open", 'auth_magic');
        } else if (($capacity - $campaignusers) > 0) {
            return get_string("capacity:seatavailable", 'auth_magic');
        } else {
            return get_string("capacity:fullbooked", 'auth_magic');
        }
    }
}
