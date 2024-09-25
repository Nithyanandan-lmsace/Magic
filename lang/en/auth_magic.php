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
 * Strings for component 'auth_magic', language 'en'.
 *
 * @package   auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Magic authentication';
$string['configtitle'] = "Magic authentication";
$string['defaultenrolmentduration'] = "Default enrolment duration";
$string['defaultenrolmentrole'] = "Default enrolment role";
$string['loginexpiry'] = "Magic login link expiry";
$string['strsupportauth'] = "Supported authentication method";
$string['magiconly'] = "Magic only";
$string['anymethod'] = "Any method";
$string['strowneraccountrole'] = "Owner account role";
$string['strkeyaccount'] = "Key account";
$string['getmagiclinkviagmail'] = "Get a magic link via email";
$string['courseenrolment'] = "Course enrolment";
$string['enrolmentduration'] = "Enrolment duration";
$string['invitationexpiry'] = "";
$string['invitationexpiry'] = "Magic invitation link expiry";
$string['hasbeencreated'] = "has been created";
$string['strenrolinto'] = "enrolled into";
$string['magiclink'] = "Magic link";
$string['copyboard'] = "Copy link to cliboard";
$string['copyloginlink'] = "Copy magic login link for the user";
$string['more'] = '{$a} more';
$string['loginsubject'] = '{$a}: Magic authentication via login';
$string['loginlinksubject'] = "Magic authentication login link";

$string['pluginisdisabled'] = 'The magic authentication plugin is disabled.';
$string['emailnotexists'] = "Doesn't exist user email";
$string['loginlinkbtnpostion'] = 'Magic login link button position';
$string['normal'] = "Normal";
$string['belowusername'] = "Below username";
$string['belowpassword'] = "Below password";
$string['quickregistration'] = "Quick registration";
$string['listofmagiclink'] = "Magic user accounts";
$string['strconfirm'] = "Confirmation";
$string['quickregisterfornonauth'] = "Magic link via login for only magic authentication has supported the user. If you change others modifiy the supported authentication method settings.";
$string['copyinvitationlink'] = "Copy magic invitation link for the user";
$string['sendlink'] = "Send the magic link to the user";
$string['sentinvitationlink'] = "Sent the invitation link to the mail";
$string['notsentinvitationlink'] = "Doesn't sent the invitation link to the mail";
$string['userkeyslist'] = "My user accounts";
$string['createuserenrolcourse'] = 'has been created and enrolled into "{$a}"';
$string['existuserenrolcourse'] = 'has been enrolled into "{$a}"';
$string['statuscreateuser'] = 'has been created';
$string['sentlinktouser'] = "If you supplied a correct email address, an email containing a magic login link should have been sent to you.";
$string['sentregisterlinktouser'] = "If you supplied a correct email address, an email containing a registration link should have been sent to you.";
$string['sentlinktousername'] = "If you supplied a correct username, an email containing a magic login link should have been sent to your email address.";
$string['preventmagicauthsubject'] = "Magic authentication support information";
$string['invitationexpiryloginlink'] = "The invitation link has expired. You cannot use the magic login link to access the site.";
$string['invitationexpiryloginlinkwithupdate'] = "The invitation link has expired. If the email address belongs to an account that supports login via link, a link has been sent via email";
$string['loginexpiryloginlinkwithupdate'] = "The magic login link has expired. A new magic login link has been sent to your email address.";
$string['loginexpiryloginlink'] = "The magic login link has expired. You cannot use the magic login link to access the site.";
$string['registrationexpirylink'] = "The registration link has expired. You cannot use the registration link to access the site.";
$string['registrationexpirylinkwithupdate'] = "The registration link has expired. A new registration link has been sent to your email address.";
$string['linkexpirytime'] = 'Set a magic login link expiry time';
$string['success'] = 'Changes updated';
$string['error'] = 'Does not updated magic login link expiration time';
$string['loginoption'] = "Allow Username to get magic link";
$string['loginoptiondesc'] = "Enable this setting to login using the username provided in the login form.";

$string['loginlinkmessage'] = 'Hi {$a->fullname},

to access your account on \'{$a->sitename}\', please use the following magic link:

<a href=\'{$a->link}\'> {$a->link} </a> <br>

This link will expire on: <b> {$a->expiry} </b> <br>

If you need help, please contact the site administrator,
{$a->admin}';


$string['registrationmessage'] = 'Hi {$a->emailplaceholder},

Thank you for your interest in joining {$a->sitename}! To create your account, please use the following registration link:

<a href={$a->link}> {$a->link} </a> <br>

This link will expire on: <b> {$a->expiry} </b> <br>

If you have any questions or need assistance, please don\'t hesitate to contact the site administrator, {$a->admin}.

We look forward to having you as a member of our community!

Best regards,
The {$a->sitename} Team';

$string['registrationsubject'] = "Magic authentication Registration link";



$string['invitationmessage'] = 'Hi {$a->fullname},

A new account has been requested at \'{$a->sitename}\' using your email address.

To login your new account, please go to this web address login directly instead username and password :

<a href=\'{$a->link}\'> {$a->link} </a> <br>

This link will expire on: <b> {$a->expiry} </b> <br>

If you need help, please contact the site administrator,
{$a->admin}';

$string['expiredregistrationmessage'] = 'Hi {$a->emailplaceholder},

you have tried to access \'{$a->sitename}\' with an expired registration link.

A new magic link was automatically created for you:

<a href=\'{$a->link}\'> {$a->link} </a> <br>

If you need help, please contact the site administrator,
{$a->admin}';


$string['expiredloginlinkmsg'] = 'Hi {$a->fullname},

you have tried to access \'{$a->sitename}\' with an expired magic login link.

A new magic link was automatically created for you:

<a href=\'{$a->link}\'> {$a->link} </a> <br>

This link will expire on: <b> {$a->expiry} </b> <br>

If you need help, please contact the site administrator,
{$a->admin}';

$string['preventmagicauthmessage'] = 'Hi {$a->fullname},

A new account has been requested at \'{$a->sitename}\' using your email address. <br>

<strong> Note: </strong> Authenticating using a Magic link is not supported for your account, please use your password instead.

<br>

{$a->forgothtml} <br>

If you need help, please contact the site administrator,
{$a->admin}';

$string['doesnotaccesskey'] = "Doesn't have access the key in your authentication method";
$string['manualinfo'] = "Manual enrolments are not available in this course.";
$string['passinfo'] = "- or type in your password -";
$string['invailduser'] = "Invaild user";
$string['magicloginlink'] = '{$a}: Magic login link';
$string['and'] = "And";
$string['instructionsforlinktype'] = "Please provide a magic link type, the types are (invitation or login)";
$string['userhavenotlinks'] = 'User have not any {$a} link';

$string['privacy:metadata:auth_magic_loginlinks'] = 'Magic links for the user.';
$string['privacy:metadata:campaignpaymentlogs'] = 'Magic Campaign payment logs entry.';
$string['privacy:metadata:campaignusers'] = 'Magic Campaign users.';
$string['privacy:metadata:roleassignments'] = "Relative role assignments.";
$string['privacy:metadata:approval'] = "Approval users.";


$string['privacy:metadata:auth_magic:userid'] = 'ID of the user';
$string['privacy:metadata:auth_magic:parent'] = 'The value of the userid to assign parent of the user.';
$string['privacy:metadata:auth_magic:magicauth'] = 'The value of whether parent assigns or not.';
$string['privacy:metadata:auth_magic:parentrole'] = 'The instance of the parent role id.';
$string['privacy:metadata:auth_magic:loginuserkey'] = 'The value of the user login key';
$string['privacy:metadata:auth_magic:invitationuserkey'] = 'The value of the user invitation key';
$string['privacy:metadata:auth_magic:magiclogin'] = 'The value of the user magic login link';
$string['privacy:metadata:auth_magic:magicinvitation'] = 'The value of the user magic invitation link';
$string['privacy:metadata:auth_magic:loginexpiry'] = 'The date that the login key is valid until';
$string['privacy:metadata:auth_magic:invitationexpiry'] = 'The date that the invitation key is valid until';
$string['privacy:metadata:auth_magic:manualexpiry'] = "The date that set the expiry to the user login key is valid until";
$string['privacy:metadata:auth_magic:timecreated'] = 'The date and time that the login link was created.';
$string['privacy:metadata:auth_magic:timemodified'] = 'The date and time that the login link was modified.';
$string['privacy:metadata:auth_magic:campaignid'] = "ID of the campaign";
$string['privacy:metadata:auth_magic:paymentid'] = "The payment account ID for the paid campaign has been updated.";
$string['privacy:metadata:auth_magic:paymentstatus'] = "The campaign payment status.";


$string['privacy:metadata:auth_magic:enrolpassword'] = "The Campaign user enrolment key.";
$string['privacy:metadata:auth_magic:passenrolmentkey'] = "Status for the valid user enrolment key.";
$string['privacy:metadata:auth_magic:roleid'] = "Relative roleid for role assigments";
$string['privacy:metadata:auth_magic:roleassignstatus'] = "The relative role assignment status.";




$string['privacy:metadata:auth_magic'] = 'Magic authentication';
$string['messageprovider:auth_magic'] = 'Magic authentication login links';
$string['firstname'] = "First name";
$string['lastname'] = "Last name";


// Campaign settings.
$string['generalsettings'] = 'General settings';
$string['strcampaignownerrole'] = 'Campaign owner role';
$string['strcampaignownerrole_desc'] = 'Add a campaign owner role for magic campaigns';
$string['managecampaign'] = 'Manage campaign';
$string['createcampaign'] = 'Create campaign';
$string['campaigns:generalsection'] = 'Genaral settings';
$string['campaigns:title'] = 'Title';
$string['campaigns:description'] = 'Description';
$string['campaigns:comments'] = 'Comments';
$string['campaigns:availabilitysection'] = 'Availability';
$string['campaigns:capacity'] = 'Capacity';
$string['campaigns:status'] = 'Status';
$string['campaigns:visibility'] = 'Visibility';
$string['campaigns:start_from'] = 'Available from';
$string['campaigns:end_from'] = 'Available closes';
$string['campaigns:password'] = 'Password';
$string['campaigns:appearancesection'] = 'Appearance';
$string['campaigns:logo'] = 'Logo';
$string['campaigns:headerimg'] = 'Header image';
$string['campaigns:backgroundimg'] = 'Background image';
$string['campaigns:transform'] = 'Transparent form';
$string['campaigns:ownerprofile'] = 'Display campaign owner\'s profile picture';
$string['campaigns:formposition'] = 'Form Position';
$string['campaigns:center'] = 'Center';
$string['campaigns:leftoverlay'] = 'Left Overlay';
$string['campaigns:rightoverlay'] = 'Right Overlay';
$string['campaigns:leftfull'] = 'Left Full';
$string['campaigns:rightfull'] = 'Right Full';
$string['campaigns:assignmentssection'] = 'Assignments';
$string['campaigns:cohorts'] = 'Cohort membership';
$string['campaigns:globalrole'] = 'Global role';
$string['campaigns:owneraccount'] = 'Campaign owner account';
$string['campaigns:privacypolicysection'] = 'Privacy policy';
$string['campaigns:privacypolicy'] = 'Display consent option';
$string['campaigns:consentstatement'] = 'Consent statement';
$string['campaigns:welcomemessagesection'] = 'Welcome message';
$string['campaigns:welcomemessage'] = 'Send welcome message to new accounts';
$string['campaigns:welcomemessagecontent'] = 'Message content';
$string['campaigns:welcomemessageowner'] = 'Also send to campaign owner';
$string['campaigns:followupmessagesection'] = 'Follow up Message';
$string['campaigns:followupmessage'] = 'Send follow up message to new accounts';
$string['campaigns:followupmessagecontent'] = 'Message content';
$string['campaigns:messagedelay'] = 'Delay';
$string['campaigns:followupmessageowner'] = 'Also send to campaign owner';
$string['campaigns:securitysection'] = 'Security';
$string['campaigns:formfieldsection'] = 'Form fields';
$string['campaigns:standard_firstname'] = 'First name';
$string['campaigns:standard_lastname'] = 'Last name';
$string['campaigns:standard_username'] = 'Username';
$string['campaigns:standard_password'] = 'Password';
$string['campaigns:standard_email'] = 'e-Mail';
$string['campaigns:standard_country'] = 'Country';
$string['campaigns:standard_lang'] = 'Language';
$string['campaigns:standard_city'] = 'City';
$string['campaigns:standard_idnumber'] = 'ID Number';
$string['campaigns:standard_alternatename'] = 'Alternatename';
$string['campaigns:standard_department'] = 'Department';
$string['campaigns:standard_institution'] = 'Institution';
$string['campaigns:standard_address'] = 'Address';
$string['campaigns:pointoffirstcontact'] = 'Point of first contact';
$string['campaigns:typeofaccount'] = 'Type of account';
$string['campaigns:required'] = 'Required';
$string['campaigns:optional'] = 'Optional';
$string['campaigns:strict'] = "Strict";
$string['campaigns:hiddentype1'] = 'Hidden (use provided text)';
$string['campaigns:hiddentype2'] = 'Hidden (use default)';
$string['campaigns:hiddentype3'] = 'Hidden (use other field\'s value)';
$string['campaigns:hidden'] = 'Hidden';
$string['disabled'] = 'Disabled';
$string['campaigns:title'] = 'Title';
$string['campaigns:campaignowner'] = 'Campaign Owner';
$string['campaigns:availability'] = 'Availability';
$string['campaigns:capacity'] = 'Capacity';
$string['campaigns:link'] = 'Copy link';
$string['campaigns:couponlink'] = 'Campaign link with coupon';
$string['campaigns:preview'] = 'Preview';
$string['campaigns:available'] = 'Available';
$string['campaigns:archived'] = 'Archived';
$string['campaigns:unlimited'] = 'Unlimited';
$string['campaigns:updatesuccess'] = 'Campaign updated successfully';
$string['campaigns:insertsuccess'] = 'Campaign created successfully';
$string['campaigns:recordmissing'] = 'Campaign record missing';
$string['campaigns:campaigndeleted'] = 'Campaign deleted successfully';
$string['campaigns:deleteconfirmcampaign'] = 'Are you sure you want to delete this campaign from the manage campaigns?';
$string['campaigns:notavailable'] = 'This campaign is not avilable to signup';
$string['campaigns:capacity_info'] = '<ul><li>No of users registered using this campaign: {$a->used}</li><li>No of users still able to signup using this campaign: {$a->available} </li></ul>';
$string['campaigns:requirepasswordmessage'] = 'To signup using this campaign you need to know this campaign password';
$string['campaigns:password'] = 'Campaign password';
$string['campaigns:emptypassword'] = 'Password verification failed';
$string['campaigns:verifiedsuccess'] = 'Password verified successfully';
$string['campaigns:notaccess'] = 'You do not have permission to view this page for the specified user.';
$string['campaigns:recaptcha'] = 'reCAPTCHA';
$string['campaigns:emailconfirm'] = "Require email confirmation";
$string['campaigns:partial'] = "Partial";
$string['campaigns:recaptchaprivatekey'] = 'Campaign ReCAPTCHA secret key';
$string['campaigns:recaptchapublickey'] = 'Campaign ReCAPTCHA site key';



$string['lang'] = "Language";
$string['none'] = 'None';
$string['verify'] = 'Verify';
$string['unlimited'] = 'unlimited';
$string['confirmpassword'] = 'Confirm password';
$string['welcomemessagesubject'] = 'Signup completed ';
$string['followupmessagesubject'] = 'This is follow up message';
$string['sendmessage'] = 'Send follow up message';
$string['campaignlink'] = 'Campaign link';
$string['signupsuccess'] = "User signup successfully.";
$string['strsupportpassword'] = "Supports password";
$string['strsignup'] = 'Sign up';
$string['auth_emailnoemail'] = 'Tried to send you an email but failed!';
$string['strprofilefield:'] = 'Profile Field: {$a}';
$string['strcampaigns'] = "Campaigns";
$string['strviewcampaign'] = "View Campaign";
$string['strvcampaignsummary'] = "Summary Campaign";
$string['strmagicsignup'] = "Magic Sign Up";
$string['strautocreateusers'] = "Auto create users";
$string['strautocreateusers_desc'] = "If enabled, visitors will be able to register using a magic registration link to the site, if the email matches the list of allowed email domains and/or does not match the list of denied email domains. The account will be created when the user clicks on the registration link in their inbox. Before they can access the site, they need to fill in mandatory profile fields like their name and potentially other fields that are required on this site, as well as consent to any existing privacy policy.";
$string['campaigns:title_help'] = 'Visible to the user.';
$string['campaigns:description_help'] = 'Visible to the user.';
$string['campaigns:comments_help'] = 'Only visible to campaign owners and campaign managers.';
$string['campaigns:capacity_help'] = 'How many users can use the campaign to sign up. Leave empty or set to 0 for unlimited.';
$string['campaigns:status_help'] = 'whether this campaign is available or archived. Archived campaigns cannot be used to sign up, even if there is still capacity. They are no longer visible to the campaign owner.';
$string['campaigns:visibility_help'] = 'whether this campaign is visible for guests.';
$string['campaigns:start_from_help'] = 'When this campaign opens.';
$string['campaigns:end_from_help'] = 'When this campaign closes.';
$string['campaigns:password_help'] = 'Secure your campaign using a password.

This can either be entered by the user, or submitted as

a parameter (use the link icon to copy the link with a token).';
$string['campaigns:logo_help'] = "Will be displayed centered at the top of the page.

It does not replace the site's logo (i.e. it is below the navbar)

If you want the logo to align differently, add it to the description.";
$string['campaigns:headerimg_help'] = 'Will be displayed below the navbar, full width.

Height depends on the image you upload.';
$string['campaigns:backgroundimg_help'] = 'Will be displayed below the header image if set,

otherweise below the navbar.

Width and height will be full.';
$string['campaigns:transform_help'] = 'whether to show a border/box shadow/background color for the form.';
$string['campaigns:ownerprofile_help'] = 'Displays the campaign owner\'s profile picture above the campaign form.';
$string['campaigns:formposition_help'] = 'Define where the form shall be placed in the sign up page.';

$string['campaigns:cohorts_help'] = 'Automatically add users to the following cohorts.';
$string['campaigns:globalrole_help'] = 'Automatically assign the following role to a user.';
$string['campaigns:campaignowner_help'] = 'Automatically assign the following user to the following account.';
$string['campaigns:privacypolicy_help'] = 'IF enable users to give their consent to the privacy policies by showing a consent statement on the campaign page.';
$string['campaigns:consentstatement_help'] = 'Will be displayed if "Display consent option" is enabled

next to a checkbox, which, when ticked by the user will

automatically set the consent to the privacy policies.';
$string['campaigns:welcomemessage_help'] = 'When enabled, sent to new users.';
$string['campaigns:welcomemessagecontent_help'] = 'You can use placeholders.

Password placeholder will only work if the global setting

"Support passwords" in auth_magic is set.';
$string['campaigns:welcomemessageowner_help'] = 'Will CC the campaign owner (if set).';
$string['campaigns:followupmessage_help'] = 'When enabled, sent to follow-up message for new users.';
$string['campaigns:messagedelay_help'] = 'Send after the X days.';
$string['campaigns:followupmessagecontent_help'] = 'You can use placeholders.

Password placeholder will only work if the global setting

"Support passwords" in auth_magic is set.';
$string['campaigns:followupmessageowner_help'] = 'Will CC the campaign owner (if set).';
$string['campaigns:recaptcha_help'] = 'Adds a visual/audio confirmation form element to the sign-up page for self-registering users. This is supposed to reduce the number of spam users and thus makes the platform more secure and avoid unnecessary notifications. ';
$string['campaigns:emailconfirm_help'] = "Force users to confirm their account with an email before the sign up is complete. This setting is only applicable for new user accounts created via a campaign.";
$string['campaigns:type_auth_help'] = "";


$string['privilegedrole'] = 'Privileged role';
$string['teacherrole'] = 'Teacher';
$string['noneditteacherrole'] = 'Non-editing teacher';
$string['managerrole'] = 'Manager';
$string['strsignsite'] = 'Sign in to {$a}';
$string['loginfooter'] = "Login footer links";
$string['loginfooter_desc'] = 'Enter the content/links on the "Login footer" block. A URL is separated by pipe characters followed by the link text.';
$string['loginfooterdefault'] = 'Imprint |#
Terms & Conditions |#
Login instructions |#
Admin login|#';
$string['strsignin'] = "Sign In";
$string['strstandardprofilefield_help'] = "Standard profile field";
$string['strstandardprofilefield'] = "Standard profile field";
$string['strcustomprofilefield'] = "Custom profile field";
$string['strcustomprofilefield_help'] = "Custom profile field";
$string['strenteryouremail'] = "enter your e-Mail address";
$string['none'] = "None";
$string['currentlylinkexpiry'] = "As the login link has expired, a new link will be sent to your email address when you click on the expired login link.";
$string['loginkeytype'] = "Configure login key link";
$string['loginkeytype_desc'] = "This setting allows administrators to configure the behavior of login keys. The login keys can be used only once or until the expiry time.";
$string['keyuseonce'] = "Only once";
$string['keyusemultiple'] = "Until it expires";


$string['magic:cansitequickregistration'] = 'Can access the site quick registration';
$string['magic:cancoursequickregistration'] = 'Can access the course quick registration';
$string['magic:viewloginlinks'] = "View the users magic login links";
$string['magic:viewchildloginlinks'] = "View the child users magic login links";
$string['magic:userdelete'] = "Delete the magic auth users";
$string['magic:usersuspend'] = "Suspend the magic auth users";
$string['magic:userupdate'] = "Updated the magic auth users";
$string['magic:usercopylink'] = "Can copy link for magic auth users";
$string['magic:usersendlink'] = "Can send link for magic auth users";
$string['magic:usersetlinkexpirytime'] = "Can override a magic login link expiry time for auth users";
$string['magic:childuserdelete'] = "Delete the magic auth child users";
$string['magic:childusersuspend'] = "Suspend the magic auth child users";
$string['magic:childuserupdate'] = "Updated the magic auth child users";
$string['magic:childusercopylink'] = "Can copy link for magic auth child users";
$string['magic:childusersendlink'] = "Can send link for magic auth child users";
$string['magic:childusersetlinkexpirytime'] = "Can override a magic login link expiry time for auth child users";
$string['magic:createcampaign'] = "Create the campaign.";


$string['campaignsnotfound'] = "Invaild campaign";
$string['magic:campaignself'] = "Can able to access the  campaign self section";
$string['magic:campaignteam'] = "Can able to campaign team section";
$string['magic:campaignnew'] = "Can able to campaign new section";
$string['magic:viewcampaignlists'] = "View the campaign lists";
$string['magic:viewcampaignownerlists'] = "View the campaign owner lists";
$string['magic:privilegeaccount'] = "Manage the magic privilege account";
$string['campaigns:type_auth'] = 'Authentication method';

$string['notexists_loginlinkmsg'] = 'Hi {$a->fullname},

You do not have a magic login link, so you don\'t have access to the site using the magic authentication.

If you need help, please contact the site administrator,
{$a->admin}';

$string['campaigns:requiredonce'] = 'Required Once';
$string['campaigns:requiredtwice'] = 'Required Twice';
$string['campaigns:hidden'] = 'Hidden';
$string['campaigns:paymenttype'] = 'Type';
$string['campaigns:paymenttype_help'] = 'Defines if the campaign is free or paid.';
$string['campaigns:paymentfee'] = 'Fee';
$string['campaigns:paymentfee_help'] = 'The amount that needs to be paid upon sign up.';
$string['campaigns:currency'] = 'Currency';
$string['campaigns:currency_help'] = 'The currency for the fee.';
$string['campaigns:payment'] = 'Payment';
$string['campaigns:strfree'] = 'Free';
$string['campaigns:strpaid'] = 'Paid';
$string['campaigns:paymentaccount'] = "Account";
$string['campaigns:paymentaccount_help'] = "The registration fee will be paid to the selected account";
$string['campaigns:aftersubmisson'] = "After submission";
$string['campaigns:redirectaftersubmisson'] = "Redirect after form submission";
$string['campaigns:redirectaftersubmisson_help'] = "This settings determines if the user shall be redirected to a summary page after submitting the form.";

$string['campaigns:noredirect'] = "No redirect";
$string['campaigns:redirectsummary'] = "Redirect to summary page";
$string['campaigns:redirecturl'] = "Redirect to URL";
$string['campaigns:redirecturl_help'] = "The user is redirected to the URL specified here.";
$string['campaigns:summarypagecontent'] = "Summary page content";
$string['campaigns:summarypagecontent_help'] = "Visible content to users.";


$string['purchasecampaigndescription'] = 'Access the {$a} campaign.';
$string['paymentrequiredcampaign'] = 'This campaign requires a payment for entry.';

$string['sendpaymentbutton'] = 'Select payment type';
$string['strpaymentcampaign'] = "Payment Campaign";
$string['campaigns:child_formfield'] = "Link to Other User Form Fields";
$string['campaigns:child_formfield_help'] = "This settings allows the user to choose another user from the selection box instead of manually filling in the campaign form fields section.";
$string['nocaptchaavilable'] = 'You can not configure the reCAPTCHA settings. <a href="{$a}" target="_blank"> Here </a>';
// ... Role assignment admin config strings.
$string['fieldroleassignment'] = 'Profile field for the role of {$a}';
$string['fieldroleassignment_desc'] = 'Select the profile field to assign the user to this role.';
$string['accountidentifier'] = 'Account identifier';
$string['accountidentifier_desc'] = 'This setting shall be used to determine which information is used to identify users.';
$string['autocreaterelativeroles'] = 'Auto create the relative role users';
$string['autocreaterelativeroles_desc'] = 'Auto create the users if not exists';
$string['relativeroleassignment'] = 'Relative role assignments';
// ...Relative role users names.
$string['relativeuserfirstname'] = '';
$string['relativeuserlastname'] = '';
$string['relativeusername'] = '';
$string['approval'] = "Approval";
$string['campaigns:approvaltype'] = "Type";
$string['campaigns:approvaltype_help'] = "Determines how the approval process works.
    The following options shall be provided to the roles configured in the “Approval roles” ";
$string['information'] = "Information";
$string['approvalroles'] = "Approval roles";
$string['campaigns:approvalroles'] = "Approval roles";
$string['campaigns:approvalroles_help'] = "Please select the roles for the parent user, considering all user contexts and the system roles available on the platform.";
$string['campaigns:campaigncourse'] = "Campaign course";
$string['campaigns:campaigncourse_help'] = "Selecting the course will enroll the user in the selected course after signing up for the campaign course.";
$string['campaigns:coursestudentrole'] = "Course role for student";
$string['campaigns:coursestudentrole_help'] = "Select the available roles in the option to assign the user to the selected course context.";
$string['campaigns:courseparentrole'] = "Course role for parent user";
$string['campaigns:courseparentrole_help'] = "Select the available roles from the options to assign the 'Parent' user to the selected course.";
$string['campaigns:groups'] = "Groups";
$string['campaigns:groups_help'] = "Disabled: This option disables the group for the user in the selected course.

Campaign: This option allows the creation of a new group with the Campaign's title and assigns the user to that group when the user signs up for the campaign.

Per User: This option allows the creation of a new group with the user's name who created the campaign and assigns the user to that group when the user signs up for the campaign.";
$string['campaigns:grouping'] = "Grouping";
$string['campaigns:grouping_help'] = "Select the grouping in the 'Grouping' option that allows the creation of the selected group within that grouping.";
$string['campaigns:groupmessaging'] = "Group messaging";
$string['campaigns:groupmessaging_help'] = "Enable or disable the 'Group Messaging' option for the campaign course groups.";
$string['campaigns:groupenrolmentkey'] = "Group enrolment key";
$string['campaigns:groupenrolmentkey_help'] = "Enable or disable the 'Group Enrolment Key' option for the campaign course groups.";
$string['campaigns:groupcapacity'] = "Group capacity";
$string['campaigns:groupcapacity_help'] = "The number specified in the 'Group capacity' option represents the maximum number of users who can utilize the 'Group enrollment key' to access the course.
If '0' is entered in the input value, the capacity is considered unlimited.";

$string['peruser'] = "Per User";
$string['strcampaign'] = "Campaign";
$string['payments_transactions'] = "Payment transactions";
$string['paymentlogs'] = "Payment logs";
$string['paymentstatus'] = "Payment status";
$string['separtegroupinstructions'] = 'Attention: The course {$a->coursename} does not enforce separate groups. Are you sure you want to use this course? {$a->courselink}';
$string['selfcourseinstructions'] = 'Attention: The course {$a->coursename} does not self enrolment. Are you sure you want to use this course? {$a->courselink}';
$string['reviewcoursesettings'] = "Review course settings";
$string['reviewcourseenrolmentsettings'] = "Review course enrolment settings";
$string['campaigns:courseenrolmentkey'] = "Enrolment key";
$string['campaigns:courseenrolmentkey_help'] = "If a user enters a valid enrollment key here, they will be added to the course or course group associated with that enrollment key. No payment will be required, as this functions as a 'coupon.'

Disabled: The default state where the field is not added to the form.

Strict: A valid enrollment key for the selected campaign course must be provided; other enrollment keys won't be accepted. This requires a campaign course to be set up.

Required: A valid enrollment key needs to be provided.

Optional: A valid enrollment key can be provided.";

$string['origin'] = "Origin";
$string['originlinked'] = "Origin Linked";
$string['optional_in'] = "Opt-in";
$string['optional_out'] = "Campaign opt-out";
$string['full_option_out'] = "Full opt-out";

$string['emailrevocationsubject'] = 'Magic authentication : Account revocation';


$string['emailrevocation'] = 'Hi,

A magic authentication has been requested account revocation for \'{$a->user}\' user at \'{$a->sitename}\'
using your email address.

To confirm revocation account, please go to this web address:

{$a->link}

In most mail programs, this should appear as a blue link
which you can just click on.  If that doesn\'t work,
then cut and paste the address into the address
line at the top of your web browser window.

If you need help, please contact the site administrator,
{$a->admin}';

$string['revoked'] = "Revocation successfully";
$string['strrevocation'] = "Revocation";
$string['strrevocationcampaign'] = "Revocation Campaign";
$string['campaignassignapplied'] = "Campaign assignments have been successfully applied.";

$string['reportsource_campaign'] = "Campaigns";
$string['campaignsource:field_name'] = "Name";
$string['campaignsource:field_description'] = "Description";
$string['campaignsource:field_comments'] = "Comments";
$string['campaignsource:field_timecreated'] = "Time created";
$string['campaignsource:field_timemodified'] = "Time modified";
$string['campaignsource:field_capacity'] = "Capacity";
$string['campaignsource:field_status'] = "Status";
$string['campaignsource:field_visibility'] = "Visibility";
$string['campaignsource:field_from'] = "Available from";
$string['campaignsource:field_startdate'] = "Available startdate";
$string['campaignsource:field_enddate'] = "Available closes";

$string['campaignsource:field_restrictbyrole'] = "Restrict by role";
$string['campaignsource:field_restrictbycohort'] = "Restrict by cohorts";


$string['campaignsource:field_availablefrom'] = "Available from";
$string['campaignsource:field_password'] = "Password";

$string['campaignsource:field_cohorts'] = "Cohort membership";
$string['campaignsource:field_globalrole'] = "Global role";
$string['campaignsource:field_campaignowner'] = "Campaign owner";
$string['campaignsource:field_consentstatement'] = "Consent option";
$string['campaignsource:field_welcomemessage'] = "Welcome message";
$string['campaignsource:field_followupmessagedelay'] = "Follow up";
$string['campaignsource:field_campaigncourse'] = "Campaign course";

$string['campaignsource:field_fee'] = "Registration fee";
$string['campaignsource:field_expirydate'] = "Expiration date";
$string['entities:campaign_statistics'] = "Campaign Statistics";
$string['entities:user_statistics'] = "User Statistics";
$string['entities:campaign_groups'] = "Campaign Groups";
$string['campaignsource:field_confirmedusers'] = "Confirmed Users";
$string['campaignsource:field_unconfirmedusers'] = "Unconfirmed Users";
$string['campaignsource:field_availableseats'] = "Available seats";
$string['campaignsource:field_campaignavailableseats'] = "Campaign Available seats";
$string['campaignsource:field_totalrevenue'] = "Total revenue";
$string['campaignsource:field_firstsignup'] = "First signup";
$string['campaignsource:field_recentsignup'] = "Most recent sign up";

$string['countavailableseats'] = '{$a} available';
$string['campaignsource:field_groupname'] = "Group name";
$string['campaignsource:field_groupid'] = "Group ID";
$string['campaignsource:field_groupcapacity'] = "Group capacity";
$string['campaignsource:field_membercount'] = "Member Count";
$string['campaignsource:field_groupavailableseats'] = "Group Available seats";
$string['campaignsource:field_groupstatus'] = "Status";

$string['field_groupid'] = "Group ID";

$string['strfull'] = "Full";
$string['stravailable'] = "Available";
$string['campaignsource:field_logins'] = "Logins";
$string['campaignsource:field_badgesawarded'] = "Badges awarded";
$string['campaignsource:field_enrolledcourses'] = "Enrolled courses";
$string['campaignsource:field_inprogresscourses'] = "Inprogress courses";
$string['campaignsource:field_completedcourses'] = "Completed courses";
$string['campaignsource:field_activitiescompleted'] = "Activities completed";
$string['struserid'] = "Userid";
$string['onlymycampaign'] = "Only my campaigns";
$string['campaignsource:field_campaigntitlelink'] = "Name with link";
$string['datasource:campaign_data_source'] = 'Campaigns';
$string['id'] = "Id";
$string['backgroundimagelink'] = "Background image (linked)";
$string['headerimage'] = "Header image";
$string['logoimage'] = "Logo image";
$string['headerimagelink'] = "Header image (linked)";
$string['logoimagelink'] = "Logo image (linked)";
$string['comments'] = "Comments";
$string['timemodified'] = "Time modified";
$string['capacitystatus'] = "Capacity status";
$string['capacity:open'] = "Open";
$string['capacity:seatavailable'] = "Seats available";
$string['capacity:fullbooked'] = "Fully booked";
$string['totalcapacitystatus'] = "Total capacity";
$string['availablecapacitystatus'] = "Available capacity";
$string['availableandtotalcapacitystatus'] = "Available/total capacity";
$string['valuetotalcapacity'] = '{$a} total';
$string['valueavailablecapacity'] = '{$a} available';
$string['valutotalandavailablecapacity'] = '{$a->available} of {$a->total} available';
$string['approvaltype'] = "Approval type";
$string['approvaltypes'] = "Approval types";
$string['campaignsource:field_campaignownerwithlink'] = "Campaign User with link";
$string['mycampaign'] = "My campaigns";
$string['hidemycampaign'] = "Hide my campaigns";
$string['strpast'] = "Past";
$string['strpresent'] = "Present";
$string['strfuture'] = "Future";

$string['campaigndates'] = "Campaign dates";
$string['campaigns:restrictaccesssection'] = "Restrict access";

$string['campaignbyrole'] = 'By role';
$string['campaignbyrole_help'] = 'Restrict the visibility based on the user\'s roles.';
$string['campaignrolecontext'] = 'Context';
$string['campaignrolecontext_help'] = 'Select the context for which the user\'s role should be checked (Any context or system context only)';
$string['campaignbycohort'] = 'By cohort';
$string['campaignbycohort_help'] = 'Restrict the visibility based on the user\'s cohorts.';

$string['campaignoperator'] = 'Operator';
$string['campaignoperator_help'] = 'Select the operator for the cohort condition (Any or All)';
$string['campaigns:expirysection'] = "Expiry";
$string['campaigns:expirytiondate'] = "Expiration date";
$string['campaign:suspenduser'] = "Suspend User";
$string['campaign:deleteuser'] = "Delete User";
$string['campaign:expiryassigncohorts'] = "Add to cohort";
$string['campaign:expiryremovecohorts'] = "Remove From cohort";

$string['campaign:unassignglobalrole'] = "Unassign global role";
$string['campaign:expirybeforenotify'] = "Notify before expiry";

$string['monthbefore3'] = "3 months before expiry";
$string['monthbefore1'] = "1 month before expiry";
$string['weekbefore3'] = "3 weeks before expiry";
$string['weekbefore2'] = "2 weeks before expiry";
$string['weekbefore1'] = "1 weeks before expiry";
$string['daybefore3'] = "3 days before expiry";
$string['daybefore1'] = "1 day before expiry";
$string['uponbefore'] = "upon expiry";

$string['subjectcampaignexpirynotify'] = 'Campaign Expiry Notification: {$a}';
$string['messagecampaignexpirynotify'] = 'This is a reminder that the campaign {$a->campaignname} is set to expire in {$a->notifytime}.';
$string['campaignexpirycheck'] = "Magic campaign expiry action task";