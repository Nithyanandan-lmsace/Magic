<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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
 * Payments datasource
 *
 * @package    auth_magic
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace auth_magic\reportbuilder\datasource;

use core_reportbuilder\datasource;
use core_reportbuilder\local\entities\user;
use auth_magic\reportbuilder\local\entities\campaign;
use auth_magic\reportbuilder\local\entities\campaign_statistics;
use auth_magic\reportbuilder\local\entities\user_statistics;
use auth_magic\reportbuilder\local\entities\campaign_groups;

/**
 * Payments datasource
 *
 * @package    auth_magic
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class campaigns extends datasource {
    /**
     * Initialise report
     */
    protected function initialise(): void {

        $main = new campaign();
        $mainalias = $main->get_table_alias('auth_magic_campaigns');
        $mainname = $main->get_entity_name();

        $this->set_main_table('auth_magic_campaigns', $mainalias);
        $this->add_entity($main);


        // Campaign Groups.
        $campaigngroups = new campaign_groups();
        $campaigngroupsname = $campaigngroups->get_entity_name();
        $this->add_entity($campaigngroups);

        // Campaign Statistics.
        $campaignstatistics = new campaign_statistics();
        $campaignstatisticsname = $campaignstatistics->get_entity_name();
        $this->add_entity($campaignstatistics);

        $magiccampaignusersalias = $main->get_table_alias('auth_magic_campaigns_users');

        // User Statistics.
        $userstatistics = new user_statistics();
        $userstatisticsname = $userstatistics->get_entity_name();
        $this->add_entity($userstatistics->add_join(
            "LEFT JOIN {auth_magic_campaigns_users} {$magiccampaignusersalias} ON {$magiccampaignusersalias}.campaignid = {$mainalias}.id"
        ));

        $user = new user();
        $useralias = $user->get_table_alias('user');
        $username = $user->get_entity_name();
        $this->add_entity($user->add_joins($userstatistics->get_joins())->add_join("LEFT JOIN {user} {$useralias}
        ON {$useralias}.id = {$magiccampaignusersalias}.userid"));


        if (method_exists($this, 'add_all_from_entities')) {
            $this->add_all_from_entities();
        } else {
            $this->add_columns_from_entity($mainname);
            $this->add_filters_from_entity($mainname);
            $this->add_conditions_from_entity($mainname);

            $this->add_columns_from_entity($campaigngroupsname);
            $this->add_filters_from_entity($campaigngroupsname);
            $this->add_conditions_from_entity($campaigngroupsname);

            $this->add_columns_from_entity($campaignstatisticsname);
            $this->add_filters_from_entity($campaignstatisticsname);
            $this->add_conditions_from_entity($campaignstatisticsname);

            $this->add_columns_from_entity($username);
            $this->add_filters_from_entity($username);
            $this->add_conditions_from_entity($username);

            $this->add_columns_from_entity($userstatisticsname);
            $this->add_filters_from_entity($userstatisticsname);
            $this->add_conditions_from_entity($userstatisticsname);
        }

    }

    /**
     * Get the visible name of the report.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('reportsource_campaign', 'auth_magic');
    }

    // alex        saeed      alex
    // aaleex      ssaaedd    aaleexa

    /**
     * Return the columns that will be added to the report once is created
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'campaign:title',
            'campaign:description',
            'campaign:comments',
            'campaign:status',
            'campaign:visibility',
            'campaign:campaignowner',
        ];
    }

    /**
     * Return the filters that will be added to the report once is created
     *
     * @return string[]
     */
    public function get_default_filters(): array {
        return [];
    }

    /**
     * Return the conditions that will be added to the report once is created
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return [];
    }
}
