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
 * Transforms unix timestamp data to readable date.
 *
 * @package    auth_magic
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic\local\block_dash\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;

/**
 * Transforms unix timestamp data to readable date.
 *
 * @package block_dash
 */
class campaign_url_attribute extends abstract_field_attribute {

    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param mixed $data Raw data associated with this field definition.
     * @param \stdClass $record Full record from database.
     * @return mixed
     */
    public function transform_data($data, \stdClass $record) {
        global $DB;

        if ($campaign = $DB->get_record('auth_magic_campaigns', ['id' => $record->amc_id])) {
            $params = ['code' => $campaign->code];
            if (!empty($campaign->password)) {
                $params['token'] = $campaign->token;
            }
            $url = new \moodle_url('/auth/magic/campaigns/view.php', $params);
            $data = \html_writer::link($url, $data);
        }
        return $data;
    }
}
