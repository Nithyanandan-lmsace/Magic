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
 * Sessions data source
 *
 * @package    auth_magic
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic\local\block_dash;

use context;
use auth_magic\local\dash_framework\structure\campaign_table;
use block_dash\local\data_source\abstract_data_source;
use block_dash\local\dash_framework\query_builder\builder;
use block_dash\local\data_grid\filter\filter_collection_interface;
use block_dash\local\data_grid\filter\filter_collection;
use block_dash\local\dash_framework\query_builder\join;
use auth_magic\local\block_dash\filter\campaign_payment_filter;
use auth_magic\local\block_dash\filter\campaign_approval_types_filter;
use auth_magic\local\block_dash\filter\campaign_owner_filter;
use auth_magic\local\block_dash\filter\campaign_password_filter;
use auth_magic\local\block_dash\filter\campaign_my_campaign_condition;
use auth_magic\local\block_dash\filter\campaign_approval_types_condition;
use auth_magic\local\block_dash\filter\campaign_dates_condition;
use auth_magic\local\block_dash\filter\campaign_hide_my_campaign_condition;
use block_dash\local\data_grid\filter\bool_filter;


/**
 * Sessions data source
 *
 * @copyright  2020 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class campaign_data_source extends abstract_data_source {

    /**
     * Constructor.
     *
     * @param context $context
     */
    public function __construct(context $context) {
        $this->add_table(new campaign_table());
        parent::__construct($context);
    }


    /**
     * Get query template
     *
     * @return builder
     */
    public function get_query_template(): builder {
        global $USER;
        $builder = new builder();
        $builder
        ->select('amc.id', 'amc_id')
        ->select('CASE WHEN amc.password = "" THEN 0 ELSE 1 END', 'amc_passwordstatus')
        ->from('auth_magic_campaigns', 'amc')
        ->join('auth_magic_campaigns_payment', 'amcp', 'campaignid', 'amc.id', join::TYPE_LEFT_JOIN);

        $builder->where('amc.status', [0]);
        $builder->where_raw('(amc.campaignowner = :userid OR amc.visibility = 1)', ['userid' => $USER->id]);
        return $builder;
    }


    /**
     * Build filter collection
     *
     * @return filter_collection_interface
     */
    public function build_filter_collection() {
        $filter = new filter_collection(get_class($this), $this->get_context());
        $filter->add_filter(new campaign_payment_filter('campaign_payment', 'amcp.type'));
        $filter->add_filter(new campaign_password_filter('campaign_password', 'amc.id'));
        $filter->add_filter(new campaign_approval_types_filter('campaign_approval_types', 'amc.approvaltype'));
        $filter->add_filter(new campaign_owner_filter('campaign_owner', 'amc.campaignowner'));
        $filter->add_filter(new campaign_my_campaign_condition('my_campaign', 'amc.campaignowner'));
        $filter->add_filter(new campaign_approval_types_condition('approval_types', 'amc.approvaltype'));
        $filter->add_filter(new campaign_dates_condition('campaign_dates', 'amc.id'));
        $filter->add_filter(new campaign_hide_my_campaign_condition('hide_my_campaign', 'amc.id'));
        return $filter;
    }

}