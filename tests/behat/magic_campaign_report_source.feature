@auth @auth_magic @magic_campaign_report_source @_file_upload
Feature: Magic campaign report source workflow.
  In order to show the campaign report source workflow for magic auth.

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
      | name          | idnumber  |
      | Cohort 1      | CH1       |
      | Cohort 2      | CH2       |
      | Cohort 3      | CH3       |
    And the following "cohort members" exist:
      | user          | cohort    |
      | user_01       | CH1       |
      | user_02       | CH1       |
    Then the following "categories" exist:
      | name          | category  | idnumber | category |
      | Category E    | 0         | CE       | 0        |
      | Category ED   | 1         | CED      | CE       |
    And the following "courses" exist:
      | fullname      | shortname | category | enablecompletion | showcompletionconditions |
      | Course C1     | CC1       | CE       | 1                | 1                        |
      | Course C2     | CC2       | 0        | 1                | 1                        |
    And the following "groups" exist:
      | name          | course  | idnumber |
      | Group 1       | CC1     | G1       |
      | Group 2       | CC2     | G2       |
    And the following "groupings" exist:
      | name          | course | idnumber |
      | Grouping 1    | CC1    | GG1      |
      | Grouping 2    | CC1    | GG2      |
      | Grouping 3    | CC2    | GG3      |
    And the following "course enrolments" exist:
      | user          | course | role           |
      | user_01       | CC1    | student        |
      | admin         | CC1    | student        |
      | teacher1      | CC1    | editingteacher |
    And the following "activities" exist:
      | activity      | course | name     | idnumber         | completion | completionview |
      | assign        | CC1    | assign2  | Test assignment2 | 1          | 1              |
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
      | Available from         | ##23 September 2024## |
      | Available closes       | ##25 September 2025## |
      | Campaign password      |                   |
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
      | Available closes       | ##31 September 2026## |
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
  Scenario: Report source campaigns fields
    Given I log in as "admin"
    Then I navigate to "Reports > Report builder > Custom reports" in site administration
    And I click on "My report" "link"

    # Report edit page
    And I should see "My report" in the ".navbar h1" "css_element"
    And I click on "Name" "link"
    And I click on "Description" "link"
    And I click on "Comments" "link"
    And I click on "Capacity" "link"
    And I click on "Status" "link"
    And I click on "Visibility" "link"
    And I click on "Restrict by role" "link"
    And I click on "Restrict by cohorts" "link"
    And I click on "Available from" "link"
    And I click on "Available closes" "link"
    And I click on "Password" "link"
    And I click on "Cohort membership" "link"
    And I click on "Global role" "link"
    And I click on "Campaign owner" "link"
    And I click on "Consent option" "link"
    And I click on "Welcome message" "link"
    And I click on "Follow up" "link"
    And I click on "Expiration date" "link"
    And I click on "Campaign course" "link"

    # Payment method
    And I am on site homepage
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign" "table_row"
    And I set the following fields to these values:
    | Type                   | Paid              |
    | Fee                    | 10                |
    | Currency               | US Dollar         |
    | Account                | Bank Transfer     |
    # Restrict by Role
    | By role                | Student           |
    # Restrict by Cohort
    | By cohort              | Cohort 1          |
    Then I press "Save changes"
    And I am on site homepage
    Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
    And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign1" "table_row"
    And I set the following fields to these values:
    # Restrict by Role
    | By role                | Teacher           |
    # Restrict by Cohort
    | By cohort              | Cohort 2          |
    Then I press "Save changes"
    Then I navigate to "Reports > Report builder > Custom reports" in site administration
    And I click on "My report" "link" in the "My report" "table_row"
    And I click on "Registration fee" "link"

    # Campaigns field
    And I am on site homepage
    Then I navigate to "Reports > Report builder > Custom reports" in site administration
    And I press "View" action in the "My report" report row
    And I should see "Description" in the "Demo campaign" "table_row"
    And I should see "Demo campaign1" in the "Description1" "table_row"
    And I should see "Comments2" in the "Demo campaign2" "table_row"
    And I should see "2" in the ".generaltable tbody tr:nth-child(2) td:nth-child(4)" "css_element"
    And I should see "5" in the ".generaltable tbody tr:nth-child(3) td:nth-child(4)" "css_element"
    And I should see "Available" in the "Demo campaign" "table_row"
    And I should see "Archived" in the "Demo campaign1" "table_row"
    And I should see "Hidden" in the "Demo campaign1" "table_row"
    And I should see "Visible" in the "Demo campaign2" "table_row"
    And I should see "Student" in the "Demo campaign" "table_row"
    And I should see "Teacher" in the "Demo campaign1" "table_row"
    # And I should see "##2 days ago##%A, %d %B %Y##" in the "Demo campaign" "table_row"
    And I should see "##+1 year##%A, %d %B %Y##" in the "Demo campaign" "table_row"
    And I should see "Yes" in the "Demo campaign1" "table_row"
    And I should see "No" in the "Demo campaign" "table_row"
    And I should see "Cohort 1" in the "Demo campaign" "table_row"
    And I should see "Cohort 2" in the "Demo campaign1" "table_row"
    And I should see "Manager" in the "Demo campaign1" "table_row"
    And I should see "Course creator" in the "Demo campaign2" "table_row"
    And I should see "parentuser_01 parentuser_01" in the "Demo campaign" "table_row"
    And I should see "user_01 user_01" in the "Demo campaign1" "table_row"
    And I should see "Yes" in the ".generaltable tbody tr:nth-child(1) td:nth-child(13)" "css_element"
    And I should see "No" in the ".generaltable tbody tr:nth-child(2) td:nth-child(13)" "css_element"
    And I should see "No" in the ".generaltable tbody tr:nth-child(2) td:nth-child(14)" "css_element"
    And I should see "Yes" in the ".generaltable tbody tr:nth-child(3) td:nth-child(14)" "css_element"
    And I should see "0" in the ".generaltable tbody tr:nth-child(2) td:nth-child(15)" "css_element"
    And I should see "1" in the ".generaltable tbody tr:nth-child(3) td:nth-child(15)" "css_element"
    And I should see "Course C1" in the "Demo campaign" "table_row"
    And I should see "Course C2" in the "Demo campaign1" "table_row"
    And I should see "3" in the "Demo campaign2" "table_row"
    And I should see "10 USD" in the "Demo campaign" "table_row"

  # @javascript
  # Scenario: Report source campaign group
  #   # Campaign Groups
  #   Given I log in as "admin"
  #   And I am on site homepage
  #   Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
  #   And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign" "table_row"
  #   And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
  #   Then I log out
  #   And I open magic campaign "Demo campaign"
  #   And I should see "Demo campaign"
  #   And I set the following fields to these values:
  #     | firstname      | demo        |
  #     | lastname       | user01      |
  #     | username       | demouser01  |
  #     | password       | Test123#    |
  #     | email          | demouser01@gmail.com |
  #     | email2         | demouser01@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   Then I should see "User signup successfully."
  #   And I log out
  #   And I log in as "admin"

  #   And I am on site homepage
  #   Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
  #   And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign" "table_row"
  #   And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
  #   Then I log out
  #   And I open magic campaign "Demo campaign"
  #   And I should see "Demo campaign"
  #   And I set the following fields to these values:
  #     | firstname      | demo        |
  #     | lastname       | user02      |
  #     | username       | demouser02  |
  #     | password       | Test123#    |
  #     | email          | demouser02@gmail.com |
  #     | email2         | demouser02@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   Then I should see "User signup successfully."
  #   And I log out
  #   And I log in as "admin"

  #   And I am on site homepage
  #   Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
  #   And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign" "table_row"
  #   And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
  #   Then I log out
  #   And I open magic campaign "Demo campaign"
  #   And I should see "Demo campaign"
  #   And I set the following fields to these values:
  #     | firstname      | demo        |
  #     | lastname       | user03      |
  #     | username       | demouser03  |
  #     | password       | Test123#    |
  #     | email          | demouser03@gmail.com |
  #     | email2         | demouser03@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   Then I should see "User signup successfully."
  #   And I log out
  #   And I log in as "admin"

  #   And I am on site homepage
  #   And I am on "Course C2" course homepage
  #   And I navigate to "Settings" in current page administration
  #   And I expand all fieldsets
  #   And I set the following fields to these values:
  #     | Group mode             | Separate groups   |
  #   Then I press "Save and display"
  #   Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
  #   And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign" "table_row"
  #   And I set the following fields to these values:
  #     | Groups                 | Campaign          |
  #     | Grouping               | Grouping 1        |
  #     | Group capacity         | 5                 |
  #   Then I press "Save changes"
  #   And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign2" "table_row"
  #   And I set the following fields to these values:
  #     | Groups                 | Per User          |
  #     | Grouping               | Grouping 3        |
  #     | Group capacity         | 3                 |
  #   Then I press "Save changes"
  #   And I click on ".icon[title='Copy link']" "css_element" in the "Demo campaign2" "table_row"
  #   And I click on "Copy link to cliboard" "button" in the ".modal-body" "css_element"
  #   Then I log out
  #   And I open magic campaign "Demo campaign2"
  #   And I should see "Demo campaign2"
  #   And I set the following fields to these values:
  #     | firstname      | demo        |
  #     | lastname       | user04      |
  #     | username       | demouser04  |
  #     | password       | Test123#    |
  #     | email          | demouser04@gmail.com |
  #     | email2         | demouser04@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   And I wait "20" seconds
  #   Then I should see "User signup successfully."
  #   And I log out
  #   And I log in as "admin"

  #   And I am on site homepage
  #   Then I navigate to "Reports > Report builder > Custom reports" in site administration
  #   And I click on "My report" "link"
  #   # Campaign Groups Reprot edit page
  #   And I click on "Group name" "link"
  #   And I click on "Group capacity" "link"
  #   And I click on "Member Count" "link"
  #   And I click on "Available seats" "link"
  #   And I click on "Status" "link"
  #   Then I click on "Close" "button"

  #   And I am on site homepage
  #   Then I navigate to "Reports > Report builder > Custom reports" in site administration
  #   And I press "View" action in the "My report" report row
  #   # Campaign Groups Reprot view page
  #   And I close block drawer if open
  #   And I should see "Demo campaign" in the "Demo campaign" "table_row"
  #   And I should see "5" in the ".generaltable tbody tr:first-child td:nth-child(2)" "css_element"
  #   And I should see "3" in the "demo user04 Demo campaign2" "table_row"
  #   And I should see "Unlimited" in the ".generaltable tbody tr:nth-child(3) td:nth-child(2)" "css_element"
  #   And I should see "0" in the ".generaltable tbody tr:nth-child(1) td:nth-child(3)" "css_element"
  #   And I should see "1" in the ".generaltable tbody tr:nth-child(2) td:nth-child(3)" "css_element"
  #   And I should see "5" in the ".generaltable tbody tr:nth-child(1) td:nth-child(4)" "css_element"
  #   And I should see "2" in the ".generaltable tbody tr:nth-child(2) td:nth-child(4)" "css_element"
  #   And I should see "Available" in the "Demo campaign" "table_row"
  #   And I should see "Archived" in the ".generaltable tbody tr:nth-child(3) td:nth-child(5)" "css_element"

  # @javascript
  # Scenario: Report source campaign statistics
  #   # Campaign statistics
  #   Given I log in as "admin"
  #   And I am on site homepage
  #   Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
  #   Then I should see "Create campaign"
  #   And I click on "Create campaign" "button"
  #   And I set the following fields to these values:
  #     | Title                  | Demo campaign04   |
  #     | Description            | Description4      |
  #     | Comments               | Comments4         |
  #     | Capacity               | 4                 |
  #     | Status                 | Available         |
  #     | Visibility             | Visible           |
  #     | Require email confirmation | No            |
  #     | Type                   | Paid              |
  #     | Fee                    | 10                |
  #     | Currency               | US Dollar         |
  #     | Account                | Bank Transfer     |
  #     | Available from         | ##31 March 2024## |
  #     | Available closes       | ##31 March 2025## |
  #     | Campaign password      |                   |
  #     | Cohort membership      | Cohort 1          |
  #     | Global role            | Disabled          |
  #     | Campaign owner account | parentuser_01 parentuser_01 |
  #     | Display consent option | 1                 |
  #     | Send welcome message to new accounts   | 1 |
  #     | Send follow up message to new accounts | 1 |
  #     | Campaign course        | Course C1         |
  #     | First name             | Required          |
  #     | Last name              | Required          |
  #     | Username               | Required          |
  #     | e-Mail                 | Required          |
  #     | Password               | Required Once     |
  #   Then I press "Save changes"
  #   And I log out

  #   And I open magic campaign "Demo campaign"
  #   And I should see "Demo campaign"
  #   And I set the following fields to these values:
  #     | firstname      | demo        |
  #     | lastname       | user01      |
  #     | username       | demouser01  |
  #     | password       | Test123#    |
  #     | email          | demouser01@gmail.com |
  #     | email2         | demouser01@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   Then I should see "User signup successfully."
  #   And I log out

  #   And I open magic campaign "Demo campaign04"
  #   And I should see "Demo campaign04"
  #   And I set the following fields to these values:
  #     | firstname      | demo        |
  #     | lastname       | user02      |
  #     | username       | demouser02  |
  #     | password       | Test123#    |
  #     | email          | demouser02@gmail.com |
  #     | email2         | demouser02@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   Then I should see "User signup successfully."
  #   Then I should see "This campaign requires a payment for entry."
  #   And I should see "10.00"
  #   And I should see "USD"
  #   Then "Select payment type" "button" should exist
  #   And I click on "Select payment type" "button"
  #   And I should see "Bank Transfer" in the ".core_payment_gateways_modal p" "css_element"
  #   And I click on "Proceed" "button"
  #   And I should see "Access the Demo campaign04 campaign." in the ".list-group .list-group-item div" "css_element"
  #   And I click on "//input[@value='Start process']" "xpath_element"
  #   And I should see "Transfer process initiated" in the ".alert-info" "css_element"
  #   And I am on site homepage
  #   And I should see "This campaign requires a payment for entry." in the ".auth_magic_payment_region" "css_element"
  #   And I log out

  #   # Campaign Payment approval
  #   And I log in as "admin"
  #   Then I navigate to "Site administration > Bank Transfer > Manage Transfers" in site administration
  #   And I should see "demouser02@gmail.com" in the "demo user" "table_row"
  #   And I click on "//input[@value='Approve']" "xpath_element"
  #   And I should see "aprobed" in the ".alert-info" "css_element"

  #   And I open magic campaign "Demo campaign04"
  #   And I should see "Demo campaign04"
  #   And I set the following fields to these values:
  #     | firstname      | demo        |
  #     | lastname       | user04      |
  #     | username       | demouser04  |
  #     | password       | Test123#    |
  #     | email          | demouser04@gmail.com |
  #     | email2         | demouser04@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   Then I should see "User signup successfully."
  #   Then I should see "This campaign requires a payment for entry."
  #   And I should see "10.00"
  #   And I should see "USD"
  #   Then "Select payment type" "button" should exist
  #   And I click on "Select payment type" "button"
  #   And I should see "Bank Transfer" in the ".core_payment_gateways_modal p" "css_element"
  #   And I click on "Proceed" "button"
  #   And I should see "Access the Demo campaign04 campaign." in the ".list-group .list-group-item div" "css_element"
  #   And I click on "//input[@value='Start process']" "xpath_element"
  #   And I should see "Transfer process initiated" in the ".alert-info" "css_element"
  #   And I am on site homepage
  #   And I should see "This campaign requires a payment for entry." in the ".auth_magic_payment_region" "css_element"
  #   And I log out

  #   # Campaign Payment approval
  #   And I log in as "admin"
  #   Then I navigate to "Site administration > Bank Transfer > Manage Transfers" in site administration
  #   And I should see "demouser04@gmail.com" in the "demo user" "table_row"
  #   And I click on "//input[@value='Approve']" "xpath_element"
  #   And I should see "aprobed" in the ".alert-info" "css_element"
  #   And I log out

  #   And I log in as "admin"
  #   Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
  #   And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign04" "table_row"
  #   And I set the following fields to these values:
  #     | Require email confirmation | Yes           |
  #   Then I press "Save changes"
  #   And I log out

  #   And I open magic campaign "Demo campaign04"
  #   And I should see "Demo campaign04"
  #   And I set the following fields to these values:
  #     | firstname      | demo        |
  #     | lastname       | user03      |
  #     | username       | demouser03  |
  #     | password       | Test123#    |
  #     | email          | demouser03@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   Then I should see "User signup successfully."
  #   And I log out

  #   # Campaigns Statistics report edit page
  #   And I log in as "admin"
  #   Then I navigate to "Reports > Report builder > Custom reports" in site administration
  #   And I click on "My report" "link" in the "My report" "table_row"
  #   And I click on "Name" "link"
  #   And I click on "Confirmed Users" "link"
  #   And I click on "Unconfirmed Users" "link"
  #   And I click on "Campaign Available seats" "link"
  #   And I click on "Total revenue" "link"

  #   # Campaigns Statistics report view page
  #   And I am on site homepage
  #   Then I navigate to "Reports > Report builder > Custom reports" in site administration
  #   And I press "View" action in the "My report" report row
  #   And I close block drawer if open

  #   # Confirmed Users
  #   And I should see "1" in the ".generaltable tbody tr:nth-child(1) td:nth-child(2)" "css_element"
  #   And I should see "2" in the ".generaltable tbody tr:nth-child(4) td:nth-child(2)" "css_element"
  #   # Unconfirmed Users
  #   And I should see "0" in the ".generaltable tbody tr:nth-child(3) td:nth-child(3)" "css_element"
  #   And I should see "1" in the ".generaltable tbody tr:nth-child(4) td:nth-child(3)" "css_element"
  #   # Available seats Users
  #   And I should see "2 available" in the "Demo campaign" "table_row"
  #   And I should see "1 available" in the "Demo campaign04" "table_row"
  #   # Total revenue Users
  #   And I should see "0" in the ".generaltable tbody tr:nth-child(3) td:nth-child(5)" "css_element"
  #   And I should see "20" in the ".generaltable tbody tr:nth-child(4) td:nth-child(5)" "css_element"

  # @javascript
  # Scenario: Report source campaign User statistics
  #   Given I log in as "admin"
  #   And I am on site homepage
  #   And I log out
  #   And I open magic campaign "Demo campaign"
  #   And I should see "Demo campaign"
  #   And I set the following fields to these values:
  #     | firstname      | demo        |
  #     | lastname       | user01      |
  #     | username       | demouser01  |
  #     | password       | Test123#    |
  #     | email          | demouser01@gmail.com |
  #     | email2         | demouser01@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   Then I should see "User signup successfully."
  #   And I log out

  #   And I open magic campaign "Demo campaign2"
  #   And I should see "Demo campaign2"
  #   And I set the following fields to these values:
  #     | firstname      | demo        |
  #     | lastname       | user02      |
  #     | username       | demouser02  |
  #     | password       | Test123#    |
  #     | email          | demouser02@gmail.com |
  #     | email2         | demouser02@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   Then I should see "User signup successfully."
  #   And I log out
  #   And I click on "Log in" "link" in the ".logininfo" "css_element"
  #   And I set the field "Username" to "demouser01"
  #   And I set the field "Password" to "Test123#"
  #   Then I press "Log in"
  #   And I am on site homepage
  #   And I log out
  #   And I log in as "admin"

  #   # Campaign User Statistics edit page
  #   Then I navigate to "Reports > Report builder > Custom reports" in site administration
  #   And I click on "My report" "link"
  #   And I click on "Name" "link"
  #   And I click on "Logins" "link"
  #   And I click on "Badges awarded" "link"
  #   And I click on "Enrolled courses" "link"
  #   And I click on "Inprogress courses" "link"
  #   And I click on "Completed courses" "link"
  #   And I click on "Activities completed" "link"
  #   And I click on "Full name" "link"

  #   # Campaign User Statistics view page
  #   And I am on site homepage
  #   Then I navigate to "Reports > Report builder > Custom reports" in site administration
  #   And I press "View" action in the "My report" report row
  #   And I close block drawer if open
  #   And I should see "1" in the ".generaltable tbody tr:nth-child(2) td:nth-child(2)" "css_element"
  #   And I should see "2" in the ".generaltable tbody tr:nth-child(1) td:nth-child(2)" "css_element"
  #   And I log out

  #   # Create Badge
  #   And I log in as "teacher1"
  #   And I am on the "Course C1" "course editing" page
  #   And I navigate to "Badges > Add a new badge" in current page administration
  #   And I set the following fields to these values:
  #     | Name | Course Badge |
  #     | Description | Course badge description |
  #   And I upload "badges/tests/behat/badge.png" file to "Image" filemanager
  #   And I press "Create badge"
  #   And I set the field "type" to "Manual issue by role"
  #   And I expand all fieldsets
  #   And I set the field "Teacher" to "1"
  #   And I set the field "Any of the selected roles awards the badge" to "1"
  #   And I press "Save"
  #   And I press "Enable access"
  #   And I press "Continue"
  #   And I log out

  #   # Award badge
  #   And I log in as "teacher1"
  #   And I am on the "Course C1" "course editing" page
  #   And I navigate to "Badges > Manage badges" in current page administration
  #   And I follow "Course Badge"
  #   And I select "Recipients (0)" from the "jump" singleselect
  #   And I press "Award badge"
  #   And I set the field "potentialrecipients[]" to "demo user01 (demouser01@gmail.com)"
  #   When I press "Award badge"
  #   And I am on "Course C1" course homepage
  #   And I navigate to "Badges > Manage badges" in current page administration
  #   And I follow "Course Badge"
  #   And I should see "Recipients (1)"
  #   And I log out
  #   # Demo user should have badge.
  #   And I click on "Log in" "link" in the ".logininfo" "css_element"
  #   And I set the field "Username" to "demouser01"
  #   And I set the field "Password" to "Test123#"
  #   Then I press "Log in"
  #   And I follow "Profile" in the user menu
  #   When I click on "Course C1" "link" in the "region-main" "region"
  #   Then I should see "Course Badge"
  #   And I log out

  #   And I log in as "admin"
  #   And I am on site homepage
  #   Then I navigate to "Reports > Report builder > Custom reports" in site administration
  #   And I press "View" action in the "My report" report row
  #   # Badge Award
  #   And I should see "1" in the ".generaltable tbody tr:nth-child(1) td:nth-child(3)" "css_element"
  #   And I should see "0" in the ".generaltable tbody tr:nth-child(2) td:nth-child(3)" "css_element"

  #   # Course Enrollment
  #   And I am on site homepage
  #   Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
  #   And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign2" "table_row"
  #   And I set the following fields to these values:
  #     | Course role for student | Student    |
  #   Then I press "Save changes"

  #   And I am on "Course C1" course homepage
  #   Given I navigate to course participants
  #   And I press "Enrol users"
  #   When I set the field "Select users" to "demouser02"
  #   And I should see "demo user02"
  #   And the "Assign role" select box should contain "Student"
  #   And I click on "Enrol selected users and cohorts" "button" in the "Enrol users" "dialogue"
  #   Then I should see "Active" in the "demo user02" "table_row"
  #   And I should see "1 enrolled users"

  #   And I am on site homepage
  #   Then I navigate to "Reports > Report builder > Custom reports" in site administration
  #   And I press "View" action in the "My report" report row
  #   # Enrolled courses
  #   And I should see "1" in the ".generaltable tbody tr:nth-child(1) td:nth-child(4)" "css_element"
  #   And I should see "1" in the ".generaltable tbody tr:nth-child(2) td:nth-child(4)" "css_element"

  #   # Inprogress courses
  #   And I should see "1" in the ".generaltable tbody tr:nth-child(1) td:nth-child(5)" "css_element"
  #   And I should see "1" in the ".generaltable tbody tr:nth-child(2) td:nth-child(5)" "css_element"

  #   And I log in as "admin"
  #   And I am on "Course C1" course homepage
  #   And I should see "assign2"
  #   And I navigate to "Course completion" in current page administration
  #   And I expand all fieldsets
  #   And I set the following fields to these values:
  #     | Assignment - assign2 | 1 |
  #   And I click on "Save changes" "button"
  #   And I log out
  #   And I click on "Log in" "link" in the ".logininfo" "css_element"
  #   And I set the field "Username" to "demouser01"
  #   And I set the field "Password" to "Test123#"
  #   Then I press "Log in"
  #   And I am on "Course C1" course homepage
  #   And I press "Mark as done"
  #   And I log out
  #   And I log in as "admin"
  #   And I trigger cron

  #   And I am on site homepage
  #   Then I navigate to "Reports > Report builder > Custom reports" in site administration
  #   And I press "View" action in the "My report" report row
  #   # Course completed
  #   And I should see "1" in the ".generaltable tbody tr:nth-child(1) td:nth-child(6)" "css_element"
  #   And I should see "1" in the ".generaltable tbody tr:nth-child(2) td:nth-child(6)" "css_element"

  #   # Activities completed
  #   And I should see "1" in the ".generaltable tbody tr:nth-child(1) td:nth-child(7)" "css_element"
  #   And I should see "0" in the ".generaltable tbody tr:nth-child(2) td:nth-child(7)" "css_element"

  #   # User Field - Full name
  #   And I should see "demo user01" in the "Demo campaign" "table_row"
  #   And I should see "demo user02" in the "Demo campaign2" "table_row"

  # @javascript
  # Scenario Outline: Report source campaign conditions
  # Given I log in as "admin"
  #   And I am on site homepage
  #   # Create Campaign
  #   And I am on site homepage
  #   Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
  #   Then I should see "Create campaign"
  #   And I click on "Create campaign" "button"
  #   And I set the following fields to these values:
  #     | Title                  | Magic campaign    |
  #     | Description            | Magic Description |
  #     | Comments               | Comments Magic    |
  #     | Capacity               | 3                 |
  #     | Status                 | Available         |
  #     | Visibility             | Visible           |
  #     | Require email confirmation | No            |
  #     | Type                   | Paid              |
  #     | Fee                    | 10                |
  #     | Currency               | US Dollar         |
  #     | Account                | Bank Transfer     |
  #     | expirytime[enabled]    | 1                 |
  #     | expirytime[number]     | 2                 |
  #     | expirytime[timeunit]   | days              |
  #     | Available from         | ##1 March 2024##  |
  #     | Available closes       | ##31 March 2025## |
  #     | Campaign password      |                   |
  #     | Cohort membership      | Cohort 1          |
  #     | Global role            | Disabled          |
  #     | Campaign owner account | parentuser_01 parentuser_01 |
  #     | Display consent option | 1                 |
  #     | Send welcome message to new accounts   | 1 |
  #     | Send follow up message to new accounts | 1 |
  #     | Campaign course        | Course C1         |
  #     | Course role for student| Student           |
  #     | First name             | Required          |
  #     | Last name              | Required          |
  #     | Username               | Required          |
  #     | e-Mail                 | Required          |
  #     | Password               | Required Once     |
  #   Then I press "Save changes"
  #   And I log out

  #   And I open magic campaign "Magic campaign"
  #   And I should see "Magic campaign"
  #   And I set the following fields to these values:
  #     | firstname      | Alan        |
  #     | lastname       | Turing      |
  #     | username       | alan_turing |
  #     | password       | Alan123#    |
  #     | email          | alanturing@gmail.com |
  #     | email2         | alanturing@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   Then I should see "User signup successfully."
  #   Then I should see "This campaign requires a payment for entry."
  #   And I should see "10.00"
  #   And I should see "USD"
  #   Then "Select payment type" "button" should exist
  #   And I click on "Select payment type" "button"
  #   And I should see "Bank Transfer" in the ".core_payment_gateways_modal p" "css_element"
  #   And I click on "Proceed" "button"
  #   And I should see "Access the Magic campaign campaign." in the ".list-group .list-group-item div" "css_element"
  #   And I click on "//input[@value='Start process']" "xpath_element"
  #   And I should see "Transfer process initiated" in the ".alert-info" "css_element"
  #   And I am on site homepage
  #   And I should see "This campaign requires a payment for entry." in the ".auth_magic_payment_region" "css_element"
  #   And I log out

  #   And I log in as "admin"
  #   And I am on site homepage
  #   Then I navigate to "Site administration > Bank Transfer > Manage Transfers" in site administration
  #   And I should see "alanturing@gmail.com" in the "Alan Turing" "table_row"
  #   And I click on "//input[@value='Approve']" "xpath_element"
  #   And I should see "aprobed" in the ".alert-info" "css_element"
  #   And I log out

  #   And I open magic campaign "Magic campaign"
  #   And I should see "Magic campaign"
  #   And I set the following fields to these values:
  #     | firstname      | Steve        |
  #     | lastname       | Carell       |
  #     | username       | steve_carell |
  #     | password       | Test123#     |
  #     | email          | stevecarell@gmail.com |
  #     | email2         | stevecarell@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   Then I should see "User signup successfully."
  #   Then I should see "This campaign requires a payment for entry."
  #   And I should see "10.00"
  #   And I should see "USD"
  #   Then "Select payment type" "button" should exist
  #   And I click on "Select payment type" "button"
  #   And I should see "Bank Transfer" in the ".core_payment_gateways_modal p" "css_element"
  #   And I click on "Proceed" "button"
  #   And I should see "Access the Magic campaign campaign." in the ".list-group .list-group-item div" "css_element"
  #   And I click on "//input[@value='Start process']" "xpath_element"
  #   And I should see "Transfer process initiated" in the ".alert-info" "css_element"
  #   And I am on site homepage
  #   And I should see "This campaign requires a payment for entry." in the ".auth_magic_payment_region" "css_element"
  #   And I log out

  #   And I log in as "admin"
  #   And I am on site homepage
  #   Then I navigate to "Site administration > Bank Transfer > Manage Transfers" in site administration
  #   And I should see "stevecarell@gmail.com" in the "Steve Carell" "table_row"
  #   And I click on "//input[@value='Approve']" "xpath_element"
  #   And I should see "aprobed" in the ".alert-info" "css_element"
  #   And I log out

  #   And I log in as "admin"
  #   # Report edit page
  #   Then I navigate to "Reports > Report builder > Custom reports" in site administration
  #   And I click on "My report" "link"
  #   # Campaigns
  #   And I should see "My report" in the ".navbar h1" "css_element"
  #   And I click on "Name with link" "link"
  #   And I click on "a.list-group-item[data-name='Name']" "css_element"
  #   And I click on "Description" "link"
  #   And I click on "Comments" "link"
  #   And I click on "Capacity" "link"
  #   And I click on "Status" "link"
  #   And I click on "Visibility" "link"
  #   And I click on "Available from" "link"
  #   And I click on "Available closes" "link"
  #   And I click on "Password" "link"
  #   And I click on "Cohort membership" "link"
  #   And I click on "Global role" "link"
  #   And I click on "Campaign owner" "link"
  #   And I click on "Consent option" "link"
  #   And I click on "Welcome message" "link"
  #   And I click on "Follow up" "link"
  #   And I click on "Campaign course" "link"
  #   And I click on "Registration fee" "link"

  #   # Conditions in the report.
  #   And I click on "Show/hide 'Conditions'" "button"
  #   Then I should see "There are no conditions selected" in the "[data-region='settings-conditions']" "css_element"
  #   And I set the field "Select a condition" to "<campaign>"
  #   # Campaign Name
  #   And I set the field "<field>" to "<operator>"
  #   And I set the field "<operator_field>" to "<search>"
  #   And I set the field "<field>" to "<operator>"
  #   And I click on "Apply" "button" in the "[data-region='settings-conditions']" "css_element"
  #   Then I should see "Conditions applied"
  #   And I should see "<value>" in the "<table>" "css_element"
  #   And I should not see "<not>" in the "<table>" "css_element"

  #   Examples:
  #     | campaign            | field                                  | search             | operator_field                        | operator            | value             | not               | table                                  |
  #     | Name                | campaign:title_operator                | magic                | campaign:title_value                 | Contains           | Magic campaign    | demo campaign     | [data-cardtitle='Name']                |
  #     | Name                | campaign:title_operator                | magic campaign       | campaign:title_value                 | Is equal to        | Magic campaign    | demo campaign     | [data-cardtitle='Name']                |
  #     | Name                | campaign:title_operator                | demo                 | campaign:title_value                 | Contains           | Demo campaign     | Magic campaign    | [data-cardtitle='Name']                |
  #     | Name                | campaign:title_operator                | demo campaign        | campaign:title_value                 | Is equal to        | Demo campaign     | Magic campaign    | [data-cardtitle='Name']                |
  #     | Description         | campaign:description_operator          | magic                | campaign:description_value           | Contains           | Magic Description | Description1      | [data-cardtitle='Description']         |
  #     | Description         | campaign:description_operator          | Description2         | campaign:description_value           | Does not contain   | Magic Description | Description2      | [data-cardtitle='Description']         |
  #     | Description         | campaign:description_operator          | description          | campaign:description_value           | Contains           | Description       | Magic description | [data-cardtitle='Description']         |
  #     | Description         | campaign:description_operator          | description          | caxmpaign:description_value          | Is equal to        | Description       | Magic description | [data-cardtitle='Description']         |
  #     | Comments            | campaign:comments_operator             | Comment              | campaign:comments_value              | Contains           | Comment           | Comment1          | [data-cardtitle='Comments']            |
  #     | Comments            | campaign:comments_operator             | Magic                | campaign:comments_value              | Does not contain   | Comments          | Comments Magic    | [data-cardtitle='Comments']            |
  #     | Capacity            | campaign:capacity_operator             | 3                    | campaign:capacity_value              | Contains           | 3                 | 2                 | [data-cardtitle='Capacity']            |
  #     | Capacity            | campaign:capacity_operator             | 5                    | campaign:capacity_value              | Is equal to        | 5                 | 3                 | [data-cardtitle='Capacity']            |
  #     | Status              | campaign:status_operator               | Archived             | campaign:status_value                | Is equal to        | Archived          | Available         | [data-cardtitle='Status']              |
  #     | Status              | campaign:status_operator               | Archived             | campaign:status_value                | Is not equal to    | Available         | Archived          | [data-cardtitle='Status']              |
  #     | Visibility          | campaign:visibility_operator           | Hidden               | campaign:visibility_value            | Is equal to        | Hidden            | Available         | [data-cardtitle='Visibility']          |
  #     | Visibility          | campaign:visibility_operator           | Visible              | campaign:visibility_value            | Is not equal to    | Hidden            | Visible           | [data-cardtitle='Visibility']          |
  #     | Available from      | campaign:startdate_operator            |                      |                                      | In the past        | Demo campaign     | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Available from      | campaign:startdate_operator            |                      |                                      | In the future      | Demo campaign2    | Magic campaign    | [data-cardtitle='Name']                |
  #     | Available closes    | campaign:enddate_operator              |                      |                                      | In the past        | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Available closes    | campaign:enddate_operator              |                      |                                      | In the future      | Demo campaign     | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Password            | campaign:password_operator             |                      |                                      | Yes                | Yes               | No                | [data-cardtitle='Password']            |
  #     | Password            | campaign:password_operator             |                      |                                      | No                 | No                | Yes               | [data-cardtitle='Password']            |
  #     | Global role         | campaign:globalrole_operator           | Manager              | campaign:globalrole_value            | Is equal to        | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Global role         | campaign:globalrole_operator           | Course creator       | campaign:globalrole_value            | Is equal to        | Demo campaign2    | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Global role         | campaign:globalrole_operator           | Manager              | campaign:globalrole_value            | Is not equal to    | Demo campaign     | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Global role         | campaign:globalrole_operator           | Course creator       | campaign:globalrole_value            | Is not equal to    | Demo campaign     | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Campaign owner      | campaign:campaignowner_operator        | user_01 user_01      | campaign:campaignowner_value         | Is equal to        | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Campaign owner      | campaign:campaignowner_operator        | user_01 user_01      | campaign:campaignowner_value         | Is not equal to    | Demo campaign     | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Consent option      | campaign:privacypolicy_operator        |                      |                                      | Yes                | Demo campaign     | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Consent option      | campaign:privacypolicy_operator        |                      |                                      | No                 | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Welcome message     | campaign:welcomemessage_operator       |                      |                                      | Yes                | Demo campaign     | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Welcome message     | campaign:welcomemessage_operator       |                      |                                      | No                 | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Follow up           | campaign:followupmessagedelay_operator | 0                    | campaign:followupmessagedelay_value  | Contains           | Demo campaign     | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Follow up           | campaign:followupmessagedelay_operator | 1                    | campaign:followupmessagedelay_value  | Is equal to        | Demo campaign2    | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Campaign course     | campaign:campaigncourse_operator       | Course C1            | campaign:campaigncourse_value        | Is equal to        | Course C1         | Course C2         | [data-cardtitle='Campaign course']     |
  #     | Campaign course     | campaign:campaigncourse_operator       | Course C1            | campaign:campaigncourse_value        | Is not equal to    | Course C2         | Course C1         | [data-cardtitle='Campaign course']     |
  #     | Registration fee    | campaign:fee_operator                  | 10                   | campaign:fee_value                   | Contains           | Magic campaign    | Demo campaign     | [data-cardtitle='Name']                |
  #     | Registration fee    | campaign:fee_operator                  | Free                 | campaign:fee_value                   | Is equal to        | Demo campaign     | Magic campaign    | [data-cardtitle='Name']                |
  #     | Expiration date     | campaign:expirydate_operator           |                      |                                      | In the past          | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Expiration date     | campaign:expirydate_operator           |                      |                                      | In the future        | Demo campaign     | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Only my campaigns   | campaign:usercohort_operator           |                      |                                      | Yes                | Yes               | No                | [data-cardtitle='Capacity']            |


  # @javascript
  # Scenario Outline: Report campaign source autocomplete conditions
  #   Given I log in as "admin"
  #   Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
  #   And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign" "table_row"
  #   And I set the following fields to these values:
  #   # Restrict by Role
  #   | By role                | Student           |
  #   # Restrict by Cohort
  #   | By cohort              | Cohort 1          |
  #   Then I press "Save changes"
  #   And I am on site homepage
  #   Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
  #   And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign1" "table_row"
  #   And I set the following fields to these values:
  #   # Restrict by Role
  #   | By role                | Teacher           |
  #   # Restrict by Cohort
  #   | By cohort              | Cohort 2          |
  #   Then I press "Save changes"
  #   # Report edit page
  #   Then I navigate to "Reports > Report builder > Custom reports" in site administration
  #   And I click on "My report" "link"
  #   # Campaigns
  #   And I should see "My report" in the ".navbar h1" "css_element"
  #   And I click on "Name with link" "link"
  #   And I click on "a.list-group-item[data-name='Name']" "css_element"
  #   And I click on "Description" "link"
  #   And I click on "Comments" "link"
  #   And I click on "Capacity" "link"
  #   And I click on "Status" "link"
  #   And I click on "Visibility" "link"
  #   And I click on "Restrict by role" "link"
  #   And I click on "Restrict by cohorts" "link"
  #   And I click on "Available from" "link"
  #   And I click on "Available closes" "link"
  #   And I click on "Password" "link"
  #   And I click on "Cohort membership" "link"
  #   And I click on "Global role" "link"
  #   And I click on "Campaign owner" "link"
  #   And I click on "Consent option" "link"
  #   And I click on "Welcome message" "link"
  #   And I click on "Follow up" "link"
  #   And I click on "Campaign course" "link"
  #   And I click on "Registration fee" "link"

  #   # Conditions in the report.
  #   And I click on "Show/hide 'Conditions'" "button"
  #   Then I should see "There are no conditions selected" in the "[data-region='settings-conditions']" "css_element"
  #   And I set the field "Select a condition" to "<campaign>"
  #   # Campaign Name
  #   # And I set the field "<field>" to "<operator>"
  #   And I open the autocomplete suggestions list
  #   And I click on "<operator>" item in the autocomplete list
  #   # And I set the field "<operator_field>" to "<search>"
  #   # And I set the field "<field>" to "<operator>"
  #   And I click on ".reportbuilder-conditions-list .list-group" "css_element"
  #   And I click on "Apply" "button" in the "[data-region='settings-conditions']" "css_element"
  #   Then I should see "Conditions applied"
  #   And I should see "<value>" in the "<table>" "css_element"
  #   And I should not see "<not>" in the "<table>" "css_element"

  #   Examples:
  #     | campaign            | field                                  | search             | operator_field            | operator            | value                  | not               | table                               |
  #     | Restrict by role    | campaign:restrictroles_values[]        |                    |                           | Student             | Demo campaign          | Demo campaign1    | [data-cardtitle='Name']             |
  #     | Restrict by role    | campaign:restrictroles_values[]        |                    |                           | Teacher             | Demo campaign1         | Demo campaign2    | [data-cardtitle='Name']             |
  #     | Cohort membership   | campaign:cohorts_values[]              |                    |                           | Cohort 2            | Demo campaign1         | Demo campaign2    | [data-cardtitle='Name']             |
  #     | Cohort membership   | campaign:cohorts_values[]              |                    |                           | Cohort 3            | Demo campaign2         | Demo campaign1    | [data-cardtitle='Name']             |


  # @javascript
  # Scenario Outline: Report source campaign filters
  #   Given I log in as "admin"
  #   And I am on site homepage
  #   # Create Campaign
  #   And I am on site homepage
  #   Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
  #   Then I should see "Create campaign"
  #   And I click on "Create campaign" "button"
  #   And I set the following fields to these values:
  #     | Title                  | Magic campaign    |
  #     | Description            | Magic Description |
  #     | Comments               | Comments Magic    |
  #     | Capacity               | 3                 |
  #     | Status                 | Available         |
  #     | Visibility             | Visible           |
  #     | Require email confirmation | No            |
  #     | Type                   | Paid              |
  #     | Fee                    | 10                |
  #     | Currency               | US Dollar         |
  #     | Account                | Bank Transfer     |
  #     | expirytime[enabled]    | 1                 |
  #     | expirytime[number]     | 2                 |
  #     | expirytime[timeunit]   | days              |
  #     | Available from         | ##31 March 2024## |
  #     | Available closes       | ##31 March 2025## |
  #     | Campaign password      |                   |
  #     | Cohort membership      | Cohort 1          |
  #     | Global role            | Disabled          |
  #     | Campaign owner account | parentuser_01 parentuser_01 |
  #     | Display consent option | 1                 |
  #     | Send welcome message to new accounts   | 1 |
  #     | Send follow up message to new accounts | 1 |
  #     | Campaign course        | Course C1         |
  #     | Course role for student | Student    |
  #     | First name             | Required          |
  #     | Last name              | Required          |
  #     | Username               | Required          |
  #     | e-Mail                 | Required          |
  #     | Password               | Required Once     |
  #   Then I press "Save changes"
  #   And I log out

  #   And I open magic campaign "Magic campaign"
  #   And I should see "Magic campaign"
  #   And I set the following fields to these values:
  #     | firstname      | Alan        |
  #     | lastname       | Turing      |
  #     | username       | alan_turing |
  #     | password       | Alan123#    |
  #     | email          | alanturing@gmail.com |
  #     | email2         | alanturing@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   Then I should see "User signup successfully."
  #   Then I should see "This campaign requires a payment for entry."
  #   And I should see "10.00"
  #   And I should see "USD"
  #   Then "Select payment type" "button" should exist
  #   And I click on "Select payment type" "button"
  #   And I should see "Bank Transfer" in the ".core_payment_gateways_modal p" "css_element"
  #   And I click on "Proceed" "button"
  #   And I wait "10" seconds
  #   And I should see "Access the Magic campaign campaign."
  #   And I click on "//input[@value='Start process']" "xpath_element"
  #   And I should see "Transfer process initiated" in the ".alert-info" "css_element"
  #   And I am on site homepage
  #   And I should see "This campaign requires a payment for entry."
  #   And I log out

  #   And I log in as "admin"
  #   And I am on site homepage
  #   Then I navigate to "Site administration > Bank Transfer > Manage Transfers" in site administration
  #   And I should see "alanturing@gmail.com" in the "Alan Turing" "table_row"
  #   And I click on "//input[@value='Approve']" "xpath_element"
  #   And I should see "aprobed" in the ".alert-info" "css_element"
  #   And I log out

  #   And I open magic campaign "Magic campaign"
  #   And I should see "Magic campaign"
  #   And I set the following fields to these values:
  #     | firstname      | Steve        |
  #     | lastname       | Carell       |
  #     | username       | steve_carell |
  #     | password       | Test123#     |
  #     | email          | stevecarell@gmail.com |
  #     | email2         | stevecarell@gmail.com |
  #     | privacypolicy  | 1           |
  #   Then I press "Sign up"
  #   Then I should see "User signup successfully."
  #   Then I should see "This campaign requires a payment for entry."
  #   And I should see "10.00"
  #   And I should see "USD"
  #   Then "Select payment type" "button" should exist
  #   And I click on "Select payment type" "button"
  #   And I should see "Bank Transfer" in the ".core_payment_gateways_modal p" "css_element"
  #   And I click on "Proceed" "button"
  #   And I should see "Access the Magic campaign campaign." in the ".list-group .list-group-item div" "css_element"
  #   And I click on "//input[@value='Start process']" "xpath_element"
  #   And I should see "Transfer process initiated" in the ".alert-info" "css_element"
  #   And I am on site homepage
  #   And I should see "This campaign requires a payment for entry." in the ".auth_magic_payment_region" "css_element"
  #   And I log out

  #   And I log in as "admin"
  #   And I am on site homepage
  #   Then I navigate to "Site administration > Bank Transfer > Manage Transfers" in site administration
  #   And I should see "stevecarell@gmail.com" in the "Steve Carell" "table_row"
  #   And I click on "//input[@value='Approve']" "xpath_element"
  #   And I should see "aprobed" in the ".alert-info" "css_element"
  #   And I log out

  #   And I log in as "admin"
  #   # Report edit page
  #   Then I navigate to "Reports > Report builder > Custom reports" in site administration
  #   And I click on "My report" "link"
  #   # Campaigns
  #   And I should see "My report" in the ".navbar h1" "css_element"
  #   And I click on "Name with link" "link"
  #   And I click on "a.list-group-item[data-name='Name']" "css_element"
  #   And I click on "Description" "link"
  #   And I click on "Comments" "link"
  #   And I click on "Capacity" "link"
  #   And I click on "Status" "link"
  #   And I click on "Visibility" "link"
  #   And I click on "Restrict by role" "link"
  #   And I click on "Restrict by cohorts" "link"
  #   And I click on "Available from" "link"
  #   And I click on "Available closes" "link"
  #   And I click on "Password" "link"
  #   And I click on "Cohort membership" "link"
  #   And I click on "Global role" "link"
  #   And I click on "Campaign owner" "link"
  #   And I click on "Consent option" "link"
  #   And I click on "Welcome message" "link"
  #   And I click on "Follow up" "link"
  #   And I click on "Campaign course" "link"
  #   And I click on "Registration fee" "link"

  #   # Filter options in the report
  #   And I click on "Show/hide 'Filters'" "button"
  #   Then I should see "There are no filters selected" in the "[data-region='settings-filters']" "css_element"
  #   And I set the field "Select a filter" to "<campaign>"
  #   And I should not see "There are no conditions selected" in the "[data-region='settings-conditions']" "css_element"
  #   When I click on "Switch to preview mode" "button"
  #   And I click on "Filters" "button" in the "[data-region='core_reportbuilder/report-header']" "css_element"
  #   And I set the field "<field>" to "<operator>"
  #   And I set the field "<operator_field>" to "<search>"
  #   And I set the field "<field>" to "<operator>"
  #   And I click on "Apply" "button" in the "[data-region='core_reportbuilder/report-header']" "css_element"
  #   And I should see "<value>" in the "<table>" "css_element"
  #   And I should not see "<not>" in the "<table>" "css_element"

  #   Examples:
  #     | campaign            | field                                  | search             | operator_field                        | operator            | value             | not               | table                                  |
  #     | Name                | campaign:title_operator                | magic                | campaign:title_value                 | Contains           | Magic campaign    | demo campaign     | [data-cardtitle='Name']                |
  #     | Name                | campaign:title_operator                | magic campaign       | campaign:title_value                 | Is equal to        | Magic campaign    | demo campaign     | [data-cardtitle='Name']                |
  #     | Name                | campaign:title_operator                | demo                 | campaign:title_value                 | Contains           | Demo campaign     | Magic campaign    | [data-cardtitle='Name']                |
  #     | Name                | campaign:title_operator                | demo campaign        | campaign:title_value                 | Is equal to        | Demo campaign     | Magic campaign    | [data-cardtitle='Name']                |
  #     | Description         | campaign:description_operator          | magic                | campaign:description_value           | Contains           | Magic Description | Description1      | [data-cardtitle='Description']         |
  #     | Description         | campaign:description_operator          | Description2         | campaign:description_value           | Does not contain   | Magic Description | Description2      | [data-cardtitle='Description']         |
  #     | Description         | campaign:description_operator          | description          | campaign:description_value           | Contains           | Description       | Magic description | [data-cardtitle='Description']         |
  #     | Description         | campaign:description_operator          | description          | caxmpaign:description_value          | Is equal to        | Description       | Magic description | [data-cardtitle='Description']         |
  #     | Comments            | campaign:comments_operator             | Comment              | campaign:comments_value              | Contains           | Comment           | Comment1          | [data-cardtitle='Comments']            |
  #     | Comments            | campaign:comments_operator             | Magic                | campaign:comments_value              | Does not contain   | Comments          | Comments Magic    | [data-cardtitle='Comments']            |
  #     | Capacity            | campaign:capacity_operator             | 3                    | campaign:capacity_value              | Contains           | 3                 | 2                 | [data-cardtitle='Capacity']            |
  #     | Capacity            | campaign:capacity_operator             | 5                    | campaign:capacity_value              | Is equal to        | 5                 | 3                 | [data-cardtitle='Capacity']            |
  #     | Status              | campaign:status_operator               | Archived             | campaign:status_value                | Is equal to        | Archived          | Available         | [data-cardtitle='Status']              |
  #     | Status              | campaign:status_operator               | Archived             | campaign:status_value                | Is not equal to    | Available         | Archived          | [data-cardtitle='Status']              |
  #     | Visibility          | campaign:visibility_operator           | Hidden               | campaign:visibility_value            | Is equal to        | Hidden            | Available         | [data-cardtitle='Visibility']          |
  #     | Visibility          | campaign:visibility_operator           | Visible              | campaign:visibility_value            | Is not equal to    | Hidden            | Visible           | [data-cardtitle='Visibility']          |
  #     | Available from      | campaign:startdate_operator            |                      |                                      | In the past        | Demo campaign     | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Available from      | campaign:startdate_operator            |                      |                                      | In the future      | Demo campaign2    | Magic campaign    | [data-cardtitle='Name']                |
  #     | Available closes    | campaign:enddate_operator              |                      |                                      | In the past        | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Available closes    | campaign:enddate_operator              |                      |                                      | In the future      | Demo campaign     | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Password            | campaign:password_operator             |                      |                                      | Yes                | Yes               | No                | [data-cardtitle='Password']            |
  #     | Password            | campaign:password_operator             |                      |                                      | No                 | No                | Yes               | [data-cardtitle='Password']            |
  #     | Global role         | campaign:globalrole_operator           | Manager              | campaign:globalrole_value            | Is equal to        | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Global role         | campaign:globalrole_operator           | Course creator       | campaign:globalrole_value            | Is equal to        | Demo campaign2    | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Global role         | campaign:globalrole_operator           | Manager              | campaign:globalrole_value            | Is not equal to    | Demo campaign     | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Global role         | campaign:globalrole_operator           | Course creator       | campaign:globalrole_value            | Is not equal to    | Demo campaign     | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Campaign owner      | campaign:campaignowner_operator        | user_01 user_01      | campaign:campaignowner_value         | Is equal to        | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Campaign owner      | campaign:campaignowner_operator        | user_01 user_01      | campaign:campaignowner_value         | Is not equal to    | Demo campaign     | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Consent option      | campaign:privacypolicy_operator        |                      |                                      | Yes                | Demo campaign     | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Consent option      | campaign:privacypolicy_operator        |                      |                                      | No                 | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Welcome message     | campaign:welcomemessage_operator       |                      |                                      | Yes                | Demo campaign     | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Welcome message     | campaign:welcomemessage_operator       |                      |                                      | No                 | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Follow up           | campaign:followupmessagedelay_operator | 0                    | campaign:followupmessagedelay_value  | Contains           | Demo campaign     | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Follow up           | campaign:followupmessagedelay_operator | 1                    | campaign:followupmessagedelay_value  | Is equal to        | Demo campaign2    | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Campaign course     | campaign:campaigncourse_operator       | Course C1            | campaign:campaigncourse_value        | Is equal to        | Course C1         | Course C2         | [data-cardtitle='Campaign course']     |
  #     | Campaign course     | campaign:campaigncourse_operator       | Course C1            | campaign:campaigncourse_value        | Is not equal to    | Course C2         | Course C1         | [data-cardtitle='Campaign course']     |
  #     | Expiration date     | campaign:expirydate_operator           |                      |                                      | In the past        | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']                |
  #     | Expiration date     | campaign:expirydate_operator           |                      |                                      | In the future      | Demo campaign2    | Demo campaign1    | [data-cardtitle='Name']                |
  #     | Registration fee    | campaign:fee_operator                  | 10                   | campaign:fee_value                   | Contains           | Magic campaign    | Demo campaign     | [data-cardtitle='Name']                |
  #     | Registration fee    | campaign:fee_operator                  | Free                 | campaign:fee_value                   | Is equal to        | Demo campaign     | Magic campaign    | [data-cardtitle='Name']                |


  # @javascript
  # Scenario Outline: Report source campaign Cohorts filter
  #   Given I log in as "admin"
  #   Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
  #   And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign" "table_row"
  #   And I set the following fields to these values:
  #   # Restrict by Role
  #   | By role                | Student           |
  #   # Restrict by Cohort
  #   | By cohort              | Cohort 1          |
  #   Then I press "Save changes"
  #   And I am on site homepage
  #   Then I navigate to "Plugins > Authentication > Manage campaign" in site administration
  #   And I click on ".icon[title='Edit']" "css_element" in the "Demo campaign1" "table_row"
  #   And I set the following fields to these values:
  #   # Restrict by Role
  #   | By role                | Teacher           |
  #   # Restrict by Cohort
  #   | By cohort              | Cohort 2          |
  #   Then I press "Save changes"
  #   # Report edit page
  #   Then I navigate to "Reports > Report builder > Custom reports" in site administration
  #   And I click on "My report" "link"
  #   # Campaigns
  #   And I should see "My report" in the ".navbar h1" "css_element"
  #   And I click on "Name with link" "link"
  #   And I click on "a.list-group-item[data-name='Name']" "css_element"
  #   And I click on "Description" "link"
  #   And I click on "Comments" "link"
  #   And I click on "Capacity" "link"
  #   And I click on "Status" "link"
  #   And I click on "Visibility" "link"
  #   And I click on "Available from" "link"
  #   And I click on "Available closes" "link"
  #   And I click on "Password" "link"
  #   And I click on "Cohort membership" "link"
  #   And I click on "Global role" "link"
  #   And I click on "Campaign owner" "link"
  #   And I click on "Consent option" "link"
  #   And I click on "Welcome message" "link"
  #   And I click on "Follow up" "link"
  #   And I click on "Campaign course" "link"
  #   And I click on "Registration fee" "link"

  #   # Filter options in the report
  #   And I click on "Show/hide 'Filters'" "button"
  #   Then I should see "There are no filters selected" in the "[data-region='settings-filters']" "css_element"
  #   And I set the field "Select a filter" to "<campaign>"
  #   And I should not see "There are no conditions selected" in the "[data-region='settings-conditions']" "css_element"
  #   When I click on "Switch to preview mode" "button"
  #   And I click on "Filters" "button" in the "[data-region='core_reportbuilder/report-header']" "css_element"
  #   And I open the autocomplete suggestions list
  #   And I click on "<select>" item in the autocomplete list
  #   And I click on ".filter-header .filter-name" "css_element"
  #   And I click on "Apply" "button" in the "[data-region='core_reportbuilder/report-header']" "css_element"
  #   And I should see "<value>" in the "<table>" "css_element"
  #   And I should not see "<not>" in the "<table>" "css_element"

  #   Examples:
  #     | campaign            | field                          | select             | operator             | operator_field                  | value             | not               | table                       |
  #     | Cohort membership   | campaign:cohorts_values[]      | Cohort 2           | Cohort 2             | campaign:cohorts_operator       | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']     |
  #     | Cohort membership   | campaign:cohorts_values[]      | Cohort 2           | Cohort 3             | campaign:cohorts_operator       | Demo campaign1    | Demo campaign 2   | [data-cardtitle='Name']     |
  #     | Restrict by role    | campaign:restrictroles_values[] | Student           |                      | Student                         | Demo campaign     | Demo campaign1    | [data-cardtitle='Name']     |
  #     | Restrict by role    | campaign:restrictroles_values[] | Teacher           |                      | Teacher                         | Demo campaign1    | Demo campaign2    | [data-cardtitle='Name']     |