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
use auth_magic\campaign;
use stdClass;


/**
 * Campaign phpunit test cases defined.
 */
class campaign_test extends \advanced_testcase {

    /**
     * Set the admin user as User.
     *
     * @return void
     */
    public function setup(): void {
        global $CFG;
        require_once($CFG->dirroot.'/auth/magic/campaigns/campaign_helper.php');
        $this->resetAfterTest(true);
    }

    /**
     * Test restriction_bydate
     * @covers ::restriction_bydate
     */
    public function test_restriction_bydate() {
        $campaigndata = campaign_helper_test::get_campaign_data("Demo campaign 01");
        $campaigndata->startdate = strtotime("-20 days");
        $campaigndata->enddate = strtotime("-1 days");
        $campaignid = \campaign_helper::create_campaign($campaigndata);
        $campaignhelper = \campaign_helper::instance($campaignid);
        $campaigninstance = new campaign($campaignid);
        $this->assertFalse($campaigninstance->restriction_bydate());
        $campaignhelper->update_field('startdate', strtotime("-1days"));
        $campaignhelper->update_field('enddate', strtotime("+10days"));
        $campaigninstance1 = new campaign($campaignid);
        $this->assertTrue($campaigninstance1->restriction_bydate());
        $campaignhelper->update_field('enddate', strtotime("-2days"));
        $campaigninstance2 = new campaign($campaignid);
        $this->assertFalse($campaigninstance2->restriction_bydate());
    }

    /**
     * Test assign_user
     * @covers ::assign_user
     */
    public function test_assign_user() {
        global $DB;
        $campaigndata = campaign_helper_test::get_campaign_data("Demo campaign 01");
        $campaignid = \campaign_helper::create_campaign($campaigndata);
        $campaigninstance = new campaign($campaignid);
        $user = $this->getDataGenerator()->create_user();
        $campaigninstance->assign_user(null, $user->id, '');
        $this->assertEquals(1, $DB->count_records('auth_magic_campaigns_users'));
        $result = $DB->get_field('auth_magic_campaigns_users', 'userid', ['campaignid' => $campaignid]);
        $this->assertEquals($user->id, $result);
    }

    /**
     * Test send_welcome_message
     * @covers ::send_welcome_message
     */
    public function test_send_welcome_message() {
        global $DB;
        $campaigndata = campaign_helper_test::get_campaign_data("Demo campaign 01");
        $campaigndata->welcomemessage = 1;
        $campaigndata->welcomemessagecontent_editor = [
            'text' => "<p> Example welcome message",
            'format' => 1,
        ];
        $campaignid = \campaign_helper::create_campaign($campaigndata);
        $campaigninstance = new campaign($campaignid);
        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $sink = $this->redirectMessages();
        $campaigninstance->send_welcome_message($user->id, "", null);
        $messages = $sink->get_messages();
        $sink->close();
        $this->assertCount(1, $messages);
    }

    /**
     * Test send_followup_message
     * @covers ::send_followup_message
     */
    public function test_send_followup_message() {
        global $DB;
        $campaigndata = campaign_helper_test::get_campaign_data("Demo campaign 01");
        $campaigndata->followupmessage = 1;
        $campaigndata->followupmessagecontent_editor = [
            'text' => "<p> Example welcome message",
            'format' => 1,
        ];
        $campaignid = \campaign_helper::create_campaign($campaigndata);
        $user = $this->getDataGenerator()->create_user(['auth' => 'magic']);
        $campaign = $DB->get_record('auth_magic_campaigns', ['id' => $campaignid]);
        $sink = $this->redirectMessages();
        campaign::send_followup_message($user, $campaign, \core_user::get_support_user());
        $messages = $sink->get_messages();
        $sink->close();
        $this->assertCount(1, $messages);
    }

    /**
     * Test campaign_capacity
     * @covers ::campaign_capacity
     */
    public function test_campaign_capacity() {
        global $DB;
        $campaigndata = campaign_helper_test::get_campaign_data("Demo campaign 01");
        $campaigndata->capacity = 3;
        $campaignid = \campaign_helper::create_campaign($campaigndata);
        $user = $this->getDataGenerator()->create_user();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $campaigninstance = new campaign($campaignid);
        $campaigninstance->assign_user(null, $user->id, '');
        $result = $campaigninstance->get_capacity();
        $this->assertEquals($result->used, 1);
        $this->assertEquals($result->available, 2);
        $this->assertTrue($campaigninstance->is_campaign_available());
        $campaigninstance->assign_user(null, $user1->id, '');
        $campaigninstance->assign_user(null, $user2->id, '');
        $result = $campaigninstance->get_capacity();
        $this->assertEquals($result->used, 3);
        $this->assertEquals($result->available, 0);
        $this->assertFalse($campaigninstance->is_campaign_available());
    }

    /**
     * Test verify_password
     * @covers ::verify_password
     */
    public function test_verify_password() {
        $campaigndata = campaign_helper_test::get_campaign_data("Demo campaign 01");
        $campaigndata->password = "Test123#";
        $campaignid = \campaign_helper::create_campaign($campaigndata);
        $campaigninstance = new campaign($campaignid);
        $data = new stdClass();
        $data->campaignpassword = "Test123#";
        $this->assertTrue($campaigninstance->verify_password($data));
        $data2 = new stdClass();
        $data2->campaignpassword = "Test123##";
        $this->assertFalse($campaigninstance->verify_password($data2));

    }

}
