@auth @auth_magic @magic_pro
Feature: List of features to magic authentication pro.
  In order to feaures to magic authentication pro.
  Background:
    Given I log in as "admin"
    And I navigate to "Plugins > Authentication > Manage authentication" in site administration
    And I click on "Enable" "link" in the "Magic authentication" "table_row"
    Then the following "users" exist:
      | username | firstname | lastname | email             | auth |
      | user_01  | user_01   | user_01  | user_01@gmail.com | magic|
      | user_02  | user_02   | user_02  | user_02@gmail.com | magic|
    Then I log out

  @javascript
  Scenario: Check allow user to use username.
    Given I am on homepage
    And I expand navigation bar
    And I click on "Log in" "link" in the ".logininfo" "css_element"
    And I should see "Get a magic link via email"
    When I set the field "Username" to "user_01"
    And I click on "Get a magic link via email" "link"
    And I should see "Invalid email address"
    Then I log in as "admin"
    And I navigate to "Plugins > Authentication > Magic authentication" in site administration
    And I set the following fields to these values:
      | Allow Username to get magic link | 1 |
    Then I press "Save changes"
    Then I log out
    And I click on "Log in" "link" in the ".logininfo" "css_element"
    Then I set the field "Username" to "user_01"
    And I click on "Get a magic link via email" "link"
    Then I should see "If you supplied a correct username, an email containing a magic login link should have been sent to your email address."

  @javascript
  Scenario: Check magic login button position.
    Given I am on homepage
    And I expand navigation bar
    And I click on "Log in" "link" in the ".logininfo" "css_element"
    And I check the getmagiclink as normal
    And I check the getmagiclink as not belowuser
    And I check the getmagiclink as not belowpass
    Then I log in as "admin"
    And I navigate to "Plugins > Authentication > Magic authentication" in site administration
    And I set the following fields to these values:
      | Magic login link button position | Below username |
    Then I press "Save changes"
    Then I log out
    And I click on "Log in" "link" in the ".logininfo" "css_element"
    And I check the getmagiclink as not normal
    And I check the getmagiclink as belowuser
    And I check the getmagiclink as not belowpass
    Then I should see "- or type in your password -"
    Then I log in as "admin"
    And I navigate to "Plugins > Authentication > Magic authentication" in site administration
    And I set the following fields to these values:
      | Magic login link button position | Below password |
    Then I press "Save changes"
    Then I log out
    And I click on "Log in" "link" in the ".logininfo" "css_element"
    Then I check the getmagiclink as not normal
    And I check the getmagiclink as not belowuser
    And I check the getmagiclink as belowpass
