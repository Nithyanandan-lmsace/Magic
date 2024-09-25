@auth @auth_magic @dash_data_source_campaign @_file_upload
Feature: Campaign data source for Dash workflow.
  In order to show the campaign report source workflow for Dash.

  Background:
    Given I log in as "admin"
    Then the following "users" exist:
      | username      | firstname     | lastname    | email                     |  auth  |
      | user_01       | user_01       | user_01     | user_01@gmail.com         |  manual|
      | user_02       | user_02       | user_02     | user_02@gmail.com         |  magic |
      | user_03       | user_03       | user_03     | user_03@gmail.com         |  magic |
      | parentuser_01 | parentuser_01 | parentuser_01 | parentuser_01@gmail.com | manual |
      | teacher1      | Teacher       | 1           | teacher1@example.com      | manual |
    Then the following "cohorts" exist:
      | name     | idnumber |
      | Cohort 1 | CH1      |
      | Cohort 2 | CH1      |
      | Cohort 3 | CH1      |
    Then the following "categories" exist:
      | name           | category | idnumber | category |
      | Category E     | 0        | CE       | 0        |
      | Category ED    | 1        | CED      | CE       |
    And the following "courses" exist:
      | fullname  | shortname | category | enablecompletion | showcompletionconditions |
      | Course C1 | CC1       | CE       | 1                | 1                        |
      | Course C2 | CC2       | 0        | 1                | 1                        |
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
      | user       | course | role           |
      | admin      | CC1    | student        |
      | teacher1   | CC1    | editingteacher |
    And the following "activities" exist:
      | activity   | course | name     | idnumber         | completion | completionview |
      | assign     | CC1    | assign2  | Test assignment2 | 1          | 1              |
    And I navigate to "Plugins > Authentication > Manage authentication" in site administration
    And I click on "Enable" "link" in the "Magic authentication" "table_row"
    Then I navigate course enrolment page "Course C1"
    And I click on "Enable" "link" in the "Self enrolment" "table_row"
    Then I navigate course enrolment page "Course C2"
    And I click on "Enable" "link" in the "Self enrolment" "table_row"

    # Create Campaign
    And I am on site homepage
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    Then I should see "Create campaign"
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title                  | Demo campaign     |
      | Description            | Description       |
      | Comments               | Comments          |
      | Capacity               | 3                 |
      | Status                 | Available         |
      | Visibility             | Visible           |
      | Require email confirmation | No            |
      | Type                   | Free              |
      | expirytime[enabled]    | 1                 |
      | expirytime[number]     | 5                 |
      | expirytime[timeunit]   | days              |
      | Available from         | ##31 March 2024## |
      | Available closes       | ##31 March 2025## |
      | Campaign password      |                   |
      | Cohort membership      | Cohort 1          |
      | Global role            | Disabled          |
      | Campaign owner account | parentuser_01 parentuser_01 |
      | Display consent option | 1                 |
      | Send welcome message to new accounts   | 1 |
      | Send follow up message to new accounts | 1 |
      | Campaign course        | Course C1         |
      | Course role for student | Student    |
      | First name             | Required          |
      | Last name              | Required          |
      | Username               | Required          |
      | e-Mail                 | Required          |
      | Password               | Required Once     |
    Then I press "Save changes"
    Then I should see "Create campaign"
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title                  | Demo campaign1    |
      | Description            | Description1      |
      | Comments               | Comments1         |
      | Capacity               | 2                 |
      | Status                 | Archived          |
      | Visibility             | Hidden            |
      | Require email confirmation | No            |
      | Type                   | Free              |
      | expirytime[enabled]    | 1                 |
      | expirytime[number]     | 0                 |
      | expirytime[timeunit]   | days              |
      | startdate[enabled]     | 0                 |
      | enddate[enabled]       | 0                 |
      | Campaign password      | Test123#          |
      | Cohort membership      | Cohort 2          |
      | Global role            | Manager           |
      | Campaign owner account | user_01 user_01   |
      | Display consent option | 0                 |
      | Send welcome message to new accounts   | 0 |
      | Send follow up message to new accounts | 0 |
      | Campaign course        | Course C2         |
      | First name             | Required          |
      | Last name              | Required          |
      | Username               | Required          |
      | e-Mail                 | Required          |
      | Password               | Required Once     |
    Then I press "Save changes"
    Then I should see "Create campaign"
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title                  | Demo campaign2    |
      | Description            | Description2      |
      | Comments               | Comments2         |
      | Capacity               | 5                 |
      | Status                 | Available         |
      | Visibility             | Visible           |
      | Require email confirmation | No            |
      | Type                   | Free              |
      | expirytime[enabled]    | 1                 |
      | expirytime[number]     | 3                 |
      | expirytime[timeunit]   | days              |
      | Available from         | ##02 September 2024## |
      | Available closes       | ##31 September 2025## |
      | Campaign password      |                   |
      | Cohort membership      | Cohort 3          |
      | Global role            | Course creator    |
      | Campaign owner account | Admin User        |
      | Display consent option | 1                 |
      | Send welcome message to new accounts   | 1 |
      | followupmessage        | 1                 |
      | Delay                  | 1                 |
      | Campaign course        | Course C2         |
      | First name             | Required          |
      | Last name              | Required          |
      | Username               | Required          |
      | e-Mail                 | Required          |
      | Password               | Required Once     |
    Then I press "Save changes"

    # Create Report
    Then I navigate to "Reports > Report builder > Custom reports" in site administration
    And I click on "New report" "button"
    And I set the following fields in the "New report" "dialogue" to these values:
      | Name                   | My report         |
      | Report source          | Campaigns         |
      | Include default setup  | 0                 |
    And I click on "Save" "button" in the "New report" "dialogue"

    # Create Payment
    And I am on site homepage
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
    Then I log out

@javascript
  Scenario: Dash data source campaign
    Given I log in as "admin"
    #Create Campaign
    And I am on site homepage
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign2" "table_row"
    And I set the following fields to these values:
      | approvaltype           | Information       |
    Then I press "Save changes"
    Then I should see "Create campaign"
    And I click on "Create campaign" "button"
    And I set the following fields to these values:
      | Title                  | Magic campaign    |
      | Description            | Magic Description |
      | Comments               | Comments Magic    |
      | Capacity               | 3                 |
      | Status                 | Available         |
      | Visibility             | Visible           |
      | Require email confirmation | No            |
      | Type                   | Paid              |
      | Fee                    | 10                |
      | Currency               | US Dollar         |
      | Account                | Bank Transfer     |
      | expirytime[enabled]    | 1                 |
      | expirytime[number]     | 2                 |
      | expirytime[timeunit]   | days              |
      | Available from         | ##31 March 2024## |
      | Available closes       | ##31 March 2025## |
      | Campaign password      | Test123#          |
      | Cohort membership      | Cohort 1          |
      | Global role            | Disabled          |
      | Campaign owner account | parentuser_01 parentuser_01 |
      | Display consent option | 1                 |
      | Send welcome message to new accounts   | 1 |
      | Send follow up message to new accounts | 1 |
      | Campaign course        | Course C1         |
      | Course role for student | Student          |
      | First name             | Required          |
      | Last name              | Required          |
      | Username               | Required          |
      | e-Mail                 | Required          |
      | Password               | Required Once     |
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
    And I open magic campaign "Magic campaign"
    And I set the field "campaignpassword" to "Test123#"
    And I press "Verify"
    And I should see "Password verified successfully"
    Then ".campaign-block" "css_element" should exist
    When I click on "New user" "button"
    Then I should see "Magic campaign" in the ".campaign-info-block" "css_element"
    And I set the following fields to these values:
      | firstname      | Alan        |
      | lastname       | Turing      |
      | username       | alan_turing |
      | password       | Alan123#    |
      | email          | alanturing@gmail.com |
      | email2         | alanturing@gmail.com |
      | privacypolicy  | 1           |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    Then I should see "This campaign requires a payment for entry."
    And I should see "10.00"
    And I should see "USD"
    Then "Select payment type" "button" should exist
    And I click on "Select payment type" "button"
    And I should see "Bank Transfer" in the ".core_payment_gateways_modal p" "css_element"
    And I click on "Proceed" "button"
    And I should see "Access the Magic campaign campaign." in the ".list-group .list-group-item div" "css_element"
    And I click on "//input[@value='Start process']" "xpath_element"
    And I should see "Transfer process initiated" in the ".alert-info" "css_element"
    And I am on site homepage
    And I should see "This campaign requires a payment for entry." in the ".auth_magic_payment_region" "css_element"
    And I log out

    And I log in as "admin"
    And I am on site homepage
    Then I navigate to "Site administration > Bank Transfer > Manage Transfers" in site administration
    And I should see "alanturing@gmail.com" in the "Alan Turing" "table_row"
    And I click on "//input[@value='Approve']" "xpath_element"
    And I should see "aprobed" in the ".alert-info" "css_element"
    And I log out

    And I open magic campaign "Magic campaign"
    And I set the field "campaignpassword" to "Test123#"
    And I press "Verify"
    And I should see "Password verified successfully"
    Then ".campaign-block" "css_element" should exist
    When I click on "New user" "button"
    Then I should see "Magic campaign" in the ".campaign-info-block" "css_element"
    And I set the following fields to these values:
      | firstname      | Steve        |
      | lastname       | Carell       |
      | username       | steve_carell |
      | password       | Test123#     |
      | email          | stevecarell@gmail.com |
      | email2         | stevecarell@gmail.com |
      | privacypolicy  | 1           |
    Then I press "Sign up"
    Then I should see "User signup successfully."
    Then I should see "This campaign requires a payment for entry."
    And I should see "10.00"
    And I should see "USD"
    Then "Select payment type" "button" should exist
    And I click on "Select payment type" "button"
    And I should see "Bank Transfer" in the ".core_payment_gateways_modal p" "css_element"
    And I click on "Proceed" "button"
    And I should see "Access the Magic campaign campaign." in the ".list-group .list-group-item div" "css_element"
    And I click on "//input[@value='Start process']" "xpath_element"
    And I should see "Transfer process initiated" in the ".alert-info" "css_element"
    And I am on site homepage
    And I should see "This campaign requires a payment for entry." in the ".auth_magic_payment_region" "css_element"
    And I log out

    And I log in as "admin"
    And I am on site homepage
    Then I navigate to "Site administration > Bank Transfer > Manage Transfers" in site administration
    And I should see "stevecarell@gmail.com" in the "Steve Carell" "table_row"
    And I click on "//input[@value='Approve']" "xpath_element"
    And I should see "aprobed" in the ".alert-info" "css_element"

    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I create dash "Campaigns" datasource
    And I open the "New Dash" block preference
    Then I click on "Fields" "link"
    And I click on "Select all" "button"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    And I should see "Description" in the "Demo campaign" "table_row"
    Then "//div[contains(@class, 'table-responsive')]//img[contains(@class, 'img-responsive')][contains(@src, 'pluginfile.php/1/auth_magic/headerimage/')][contains(@src, 'headerbg.jpg')]" "xpath_element" should exist
    Then "//div[contains(@class, 'table-responsive')]//img[contains(@class, 'img-responsive')][contains(@src, 'pluginfile.php/1/auth_magic/backgroundimage/')][contains(@src, 'backgroundbg.jpg')]" "xpath_element" should exist
    Then "//div[contains(@class, 'table-responsive')]//img[contains(@class, 'img-responsive')][contains(@src, 'pluginfile.php/1/auth_magic/logo/')][contains(@src, 'logo.png')]" "xpath_element" should exist
    And I should see "Magic campaign" in the ".table-responsive tr:nth-child(3) td:nth-child(1)" "css_element"
    And I should see "Comments2" in the "Description2" "table_row"
    And I should see "Seats available" in the "Description2" "table_row"
    And I should see "5 total" in the ".table-responsive tr:nth-child(2) td:nth-child(13)" "css_element"
    And I should see "3 total" in the ".table-responsive tr:nth-child(3) td:nth-child(13)" "css_element"
    And I should see "3 available" in the ".table-responsive tr:nth-child(1) td:nth-child(14)" "css_element"
    And I should see "1 available" in the ".table-responsive tr:nth-child(3) td:nth-child(14)" "css_element"
    And I should see "5 of 5 available" in the ".table-responsive tr:nth-child(2) td:nth-child(15)" "css_element"
    And I should see "1 of 3 available" in the ".table-responsive tr:nth-child(3) td:nth-child(15)" "css_element"
    And I should see "Available" in the ".table-responsive tr:nth-child(3) td:nth-child(16)" "css_element"
    And I should see "Yes" in the ".table-responsive tr:nth-child(3) td:nth-child(17)" "css_element"
    And I should see "information" in the ".table-responsive tr:nth-child(2) td:nth-child(18)" "css_element"
    And I should see "disabled" in the ".table-responsive tr:nth-child(3) td:nth-child(18)" "css_element"
    And I should see "No" in the ".table-responsive tr:nth-child(2) td:nth-child(22)" "css_element"
    And I should see "Yes" in the ".table-responsive tr:nth-child(3) td:nth-child(22)" "css_element"
    And I should see "No" in the ".table-responsive tr:nth-child(2) td:nth-child(23)" "css_element"
    And I should see "Cohort 1" in the ".table-responsive tr:nth-child(1) td:nth-child(24)" "css_element"
    And I should see "Cohort 3" in the ".table-responsive tr:nth-child(2) td:nth-child(24)" "css_element"
    And I should see "Course creator" in the ".table-responsive tr:nth-child(2) td:nth-child(25)" "css_element"
    And I should see "parentuser_01 parentuser_01" in the ".table-responsive tr:nth-child(1) td:nth-child(26)" "css_element"
    And I should see "Admin User" in the ".table-responsive tr:nth-child(2) td:nth-child(26)" "css_element"
    And I should see "Free" in the ".table-responsive tr:nth-child(1) td:nth-child(28)" "css_element"
    And I should see "Paid" in the ".table-responsive tr:nth-child(3) td:nth-child(28)" "css_element"
    And I should see "Free" in the ".table-responsive tr:nth-child(1) td:nth-child(29)" "css_element"
    And I should see "10 USD" in the ".table-responsive tr:nth-child(3) td:nth-child(29)" "css_element"
    And I should see "##2 days ago##%A, %d %B %Y##" in the ".table-responsive tr:nth-child(1) td:nth-child(30)" "css_element"
    And I should see "##2 days ago##%A, %d %B %Y##" in the ".table-responsive tr:nth-child(2) td:nth-child(30)" "css_element"

    # Filter preferences
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "New Dash" block preference
    Then I click on "Filters" "link"
    And I set the field "config_preferences[filters][campaign_payment][enabled]" to "1"
    And I set the field "config_preferences[filters][campaign_password][enabled]" to "1"
    And I set the field "config_preferences[filters][campaign_approval_types][enabled]" to "1"
    And I set the field "config_preferences[filters][campaign_owner][enabled]" to "1"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard

    And I should see "Description" in the "Demo campaign" "table_row"
    Then "//div[contains(@class, 'table-responsive')]//img[contains(@class, 'img-responsive')][contains(@src, 'pluginfile.php/1/auth_magic/headerimage/')][contains(@src, 'headerbg.jpg')]" "xpath_element" should exist
    Then "//div[contains(@class, 'table-responsive')]//img[contains(@class, 'img-responsive')][contains(@src, 'pluginfile.php/1/auth_magic/backgroundimage/')][contains(@src, 'backgroundbg.jpg')]" "xpath_element" should exist
    Then "//div[contains(@class, 'table-responsive')]//img[contains(@class, 'img-responsive')][contains(@src, 'pluginfile.php/1/auth_magic/logo/')][contains(@src, 'logo.png')]" "xpath_element" should exist

    # Payment
    When I set the field "campaign_payment" to "Free"
    Then I should see "Demo campaign2" in the "Admin User" "table_row"
    Then I should not see "Magic campaign"
    And I click on ".select2-selection__choice__remove" "css_element"
    When I set the field "campaign_payment" to "Paid"
    Then I should see "Magic campaign" in the "Magic Description" "table_row"
    Then I should not see "Demo campaign2"
    And I click on ".select2-selection__choice__remove" "css_element"

    # Password
    When I set the field "campaign_password" to "No"
    Then I should see "Demo campaign2" in the "Admin User" "table_row"
    Then I should not see "Magic campaign"
    And I click on ".select2-selection__choice__remove" "css_element"

    When I set the field "campaign_password" to "Yes"
    Then I should see "Magic campaign" in the "Magic Description" "table_row"
    Then I should not see "Demo campaign2"
    And I click on ".select2-selection__choice__remove" "css_element"

    # Approval Types
    When I set the field "campaign_approval_types" to "Disabled"
    Then I should see "Magic campaign" in the "Magic Description" "table_row"
    Then I should not see "Demo campaign2"
    And I click on ".select2-selection__choice__remove" "css_element"

    When I set the field "campaign_approval_types" to "Information"
    Then I should see "Demo campaign2" in the "Description2" "table_row"
    Then I should not see "Magic campaign"
    And I click on ".select2-selection__choice__remove" "css_element"

    # Campaign Owner
    When I set the field "campaign_owner" to "Admin User"
    Then I should see "Demo campaign2" in the "Description2" "table_row"
    Then I should not see "Magic campaign"
    And I click on ".select2-selection__choice__remove" "css_element"

    When I set the field "campaign_owner" to "parentuser_01 parentuser_01"
    Then I should see "Magic campaign" in the "Magic Description" "table_row"
    Then I should not see "Demo campaign2"

    # Condition preferences
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    # My Campaigns
    And I set the field "config_preferences[filters][my_campaign][enabled]" to "1"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    And I should see "Demo campaign2" in the "Description2" "table_row"
    And I should not see "Magic campaign"

    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][my_campaign][enabled]" to "0"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    And I should see "Demo campaign" in the ".table-responsive tr:nth-child(1) td:nth-child(1)" "css_element"
    And I should see "Magic campaign" in the ".table-responsive tr:nth-child(3) td:nth-child(1)" "css_element"

    # Approval Types
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][approval_types][enabled]" to "1"
    When I set the field "config_preferences[filters][approval_types][approvaltypes][]" to "Information"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    Then I should see "Demo campaign2" in the "Admin User" "table_row"
    Then I should not see "Magic campaign"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I click on ".select2-selection__choice__remove" "css_element"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    And I should see "Demo campaign" in the ".table-responsive tr:nth-child(1) td:nth-child(1)" "css_element"
    And I should see "Magic campaign" in the ".table-responsive tr:nth-child(3) td:nth-child(1)" "css_element"

    # Campaign Dates
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I set the field "config_preferences[filters][campaign_dates][enabled]" to "1"
    When I set the field "config_preferences[filters][campaign_dates][campaigndates][]" to "Past"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    Then I should see "Demo campaign" in the "Admin User" "table_row"
    Then I should not see "Magic campaign"

    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I click on ".select2-selection__choice__remove" "css_element"
    And I set the field "config_preferences[filters][campaign_dates][campaigndates][]" to "Present"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    And I should see "Demo campaign2" in the ".table-responsive tr:nth-child(1) td:nth-child(1)" "css_element"
    Then I should not see "Magic campaign"

    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    And I click on ".select2-selection__choice__remove" "css_element"
    And I set the field "config_preferences[filters][campaign_dates][campaigndates][]" to "Future"
    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    And I should see "Magic campaign" in the ".table-responsive tr:nth-child(3) td:nth-child(1)" "css_element"
    And I should not see "Demo campaign"
    And I log out

    ## Hide My Campaign
    And I click on "Log in" "link" in the ".logininfo" "css_element"
    And I set the field "Username" to "alan_turing"
    And I set the field "Password" to "Alan123#"
    Then I press "Log in"
    And I follow dashboard
    And I should see "Demo campaign" in the ".table-responsive tr:nth-child(1) td:nth-child(1)" "css_element"
    And I should see "Demo campaign2" in the ".table-responsive tr:nth-child(2) td:nth-child(1)" "css_element"
    And I should see "Magic campaign" in the ".table-responsive tr:nth-child(3) td:nth-child(1)" "css_element"
    And I log out

    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn dash block editing mode on
    And I open the "New Dash" block preference
    Then I click on "Conditions" "link"
    When I set the field "config_preferences[filters][campaign_dates][enabled]" to "0"
    And I set the field "config_preferences[filters][hide_my_campaign][enabled]" to "1"

    And I press "Save changes"
    And I click on "Reset Dashboard for all users" "button"
    And I follow dashboard
    And I log out

    And I click on "Log in" "link" in the ".logininfo" "css_element"
    And I set the field "Username" to "alan_turing"
    And I set the field "Password" to "Alan123#"
    Then I press "Log in"
    And I follow dashboard
    And I should see "Demo campaign" in the ".table-responsive tr:nth-child(1) td:nth-child(1)" "css_element"
    And I should not see "Magic campaign"