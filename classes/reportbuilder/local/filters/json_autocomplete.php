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

declare(strict_types=1);

namespace auth_magic\reportbuilder\local\filters;

use core_reportbuilder\local\filters\autocomplete;
use core_reportbuilder\local\helpers\database;


/**
 * Autocomplete report filter
 *
 * @package     core_reportbuilder
 * @copyright   2022 Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class json_autocomplete extends autocomplete {

    /**
     * Return filter SQL
     *
     * @param array $values
     * @return array
     */
    public function get_sql_filter(array $values): array {
        global $DB;

        $fieldsql = $this->filter->get_field_sql();
        $params = $this->filter->get_field_params();

        $invalues = $values["{$this->name}_values"] ?? [];
        if (empty($invalues)) {
            return ['', []];
        }

         // Loop through the values you want to check against the JSON field
        foreach ($invalues as $index => $value) {
            // Create a named parameter for each value
            $paramname = "{$this->filter->get_name()}$index";  // Create unique param name for each iteration
            $filtersql[] = "JSON_CONTAINS($fieldsql, :$paramname, '$')";
            // Bind the parameter value
            $params[$paramname] = json_encode($value);
        }

        // Combine all JSON_CONTAINS conditions using OR (if any of the values should match)
        $filtersql = implode(' OR ', $filtersql);
       /*  print_r([$filtersql, $params]);
        exit; */
        return [$filtersql, $params];
    }
}
