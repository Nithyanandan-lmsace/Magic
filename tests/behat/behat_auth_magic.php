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
 * Steps definitions related to auth_magic.
 *
 * @package   auth_magic
 * @category  test
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../../question/tests/behat/behat_question_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;

use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Steps definitions related to auth_magic.
 *
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_auth_magic extends behat_question_base {


    /**
     * Turns block editing mode on.
     *
     * @Given I turn dash block editing mode on
     */
    public function i_turn_dash_block_editing_mode_on() {
        global $CFG;

        if ($CFG->branch >= "400") {
            $this->execute('behat_forms::i_set_the_field_to', [get_string('editmode'), 1]);
            if (!$this->running_javascript()) {
                $this->execute('behat_general::i_click_on', [
                    get_string('setmode', 'core'),
                    'button',
                ]);
            }
        } else {
            $this->execute('behat_general::i_click_on', ['Blocks editing on', 'button']);
        }
    }

    /**
     * Check the magic link button position as normal.
     *
     * @When /^I check the getmagiclink as normal$/
     */
    public function i_check_the_get_magiclink_button_normal() {
        global $CFG;
        if (intval($CFG->version) > 2022030300) {
            // Moodle - 4.0. above.
            $this->execute('behat_general::assert_element_contains_text',
                ["Get a magic link via email", ".login-identityproviders", "css_element"]);
        } else {
            // Moodle - 3.11 below.
            $this->execute('behat_general::assert_element_contains_text',
                ["Get a magic link via email", ".potentialidplist", "css_element"]);
        }
    }

    /**
     * Check the magic cohort assign.
     *
     * @When /^I check the magic cohort$/
     */
    public function i_check_the_magic_cohort() {
        global $CFG;
        if (intval($CFG->version) > 2022112800) {
            // Moodle - 4.0. above.
            $this->execute('behat_reportbuilder::i_press_action_in_the_report_row', ['Assign', 'Cohort 1']);
        } else if (intval($CFG->version) > 2022041900) {
            // Moodle - 3.11 above and 4.0.
            $this->execute('behat_general::i_click_on_in_the', ['Assign', 'link', 'Cohort 1', 'table_row']);
        } else {
            // Moodle - 3.11 below.
            $this->execute('behat_general::assert_element_contains_text',
                ["2", "#cohorts", "css_element"]);
            $this->execute('behat_general::i_click_on_in_the',
            ["Assign", "link", "Cohort 1", "table_row"]);
        }
    }


    /**
     * Click on the dashboard.
     *
     * @When /^I click on magic dashboard$/
     */
    public function i_click_on_magic_dashboard() {
        global $CFG;
        if (intval($CFG->version) > 2022030300) {
            // Moodle - 4.0. above.
            $this->execute('behat_general::click_link',
                ["Dashboard"]);
        } else {
            // Moodle - 3.11 below.
            $this->execute('behat_navigation::i_follow_in_the_user_menu',
                ["Dashboard"]);
        }
    }

    /**
     * Scroll down page.
     *
     * @When /^I scroll down page$/
     */
    public function i_scroll_down_page() {
        global $CFG;
        $this->evaluate_script("window.scrollTo(0, document.body.scrollHeight);");
    }

    /**
     * Check the magic link button position as does not normal.
     *
     * @When /^I check the getmagiclink as not normal$/
     */
    public function i_check_the_get_magiclink_button_does_not_normal() {
        global $CFG;
        if (intval($CFG->version) > 2022030300) {
            // Moodle - 4.0. above.
            $this->execute('behat_general::assert_element_not_contains_text', ["Get a magic link via email",
                ".login-identityproviders", "css_element"]);
        } else {
            // Moodle - 3.11 below.
            $this->execute('behat_general::assert_element_not_contains_text', ["Get a magic link via email",
                ".potentialidplist", "css_element"]);
        }
    }

    /**
     * Check the magic link button position as belowuser.
     *
     * @When /^I check the getmagiclink as belowuser$/
     */
    public function i_check_the_get_magiclink_button_belowuser() {
        global $CFG;
        if (intval($CFG->version) > 2022030300) {
            // Moodle - 4.0. above.
            $this->execute('behat_general::assert_element_contains_text', ["Get a magic link via email",
                ".login-form-username", "css_element"]);
        } else {
            // Moodle - 3.11 below.
            $this->execute('behat_general::assert_element_contains_text', ["Get a magic link via email",
                "//form[@id='login']//div[@class='form-group'][1]", "xpath_element"]);
        }
    }

    /**
     * Check the magic link button position as does notbelowuser.
     *
     * @When /^I check the getmagiclink as not belowuser$/
     */
    public function i_check_the_get_magiclink_button_does_not_belowuser() {
        global $CFG;
        if (intval($CFG->version) > 2022030300) {
            // Moodle - 4.0. above.
            $this->execute('behat_general::assert_element_not_contains_text', ["Get a magic link via email",
                ".login-form-username", "css_element"]);
        } else {
            // Moodle - 3.11 below.
            $this->execute('behat_general::assert_element_not_contains_text', ["Get a magic link via email",
                "//form[@id='login']//div[@class='form-group'][1]", "xpath_element"]);
        }
    }


    /**
     * Check the magic link button position as belowpass.
     *
     * @When /^I check the getmagiclink as belowpass$/
     */
    public function i_check_the_get_magiclink_button_belowpass() {
        global $CFG;
        if (intval($CFG->version) > 2022030300) {
            // Moodle - 4.0. above.
            $this->execute('behat_general::assert_element_contains_text', ["Get a magic link via email",
                ".login-form-password", "css_element"]);
        } else {
            // Moodle - 3.11 below.
            $this->execute('behat_general::assert_element_contains_text', ["Get a magic link via email",
                "//form[@id='login']//div[@class='form-group'][2]", "xpath_element"]);
        }
    }

    /**
     * Check the magic link button position as does not belowpass.
     *
     * @When /^I check the getmagiclink as not belowpass$/
     */
    public function i_check_the_get_magiclink_button_does_not_belowpass() {
        global $CFG;
        if (intval($CFG->version) > 2022030300) {
            // Moodle - 4.0. above.
            $this->execute('behat_general::assert_element_not_contains_text', ["Get a magic link via email",
                ".login-form-password", "css_element"]);
        } else {
            // Moodle - 3.11 below.
            $this->execute('behat_general::assert_element_not_contains_text', ["Get a magic link via email",
                "//form[@id='login']//div[@class='form-group'][2]", "xpath_element"]);
        }
    }


    /**
     * Navigate the course enrolment page.
     *
     * @When /^I navigate course enrolment page "(?P<course_name>[^"]+)"$/
     * @param string $coursename
     */
    public function i_navigate_to_the_course_enrolmentspage($coursename) {
        global $CFG;
        if (intval($CFG->version) > 2022030300) {
            // Moodle - 4.0. above.
            $this->execute('behat_navigation::i_am_on_page_instance', [$coursename,
                "enrolment methods"]);
        } else {
            // Moodle - 3.11 below.
            $this->execute('behat_navigation::i_am_on_course_homepage', [$coursename]);
            $this->execute("behat_navigation::i_navigate_to_in_current_page_administration", ["Users > Enrolment methods"]);
        }
    }

    /**
     * Navigate the course group page.
     *
     * @When /^I navigate course groups page "(?P<course_name>[^"]+)"$/
     * @param string $coursename
     */
    public function i_navigate_to_the_course_groupspage($coursename) {
        global $CFG;
        if (intval($CFG->version) > 2022030300) {
            // Moodle - 4.0. above.
            $this->execute('behat_navigation::i_am_on_page_instance', [$coursename,
                "groups"]);
        } else {
            // Moodle - 3.11 below.
            $this->execute('behat_navigation::i_am_on_course_homepage', [$coursename]);
            $this->execute("behat_navigation::i_navigate_to_in_current_page_administration", ["Users > Groups"]);
        }
    }

    /**
     * Open a magic campaign.
     * @When /^I open magic campaign "(?P<task_name>[^"]+)"$/
     * @param string $name
     */
    public function i_open_magic_campaign_link($name) {
        global $DB;
        $campaign = $DB->get_record("auth_magic_campaigns", ['title' => $name]);
        $campaignurl = "/auth/magic/campaigns/view.php?code=" . $campaign->code;
        $this->execute('behat_general::i_visit', [$campaignurl]);
    }

    /**
     * Change the campaign config.
     * @When /^I change single campaign config "(?P<field_name>[^"]+)" to "(?P<value>[^"]+)"$/
     * @param string $field
     * @param string $value
     */
    public function i_change_campaign_single_config($field, $value) {
        global $DB;
        $this->execute('behat_auth::i_log_in_as', ["admin"]);
        $this->execute('behat_navigation::i_navigate_to_in_site_administration', ['Plugins > Authentication > Manage campaign']);
        $this->execute('behat_general::i_click_on_in_the', ['.icon[title=Edit]', 'css_element', 'Demo campaign', 'table_row']);
        $this->execute('behat_general::click_link', ["Expand all"]);
        $this->execute('behat_forms::i_set_the_field_to', [$field, $value]);
        $this->execute('behat_forms::press_button', "Save changes");
        $this->execute('behat_auth::i_log_out');
    }
    /**
     * Creates a datasource for dash block.
     *
     * @Given I create dash :arg1 datasource
     *
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $datasource
     */
    public function i_create_dash_datasource($datasource) {
        global $CFG;

        $this->execute('behat_navigation::i_navigate_to_in_site_administration',
            ['Appearance > Default Dashboard page']);
        $this->execute('behat_block_dash::i_turn_dash_block_editing_mode_on', []);
        $this->execute('behat_blocks::i_add_the_block', ["Dash"]);
        $this->execute('behat_general::i_click_on_in_the', [$datasource, 'text', 'New Dash', 'block']);
    }

}
