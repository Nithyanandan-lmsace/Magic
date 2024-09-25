# About Magic Authentication

## Magic Authentication — free version

The Magic Authentication plugin for Moodle is a convenient and secure authentication plugin designed to simplify the login process for users. With the Magic Authentication plugin, users no longer need to remember and enter their usernames and passwords to access the Moodle site. Instead, they can use a magic link that is sent to their email address, providing them with direct access to the Moodle platform.

#### Key Features: ####

1. **Magic Link Authentication**: Instead of traditional username and password login, users can authenticate themselves by clicking on a magic link sent to their email address. This eliminates the need for remembering and entering credentials, providing a more convenient login experience.

2. **Email-based Verification**: The plugin relies on email verification to ensure the authenticity of the user. A unique magic link is sent to the user's registered email address, serving as their authentication token.

3. **Secure Authentication**: Magic links are time-limited and expire after a certain period, adding an extra layer of security. This helps to prevent unauthorized access and reduces the risk of password-related vulnerabilities.

4. **User-friendly Interface**: The plugin provides a user-friendly interface on the Moodle login page, allowing users to easily request a magic link by entering their email address and clicking a designated button.

5. **Seamless Single Sign-On**: Once the user clicks on the magic link and completes the authentication process, they are automatically logged in to Moodle without requiring any additional credentials. This creates a seamless single sign-on experience for users.

6. **Compatibility**: The Magic Authentication plugin is designed to work with the latest versions of Moodle, ensuring compatibility and seamless integration into your Moodle environment.


## Magic Authentication Pro — paid version

The Magic Authentication Pro plugin is an enhanced version of the Magic Authentication plugin for Moodle. It introduces additional features that enhance the authentication and login experience for users. With this plugin, users can conveniently register by entering their email addresses. Administrators gain the ability to create and manage registration campaign, customize the login page to align with their organization's branding, and leverage a range of customization options. These enhancements result in a smoother and more user-friendly authentication and login process within Moodle.

#### Key Features: ####

1. **Magic Signup**: The Magic Signup feature allows users to create an account simply by entering their email addresses. Whether or not an account with the provided email address already exists, a magic link will be sent. If the account doesn't exist, clicking on the magic link will create the user account.

2. **Magic Custom Login Page**: With the Magic Custom Login Page feature, you gain full control over the login page. A separate custom login page is created, enabling you to customize its appearance and layout. You can choose to display only one input field for the email address and conditionally show additional input elements based on the entered email address and its associated authentication method.

3. **Campaign**: The campaign feature allows users with the appropriate capabilities, such as site managers, to create registration forms. These forms can be customized visually, including the addition of a logo, text, and background image. You can pre-set profile information, reCAPTCHA, Payment, Redirect after form submission, Approval roles for Parent, Campaign course allows to define roles (student and their parent) and for the course Group & Grouping ,  define form fields, set up enrolments, and assignments, and even manage capacity within the campaign.

4. **Allow User to Use Username for Login**: By enabling this feature, users have the option to enter their username instead of their email address in the login form. The plugin will determine the corresponding email address for the provided username and send the magic link to that associated email address. A global setting controls this behavior.

5. **Button Position**: This feature allows you to configure the position of the "Get a magic link" button on the login page. You can choose to display the button in the normal position, below the username field, or below the password field, depending on your preference.

6. **Manually Override Expiration Time**: Administrators with the appropriate capability can manually override the expiration time of a magic link. This feature is particularly useful for creating everlasting login links, such as for demo accounts or specific use cases where a longer validity period is required.

7. **Get Link via Moodle Webservice**: The Magic Authentication Pro plugin provides the capability to retrieve the magic link via the Moodle web service. This feature enables integration with other systems or external applications that can programmatically fetch the magic link.

8. **Quick Registration**: The Quick Registration feature facilitates a streamlined registration process by allowing users to quickly create accounts using their email addresses without the need for additional profile information. This can be useful in scenarios where minimal user information is required.

9. **Magic user accounts**: The Magic User Accounts offer a convenient interface for managing users with magic authentication, with options to view and modify user information, control access, and perform essential administrative tasks. This list includes users with magic authentication enabled and authorized to access Moodle using this method.

# Installation and initial setup

### Installation for Magic Authentication

You can install the Magic Authentication plugin using the Moodle plugin installer. Here are the steps to follow:

1. Download the [**Magic Authentication**](https://moodle.org/plugins/auth_magic) plugin from the Moodle plugins repository or from the [Bdecent](https://bdecent.de/product/magic-authentication/) website.
2. Log in to your Moodle site as an administrator.
3. Go to "*`Site administration > Plugins > Install plugins`*".
4. Upload the downloaded plugin ZIP file.
5. Follow the prompts to install the plugin.
6. Once the Magic Authentication plugin is installed, you need to enable Magic Authentication by going to "*`Site Administration > Plugins > Authentication > Manage authentication`*".
From there, Locate the Magic Authentication plugin and click the eye icon to enable it.
7. Click the Settings link for the Magic Authentication, From there, you can configure the magic link expiration times.

Alternatively, you can also install the Magic Authentication plugin manually. Here are the steps to follow:

1. Download the [**Magic Authentication**](https://moodle.org/plugins/auth_magic) plugin from the Moodle plugins repository.
2. Unzip the downloaded file and rename the "auth-magic" folder as "magic".
3. Upload the magic folder to the "*`moodle/auth`*" directory on your Moodle server.
4. Log in to your Moodle site as an administrator.
5. Go to "*`Site administration > Notifications`*".
6. Follow the prompts to install the plugin.

### Installation for Magic Authentication Pro.

> Install using Moodle plugin installer, Follow the same steps mentioned in the above installation steps. Use the [**Magic Authentication Pro**](https://bdecent.de/product/magic-authentication-pro/) source instead of the Magic Authentication

Alternatively, you can also install the Magic Authentication Pro plugin manually. Here are the steps to follow:

1. Download the [**Magic Authentication Pro**](https://bdecent.de/product/magic-authentication-pro/) plugin from the Moodle plugins repository
2. Unzip the downloaded file and rename the "auth-magic-pro" folder as "magic".
3. Upload the magic folder to the "*`moodle/auth`*" directory on your Moodle server.
4. Log in to your Moodle site as an administrator.
5. Go to "*`Site administration > Notifications`*".
6. Follow the prompts to install the plugin.

### Global Configurations:

The Magic Authentication plugin for Moodle includes a Global Configuration where you can customize various settings to be applied by expiration times for magic links.

To access the Magic Authentication global configuration:
Go to "*`Site administration > Plugins > Authentication > Magic authentication`*"

#### General Configurations (Free):

1. **Default enrolment duration**:

   The Default Enrolment Duration setting in Quick Registration enables you to specify the default duration for course enrolments. This setting controls the length of time users will have access to a course upon enrolment.


2. **Default enrolment role**:

   The Default Enrolment Role setting enables you to specify the role assigned to users when they are enrolled in a course using the Quick Registration feature.

3. **Magic invitation link expiry**:

   The Magic Invitation Link Expiry setting allows you to define the duration for which a magic invitation link remains valid. This global setting ensures that invitation links have a limited lifespan and expire after a specified period.

    **Note**: The duration is set to 0, indicating that the invitation links will never expire.


4. **Magic login link expiry**:

   The Magic Login Link Expiry setting allows you to define the duration for which a magic login link remains valid. This global setting controls the lifespan of the login links sent to users for authentication purposes.

    **Note**:  The duration is set to 0, indicating that the login links will never expire.

5. **Supported authentication method**:

    The Supported Authentication Method setting allows you to define the method by which users can obtain a magic link via email for authentication.

    **Magic only**: This option allows only users authenticated through the magic authentication method will be able to receive a magic link via email. Users who have registered and authenticated using other methods will not have access to this feature.

    **Any method**: This option allows users authenticated through any authentication method to receive a magic link via email. This means that users who have registered and authenticated using different authentication methods.


6. **Owner account role**:

    The setting is used to define the role assigned to the user who creates a new user account.

    If the setting is configured to a role other than "none," the user who creates the new account will be assigned the specified role as the owner account role for the newly created user.

    **Note**: This setting is populated with all available roles within the user context.

<br>


![magic-general-setting-free](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/9a3e1c85-1a5d-4056-878d-f2ca6f0423dc)



#### General Configurations (Pro):

1. **Supports password**:

    ***Disabled***: This option allows users to don't utilize a password for accessing Moodle.

    ***Enabled***: This option allows users to utilize a password for accessing Moodle.


2. **Configure login key link**:

    This setting allows administrators to adjust the behavior of login keys.

    - Only once: Login keys can be utilized for a single use

    - Until it expires: Login keys can be utilized for until their expiry time


3. **Allow Username to get magic link**:

    **Enabled**:  This option allows the users to provide the Username / Email in the login form to receive the magic link via email.

    **Disabled**: This option allows users to provide only their email address in the login form to receive the magic link via email.


4. **Magic login link button position**:

    This setting allows you to configure the placement of the "Get a Magic Link" button on the login form.

    **Below username**:  This option moves the "Get a Magic Link" button below the username field on the login form.

    **Below password**:  This option positions the "Get a Magic Link" button below the password field on the login form.

    **Normal**: This option positions the "Get a Magic Link" button in the default location based on the identity provider being used.


5. **Privileged role**:

    This setting allows you to define which roles are considered "privileged" during the magic login process.

    **Note**: When signing in with an email address, if the email address belongs to an account that supports magic login but has a privileged role, users will be required to enter their password before they can obtain the magic login link via email on the magic custom login page.


6. **Login footer links**:

    This setting allows you to display a footer at the bottom of the magic custom login page that contains additional information and links.

7. **Campaign owner role**:

    Select a campaign owner role for a magic campaign.


![magic-general-setting-pro](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/a385e95c-0894-4c19-adb4-a293b5b8522d)


#### Relative role assignments:
Create a user context role and a profile field with a text type for the role. Assign the corresponding profile field to each user context role. Provide the user email ID or other values (based on the account identifier) for the users. Once the profile fields are mapped with roles, and the user profile is filled with the parent user's email ID, the Auth Magic plugin will assign the parent user to the user.

1. **Profile field for the role**:

    Choose the "profile field" to associate with the roles for the user. Subsequently, the user will be assigned to the parent account linked with that user.

1. **Account identifier**:

    This setting is used to specify the information utilized to identify the relative role assignment for the user. Users may be identified through their email address, username, ID number, or full name.

2. **Auto create the relative role users**:

    When enabled, it automatically creates user information provided in the user "profile fields" if the user does not exist and assigns them as relative role users.


![relative_role_assign](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/b13afa2c-aeaf-4a9d-95c1-2454ad805986)





# Feature

## Feature: Quick registration

Quick Registration is a convenient feature in Moodle that allows for fast and efficient user registration and enrollment into a course. It simplifies the process by enabling users to register and join a course in just one step. The feature is accessible to users with the appropriate capability in the context, ensuring that only authorized individuals can utilize it.

![Quick_registration](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/1e27eaf1-483e-40d6-95cd-98e803613b48)


To use Quick Registration, the user must have the capability
"*`auth/magic:cansitequickregistration`*" at the site level
or "*`auth/magic:cancoursequickregistration`*" at the course level.
This ensures that only users with the necessary permissions can utilize the feature.

The Quick Registration feature is available in two locations within Moodle: at the site level and within individual courses.

At the site level, site administrators can access it through the "*`Site administration > Users > Accounts > Quick registration`*".

![register_sitelevel-png-1366×625-](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/28985527-e3cc-458a-973e-69d99f5ab96f)


Within a course, participants can access it by clicking on the "*`Quick registration`*" button located next to the "Enrol users" option.

![Demo-course-01-Participants](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/6b3770ae-205c-4671-847a-40fe43c91677)


## Feature: Magic User Accounts

The Magic User Accounts presents a table displaying the list of users with magic authentication.

The Magic User Accounts feature is available in two locations within the site:

1. **Site Level Access**:

Site administrators can access the user list by navigating to "*`Site admin > Users > Accounts > Magic user accounts`*".

![m-311-Administration-Search](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/cc8cc807-e445-4d49-bd05-2b68bfdedb98)

Users with site-level contexts, such as managers, can also access the user list through their profile settings.
By going to "*`Profile > Magic Authentication > My user accounts`*" users with the manager role can manage the magic authentication users associated with their profile. This access is specific to site-level contexts, and the capability required is "*`auth/magic:viewloginlinks`*".

![manager_01-manager_01-Public-profile](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/75f7314d-d91e-47bb-8e00-341f0ca3c870)

2. **Parent Level Access**:

Parent Users can access the user list through their profile settings. By going to "*`Profile > Magic Authentication > My user accounts`*" parents can view and manage the magic authentication users that are linked to their parent account. This access is specific to user-level contexts, and the capability required is "*`auth/magic:viewchildloginlinks`*".


![manager_01-manager_01-Public-profile (1)](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/cb5b99b4-6732-4609-96d8-b56f40bf2fbd)


The Magic User Accounts feature offers a convenient way to manage users with magic authentication. Authorized users with the appropriate capabilities can access a user list and perform various actions such as editing, deleting, suspending, copying invitation links, sending invitation links and Overriding invitation links.

1. **Firstname / Lastname**: Displays the user's first name and last name.

2. **Email Address**: This shows the user's email address as a unique identifier.

3. **Courses**: Indicates the courses in which the user is enrolled.

4. **Last Access**: Displays the timestamp of the user's last access to the site.

5. **Edit Actions**: Provides various options for managing user accounts:

    **Delete**: Allows for the deletion of user accounts.

        - Site-level capability required: "auth/magic:userdelete"

        - User-level capability required: "auth/magic:childuserdelete"

    **Suspend**: Enables / Disables the suspension of user accounts.

        - Site-level capability required: "auth/magic:usersuspend"
       - User-level capability required: "auth/magic:childusersuspend"

    **Settings**: Provides the ability to edit user account settings.

        - Site-level capability required: "auth/magic:userupdate"

        - User-level capability required: "auth/magic:childuserupdate"

    **Copy Link**: Enables the sending of a magic invitation link to the user.

        - Site-level capability required: "auth/magic:usercopylink"

        - User-level capability required: "auth/magic:childusercopylink"

    **Invite Link**: Enables the sending of a magic invitation link to the user.

        - Site-level capability required: "auth/magic:usersendlink"

        - User-level capability required: "auth/magic:childusersendlink"

    **Override Invite Link**: Allows for overriding the expiry time interval of a magic invitation link.

        - Site-level capability required: "auth/magic:usersetlinkexpirytime"

        - User-level capability required: "auth/magic:childusersetlinkexpirytime"

![listusers](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/5b45296e-4090-47fe-a09c-5793c62950ee)


## Feature: Magic Login Page

The Magic Login Page provides significant enhancements to the standard Moodle login process. By simplifying the login interface to a single email input and dynamically displaying relevant additional input elements, we streamline the login experience for users. This customization allows for a more user-friendly and efficient authentication process tailored to individual authentication methods associated with email addresses.


To access the custom Magic login page in Magic Authentication, follow these steps:

1. Log in to your Moodle site with administrator credentials.
2. Navigate to "*`Site Administration > Plugins > Authentication > Manage authentication`*".
3. Look for the "Common settings" section.
4. Locate the "Alternate login URL" setting.
5. In the field provided, enter the URL for your custom Magic login page. For example, set it to "https://yourmoodlesite//auth/magic/login.php".
6. Save the changes.


![alternate-login](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/ef2c630c-2b5c-4df1-ad7d-90ba2954de45)



The magic login page offers a simplified and intuitive login experience by requesting only the user's email address initially. Once the email address is entered, the page dynamically adapts to show the relevant input fields based on the authentication method associated with that email.


To sign in, please enter your email address. If the email address belongs to an account that supports a specific authentication method, additional input elements will be displayed accordingly based on the associated authentication method.

![Moodle-411-Demo-Log-in-to-the-site](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/e9107223-9972-4df4-8444-99c268d30750)



If the email address belongs to an account that supports magic login and is not a privileged user, or if there is no account associated with the entered email address and Magic Pro is configured to Create user accounts, you will be able to obtain the magic login link after entering the email address.

![magic_account_login](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/d67fea3b-32d0-4efc-8035-f4ffebe28108)



If the email address belongs to an account that does not support magic login, you will be required to enter your password.

![manual_account_login](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/23729240-4645-4385-ba8c-2e7539093614)



If the email address belongs to a privileged user account that supports magic login, you will be prompted to enter your password before obtaining the magic login link via email.

![magic_privillage_account](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/a27398e8-7ee0-411b-8b96-86fa0e7f8eae)



If the email address belongs to an account that uses an identity provider for authentication, the appropriate button associated with that identity provider will be displayed.

![oauth_account](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/90360904-cbe8-42d6-9da0-1f7474da8096)


## Feature: Magic Sign Up


The concept of magic sign-up enables users to create an account simply by entering their email addresses. When users enter an email address that does not correspond to an existing account, they will receive a magic registration link. Clicking on this link will trigger the account creation process, and the user account will be successfully created upon completion. It is important to note that the magic registration link will have the same expiration duration as an invitation link, ensuring consistency in the account registration process.

To enable Magic Sign Up in Magic Authentication, follow these steps:

1. Log in to your Moodle site with administrator credentials.
2. Go to "*`Site Administration > Plugins > Authentication > Magic authentication > Magic Sign Up`*".
3. Enable the "Auto create users" setting to allow users to register automatically.
4. Save the changes.

![M411-Administration-Plugins-Auth-plugins-Magic-authentication-pro-Magic-Sign-Up](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/d9f58f61-c802-4bce-a25e-06f9b7770ee1)


By default, the "Auto create users" checkbox is set to false. In this default state:

- Only existing users will receive a magic link when they enter their email address in the login form. If the email address does not match an existing account, no action will be taken.

However, when the "Auto create users" checkbox is set to true:

- Users who enter an email address that does not belong to an existing account will receive a magic registration link.


![Moodle 411 Demo_ Log in to the site](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/055f2243-beb5-4dd9-8ec2-7dba0968259c)

## Report sources: Payment transactions

It indicates a specialized approach to reporting on payment transactions, where the authentication method, is integrated into the process.

**Filters**:
- This option aids in filtering the User object, Payment gateway, Origin, and Status.

![payment-transaction-filter](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/b89390d2-1b02-44f9-8f1a-17d6d49ed97b)


**Name**:
- This field refers to the name associated with the payment transaction.

**Payment Gateway**:
- This section is likely for specifying the payment gateway through which the transaction was processed. It could include popular gateways like PayPal, Stripe, or others.

**Fullname with link**:
- This field displays the full name with a link leading to the user's profile page.

**Cost**:
- This is where the monetary value of the payment transaction is recorded.

**Currency**:
- The currency field indicates the type of currency in which the payment transaction occurred. It could be USD, EUR, or any other applicable currency code.

**Date**:
- This field is designed to capture the date of the payment transaction, facilitating the tracking of the timeline of financial activities.

**Payment Status**:
- This section provides information about the status of the payment transaction, indicating whether it was successful, pending, declined, or has another status.

![payment-transaction-report](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/712218d8-fef4-433e-b37f-4bc6f7e6dd2f)


## Feature: Magic campaign

The concept of magic campaign in Magic authentication allows users to create customized registration forms for specific purposes or events. The campaign provide the flexibility to customize various aspects of the registration form to meet specific requirements. Participants can sign up using the customized registration forms of each campaign, allowing for a personalized and efficient registration experience aligned with the campaign.

![campaign_view (1)](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/6227ba94-1ecc-4a11-8601-d1e9b5052507)


![form-left-full](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/834ef76e-a055-41ce-bed8-057bc6d083d3)

![View Campaign](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/5f5b71a9-0eef-48fc-be76-db6f0dda6df7)


**To create a Magic campaign in Magic Authentication, please follow these steps**:

1. Log in to your Moodle site using your administrator credentials.
2. Navigate to "Site Administration > Plugins > Authentication > Magic authentication > Manage campaign".
3. On the campaign list page, click on the "Create campaign" button.
4. The campaign form will be displayed, allowing you to customize the settings and details for the new campaign.
5. Once you have configured all the settings for the campaign, click on the "Save changes" button to create the campaign.


**In Magic campaign, two capabilities control access to the campaign list**:

1. **"auth/magic:viewcampaignlists"**: This capability is assigned to admins or managers, permitting them to view all campaign. With this capability, they can see the list of all campaign created by different campaign owners.

2. **"auth/magic:viewcampaignownerlists"** : This capability is assigned to campaign owners, allowing them to view their campaign only. Campaign owners will be able to see the campaign they have created and manage them, but they won't have access to campaign created by other users.

**Each campaign can be customized using the following settings**:


**General Settings**:

- Title of the campaign: A mandatory text input field to provide a title for the campaign.

- Description: An optional text editor field to add a description of the campaign.

- Comments: An optional text editor field to include additional comments or instructions.

![campaign_general_settings](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/35386238-f119-40bf-9003-ec01113077fb)



**Availability Settings**:

- **Capacity**: A number input field with a default value of 0 (indicating unlimited users) to specify the maximum number of users who can register via the campaign.

    **Note**: Information is displayed to show how many users have already registered and how many can still register.

- **Status**:  A select field to set the campaign as either "Available" or "Archived," with the "Available" option selected by default.

    - **available**: This option allows the campaign to be visible to campaign managers and users. Participants can sign up for the campaign and access its content.

    - **archived**: This option hides the campaign from campaign managers and users. The campaign will not be visible, and participants will no longer be able to sign up or access its content.

- **Visibility**: A select field to choose between "Visible" or "Hidden," with the "Hidden" option selected by default.

    - **Visible**: This option allows the campaign to be visible to users. Users will be able to see the campaign and its registration form.

    - **Hidden**: This option hides the campaign from users. The campaign will not be visible, and users will not be able to access its registration form or view its details.

- **Available from**: An optional date select field to set the start date for the campaign's availability.

- **Available closes**: An optional date select field to set the end date for the campaign's availability.

- **Campaign password**: An optional password input field to add an additional layer of security for the campaign.

    By using a password, campaign owners can ensure that only authorized individuals can participate in the campaign and access its content.

    **Note**: Campaign owners can set a password to secure their campaign. The password can be entered manually by the participant or submitted as a parameter in the campaign link. By using the link icon on the campaign list page, campaign owners can generate a tokenized link that includes the password as a parameter. This allows for a convenient way to share the campaign with others while pre-filling the password field.

![campaign_available](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/5d7bb2a0-fa84-4fcf-a24d-7ab7a2adf7dc)


**Appearance Settings**:

- Logo: A file picker to upload and display a logo for the campaign.

- Header image: A file picker to upload and display a header image for the campaign.

- Background image: A file picker to upload and set a background image for the campaign.

- Transparent form: A checkbox field that allows users to choose whether to display a border, box-shadow, or background color for the form.

- Display campaign owner's profile picture: A checkbox to choose whether to display the campaign owner's profile picture.

- Form Position: A checkbox field that allows campaign owners to define the placement of the registration form on the signup page.

![campaign_apperaence](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/b6a41456-128f-4d21-95d3-f7ba967bf029)

**Security**

1. **reCAPTCHA**: Choose an option from the multi-select field to enable or disable the reCAPTCHA feature on the campaign form.

2. **Require email confirmation**: Force users to confirm their account with an email before the sign up is complete. This setting is only applicable for new user accounts created via a campaign.

   - No: After signing up for the campaign, the specific user is logged in, and email confirmation is not needed.

   - Yes: After signing up for the campaign, the specific user needs to confirm their account via email to log in.

   - Partial: After signing up for the campaign, the specific user will be logged in. However, for subsequent logins, the user needs to confirm their account via email.

![security](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/3be2773b-1092-46c5-afa1-be0551ed85d0)


**Payment**

1. **Type**: Specifies whether the campaign is free or paid using the multi-select option.
   - Free: Users can sign up for the campaign without any cost.
   - Paid: Users are required to make a payment for the campaign after signing up.
     - Fee: The amount that needs to be paid upon sign up.
     - Currency: Choose the currency for the fee from the multi-select option.
     - Account: The registration fee will be paid to the selected account in the multi-select option.

![payment](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/c7fe44a1-d015-4820-b793-14a6f637176e)


**Redirect after form submission**

1. **Redirect after form submission**: This setting determines if the user shall be redirected to a summary page after submitting the form.
    - No redirect: After submitting the campaign, the summary page content is displayed on the same page.
   - Redirect to the summary page: After submitting the campaign, the user is redirected to the summary page.
     - Summary page content: Enter the summary content in the text area field that you want to display on the page.
   - Redirect to the URL: After submitting the campaign, the user is redirected to the specified URL page.
     - Redirect to URL: Enter the URL to which the user should be redirected after submission.

![redirect](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/958e51ea-d06c-4f5d-8d14-3e76c3481342)

**Approval**
1. **Type**:
    - Information: A notification (with a confirmation link) is sent upon sign-up for the 'parent' user mail.
    - Approval roles: Please select the roles for the parent user, considering all user contexts and the system roles available on the platform.

![approval-campaign](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/8a5c8500-01a5-4b84-8f5a-e93dedcb75d0)


**Campaign Course**
1. **Campaign course**:
    - Disabled: Selecting this option disables the user from auto-enrolling in the campaign course.
    - Course1: Selecting the course will enroll the user in the selected course after signing up for the campaign course.

2. **Course role for student**:
    - Disabled: Selecting this option prevents the user from assigning roles for the selected course context.
    - Roles: Select the available roles in the option to assign the user to the selected course context.

3. **Course role for "Parent" user**:
    - Disabled: Selecting this option prevents the "Parent" user from assigning system roles.
    - Roles: Select the available roles from the options to assign the 'Parent' user to the selected course.

4. **Groups**:
    - Disabled: This option disables the group for the user in the selected course.
    - Campaign: This option allows the creation of a new group with the Campaign's title and assigns the user to that group when the user signs up for the campaign.
    - Per User: This option allows the creation of a new group with the user's name who created the campaign and assigns the user to that group when the user signs up for the campaign.

        ***Group messaging***:
        - Enable or disable the "Group Messaging" option for the campaign course.
        - If the 'Disabled' option is selected for the 'Group' setting, the 'Group Messaging' option will be hidden.

        ***Group enrolment key***:
        - Enable or disable the "Group Enrolment Key" option for the campaign course.
        - If the 'Disabled' option is selected for the 'Group' setting, the 'Group Enrolment key' option will be hidden.


5. **Group Capacity**:
    - The number specified in the 'Group capacity' option represents the maximum number of users who can utilize the 'Group enrollment key' to access the course.
    - If '0' is entered in the input value, the capacity is considered unlimited.
    - If the 'Disabled' option is selected for the 'Group' setting, the 'Group Capacity' option will be hidden.

6. **Grouping**:
    - Disabled: This option prevents the user from being grouped.
    - Select the grouping in the 'Grouping' option that allows the creation of the selected group within that grouping.
    - When the 'Disabled' option is selected for the 'Group' setting, the 'Grouping' option will be hidden.

![campaign-course](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/2b264803-006d-4ab0-8256-a89d219f57c2)


**Form fields**

Form fields in the campaign allow customization of the user signup form. These fields include default Moodle profile fields as well as custom user profile fields. These fields can be used to customize the signup form for each campaign. The campaign owner can select the desired form fields and configure their visibility and requirement settings accordingly.

1. **Authentication method**:
    - **Manual accounts**:
        - This process involves the manual verification of user accounts by system administrators or authorized personnel.
    - **No Login**:
        -No-Login denotes a system or feature that provides access to certain functionalities or information without requiring users to log in with traditional credentials, offering quick and anonymous access.
    - **Email-based self-registration**:
        - In this method, users register themselves by providing necessary information through an email-based system.
    - **OAuth2**:
        - OAuth2 is a protocol that allows secure authorization and authentication, commonly used for third-party application access to user data.
    - **Magic authentication**:
        - Users no longer need to remember and enter their usernames and passwords to access the Moodle site. Instead, they can use a magic link that is sent to their email address, providing them with direct access to the Moodle platform.

2. **Enrolment key**:

    - If a user enters a valid enrollment key here, they will be added to the course or course group associated with that enrollment key. No payment will be required, as this functions as a 'coupon.' It should be possible to pass the enrollment key as an encrypted parameter via the URL.

        - **Disabled**: The default state where the field is not added to the form.
        - **Strict**: A valid enrollment key for the selected campaign course must be provided; other enrollment keys won't be accepted. This requires a campaign course to be set up.
        - **Required**: A valid enrollment key needs to be provided.
        - **Optional**: A valid enrollment key can be provided.

Each form field can be configured with the following options:

1. **Required**: The field will be visible on the campaign signup form and users will be required to provide a value for this field.

2. **Optional**: The field will be visible on the campaign signup form, but users will have the choice to leave it blank if they prefer.

3. **Hidden (use provided text)**: The field will not be visible on the campaign signup form, but the campaign owner can provide a custom value for this field in the backend.

4. **Hidden (use default)**: The field will not be visible on the campaign signup form, but a default value will be used for this field in the backend.

5. **Hidden (use other field's value)**: The field will not be visible on the campaign signup form, but its value will be determined by the selected value of another selected field.

![form-fields](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/c7c678ba-8499-4ca9-a0d1-dd9db47adbe1)


**Assignments**:

- **Cohort membership**: A autocomplete select field populated with all cohorts on the site level context, allowing the selection of cohorts to be assigned to the user accounts created through the campaign.

- **Global roles**: A multi-select field populated with all roles available on the site context level, allowing the selection of roles to be assigned to the user accounts created through the campaign.

- **Campaign owner account**:  A autocomplete select field populated with all users on the site, allowing the selection of a user who will be assigned as the parent to the user accounts created through the campaign.

![campaign_assign](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/5d06641c-3f8e-4048-903a-006ede64315b)



**Privacy Policy Settings**:

- **Display consent option**: A checkbox to enable the display of a consent statement on the campaign page, allowing users to give their consent to the privacy policies.

- **Consent statement**: A text input to enter the consent statement.

![campaign_privacy](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/6e229923-a0ef-463e-a4f6-e8e47552b032)



**Welcome Message Settings**:

- **Send welcome message to new accounts**: A checkbox to enable sending a welcome message to newly created accounts, with placeholders to personalize the message.

- **Welcome message content**: A text editor field pre-filled with a standard message, which can be customized.

- **Also send to campaign owner**: A checkbox to determine whether the welcome message should also be sent to the campaign owner.


![campaign_welcome](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/96d86cb0-0eb3-41f9-a1b9-034fe44fbcf6)



**Follow-up Message Settings**:

- **Send follow-up message to new accounts**: A field to specify the number of days after which a follow-up message will be sent to newly created accounts, using the same placeholders as the welcome message.

- **Delay**: Send the message after the X days.

- **Message content**: A text editor field pre-filled with a standard message for the follow-up message, which can be customized.

- **Also send to campaign owner**: A checkbox to determine whether the follow-up message should also be sent to the campaign owner.

![campaign_followup](https://github.com/bdecentgmbh/moodle-auth_magic/assets/44221518/5a9b62e5-99a4-4503-b5dd-725d05fe4396)



# Best Practices and Tips:

1. **Generate Unique and Secure Links**:

    - Ensure that each magic login link generated is unique to maintain security.
    - Implement proper tokenization techniques to prevent unauthorized access.

2. **Design for Simplicity and Clarity**:

    - Create a clean and user-friendly custom login page.
    - Avoid cluttered layouts or excessive design elements that may confuse or overwhelm users.

3. **Simplify the Registration Process**:

    - Minimize the number of required fields during quick registration to streamline the process.
    - Collect only essential information initially and allow users to complete their profiles later if necessary.

4. **Customize Form Fields**:

    - Customize the form fields to collect the specific information required for each campaign.
    - Determine the fields that are required, optional, or hidden based on the campaign's goals.

5. **Streamline Account Creation**:

    - Design the signup process to be seamless and user-friendly.
    - Minimize the number of steps and required fields to encourage participation.

# Troubleshooting and Support:

By following these troubleshooting hints, you should be able to diagnose and resolve common issues with the Magic Authentication plugin in Moodle. If you continue to experience issues, consider reaching out to the Moodle community or contacting the support team for the Magic Authentication plugin for further assistance.

1. **Verify Plugin Installation** :

    - Ensure that the Magic Authentication plugin is installed correctly and activated on your Moodle site.

    - Check for any error messages during the installation process and consult the plugin documentation for troubleshooting steps.


2. **Check Plugin Compatibility**:

    - Ensure that the Magic Authentication plugin is compatible with your Moodle version.

    - Check for any plugin updates or patches that may address compatibility issues.

3. **Review Plugin Configuration**:

    - Double-check the plugin settings and configuration options.

    - Verify that the required settings are correctly set, such as email templates, login page customization, and campaign configurations.

4. **Review Error Logs** :

    - Check the Moodle error logs for any relevant error messages related to the Magic Authentication plugin.

    - Look for any specific error codes or messages that can help identify the issue.

5. **Disable Other Plugins** :

    - Temporarily disable other plugins to check if any conflicts or interference are causing the issue.

    - Enable plugins one by one to identify if a specific plugin is causing the problem.

6. **Update and Patch**:

    - Keep the Magic Authentication plugin up to date by applying any available updates or patches.

    - Developers often release updates to address bugs, improve performance, and add new features.


# Changelog:

**[1.0] - Initial Release - 2022-09-29**

- Added "Login via Magic Links" functionality, allowing users to log in using unique magic links instead of traditional credentials.

- Implemented the "List of Magic Links" feature, providing users with a convenient overview of all their generated magic links.

- Introduced the "Quick Registration" option, allowing users to create an account effortlessly by simply entering their email addresses.

**[1.1] - 2023-04-05**

- Added the option to use a username for login

- Improved the position of the "Get magic link" button.

- Added the ability to manually override the expiration time for magic links

**[1.2] - 2023-06-22**

- Introduce the Magic Login Page feature

- Added magic registration functionality

- Implemented campaign feature

**[1.3] - 2023-10-16**

- Migrated the Magic Pro from the local plugin to the Auth Plugin.

- Improved the security of the magic login key.

**[1.4] - 2023-11-18**

Added new features:

- Authentication method and Password for the form fields
- Payment method for the campaign
- Security option (reCaptcha, Require email confirmation)
- Redirect after campaign submission
- Relative role assignment

**[1.5] - 2024-01-05**

New Features:

- Course campaign, Approval setting in the campaign.
- Report sources: Payment transactions.