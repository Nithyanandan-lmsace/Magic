@auth @auth_magic @magic_campaign @_file_upload
Feature: Magic campaign workflow.
  In order campaign workflow for magic auth.

  Background:
    Given I log in as "admin"
    And I navigate to "Plugins > Authentication > Manage authentication" in site administration
    And I click on "Enable" "link" in the "Magic authentication" "table_row"
    Then the following "users" exist:
      | username      | firstname     | lastname    | email                     | auth   |
      | user_01       | user_01       | user_01     | user_01@gmail.com         | manual |
      | user_02       | user_02       | user_02     | user_02@gmail.com         | manual |
      | user_03       | user_03       | user_03     | user_03@gmail.com         | manual |
      | parentuser_01 | parentuser_01 | parentuser_01 | parentuser_01@gmail.com | manual |
      | manager1      | manager1      | manager1    | manager1@gmail.com        | manual |
      | manager_01    | manager_01    | manager_01  | manager_01@gmail.com      | manual |
    Then the following "cohorts" exist:
      | name     | idnumber |
      | Cohort 1 | CH1      |
      | Cohort 2 | CH2      |
      | Cohort 3 | CH3      |
    And the following "cohort members" exist:
      | user          | cohort    |
      | user_01       | CH1       |
      | user_01       | CH3       |
      | user_02       | CH2       |
      | user_03       | CH3       |
    Given the following "role assigns" exist:
      | user       | role    | contextlevel | reference |
      | manager_01 | manager | System       |           |
    Then the following "categories" exist:
      | name           | category | idnumber | category |
      | Category E     | 0        | CE       | 0        |
      | Category ED    | 1        | CED      | CE       |
    And the following "courses" exist:
      | fullname  | shortname | category |
      | Course C1 | CC1       | CE       |
      | Course C2 | CC2       | 0        |
    And the following "groups" exist:
      | name      | course  | idnumber |
      | Group 1   | CC1     | G1       |
      | Group 2   | CC2     | G2       |
    And the following "groupings" exist:
      | name       | course | idnumber |
      | Grouping 1 | CC1    | GG1      |
      | Grouping 2 | CC1    | GG2      |
      | Grouping 3 | CC2    | GG3      |
    And the following "course enrolments" exist:
      | user       | course | role     |
      | user_01    | CC1    | student  |
      | admin      | CC1    | student  |
      | manager1   | CC1    | student  |
      | manager1   | CC1    | manager  |
    Then I navigate course enrolment page "Course C1"
    And I click on "Enable" "link" in the "Self enrolment" "table_row"
    Then I log out

  @javascript
  Scenario: Check campaign course.
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title           | Demo campaign   |
      | Status          |  Available      |
      | Visibility      | Visible         |
      | Campaign course | Course C1       |
      | Course role for student | Student |
    Then I press "Save changes"
    And I log out
    And I open magic campaign "Demo campaign"
    Then I should see "Demo campaign"
    And I set the following fields to these values:
      | firstname  | campaignuser_01 |
      | lastname   | campaignuser_01 |
      | username   | campaignuser_01 |
      | password   | Test123#        |
      | email      | campaignuser_01@gmail.com |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    And I log out
    Then I log in as "admin"
    And I am on "Course C1" course homepage
    And I navigate to course participants
    Then I should see "campaignuser_01 campaignuser_01"
    Then I should see "campaignuser_01@gmail.com" in the "campaignuser_01 campaignuser_01" "table_row"
    Then I should see "Student" in the "campaignuser_01 campaignuser_01" "table_row"
    And I log out

  @javascript
  Scenario: Check campaign course and approval for parent.
    Given I log in as "admin"
    And I navigate to "Users > Accounts > User profile fields" in site administration
    And I click on "Create a new profile field" "link"
    And I click on "Text input" "link"
    And I set the following fields to these values:
      | Short name                    | emailparent  |
      | Name                          | Email of parent |
    Then I click on "Save changes" "button"
    And I navigate to "Users > Permissions > Define roles" in site administration
    And I click on "Add a new role" "button"
    And I click on "Continue" "button"
    And I set the following fields to these values:
      | Short name | parent |
      | Custom full name | Parent User |
      | contextlevel30 | 1 |
    And I click on "Create this role" "button"
    Then I navigate to "Plugins > Authentication > Magic authentication > General settings" in site administration
    And I set the following fields to these values:
      | Profile field for the role of Parent User | Email of parent |
      | Account identifier            | Email address |
      | s_auth_magic_autocreate_relativeusers | 1 |
    Then I press "Save changes"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title | Demo campaign  |
      | Status   |  Available  |
      | Visibility | Visible   |
      | Approval roles | Manager |
      | Campaign course | Course C1 |
      | Course role for student | Student |
      | Course role for parent | Teacher |
      | Email of parent | Required |
    Then I press "Save changes"
    And I log out
    And I open magic campaign "Demo campaign"
    Then I should see "Demo campaign"
    And I set the following fields to these values:
      | firstname  | campaignuser_01 |
      | lastname   | campaignuser_01 |
      | username   | campaignuser_01 |
      | password   | Test123#        |
      | email      | campaignuser_01@gmail.com |
      | Email of parent | campaignparent_01@gmail.com |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    And I log out
    Then I log in as "admin"
    And I am on "Course C1" course homepage
    And I navigate to course participants
    Then I should see "campaignuser_01 campaignuser_01"
    Then I should see "campaignuser_01@gmail.com" in the "campaignuser_01 campaignuser_01" "table_row"
    Then I should see "Student" in the "campaignuser_01 campaignuser_01" "table_row"
    Then I should see "campaignuser_01 campaignuser_01 Parent"
    Then I should see "campaignparent_01@gmail.com" in the "campaignuser_01 campaignuser_01 Parent" "table_row"
    And I navigate to "Users > Permissions > Assign system roles" in site administration
    And I follow "Manager"
    And the "Existing users" select box should contain "campaignuser_01 campaignuser_01 Parent User (campaignparent_01@gmail.com)"
    And I log out

  @javascript
  Scenario: Check campaign course group features.
    Given I log in as "admin"
    And I am on the "Course C1" "course editing" page
    And I expand all fieldsets
    And I set the field "Group mode" to "Separate groups"
    And I press "Save and display"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title | Demo campaign  |
      | Status   |  Available  |
      | Visibility | Visible   |
      | Campaign course | Course C1 |
      | Course role for student | Student |
      | Groups | Campaign |
      | Grouping | Grouping 1 |
    Then I press "Save changes"
    And I log out
    And I open magic campaign "Demo campaign"
    Then I should see "Demo campaign"
    And I set the following fields to these values:
      | firstname  | campaignuser_01 |
      | lastname   | campaignuser_01 |
      | username   | campaignuser_01 |
      | password   | Test123#        |
      | email      | campaignuser_01@gmail.com |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    And I log out
    Then I log in as "admin"
    And I am on "Course C1" course homepage
    And I navigate to course participants
    Then I should see "campaignuser_01 campaignuser_01"
    Then I should see "campaignuser_01@gmail.com" in the "campaignuser_01 campaignuser_01" "table_row"
    Then I should see "Student" in the "campaignuser_01 campaignuser_01" "table_row"
    And I navigate course groups page "Course C1"
    Then the "groups" select box should contain "Demo campaign (1)"
    And I set the field "groups" to "Demo campaign (1)"
    And the "members" select box should contain "campaignuser_01 campaignuser_01 (campaignuser_01@gmail.com)"
    And I navigate course groups page "Course C1"
    And I select "Groupings" from the "jump" singleselect
    Then I should see "Demo campaign"
    And I log out

  @javascript
  Scenario: Check campaign course peruser group features.
    Given I log in as "admin"
    And I am on the "Course C1" "course editing" page
    And I expand all fieldsets
    And I set the field "Group mode" to "Separate groups"
    And I press "Save and display"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title | Demo campaign  |
      | Status   |  Available  |
      | Visibility | Visible   |
      | Campaign course | Course C1 |
      | Course role for student | Student |
      | Groups | Per User |
    Then I press "Save changes"
    And I log out
    And I open magic campaign "Demo campaign"
    Then I should see "Demo campaign"
    And I set the following fields to these values:
      | firstname  | campaignuser_01 |
      | lastname   | campaignuser_01 |
      | username   | campaignuser_01 |
      | password   | Test123#        |
      | email      | campaignuser_01@gmail.com |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    And I log out
    Then I log in as "admin"
    And I am on "Course C1" course homepage
    And I navigate to course participants
    Then I should see "campaignuser_01@gmail.com" in the "campaignuser_01 campaignuser_01" "table_row"
    And I navigate course groups page "Course C1"
    Then the "groups" select box should contain "campaignuser_01 campaignuser_01 Demo campaign (1)"
    And I set the field "groups" to "campaignuser_01 campaignuser_01 Demo campaign (1)"
    And the "members" select box should contain "campaignuser_01 campaignuser_01 (campaignuser_01@gmail.com)"

  @javascript
  Scenario: Check campaign workflow.
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    Then I should see "Create campaign"
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title | Demo campaign 01 |
      | Description | Demo campaign description |
      | Comments    | Demo campaign comments  |
      | Capacity    |  2         |
      | Status      |  Available  |
      | Visibility  | Visible  |
      | Country | Optional |
      | Language | Hidden (use default) |
      | City |  Optional |
      | ID Number | Hidden (use default) |
      | Alternatename | Hidden (use default) |
      | Department | Hidden (use default) |
      | Institution | Hidden (use default) |
      | Address | Hidden (use default) |
    Then I press "Save changes"
    Then I should see "Campaign created successfully"
    Then I should see "Manage campaign"
    And I should see "Demo campaign 01"
    Then I should see "Available" in the "Demo campaign 01" "table_row"
    Then I should see "2 available" in the "Demo campaign 01" "table_row"
    Then I should see "Demo campaign comments" in the "Demo campaign 01" "table_row"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    Then I click on "Copy link to cliboard" "button"
    Then I log out
    And I open magic campaign "Demo campaign 01"
    Then ".campaign-block" "css_element" should exist
    Then I should see "Demo campaign 01" in the ".campaign-info-block" "css_element"
    Then I should see "Demo campaign description"
    And I should see "First name"
    And I should see "Username"
    And I should see "Country"
    And I should see "City/town"
    And I should not see "Institution"
    And I should not see "Alternatename"
    Then I should not see "Department"
    And I set the following fields to these values:
      | First name  | campaignuser_01 |
      | lastname  | campaignuser_01 |
      | Username  | campaignuser_01 |
      | New password  | Test123# |
      | Email address  | campaignuser_01@gmail.com |
      | Country  | India |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    Then I follow "Profile" in the user menu
    Then I should see "campaignuser_01 campaignuser_01" in the "page-header" "region"
    Then I log out
    And I log in as "admin"
    Then I navigate to "Users > Accounts > Browse list of users" in site administration
    Then I should see "campaignuser_01 campaignuser_01"
    Then I should see "campaignuser_01@gmail.com" in the "campaignuser_01 campaignuser_01" "table_row"
    Then I should see "India" in the "campaignuser_01 campaignuser_01" "table_row"
    Then I log out
    And I follow "Log in"
    When I set the field "Username" to "campaignuser_01@gmail.com"
    And I click on "Get a magic link via email" "link"
    Then I should see "If you supplied a correct email address, an email containing a magic login link should have been sent to you."

  @javascript
  Scenario: Check campaign availability workflow.
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title | Demo campaign    |
      | Capacity | 2             |
      | Status   |  Archived     |
      | Visibility | Visible     |
      | Available from | ## now ##|
      | Available closes | ## +5days ##|
      | Country | Optional |
      | Language | Hidden (use default) |
      | City |  Optional |
      | ID Number | Hidden (use default) |
      | Alternatename | Hidden (use default) |
      | Department | Hidden (use default) |
      | Institution | Hidden (use default) |
      | Address | Hidden (use default) |
      | Require email confirmation  |  Yes  |
    Then I press "Save changes"
    Then I should see "2 available" in the "Demo campaign" "table_row"
    Then I log out
    And I open magic campaign "Demo campaign"
    Then I should see "This campaign is not avilable to signup"
    Then I change single campaign config "Status" to "Available"
    And I open magic campaign "Demo campaign"
    Then I change single campaign config "Visibility" to "Hidden"
    And I open magic campaign "Demo campaign"
    Then I should see "This campaign is not avilable to signup"
    Then I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on ".icon[title=Edit]" "css_element" in the "Demo campaign" "table_row"
    And I set the following fields to these values:
      | Visibility | Visible |
      | Available from | ## +2days ## |
    Then I press "Save changes"
    And I log out
    And I open magic campaign "Demo campaign"
    Then I should see "This campaign is not avilable to signup"
    Then I change single campaign config "Available from" to "## now ##"
    And I open magic campaign "Demo campaign"
    Then I should see "Demo campaign"
    And I set the following fields to these values:
      | firstname | campaignuser_01 |
      | lastname  | campaignuser_01 |
      | username  | campaignuser_01 |
      | email  | campaignuser_01@gmail.com |
      | password | Test123# |
      | country  | India |
    And I scroll down page
    Then I press "Sign up"
    Then I should see "User signup successfully."
    And I open magic campaign "Demo campaign"
    And I set the following fields to these values:
      | firstname | campaignuser_02 |
      | lastname  | campaignuser_02 |
      | username  | campaignuser_02 |
      | email  | campaignuser_02@gmail.com |
      | password | Test123# |
      | country  | India |
    And I scroll down page
    Then I press "Sign up"
    And I open magic campaign "Demo campaign"
    Then I should see "This campaign is not avilable to signup"
    Then I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on ".icon[title=Edit]" "css_element" in the "Demo campaign" "table_row"
    And I set the following fields to these values:
      | Capacity | 3 |
      | Campaign password | Test123# |
    Then I press "Save changes"
    And I log out
    And I open magic campaign "Demo campaign"
    Then I should see "Confirm password"
    Then I set the field "campaignpassword" to "Test1234#"
    Then I press "Verify"
    And I should see "Password verification failed"
    Then I set the field "campaignpassword" to "Test123#"
    Then I press "Verify"
    Then I should see "Password verified successfully"
    And I should see "Demo campaign"

  @javascript
  Scenario: Check campaign Appearance workflow.
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title | Demo campaign  |
      | Visibility | Visible   |
      | Transparent form | 1   |
      | Display campaign owner's profile picture | 1   |
      | Form Position | Center |
    And I upload "auth/magic/tests/behat/assets/headerbg.jpg" file to "Header image" filemanager
    Then I press "Save changes"
    And I click on ".icon[title=Edit]" "css_element" in the "Demo campaign" "table_row"
    Then I follow "Expand all"
    And I upload "auth/magic/tests/behat/assets/backgroundbg.jpg" file to "Background image" filemanager
    Then I press "Save changes"
    And I click on ".icon[title=Edit]" "css_element" in the "Demo campaign" "table_row"
    Then I follow "Expand all"
    And I upload "auth/magic/tests/behat/assets/logo.png" file to "Logo" filemanager
    Then I press "Save changes"
    And I log out
    And I open magic campaign "Demo campaign"
    Then I should see "Demo campaign"
    Then the image at ".campaign-block .campaign-header-block img" "css_element" should be identical to "auth/magic/tests/behat/assets/headerbg.jpg"
    Then the image at ".campaign-block .logo-block img" "css_element" should be identical to "auth/magic/tests/behat/assets/logo.png"
    Then the image at ".campaign-block .campaign-background-block img" "css_element" should be identical to "auth/magic/tests/behat/assets/backgroundbg.jpg"
    Then ".campaign-block.form-transparent" "css_element" should exist
    Then ".campaign-block.form-owner-profile" "css_element" should exist

  @javascript
  Scenario: Check campaign Form fields workflow.
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title | Demo campaign    |
      | Capacity | 2             |
      | Status   |  Archived     |
      | Visibility | Visible     |
      | standard_firstname_option | Hidden (use other field's value) |
      | standard_firstname_otherfield | Last name  |
      | standard_lastname_option | Hidden (use other field's value) |
      | standard_lastname_otherfield | Username  |
      | standard_country_option | Hidden (use provided text) |
      | standard_country | Germany  |
      | standard_city_option | Hidden (use provided text) |
      | standard_city | Hamburg  |
      | Language | Hidden (use default) |
      | City |  Optional |
      | ID Number | Hidden (use default) |
      | Alternatename | Hidden (use default) |
      | Department | Hidden (use default) |
      | Institution | Hidden (use default) |
      | Address | Hidden (use default) |
    Then I press "Save changes"
    And I open magic campaign "Demo campaign"
    Then I should see "Demo campaign"
    And I set the following fields to these values:
      | username  | campaignuser_01 |
      | email  | campaignuser_01@gmail.com |
      | password | Test123#      |
      | city       | Hamburg   |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    Then I log in as "admin"
    Then I navigate to "Users > Accounts > Browse list of users" in site administration
    Then I should see "campaignuser_01 campaignuser_01"
    Then I should see "campaignuser_01@gmail.com" in the "campaignuser_01 campaignuser_01" "table_row"
    Then I should see "Germany" in the "campaignuser_01 campaignuser_01" "table_row"
    Then I should see "Hamburg" in the "campaignuser_01 campaignuser_01" "table_row"
    Then I log out

  @javascript
  Scenario: Check campaign Assignments workflow.
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title | Demo campaign  |
      | Visibility | Visible   |
      | Country | Optional |
      | Language | Hidden (use default) |
      | City |  Optional |
      | ID Number | Hidden (use default) |
      | Alternatename | Hidden (use default) |
      | Department | Hidden (use default) |
      | Institution | Hidden (use default) |
      | Address | Hidden (use default) |
      | Require email confirmation | No |
      | Cohort membership | Cohort 1  |
      | Global role | Course creator |
      | Campaign owner account | parentuser_01 parentuser_01|
    Then I press "Save changes"
    Then I log out
    And I open magic campaign "Demo campaign"
    Then I should see "Demo campaign"
    And I set the following fields to these values:
      | firstname | campaignuser_01 |
      | lastname  | campaignuser_01 |
      | username  | campaignuser_01 |
      | email  | campaignuser_01@gmail.com |
      | email2 | campaignuser_01@gmail.com |
      | password | Test123# |
      | country  | India |
    And I scroll down page
    Then I press "Sign up"
    Then I should see "User signup successfully."
    Then I log in as "admin"
    And I navigate to "Users > Accounts > Cohorts" in site administration
    Then I check the magic cohort
    And the "Current users" select box should contain "campaignuser_01 campaignuser_01 (campaignuser_01@gmail.com)"
    And I navigate to "Users > Permissions > Assign system roles" in site administration
    And I follow "Course creator"
    And the "Existing users" select box should contain "campaignuser_01 campaignuser_01 (campaignuser_01@gmail.com)"

  @javascript
  Scenario: Check campaign Privacy policy workflow.
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title | Demo campaign  |
      | Visibility | Visible   |
      | Country | Optional |
      | Language | Hidden (use default) |
      | City |  Optional |
      | ID Number | Hidden (use default) |
      | Alternatename | Hidden (use default) |
      | Department | Hidden (use default) |
      | Institution | Hidden (use default) |
      | Address | Hidden (use default) |
      | Display consent option | 1    |
      | Consent statement  | I agree to the Privacy notice and the Cookies policy |
    Then I press "Save changes"
    Then I log out
    And I open magic campaign "Demo campaign"
    Then I should see "Demo campaign"
    Then "input[name=privacypolicy]" "css_element" should exist
    Then I should see "I agree to the Privacy notice and the Cookies policy"
    And I set the following fields to these values:
      | firstname | campaignuser_01 |
      | lastname  | campaignuser_01 |
      | username  | campaignuser_01 |
      | email  | campaignuser_01@gmail.com |
      | country  | India |
      | password | Test123# |
      | privacypolicy | 1 |
    And I press the down key
    Then I press "Sign up"
    Then I should see "User signup successfully."

  @javascript
  Scenario: Magic campaign payment
    Given I log in as "admin"
    Then I navigate to "Plugins > Payment gateways > Manage payment gateways" in site administration
    Then I should see "Payment accounts"
    And I click on "Payment accounts" "link"
    Then I should see "Create payment account"
    And I click on "Create payment account" "button"
    And I set the following fields to these values:
      | Account name    |  LMSACE FEE |
    And I press "Save changes"
    Then I should see "PayPal" in the "LMSACE FEE" "table_row"
    And I click on "PayPal" "link"
    And I set the following fields to these values:
      | enabled       |  1  |
      | Brand name    |  LMSACE  |
      | Client ID     |  amdfkaasd767asdk  |
      | Secret        |  amdfkaasd767asdk  |
      | Environment   |  Sandbox  |
    And I press "Save changes"
    And I am on site homepage
    And I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    Then I should see "Create campaign"
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title       | Demo campaign 01 |
      | Status      |  Available       |
      | Visibility  |  Visible         |
      | Require email confirmation  |  No  |
      | Type        | Paid             |
      | Fee         | 25               |
      | Currency    | Euro             |
      | Account     | LMSACE FEE       |
      | First name  | Required         |
      | Last name   | Required         |
      | e-Mail      | Required         |
      | Password    | Required Once    |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out
    And I open magic campaign "Demo campaign 01"
    Then ".campaign-block" "css_element" should exist
    Then I should see "Demo campaign 01" in the ".campaign-info-block" "css_element"
    And I set the following fields to these values:
      | firstname | demo  |
      | lastname  | user01 |
      | username  | demouser01  |
      | password  | Test123# |
      | email     | demouser01@gmail.com |
      | email2    | demouser01@gmail.com |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    Then I should see "This campaign requires a payment for entry."
    And I should see "25.00"
    And I should see "EUR"
    Then "Select payment type" "button" should exist
    And I log out
    And I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    Then I should see "Demo campaign 01" in the "Demo campaign 01" "table_row"
    And I click on ".fa-cog" "css_element" in the "Demo campaign 01" "table_row"
    And I set the following fields to these values:
      | Fee         | 12    |
      | Currency    | US Dollar  |
    Then I press "Save changes"
    Then I log out
    And I click on "Log in" "link" in the ".logininfo" "css_element"
    And I set the field "Username" to "demouser01"
    And I set the field "Password" to "Test123#"
    Then I press "Log in"
    Then I should see "This campaign requires a payment for entry."
    And I should see "12.00"
    And I should see "USD"
    Then "Select payment type" "button" should exist
    And I log out

  # Bank Transfer payment
  @javascript
  Scenario: Magic campaign Bank Transfer payment
    Given I log in as "admin"
    Then I navigate to "Plugins > Payment gateways > Manage payment gateways" in site administration
    Then I should see "Payment accounts"
    And I click on "Payment accounts" "link"
    Then I should see "Create payment account"
    And I click on "Create payment account" "button"
    And I set the following fields to these values:
      | Account name    |  Bank Transfer |
    And I press "Save changes"
    Then I should see "Bank Transfer" in the "Bank Transfer" "table_row"
    And I click on "Bank Transfer" "link"
    And I set the following fields to these values:
      | enabled       |  1  |
    And I press "Save changes"
    And I am on site homepage
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    Then I should see "Create campaign"
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title       | Demo campaign    |
      | Status      | Available        |
      | Visibility  | Visible          |
      | Require email confirmation     | No  |
      | Type        | Paid             |
      | Fee         | 10               |
      | Currency    | US Dollar        |
      | Account     | Bank Transfer    |
      | First name  | Required         |
      | Last name   | Required         |
      | Username    | Required         |
      | e-Mail      | Required         |
      | Password    | Required Once    |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out
    And I open magic campaign "Demo campaign"
    Then ".campaign-block" "css_element" should exist
    Then I should see "Demo campaign" in the ".campaign-info-block" "css_element"
    And I wait "10" seconds
    And I set the following fields to these values:
      | firstname | demo        |
      | lastname  | user01      |
      | username  | demouser01  |
      | password  | Test123#    |
      | email     | demouser01@gmail.com |
      | email2    | demouser01@gmail.com |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    Then I should see "This campaign requires a payment for entry."
    And I should see "10.00"
    And I should see "USD"
    Then "Select payment type" "button" should exist
    And I click on "Select payment type" "button"
    And I should see "Bank Transfer" in the ".core_payment_gateways_modal p" "css_element"
    And I click on "Proceed" "button"
    And I should see "Access the Demo campaign campaign." in the ".list-group .list-group-item div" "css_element"
    And I click on "//input[@value='Start process']" "xpath_element"
    And I should see "Transfer process initiated" in the ".alert-info" "css_element"
    And I am on site homepage
    And I should see "This campaign requires a payment for entry." in the ".auth_magic_payment_region" "css_element"
    And I log out
    And I log in as "admin"
    Then I navigate to "Site administration > Bank Transfer > Manage Transfers" in site administration
    And I should see "demouser01@gmail.com" in the "demo user" "table_row"
    And I click on "//input[@value='Approve']" "xpath_element"
    And I should see "aprobed" in the ".alert-info" "css_element"
    Then I log out
    And I click on "Log in" "link" in the ".logininfo" "css_element"
    And I set the field "Username" to "demouser01"
    And I set the field "Password" to "Test123#"
    Then I press "Log in"
    And I am on site homepage
    And I should see "Acceptance test site" in the "#page-header h1.h2" "css_element"
    

  @javascript
  Scenario: Magic campaign recaptcha
    Given I log in as "admin"
    And I navigate to "Plugins > Authentication > Manage authentication" in site administration
    And I set the following fields to these values:
      | ReCAPTCHA site key | asdjaisdh883jkahdjlkasdl |
      | ReCAPTCHA secret key | asdjaisdh883jkahdjlkasdl |
    And I press "Save changes"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    Then I should see "Create campaign"
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title       | Demo campaign 01 |
      | Status      |  Available       |
      | Visibility  |  Visible         |
      | reCAPTCHA   |  1               |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out
    And I open magic campaign "Demo campaign 01"
    Then ".campaign-block" "css_element" should exist
    Then I should see "Demo campaign 01" in the ".campaign-info-block" "css_element"
    And "#recaptcha_element" "css_element" should exist
    And I log out

    And I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    Then I should see "Demo campaign 01" in the "Demo campaign 01" "table_row"
    And I click on ".fa-cog" "css_element" in the "Demo campaign 01" "table_row"
    And I set the following fields to these values:
      | reCAPTCHA   | 0                |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out
    And I open magic campaign "Demo campaign 01"
    Then ".campaign-block" "css_element" should exist
    Then I should see "Demo campaign 01" in the ".campaign-info-block" "css_element"
    And "#recaptcha_element" "css_element" should not exist
    And I log out

  @javascript
  Scenario: Magic campaign summary content after submission.
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    Then I should see "Create campaign"
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title       | Demo campaign 01 |
      | Status      |  Available       |
      | Visibility  |  Visible         |
      | Redirect after form submission | No redirect  |
      | Summary page content    |  There are many variations of passages of Lorem Ipsum available.    |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out
    And I open magic campaign "Demo campaign 01"
    Then ".campaign-block" "css_element" should exist
    Then I should see "Demo campaign 01" in the ".campaign-info-block" "css_element"
    And I set the following fields to these values:
      | firstname | demo  |
      | lastname  | user01 |
      | username  | demouser01  |
      | password  | Test123# |
      | email     | demouser01@gmail.com |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    Then "#page-auth-magic-campaigns-view" "css_element" should exist
    And ".campaign-submisson-summary" "css_element" should not exist
    Then I should not see "There are many variations of passages of Lorem Ipsum available."
    Then I follow "Profile" in the user menu
    Then I should see "demo user01" in the "page-header" "region"
    And I log out

    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on ".icon[title=Edit]" "css_element" in the "Demo campaign 01" "table_row"
    And I set the following fields to these values:
      | Redirect after form submission | Redirect to summary page |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out
    And I open magic campaign "Demo campaign 01"
    Then ".campaign-block" "css_element" should exist
    Then I should see "Demo campaign 01" in the ".campaign-info-block" "css_element"
    And I set the following fields to these values:
      | firstname | demo  |
      | lastname  | user02 |
      | username  | demouser02  |
      | password  | Test123# |
      | email     | demouser02@gmail.com |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    Then "#page-auth-magic-campaigns-summary" "css_element" should exist
    And ".campaign-submisson-summary" "css_element" should exist
    Then I should see "There are many variations of passages of Lorem Ipsum available."
    Then I follow "Profile" in the user menu
    Then I should see "demo user02" in the "page-header" "region"
    And I log out

    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on ".icon[title=Edit]" "css_element" in the "Demo campaign 01" "table_row"
    Then I follow "Expand all"
    Then I set the field "Redirect after form submission" to "Redirect to URL"
    Then I should see "Redirect to URL"
    Then I set the field "Redirect to URL" to "https://example.com/"
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out
    And I open magic campaign "Demo campaign 01"
    Then ".campaign-block" "css_element" should exist
    Then I should see "Demo campaign 01" in the ".campaign-info-block" "css_element"
    And I set the following fields to these values:
      | firstname | demo  |
      | lastname  | user03 |
      | username  | demouser03  |
      | password  | Test123# |
      | email     | demouser03@gmail.com |
    Then I press "Sign up"
    Then "#page-auth-magic-campaigns-summary" "css_element" should not exist
    And ".campaign-submisson-summary" "css_element" should not exist
    Then I should see "Example Domain"

  @javascript
  Scenario: Magic campaign auth method.
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title       | Demo campaign 01   |
      | Status      |  Available         |
      | Visibility  |  Visible           |
      | Require email confirmation | Yes |
      | Authentication method   |  Manual accounts  |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    And I open magic campaign "Demo campaign 01"
    Then ".campaign-block" "css_element" should exist
    Then I should see "Demo campaign 01" in the ".campaign-info-block" "css_element"
    And I set the following fields to these values:
      | firstname | demo        |
      | lastname  | user01      |
      | username  | demouser01  |
      | password  | Test123#    |
      | email     | demouser01@gmail.com |
    Then I press "Sign up"
    Then I navigate to "Users > Accounts > Browse list of users" in site administration
    Then I should see "demouser01@gmail.com" in the "users" "table"
    And I follow "Show more..."
    And I set the field "auth" to "Manual accounts"
    Then I press "Add filter"
    Then I should see "demouser01@gmail.com" in the "users" "table"
    Then I press "Remove all filters"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on ".icon[title=Edit]" "css_element" in the "Demo campaign 01" "table_row"
    And I set the following fields to these values:
      | Authentication method | Magic authentication  |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    And I open magic campaign "Demo campaign 01"
    Then I should see "Demo campaign 01" in the ".campaign-info-block" "css_element"
    And I set the following fields to these values:
      | firstname | demo        |
      | lastname  | user02      |
      | username  | demouser02  |
      | password  | Test123#    |
      | email     | demouser02@gmail.com |
    Then I press "Sign up"
    Then I navigate to "Users > Accounts > Browse list of users" in site administration
    Then I should see "demouser02@gmail.com" in the "users" "table"
    And I follow "Show more..."
    And I set the field "auth" to "Magic authentication"
    Then I press "Add filter"
    Then I should see "demouser02@gmail.com" in the "users" "table"
    And I log out

  @javascript
  Scenario: Magic campaign require email confirmation
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    Then I should see "Create campaign"
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title       | Demo campaign 01  |
      | Status      |  Available        |
      | Visibility  |  Visible          |
      | Require email confirmation | No |
      | First name  | Required          |
      | Last name   | Required          |
      | e-Mail      | Required          |
      | Password    | Required Once     |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out
    And I open magic campaign "Demo campaign 01"
    Then I should see "Demo campaign 01" in the ".campaign-info-block" "css_element"
    Then I should see "Email address"
    Then I should see "Email (again)"
    And I set the following fields to these values:
      | firstname | demo        |
      | lastname  | user01      |
      | username  | demouser01  |
      | password  | Test123#    |
      | email     | demouser01@gmail.com |
      | email2    | demouser01@gmail.com |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    Then I follow "Profile" in the user menu
    Then I should see "demo user01" in the "page-header" "region"
    Then I log out
    And I click on "Log in" "link" in the ".logininfo" "css_element"
    And I set the field "Username" to "demouser01"
    And I set the field "Password" to "Test123#"
    Then I press "Log in"
    Then I follow "Profile" in the user menu
    Then I should see "demo user01" in the "page-header" "region"
    Then I log out
    And I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on ".icon[title=Edit]" "css_element" in the "Demo campaign 01" "table_row"
    And I set the following fields to these values:
      | Require email confirmation | Yes  |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out
    And I open magic campaign "Demo campaign 01"
    Then I should see "Demo campaign 01" in the ".campaign-info-block" "css_element"
    And I set the following fields to these values:
      | firstname | demo        |
      | lastname  | user02      |
      | username  | demouser02  |
      | password  | Test123#    |
      | email     | demouser02@gmail.com |
    Then I press "Sign up"
    And I click on "Log in" "link" in the ".logininfo" "css_element"
    And I set the field "Username" to "demouser02"
    And I set the field "Password" to "Test123#"
    Then I press "Log in"
    Then I should see "You need to confirm your account"
    And I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on ".icon[title=Edit]" "css_element" in the "Demo campaign 01" "table_row"
    And I set the following fields to these values:
      | Require email confirmation | Partial  |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out
    And I open magic campaign "Demo campaign 01"
    Then I should see "Demo campaign 01" in the ".campaign-info-block" "css_element"
    And I set the following fields to these values:
      | firstname | demo        |
      | lastname  | user03      |
      | username  | demouser03  |
      | password  | Test123#    |
      | email     | demouser03@gmail.com |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    Then I follow "Profile" in the user menu
    Then I should see "demo user03" in the "page-header" "region"
    Then I log out
    And I click on "Log in" "link" in the ".logininfo" "css_element"
    And I set the field "Username" to "demouser03"
    And I set the field "Password" to "Test123#"
    Then I press "Log in"
    Then I should see "You need to confirm your account"

  @javascript
  Scenario: Check campaign Restrict by role workflow.
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    Then I should see "Create campaign"
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title       | Demo campaign 01  |
      | Status      | Available         |
      | Visibility  | Visible           |
      | Require email confirmation | No |
      | By role     | Student, Teacher  |
      | First name  | Required          |
      | Last name   | Required          |
      | e-Mail      | Required          |
      | Password    | Required Once     |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out

    And I log in as "user_01"
    And I open magic campaign "Demo campaign 01"
    Then ".campaign-block" "css_element" should exist
    And I log out

    And I log in as "user_02"
    And I open magic campaign "Demo campaign 01"
    And I should see "This campaign is not avilable to signup"
    And I log out

    And I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign 01" "table_row"
    And I set the following fields to these values:
      | Title       | Demo campaign 01  |
      | By role     | Student, Manager  |
      | Context     | Any               |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out

    And I log in as "manager_01"
    And I open magic campaign "Demo campaign 01"
    Then ".campaign-block" "css_element" should exist
    And I log out

    And I log in as "manager1"
    And I open magic campaign "Demo campaign 01"
    Then ".campaign-block" "css_element" should exist
    And I log out

    And I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign 01" "table_row"
    And I set the following fields to these values:
      | Title       | Demo campaign 01  |
      | Context     | System            |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out

    And I log in as "manager_01"
    And I open magic campaign "Demo campaign 01"
    Then ".campaign-block" "css_element" should exist
    And I log out

    And I log in as "manager1"
    And I open magic campaign "Demo campaign 01"
    And I should see "This campaign is not avilable to signup"
    And I log out

  @javascript
  Scenario: Check campaign Restrict by cohort workflow.
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    Then I should see "Create campaign"
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title       | Demo campaign 01   |
      | Status      | Available          |
      | Visibility  | Visible            |
      | Require email confirmation | No  |
      | By cohort   | Cohort 1, Cohort 3 |
      | First name  | Required           |
      | Last name   | Required           |
      | e-Mail      | Required           |
      | Password    | Required Once      |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out

    And I log in as "user_01"
    And I open magic campaign "Demo campaign 01"
    And I should see "Demo campaign 01"
    When I click on "New user" "button"
    And I should see "First name"
    And I log out

    And I log in as "user_02"
    And I open magic campaign "Demo campaign 01"
    And I should see "This campaign is not avilable to signup"
    And I log out

    And I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign 01" "table_row"
    And I set the following fields to these values:
      | Title       | Demo campaign 01  |
      | Operator    | All               |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out

    And I log in as "user_01"
    And I open magic campaign "Demo campaign 01"
    Then ".campaign-block" "css_element" should exist
    And I log out

    And I log in as "user_02"
    And I open magic campaign "Demo campaign 01"
    And I should see "This campaign is not avilable to signup"
    And I log out

  @javascript
  Scenario: Check campaign Expiration date workflow.
    Given I log in as "admin"
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    Then I should see "Create campaign"
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title       | Demo campaign 01     |
      | Status      | Available            |
      | Visibility  | Visible              |
      | expirytime[enabled]  | 1           |
      | expirytime[number]   | 2           |
      | expirytime[timeunit] | days        |
      | Require email confirmation | No    |
      | First name  | Required             |
      | Last name   | Required             |
      | e-Mail      | Required             |
      | Password    | Required Once        |
    Then I press "Save changes"
    And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign 01" "table_row"
    And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
    Then I log out

    And I open magic campaign "Demo campaign 01"
    And I should see "Demo campaign 01"
    When I click on "New user" "button"
    And I should see "First name"
    And I log out