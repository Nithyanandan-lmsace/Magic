@auth @auth_magic @custom_magic_login
Feature: Login to user for magic authentication custom page.
  In order login user actions properly for magic auth.

  Background:
    Given I log in as "admin"
    And I navigate to "Plugins > Authentication > Manage authentication" in site administration
    And I click on "Enable" "link" in the "Magic authentication" "table_row"
    Then the following "users" exist:
        | username | firstname | lastname | email             | auth |
        | user_01  | user_01   | user_01  | user_01@gmail.com | manual|
        | user_02  | user_02   | user_02  | user_02@gmail.com | magic|
        | user_03  | user_03   | user_03  | user_03@gmail.com | magic|
    Then I navigate to "Plugins > Authentication > Manage authentication" in site administration
    And I set the field "Alternate login URL" to "/auth/magic/signin.php"
    Then I press "Save changes"
    Then I log out

  @javascript
  Scenario: Login the manual auth user to magic login page.
    Given I am on site homepage
    And I follow "Log in"
    And ".loginform.magic" "css_element" should exist
    Then I set the field "email" to "user_01@gmail.com"
    Then I click on ".magic-submit-action" "css_element"
    Then I set the field "password" to "user_01"
    And I press "Sign In"
    Then I click on magic dashboard
    Then "#page-my-index" "css_element" should exist

  @javascript
  Scenario: Login the magic auth user to magic login page.
    Given I am on site homepage
    And I follow "Log in"
    And ".loginform.magic" "css_element" should exist
    Then I set the field "email" to "user_02@gmail.com"
    Then I click on ".magic-submit-action" "css_element"
    And I should see "Get a magic link via email"
    And I click on "Get a magic link via email" "link"
    Then I should see "If you supplied a correct email address, an email containing a magic login link should have been sent to you."
