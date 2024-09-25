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
 * After records are relieved from database each field has a chance to transform the data.
 *
 * @package     auth_magic
 * @copyright   2020 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic\local\block_dash\attribute;

use block_dash\local\data_grid\field\attribute\abstract_field_attribute;

/**
 * Transform data to URL of course image.
 *
 * @package local_dash
 */
class campaign_headerimage_url extends abstract_field_attribute {
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
        global $DB, $CFG;

        require_once("$CFG->dirroot/course/lib.php");
        require_once("$CFG->dirroot/blocks/dash/lib.php");
        $image = '';
        $fs = get_file_storage();
        $files = $fs->get_area_files(\context_system::instance()->id, 'auth_magic', 'headerimage', $data, '', false);
        if (!empty($files)) {
            // Get the first file.
            $file = reset($files);

            // Conver the file to url.
            $image = \moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename(),
                false
            );
        }

        return $image;
    }
}

