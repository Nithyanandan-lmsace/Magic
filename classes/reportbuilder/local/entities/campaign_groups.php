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
 * Payment entity class implementation.
 *
 * @package   auth_magic
 * @copyright 2023 Medical Access Uganda Limited
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic\reportbuilder\local\entities;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\{column, filter};
use lang_string;
use core_reportbuilder\local\helpers\format;

/**
 * Payment entity class implementation.
 *
 * @package   auth_magic
 * @copyright 2023 Medical Access Uganda Limited
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class campaign_groups extends base {



    /**
     * Database tables that this entity uses
     *
     * @return array
     */
    protected function get_default_tables(): array {
        return [
            'auth_magic_campaigns',
        ];
    }

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return ['auth_magic_campaigns' => 'amc'];
    }


    /**
     * The default title for this entity in the list of columns/conditions/filters in the report builder
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entities:campaign_groups', 'auth_magic');
    }

    /**
     * Initialise the entity
     *
     * @return base
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }
        return $this;
    }


    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        global $DB;
        $campaigntablealias = "amc";
        $campaigngrouptablealias = "amcg";
        $name = $this->get_entity_name();

        // Confirmed users.
        $columns[] = (new column('groupname', new lang_string('campaignsource:field_groupname', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->add_join("LEFT JOIN {auth_magic_campaign_groups} {$campaigngrouptablealias}
                ON {$campaigntablealias}.id = {$campaigngrouptablealias}.campaignid")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$campaigngrouptablealias}.groupid")
            ->add_callback(static function(?int $value) use ($DB): string {
                return $DB->get_field('groups', 'name', ['id' => $value]);
            });

        // Group ID.
        $columns[] = (new column('groupid', new lang_string('campaignsource:field_groupid', 'auth_magic'), $name))
            ->add_join("LEFT JOIN {auth_magic_campaign_groups} {$campaigngrouptablealias} ON
                $campaigntablealias}.id = {$campaigngrouptablealias}.campaignid")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$campaigngrouptablealias}.groupid");

        // Group capacity.
        $columns[] = (new column('groupcapacity', new lang_string('campaignsource:field_groupcapacity', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$campaigntablealias}.groupcapacity")
            ->add_callback(static function(?int $value): string {
                if ($value) {
                    return (string) $value;
                }
                return get_string('campaigns:unlimited', 'auth_magic');
            });

        // Group member count.
        $columns[] = (new column('membercount', new lang_string('campaignsource:field_membercount', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->add_join("LEFT JOIN {auth_magic_campaign_groups} {$campaigngrouptablealias} ON
                {$campaigntablealias}.id = {$campaigngrouptablealias}.campaignid")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$campaigngrouptablealias}.groupid")
            ->add_callback(static function(?int $value) use ($DB): int {
                return $DB->count_records('groups_members', ['groupid' => $value]);
            });

        // Available Seats.
        $columns[] = (new column('availableseats', new lang_string('campaignsource:field_groupavailableseats', 'auth_magic'),
        $name))
            ->add_joins($this->get_joins())
            ->add_join("LEFT JOIN {auth_magic_campaign_groups} {$campaigngrouptablealias} ON
                {$campaigntablealias}.id = {$campaigngrouptablealias}.campaignid")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$campaigngrouptablealias}.groupid")
            ->add_field("{$campaigntablealias}.groupcapacity")
            ->add_callback(static function(?int $value, \stdClass $row) use ($DB): string {
                $totalmembers = $DB->count_records('groups_members', ['groupid' => $value]);
                if ($row->groupcapacity) {
                    return (string) $row->groupcapacity - $totalmembers;
                }
                return get_string('campaigns:unlimited', 'auth_magic');
            });

        // Group status.
        $columns[] = (new column('status', new lang_string('campaignsource:field_groupstatus', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->add_join("LEFT JOIN {auth_magic_campaign_groups} {$campaigngrouptablealias} ON
                {$campaigntablealias}.id = {$campaigngrouptablealias}.campaignid")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$campaigngrouptablealias}.groupid")
            ->add_field("{$campaigntablealias}.groupcapacity")
            ->add_callback(static function(?int $value, \stdClass $row) use ($DB): String {
                $totalmembers = $DB->count_records('groups_members', ['groupid' => $value]);
                if ($row->groupcapacity) {
                    $bal = $row->groupcapacity - $totalmembers;
                    if ($bal == 0) {
                        return get_string('strfull', 'auth_magic');
                    }
                    return get_string('stravailable', 'auth_magic');

                }
                return get_string('stravailable', 'auth_magic');
            });

        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        return [];
    }
}
