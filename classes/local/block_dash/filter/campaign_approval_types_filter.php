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
class campaign_approval_types_filter extends select_filter {
    /**
     * Initialize the filter. It must be initialized before values are extracted or SQL generated.
     * If overridden call parent.
     */
    public function init() {

        $options = [
            'disabled' => get_string('disabled', 'auth_magic'),
            'information' => get_string('information', 'auth_magic'),
            'optionalin' => get_string('optional_in', 'auth_magic'),
            'optionalout' => get_string('optional_out', 'auth_magic'),
            'fulloptionout' => get_string('full_option_out', 'auth_magic'),
        ];

        foreach ($options as $option => $string) {
            $this->add_option($option, $string);
        }
        parent::init();
    }

    /**
     * Get the enrolment status filter label.
     * @return string
     */
    public function get_label() {
        return get_string('approvaltypes', 'auth_magic');
    }
}
