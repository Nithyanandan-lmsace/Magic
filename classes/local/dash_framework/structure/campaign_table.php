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
 * Class campaign_table.
 *
 * @package     auth_magic
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic\local\dash_framework\structure;
use block_dash\local\dash_framework\structure\table;
use block_dash\local\dash_framework\structure\field;
use lang_string;
use moodle_url;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use auth_magic\local\block_dash\attribute\campaign_bgimage_url;
use block_dash\local\data_grid\field\attribute\image_attribute;
use block_dash\local\data_grid\field\attribute\linked_data_attribute;
use auth_magic\local\block_dash\attribute\campaign_headerimage_url;
use auth_magic\local\block_dash\attribute\campaign_logo_url;
use auth_magic\local\block_dash\attribute\campaign_description;
use auth_magic\local\block_dash\attribute\campaign_notes;
use auth_magic\local\block_dash\attribute\campaign_capacitystatus;
use auth_magic\local\block_dash\attribute\campaign_availableandtotalcapacitystatus;
use auth_magic\local\block_dash\attribute\campaign_availablecapacitystatus;
use auth_magic\local\block_dash\attribute\campaign_totalcapacitystatus;
use block_dash\local\data_grid\field\attribute\date_attribute;
use block_dash\local\data_grid\field\attribute\bool_attribute;
use auth_magic\local\block_dash\attribute\campaign_status_bool_attribute;
use auth_magic\local\block_dash\attribute\campaign_approvalroles;
use auth_magic\local\block_dash\attribute\campaign_enrolmentkey;
use auth_magic\local\block_dash\attribute\campaign_cohortmembership;
use auth_magic\local\block_dash\attribute\campaign_globalrole;
use auth_magic\local\block_dash\attribute\campaign_owner;
use auth_magic\local\block_dash\attribute\campaign_payment;
use auth_magic\local\block_dash\attribute\campaign_registerfee;
use auth_magic\local\block_dash\attribute\campaign_url_attribute;

use auth_magic\local\block_dash\attribute\campaign_restrictbycohort;
use auth_magic\local\block_dash\attribute\campaign_restrictbyrole;


/**
 * Class campaign_table.
 *
 * @package auth_magic
 */
class campaign_table extends table {

    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('auth_magic_campaigns', 'amc');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('strcampaign', 'auth_magic');
    }

    /**
     * Get fields
     *
     * @return field_interface[]
     */
    public function get_fields(): array {
        $fields = [
            new field('id', new lang_string('id', 'auth_magic'), $this, null, [
                new identifier_attribute(),
            ]),
            new field('title', new lang_string('name'), $this),
            // Background image link.
            new field('bgimage', new lang_string('backgroundimage', 'block_dash'), $this, 'amc.id', [
                new campaign_bgimage_url(),
                new image_attribute(),
            ]),
            new field('bgimage_link', new lang_string('backgroundimagelink', 'auth_magic'), $this, 'amc.id', [
                new campaign_bgimage_url(), new image_attribute(),
                new campaign_url_attribute(),
            ]),
            new field('headerimage', new lang_string('headerimage', 'auth_magic'), $this, 'amc.id', [
                new campaign_headerimage_url(),
                new image_attribute(),
            ]),
            // Header image link.
            new field('headerimage_link', new lang_string('headerimagelink', 'auth_magic'), $this, 'amc.id', [
                new campaign_headerimage_url(), new image_attribute(),
                new campaign_url_attribute(),
            ]),
            new field('logoimage', new lang_string('logoimage', 'auth_magic'), $this, 'amc.id', [
                new campaign_logo_url(),
                new image_attribute(),
            ]),

            // Logo image link.
            new field('logoimage_link', new lang_string('logoimagelink', 'auth_magic'), $this, 'amc.id', [
                new campaign_logo_url(), new image_attribute(),
                new campaign_url_attribute(),
            ]),
            new field('description', new lang_string('description'), $this, 'amc.description', [
                new campaign_description(),
            ]),
            new field('notes', new lang_string('comments', 'auth_magic'), $this, 'amc.comments', [
                new campaign_notes(),
            ]),
            new field('timecreated', new lang_string('timecreated'), $this, 'amc.timecreated', [
                new date_attribute(),
            ]),
            new field('timemodified', new lang_string('timemodified', 'auth_magic'), $this, 'amc.timemodified', [
                new date_attribute(),
            ]),
            new field('capacitystatus', new lang_string('capacitystatus', 'auth_magic'), $this, 'amc.capacity', [
                new campaign_capacitystatus(),
            ]),
            new field('totalcapacitystatus', new lang_string('totalcapacitystatus', 'auth_magic'), $this, 'amc.capacity', [
                new campaign_totalcapacitystatus(),
            ]),
            new field('availablecapacitystatus', new lang_string('availablecapacitystatus', 'auth_magic'), $this, 'amc.capacity', [
                new campaign_availablecapacitystatus(),
            ]),
            new field('availableandtotalcapacitystatus', new lang_string('availableandtotalcapacitystatus', 'auth_magic'),
             $this, 'amc.capacity', [new campaign_availableandtotalcapacitystatus(),
            ]),
            new field('status', new lang_string('campaigns:status', 'auth_magic'), $this, 'amc.status',
            [
                new campaign_status_bool_attribute(),
            ]),
            new field('visibility', new lang_string('campaignsource:field_visibility', 'auth_magic'), $this, 'amc.visibility', [
                new bool_attribute(),
            ]),
            new field('restrictbyrole', new lang_string('campaignsource:field_restrictbyrole', 'auth_magic'), $this,
                'amc.restrictroles', [ new campaign_restrictbyrole(),
            ]),
            new field('restrictbycohort', new lang_string('campaignsource:field_restrictbycohort', 'auth_magic'), $this,
                'amc.restrictcohorts', [ new campaign_restrictbycohort(),
            ]),
            new field('approvaltype', new lang_string('approvaltype', 'auth_magic'), $this, 'amc.approvaltype'),
            new field('approvalroles', new lang_string('approvalroles', 'auth_magic'), $this, 'amc.approvalroles', [
                new campaign_approvalroles(),
            ]),
            new field('availablefrom', new lang_string('campaignsource:field_startdate', 'auth_magic'), $this, 'amc.startdate',
            [
                new date_attribute(),
            ]),
            new field('availablecloses', new lang_string('campaignsource:field_enddate', 'auth_magic'), $this, 'amc.enddate',
            [
                new date_attribute(),
            ]),
            new field('password', new lang_string('campaignsource:field_password', 'auth_magic'), $this, 'amc.password', [
                new bool_attribute(),
            ]),
            new field('enrolmentkey', new lang_string('campaigns:courseenrolmentkey', 'auth_magic'), $this,
            'amc.courseenrolmentkey', [ new campaign_enrolmentkey(),
            ]),
            new field('cohortmembership', new lang_string('campaignsource:field_cohorts', 'auth_magic'), $this, 'amc.cohorts', [
                new campaign_cohortmembership(),
            ]),
            new field('globalrole', new lang_string('campaignsource:field_globalrole', 'auth_magic'), $this, 'amc.globalrole', [
                new campaign_globalrole(),
            ]),
            new field('campaignowner', new lang_string('campaignsource:field_campaignowner', 'auth_magic'), $this,
            'amc.campaignowner', [ new campaign_owner(),
            ]),
            new field('campaignownerwithlink', new lang_string('campaignsource:field_campaignownerwithlink', 'auth_magic'),
            $this, 'amc.campaignowner', [
                new campaign_owner(), new linked_data_attribute(['url' => new moodle_url('/user/profile.php',
                ['id' => 'amc_campaignowner'],)])
            ]),
            new field('payment', new lang_string('campaigns:payment', 'auth_magic'), $this, 'amc.id', [
                new campaign_payment(),
            ]),
            new field('registerfee', new lang_string('campaignsource:field_fee', 'auth_magic'), $this, 'amc.id', [
                new campaign_registerfee(),
            ]),
            new field('expirydate', new lang_string('campaignsource:field_expirydate', 'auth_magic'), $this, 'amc.expirydate',
            [
                new date_attribute(),
            ]),
        ];
        return $fields;
    }
}
