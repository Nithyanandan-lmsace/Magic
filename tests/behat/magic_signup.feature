@auth @auth_magic @magic_signup
Feature: Signup to the user using magic authentication.
  In order signup user actions properly for magic auth.

  Background:
    Given I log in as "admin"
    And I navigate to "Plugins > Authentication > Manage authentication" in site administration
    And I click on "Enable" "link" in the "Magic authentication" "table_row"
    Then the following "users" exist:
        | username | firstname | lastname | email             | auth |
        | user_01  | user_01   | user_01  | user_01@gmail.com | manual|
    Then I navigate to "Plugins > Authentication > Magic authentication> Magic Sign Up" in site administration
    And I set the field "Auto create users" to "1"
    Then I press "Save changes"
    Then I log out

  @javascript
  Scenario: Signup the custom login page.
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Magic authentication> Manage authentication" in site administration
    And I set the field "Alternate login URL" to "/auth/magic/signin.php"
    Then I press "Save changes"
    Then I log out
    And I follow "Log in"
    And ".loginform.magic" "css_element" should exist
    Then I set the field "email" to "campaignuser_01@gmail.com"
    Then I click on ".magic-submit-action" "css_element"
    And I should see "Get a magic link via email"
    And I click on "Get a magic link via email" "link"
    Then I should see "If you supplied a correct email address, an email containing a registration link should have been sent to you."
    Then I log in as "admin"
    Then I navigate to "Plugins > Authentication > Magic Sign Up" in site administration
    And I set the field "Auto create users" to "0"
    Then I press "Save changes"
    Then I log out
    And I follow "Log in"
    And ".loginform.magic" "css_element" should exist
    Then I set the field "email" to "campaignuser_02@gmail.com"
    Then I click on ".magic-submit-action" "css_element"
    #Then I should see "Doesn't exist user email"

  @javascript
  Scenario: Signup the default login page.
    And I follow "Log in"
    And I should see "Get a magic link via email"
    Then I set the field "Username" to "campaignuser_01@gmail.com"
    And I click on "Get a magic link via email" "link"
    Then I should see "If you supplied a correct email address, an email containing a registration link should have been sent to you."
    Then I log in as "admin"
    Then I navigate to "Plugins > Authentication > Magic Sign Up" in site administration
    And I set the field "Auto create users" to "0"
    Then I press "Save changes"
    Then I log out
    And I follow "Log in"
    And I should see "Get a magic link via email"
    Then I set the field "Username" to "campaignuser_01@gmail.com"
    And I click on "Get a magic link via email" "link"
    Then I should not see "If you supplied a correct email address, an email containing a registration link should have been sent to you."
    Then I should see "Doesn't exist user email"
