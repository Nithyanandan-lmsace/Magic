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
 *  Check the campaigns works fine.
 *
 * @package   auth_magic
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic;

use phpunit_util;
use stdClass;

/**
 * Campaign phpunit test cases defined.
 */
class campaign_helper_test extends \advanced_testcase {

    /**
     * Set the admin user as User.
     *
     * @return void
     */
    public function setup(): void {
        global $CFG;
        require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_helper.php');
        $this->resetAfterTest(true);
        $this->setAdminUser();
    }

    /**
     * Get the campaign data.
     * @param string $title
     * @return array data
     */
    public static function get_campaign_data($title) {
        $data = new stdClass;
        $data->title = $title;
        $data->status = 0;
        $data->visibility = 1;
        $data->description_editor = [
            'text' => '',
            'format' => '',
        ];
        $data->logo_filemanager = '';
        $data->paymenttype = 'free';
        $data->headerimage_filemanager = '';
        $data->backgroundimage_filemanager = '';
        $data->comments_editor = [
            'text' => '',
            'format' => '',
        ];
        $data->consentstatement_editor = [
            'text' => '',
            'format' => '',
        ];
        $data->welcomemessagecontent_editor = [
            'text' => '',
            'format' => '',
        ];

        $data->followupmessagecontent_editor = [
            'text' => '',
            'format' => '',
        ];
        $data->submissioncontent_editor = [
            'text' => '',
            'format' => '',
        ];
        return $data;

    }

    /**
     * Create a campaign
     * @param string $title
     * @return int campaign id
     */
    public static function create_campaign($title) {
        $campaigndata = self::get_campaign_data($title);
        return \campaign_helper::create_campaign($campaigndata);
    }

    /**
     * Test create_campaign
     * @covers ::create_campaign
     */
    public function test_create_campaign() {
        global $DB;
        $formdata = [];
        $campaigndata = self::get_campaign_data('Demo campaign 01');
        $campaignid = \campaign_helper::create_campaign($campaigndata);
        $this->assertEquals(1, $DB->count_records('auth_magic_campaigns'));
        $result = $DB->get_record('auth_magic_campaigns', ['id' => $campaignid]);
        $this->assertEquals($result->title, 'Demo campaign 01');
    }

    /**
     * Test update_campaign_payment
     * @covers ::update_campaign_payment
     */
    public function test_update_campaign_payment() {
        $campaigndata = self::get_campaign_data('Demo campaign 01');
        $campaigndata->paymenttype = 'paid';
        $campaigndata->paymentfee = 0.5;
        $campaigndata->paymentcurrency = 0.5;
        $campaigndata->paymentaccount = 0.5;

    }

    /**
     * Test update_campaign
     * @covers ::update_campaign
     */
    public function test_update_campaign() {
        global $DB;
        $formdata = [];
        $title = "Demo campaign 01";
        $campaignid = $this->create_campaign($title);
        $campaign = self::get_campaign_data("Demo campaign 02");
        $campaign->id = $campaignid;
        \campaign_helper::update_campaign($campaign);
        $this->assertEquals(1, $DB->count_records('auth_magic_campaigns'));
        $result = $DB->get_record('auth_magic_campaigns', ['id' => $campaignid]);
        $this->assertEquals($result->title, 'Demo campaign 02');
    }

    /**
     * Test get_campaign
     * @covers ::get_campaign
     */
    public function test_get_campaign() {
        $campaignid = $this->create_campaign("Demo campaign 01");
        $campaign = \campaign_helper::get_campaign($campaignid);
        $this->assertEquals($campaign->title, 'Demo campaign 01');
        $this->assertEquals($campaign->id, $campaignid);
    }


    /**
     * Test update_visible
     * @covers ::update_visible
     */
    public function test_update_visible() {
        $campaignid = $this->create_campaign("Demo campaign 01");
        $campaign = \campaign_helper::instance($campaignid);
        $campaigninfo = \campaign_helper::get_campaign($campaignid);
        $this->assertEquals(1, $campaigninfo->visibility);
        $campaign->update_visible(false);
        $result = \campaign_helper::get_campaign($campaignid);
        $this->assertEquals(0, $result->visibility);
    }

    /**
     * Test delete_campaign
     * @covers ::delete_campaign
     */
    public function test_delete_campaign() {
        global $DB;
        $campaigndata = self::get_campaign_data("Demo campaign 01");
        $campaignid = \campaign_helper::manage_instance($campaigndata);
        $this->assertEquals(1, $DB->count_records('auth_magic_campaigns'));
        $campaign = \campaign_helper::instance($campaignid);
        $campaign->delete_campaign();
        $this->assertEquals(0, $DB->count_records('auth_magic_campaigns'));
    }
}
