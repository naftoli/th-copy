<?php

//------------------------------------------------------------------------------
// LANGUAGE FILE
// Edit with care. Make a backup first before making changes.
//
// [1] Apostrophes should be escaped. eg: it\'s christmas.
// [2] Take care when editing arrays as they are spread across multiple lines
//
// If you make a mistake and you see a parse error, revert to backup file
//------------------------------------------------------------------------------

/* Public */
$msg_public_login = 'Sign In';
$msg_public_login2 = 'Please sign into your account below';
$msg_public_login3 = 'Login';
$msg_public_login4 = 'Access to your account has been blocked, please try again later';
$msg_public_login5 = 'Account Disabled';

$msg_public_main = 'Support Help Desk';
$msg_public_main2 = 'Welcome to <b>{name}</b>.<br><br>If you already have a support account, please <a href="?p=login">login to your account</a> or choose another option from the menu.<br><br>
                     If enabled, please check out the F.A.Q section below before opening tickets as your question may have already been answered. Thank you.';
$msg_public_main3 = 'Latest Questions';

$msg_public_create = 'Create Account';
$msg_public_create2 = 'Enter your account details below';
$msg_public_create3 = 'Re-Enter Email Address';
$msg_public_create4 = 'Name/Alias';
$msg_public_create5 = 'Invalid email address, please try again.';
$msg_public_create6 = 'Email address already exists, please try again.';
$msg_public_create7 = 'Account Verification';
$msg_public_create8 = '<b><i class="fa fa-warning fa-fw"></i> ERROR - INVALID CODE</b><br><br>The link you clicked could not be verified. Please re-check the link in the email and try again.<br><br>
                       It could be that you accidentally clicked the link twice, in which case your account may already be active.<br><br>Sorry for the inconvenience.';
$msg_public_create9 = '<b><i class="fa fa-warning fa-fw"></i> ERROR - ACCOUNT ALREADY VERIFIED</b><br><br>Your account has already been activated and your account password has been emailed to you.<br><br>
                       Check your spam folder if you haven`t received the email.<br><br>To resend the email, please click the button below.<br><br>Sorry for the inconvenience.';
$msg_public_create10 = '<b><i class="fa fa-check fa-fw"></i> THANK YOU - ACCOUNT VERIFIED</b><br><br>Please check your inbox at "{email}" for your account password.<br><br>If this email doesn`t arrive in the next few minutes, check your spam folder or click the button below to have it resent.';
$msg_public_create11 = 'Please confirm you are not a robot by checking the spam box.';

$msg_public_dashboard1 = 'Account Information';
$msg_public_dashboard2 = 'Current Open Tickets';
$msg_public_dashboard3 = 'Current Open Disputes';
$msg_public_dashboard4 = 'Timezone';
$msg_public_dashboard5 = 'Language';
$msg_public_dashboard6 = 'View';
$msg_public_dashboard7 = 'There are currently 0 open tickets';
$msg_public_dashboard8 = 'There are currently 0 open dispute tickets';
$msg_public_dashboard9 = 'Waiting for Support Team';
$msg_public_dashboard10 = 'Waiting Your Reply';
$msg_public_dashboard11 = 'Welcome back <b>{name}</b>,<br><br>Please use the links on the menu to manage your support account. Please make sure that your email address is kept up to date to ensure ticket notifications are received.<br><br>Your current open tickets are shown below. Before opening a ticket, please check the F.A.Q.';
$msg_public_dashboard12 = 'IP';
$msg_public_dashboard13 = 'Waiting for Your/Other Reply';

$msg_public_account = 'Update your profile below';
$msg_public_account2 = 'Name/Timezone';
$msg_public_account3 = 'Email Address';
$msg_public_account4 = 'Update Profile';
$msg_public_account5 = 'Timezone';

$msg_public_profile = 'Email addresses do NOT match, check and try again..';
$msg_public_profile2 = 'Profile Updated';
$msg_public_profile3 = 'Language';
$msg_public_profile4 = 'Preferred Language';
$msg_public_profile5 = 'Email address already in uses, please try again..';
$msg_public_profile6 = 'Password';
$msg_public_profile7 = 'Current Password';
$msg_public_profile8 = 'New Password';
$msg_public_profile9 = 'Re-Enter New Password';
$msg_public_profile10 = 'Invalid password, please try again..';
$msg_public_profile11 = 'Please enter new password and confirm..';
$msg_public_profile12 = 'Password confirmation doesn`t match new password..';
$msg_public_profile13 = 'Error..Min characters for passwords is {min}..';
$msg_public_profile14 = '';

$msg_public_history = '{count} Ticket(s)';
$msg_public_history2 = 'ID / Priority';
$msg_public_history3 = 'Subject/Info';
$msg_public_history4 = 'Open - Waiting for Support';
$msg_public_history5 = 'Open - Please Reply';
$msg_public_history6 = 'Resolved';
$msg_public_history7 = 'Opened: <span class="highlight">{date}</span> &#8226; Dept: <span class="highlight">{dept}</span> &#8226; Replies: <span class="highlight">{replies}</span>';
$msg_public_history8 = 'To Be Assigned';
$msg_public_history9 = 'Opened: <span class="highlight">{date}</span> &#8226; Dept: <span class="highlight">{dept}</span> &#8226; Replies: <span class="highlight">{replies}</span> &#8226; User(s) in Dispute: <span class="highlight">{dispute}</span>';
$msg_public_history10 = 'There are currently 0 disputes in your account. Previous disputes may have been deleted.';
$msg_public_history11 = '{count} Dispute(s)';
$msg_public_history12 = 'Delete Entry';
$msg_public_history13 = 'All Priority Levels';

$msg_public_ticket = 'This ticket has been locked by the admin support team and cannot be re-opened';
$msg_public_ticket2 = 'Unlimited';
$msg_public_ticket3 = 'This ticket is waiting to be assigned to a member of staff. Please be patient..';
$msg_public_ticket4 = 'New Ticket Created';
$msg_public_ticket5 = '<i class="fa fa-check-circle fa-fw"></i> Thank you, your new ticket <a href="?t={ticket}" title="View Ticket">#{ticket_long}</a> has been created.<br><br>This ticket will be updated by a member of staff as soon as possible, thank you for your patience.';
$msg_public_ticket6 = 'Important - Please Read';
$msg_public_ticket7 = '<i class="fa fa-user fa-fw"></i> As this is your first support ticket, an account has been created for you.<br><br><b>Login Email</b>: {email}<br><b>Login Password</b>: {pass}<br><br>Please click <a href="{url}/?p=login">here</a> to sign in and manage your account. We would recommend you update your password as soon as possible.<br><br>Confirmation of this account has been emailed to you.<br><br>Thank you.';
$msg_public_ticket8 = '{count} error(s) have been found and are highlighted below. Please correct and try again..';
$msg_public_ticket9 = 'Invalid input or blank field, please update or try again..';
$msg_public_ticket10 = 'One or more invalid attachments, please re-select..';
$msg_public_ticket11 = 'This ticket is waiting to have other users assigned to the dispute. Please be patient..';
$msg_public_ticket12 = 'New Ticket Confirmation';
$msg_public_ticket13 = 'Ticket Successfully Closed';
$msg_public_ticket14 = 'Ticket Successfully Re-Opened';
$msg_public_ticket15 = 'This ticket is waiting for a response from the support team. No further replies are allowed during this time. Please be patient..';

$msg_header10 = 'Menu';
$msg_header11 = 'Ticket History';
$msg_header12 = 'Sign In';
$msg_header13 = 'Dashboard';
$msg_header14 = 'Create Account';
$msg_header15 = 'Account Profile';
$msg_header16 = 'Dispute History';
$msg_header17 = 'Update Profile';

$msg_adheader37 = 'Settings & Tools';
$msg_adheader38 = 'Visitor Accounts';
$msg_adheader39 = 'Add Account';
$msg_adheader40 = 'Manage Accounts';
$msg_adheader41 = 'Ticket Management';
$msg_adheader42 = 'Imap Accounts';
$msg_adheader43 = 'Manage Custom Fields';
$msg_adheader44 = 'Add Category';
$msg_adheader45 = 'Manage Categories';
$msg_adheader46 = 'Add Question';
$msg_adheader47 = 'Manage Questions';
$msg_adheader48 = 'Add Attachments';
$msg_adheader49 = 'Manage Attachments';
$msg_adheader50 = 'Add Priority Level';
$msg_adheader51 = 'Manage Priority Levels';
$msg_adheader52 = 'Priority Levels';
$msg_adheader53 = 'Add Response';
$msg_adheader54 = 'Manage Responses';
$msg_adheader55 = 'Import Questions';
$msg_adheader56 = 'Search Accounts';
$msg_adheader57 = 'Add Staff';
$msg_adheader58 = 'Manage Staff';
$msg_adheader59 = 'Import Accounts';
$msg_adheader60 = 'Import Responses';
$msg_adheader61 = 'Mailbox';
$msg_adheader62 = 'Imap Spam Filter';
$msg_adheader63 = 'Spam Tickets';
$msg_adheader64 = 'Profile';

$msg_accounts = 'Name/Alias';
$msg_accounts2 = 'Email Address';
$msg_accounts3 = 'Tickets';
$msg_accounts4 = 'Add New Account';
$msg_accounts5 = 'There are currently 0 visitor accounts to display';
$msg_accounts6 = 'Update Account';
$msg_accounts7 = 'Personal';
$msg_accounts8 = 'Options';
$msg_accounts9 = 'Email Address (A-Z)';
$msg_accounts10 = 'Email Address (Z-A)';
$msg_accounts11 = 'Most Tickets';
$msg_accounts12 = 'Least Tickets';
$msg_accounts13 = 'Ticket History';
$msg_accounts14 = 'All Accounts';
$msg_accounts15 = 'Selected Account(s) &amp; Ticket History Deleted';
$msg_accounts16 = 'IP Address';
$msg_accounts17 = 'Options';
$msg_accounts18 = 'Notes';
$msg_accounts19 = 'Enable Account';
$msg_accounts20 = 'Auto Generate';
$msg_accounts21 = 'Account Added';
$msg_accounts22 = 'Account Updated';
$msg_accounts23 = 'Send Welcome Email';
$msg_accounts24 = 'Account Added &amp; Email Sent';
$msg_accounts25 = 'Date/Time Options';
$msg_accounts26 = 'Keyword (Name,Email,Notes)';
$msg_accounts27 = 'Date Added (From/To)';
$msg_accounts28 = 'Timezone';
$msg_accounts29 = 'Account Status';
$msg_accounts30 = 'Amount of Tickets Opened is Between';
$msg_accounts31 = 'Reason if Disabled (HTML may be used)';
$msg_accounts32 = 'Tools';
$msg_accounts33 = 'Global Options';
$msg_accounts34 = 'Import Info';
$msg_accounts35 = '{count} Account(s) Imported';
$msg_accounts36 = 'Export to CSV';
$msg_accounts37 = 'Name,Email,IP,Timezone,Tickets'; // For CSV export..
$msg_accounts38 = 'Tick / Disp';
$msg_accounts39 = 'Language Set';
$msg_accounts40 = 'Enable Entry Log';

$msg_add = 'Ticket Info';
$msg_add2 = 'Additional Fields';
$msg_add3 = 'Attachments';
$msg_add4 = 'Assign Staff';
$msg_add5 = 'Account';
$msg_add6 = 'Account Search';
$msg_add7 = 'Nothing found';
$msg_add8 = 'New Ticket Successfully Added - <a style="margin-left:20px" href="?p=view-ticket&amp;id={id}"><i class="fa fa-search fa-fw"></i> View Ticket</a><a style="margin-left:20px" href="?p=edit-ticket&amp;id={id}"><i class="fa fa-edit fa-fw"></i> Edit Ticket</a>';
$msg_add9 = 'Department error. No departments exist or are incorrectly assigned to the logged in user.';
$msg_add10 = 'Assign Later via Assign Tickets Page';
$msg_add11 = 'An error occurred adding this ticket, please refresh screen to try again.';
$msg_add12 = '(If applicable. Includes emails sent to staff via department restrictions)';
$msg_add13 = 'Close Ticket (If set, disables any email notifications)';
$msg_add14 = 'Unable to Render Preview';

$msg_assign7 = '[#{id}] {subject}';

$msg_attachments16 = 'Display/File Name';
$msg_attachments17 = 'Display Name (A-Z)';
$msg_attachments18 = 'Display Name (Z-A)';
$msg_attachments19 = 'Order Sequence Updated';
$msg_attachments20 = 'All Attachments';
$msg_attachments21 = 'Remote Files ONLY';

$msg_backup16 = '{count} Tables';

$msg_bbcode31 = 'Image Display';
$msg_bbcode32 = 'Website';

$msg_customfields33 = 'Required: <span class="highlight">{required}</span> &#8226; Display: <span class="highlight">{display}</span><br>Department(s): <span class="highlight">{depts}</span>';
$msg_customfields34 = 'Field Info';
$msg_customfields35 = 'Options';
$msg_customfields36 = 'Departments';
$msg_customfields37 = 'Instructions/Text (A-Z)';
$msg_customfields38 = 'Instructions/Text (Z-A)';
$msg_customfields39 = 'All Custom Fields';
$msg_customfields40 = 'Ticket Creation';
$msg_customfields41 = 'Ticket Reply';
$msg_customfields42 = 'Admin Reply';
$msg_customfields43 = 'Show Required Only';
$msg_customfields44 = 'Show Ticket Creation Only';
$msg_customfields45 = 'Show Ticket Reply Only';
$msg_customfields46 = 'Show Admin Reply Only';

$msg_dept24 = 'Dept Info';
$msg_dept25 = 'Auto Populate';
$msg_dept26 = 'Manual Assign (Yes)';
$msg_dept27 = 'Manual Assign (No)';
$msg_dept28 = 'Visibility (Yes)';
$msg_dept29 = 'Visibility (No)';

$msg_edit = 'Reply Info';

$msg_home50 = '<a href="?p=accountman">{visitors} Visitor Accounts</a>';
$msg_home51 = '<a href="?p=deptman">{dept} Departments</a> &amp; <a href="?p=levelsman">{levels} Priority Levels</a>';
$msg_home52 = 'Tickets - Awaiting Support Team Assignment';
$msg_home53 = 'View';
$msg_home54 = 'Overview';
$msg_home55 = 'From/To';
$msg_home56 = 'Change Date Range';
$msg_home57 = 'Reload';
$msg_home58 = 'No graph data to display';
$msg_home59 = 'Default Range on Load';
$msg_home60 = 'Days';
$msg_home61 = 'Tickets';
$msg_home62 = 'Disputes';
$msg_home63 = 'Overview of Tickets';
$msg_home64 = 'Compose disabled. There are no other users in the system';

$msg_imap32 = 'Mailbox';
$msg_imap33 = 'Options';
$msg_imap34 = 'Assign Preferences';
$msg_imap35 = 'Mailbox Host (A-Z)';
$msg_imap36 = 'Mailbox Host (Z-A)';
$msg_imap37 = 'Mailbox User (A-Z)';
$msg_imap38 = 'Mailbox User (Z-A)';
$msg_imap39 = 'All Accounts';
$msp_imap40 = 'Spam Filter';
$msp_imap41 = 'Enable Spam Filter';
$msg_imap42 = 'Delete Spam Messages Immediately';
$msg_imap43 = 'Base Settings';
$msg_imap44 = 'Lexer Settings';
$msg_imap45 = 'Degenerator Settings';
$msg_imap46 = 'Spam Relevancy Tokens';
$msg_imap47 = 'Spam Score Deviation';
$msg_imap48 = 'Gary Robinsons X Constant';
$msg_imap49 = 'Gary Robinsons S Constant';
$msg_imap50 = 'Enable Learning';
$msg_imap51 = 'Min Token Length';
$msg_imap52 = 'Max Token Length';
$msg_imap53 = 'Check Pure Numbers';
$msg_imap54 = 'Check URIs';
$msg_imap55 = 'Extract HTML';
$msg_imap56 = 'Enable Multibyte Operations';
$msg_imap57 = 'Internal Encoding Set for Multibyte Operations';
$msg_imap58 = '&nbsp;&nbsp;&nbsp;<span class="eLink"><i class="fa fa-warning fa-fw"></i> Server not compiled with mbstring functions.</span>';
$msg_imap59 = 'Update Spam Filter Settings';
$msg_imap60 = 'Spam Filters Successfully Updated';
$msg_imap61 = 'Reset Learning Filters';
$msg_imap62 = 'Reset Learning Filters';
$msg_imap63 = 'If Deleting, Only Delete With Spam Score Greater Than or Equal To';
$msg_imap64 = 'Learning Options';
$msg_imap65 = 'Add to Learning Filters';
$msg_imap66 = 'Enter Text Block (Email Message Body)';
$msg_imap67 = 'Analyse &amp; Classify as Spam';
$msg_imap68 = 'Analyse &amp; Classify as Ham';
$msg_imap69 = 'Reset Filters Older Than X Days';

$msg_kbase40 = 'Categories';
$msg_kbase41 = 'Questions/Answers';
$msg_kbase42 = 'Question Info';
$msg_kbase43 = 'Category Name (A-Z)';
$msg_kbase44 = 'Category Name (Z-A)';
$msg_kbase45 = 'Order Sequence Updated';
$msg_kbase46 = 'Question (A-Z)';
$msg_kbase47 = 'Question (Z-A)';
$msg_kbase48 = 'All Questions';
$msg_kbase49 = 'Type/Size';
$msg_kbase50 = 'Click to View';
$msg_kbase51 = 'Attachments';
$msg_kbase52 = 'Added';
$msg_kbase53 = 'Results';
$msg_kbase54 = 'Did you find this article helpful?';
$msg_kbase55 = 'Thank You';
$msg_kbase56 = 'Questions';
$msg_kbase57 = 'Least Questions';
$msg_kbase58 = 'Most Questions';
$msg_kbase59 = 'Category Info';
$msg_kbase60 = 'Attachment';
$msg_kbase61 = 'Least Attachments';
$msg_kbase62 = 'Most Attachments';
$msg_kbase63 = 'This question has not been assigned to any category';
$msg_kbase64 = 'At least 1 <a href="?p=faq-cat">category</a> must exist before adding questions';

$msg_levels21 = 'Name (A-Z)';
$msg_levels22 = 'Name (Z-A)';
$msg_levels23 = 'Order Sequence (1-9)';
$msg_levels24 = 'Order Sequence (9-1)';
$msg_levels25 = 'Level Info';

$msg_log7 = 'Login Date / Time';
$msg_log8 = 'IP Address(es)';
$msg_log9 = 'Selected Log(s) Deleted';
$msg_log10 = 'Search';
$msg_log11 = 'All Login Events';
$msg_log12 = 'Visitor Accounts Only';
$msg_log13 = 'Staff Only';
$msg_log14 = 'Visitor';
$msg_log15 = 'Staff';
$msg_log16 = 'Account Type';

$msg_mailbox = 'Inbox';
$msg_mailbox2 = 'Outbox';
$msg_mailbox3 = 'Bin';
$msg_mailbox4 = 'New Message';
$msg_mailbox5 = 'Folders';
$msg_mailbox6 = 'Manage Folders';
$msg_mailbox7 = 'Message';
$msg_mailbox8 = 'Send Private Message';
$msg_mailbox9 = 'New Message Successfully Sent';
$msg_mailbox10 = 'Subject';
$msg_mailbox11 = 'Select Staff Member(s)';
$msg_mailbox12 = 'Update Folders';
$msg_mailbox13 = 'Folders';
$msg_mailbox14 = 'Folders Successfully Updated. {count} Message(s) Deleted';
$msg_mailbox15 = 'Max';
$msg_mailbox16 = 'This folder is currently empty';
$msg_mailbox17 = 'Sent By';
$msg_mailbox18 = 'View Message';
$msg_mailbox19 = 'Read';
$msg_mailbox20 = 'UnRead';
$msg_mailbox21 = 'Move to Bin';
$msg_mailbox22 = 'Delete';
$msg_mailbox23 = 'Empty Bin';
$msg_mailbox24 = 'Actions';
$msg_mailbox25 = '{count} Message(s) Marked as Read';
$msg_mailbox26 = '{count} Message(s) Marked as UnRead';
$msg_mailbox27 = '{count} Message(s) Moved to "{folder}"';
$msg_mailbox28 = '{count} Message(s) Deleted';
$msg_mailbox29 = 'Message(s) Deleted';
$msg_mailbox30 = 'Reply to Message';
$msg_mailbox31 = 'Message Reply Successfully Added';
$msg_mailbox32 = 'Search Results';
$msg_mailbox33 = 'From';
$msg_mailbox34 = 'To';

$msg_open32 = 'Waiing for Support';
$msg_open33 = 'Waiiting for Visitor';
$msg_open34 = '<a href="?p=assign" class="aWarning"><i class="fa fa-warning fa-fw"></i> Not Currently Assigned</a>';
$msg_open35 = '<i class="fa fa-user fa-fw"></i> Assigned to: <span class="highlight">{users}</span>';
$msg_open36 = 'Started By';
$msg_open37 = 'Last Reply';
$msg_open38 = 'Re-Open Selected Ticket(s)';
$msg_open39 = 'Selected Ticket(s) Re-Opened';
$msg_open40 = 'Re-Open Selected Dispute(s)';

$msg_reports15 = 'Keywords';

$msg_response19 = 'Response Info';
$msg_response20 = 'Departments';
$msg_response21 = 'Enable Response';
$msg_response22 = 'Choose CSV File';
$msg_response23 = 'Title (A-Z)';
$msg_response24 = 'Title (Z-A)';
$msg_response25 = 'All Responses';
$msg_response26 = 'in';
$msg_response27 = 'Show Disabled Only';
$msg_response28 = 'Enable/Disable';

$msg_search19 = 'Date Range';
$msg_search20 = 'Filters';
$msg_search21 = 'Update Search Parameters';
$msg_search22 = 'Cancel Search';
$msg_search23 = 'Search Replies (may result in slower search)';
$msg_search24 = 'No Change';
$msg_search25 = 'Export Selected Ticket(s) Stats';

// For ticket export..
// csv header..
$msg_search26 = array(
  'Ticket No',
  'Created By',
  'Email',
  'Created On',
  'First Reply On',
  'First Reply By',
  'Last Reply On',
  'Last Reply By',
  'Agents Assigned',
  'Subject',
  'Department',
  'Ticket Status',
  'Reply Status',
  'Priority',
  'Via',
  'Is Dispute',
  'Total Replies',
  'Total History Actions'
);
// Via options..DO NOT change key..
$msg_search27 = array(
  'standard' => 'Web',
  'imap' => 'Email',
  'api' => 'API'
);
$msg_search28 = 'Waiting for Support Team';
$msg_search29 = 'Waiting for Visitor';

$msg_settings77 = 'Current Server Time';
$msg_settings78 = 'None';
$msg_settings79 = 'TLS';
$msg_settings80 = 'SSL';
$msg_settings81 = 'Enable Dispute System';
$msg_settings82 = 'Offline Reason (HTML may be used)';
$msg_settings83 = 'Status';
$msg_settings84 = 'Default Language';
$msg_settings85 = 'Other Options';
$msg_settings86 = 'Helpdesk Settings';
$msg_settings87 = 'Auto Close';
$msg_settings88 = 'Dispute System';
$msg_settings89 = 'API Settings';
$msg_settings90 = 'Visitors Must Be Logged In To Open Tickets';
$msg_settings91 = 'Ticket System';
$msg_settings92 = 'Account Settings';
$msg_settings93 = 'Enable Create Account Option';
$msg_settings94 = 'HTTP Path to Attachments Folder';
$msg_settings95 = 'Choose';
$msg_settings96 = 'No Restriction';
$msg_settings97 = 'HTTP Path to F.A.Q Attachments Folder';
$msg_settings98 = 'Server Path to F.A.Q Attachments Folder';
$msg_settings99 = 'Max Login Attempts';
$msg_settings100 = 'Ban Time (in Minutes)';
$msg_settings101 = 'Enable Ticket History';
$msg_settings102 = 'Uncompressed - Est.';
$msg_settings103 = 'Do NOT Send Notification(s) if Visitor Closes Ticket with Reply';
$msg_settings104 = 'Email Notification "Reply-To" Address (Optional)';
$msg_settings105 = 'Language &amp; Template Sets';
$msg_settings106 = 'Send Email Notification After Profile Update';
$msg_settings107 = 'Minimum Length For Passwords';
$msg_settings108 = 'Send Email Notification to Admin When New Account is Created Manually';
$msg_settings109 = 'Language / Theme';
$msg_settings110 = 'Enable Entry Log For New Accounts';
$msg_settings111 = 'Logging';
$msg_settings112 = 'Max Entries to Keep for Account Entry Logs';
$msg_settings113 = 'Max Entries to Keep for Staff Entry Logs';
$msg_settings114 = 'Min Digits for Ticket Numbers';
$msg_settings115 = 'Enable Mail';
$msg_settings116 = 'Test Mail';
$msg_settings117 = 'Mail Test: Enter multiple addresses separated with a comma if applicable.';
$msg_settings118 = 'Send';
$msg_settings119 = 'Imap Settings';
$msg_settings120 = 'Enable Imap Debug Log';
$msg_settings121 = 'Imap Query String Parameter';
$msg_settings122 = 'Ini Set Memory Override';
$msg_settings123 = 'Ini Set Timeout Override';
$msg_settings124 = 'Enable API Debug Log';
$msg_settings125 = 'Enable XML Handler';
$msg_settings126 = 'Enable JSON Handler';
$msg_settings127 = 'Add Path';
$msg_settings128 = 'FOLDER_NAME_HERE';
$msg_settings129 = 'After Each Visitor Response, Allow No Futher Replies Until Admin Has Responded';

$msg_spam = 'Accept Selected Ticket(s)';
$msg_spam2 = 'View Ticket';
$msg_spam3 = 'This ticket is currently flagged as a spam ticket. No replies are allowed until the ticket has been accepted via the <a href="?p=spam">spam tickets</a> screen.';
$msg_spam4 = 'Selected Spam Ticket(s) Deleted. Learning Filters Updated if Enabled.';
$msg_spam5 = 'Selected Spam Ticket(s) Accepted. Learning Filters Updated if Enabled.';
$msg_spam6 = 'Skip Filters';
$msg_spam7 = 'Skip Filters - If match is found, message is always ignored and deleted. Comma delimit. Use cautiously.';

$msg_staffprofile = 'Profile Updated';
$msg_staffprofile2 = 'Update Profile';

$msg_tools12 = 'Global Password Reset';
$msg_tools13 = 'Purge Options';
$msg_tools14 = 'Reset and Update Passwords';
$msg_tools15 = 'All Visitor Accounts';
$msg_tools16 = 'All Support Team Accounts (Excluding global admin)';
$msg_tools17 = 'Message';
$msg_tools18 = '<i class="fa fa-check fa-fw"></i> ({count} Visitor(s)<br><i class="fa fa-check fa-fw"></i> {count2} Staff)';
$msg_tools19 = 'Include Disabled Accounts';
$msg_tools20 = 'Source Template';
$msg_tools21 = 'Send Email';
$msg_tools22 = 'Mail Tags';

// Do NOT edit array keys (left)
$msg_tools23 = array(
  '{NAME}' => 'Name',
  '{EMAIL}' => 'Login Email',
  '{PASS}' => 'New Password',
  '{LOGIN_URL}' => 'Login Url',
  '{WEBSITE_NAME}' => 'Helpdesk Name',
  '{WEBSITE_URL}' => 'Helpdesk Url'
);

$msg_tools24 = 'Batch Enable/Disable';
$msg_tools25 = '{count} Account(s) Successfully Deleted';
$msg_tools26 = 'Clear accounts with NO tickets X days old';
$msg_tools27 = 'Send Email Notification About Account Removal';
$msg_tools28 = 'Enable Selected';
$msg_tools29 = 'Disable Selected';
$msg_tools30 = 'All Staff Accounts (accept admin, ID:1)';
$msg_tools31 = 'All Visitor Accounts';
$msg_tools32 = 'All Custom Fields';
$msg_tools33 = 'All Standard Responses';
$msg_tools34 = 'All Imap Accounts';
$msg_tools35 = 'All FAQ Categories';
$msg_tools36 = 'All FAQ Questions';
$msg_tools37 = 'Selected Areas Enabled';
$msg_tools38 = 'Selected Areas Disabled';

$msg_user73 = 'Personal';
$msg_user74 = 'Admin Access';
$msg_user75 = 'Ticket Access';
$msg_user76 = 'Preferences';
$msg_user77 = 'Responses';
$msg_user78 = 'Most Responses';
$msg_user79 = 'Least Responses';
$msg_user80 = 'With Notifications Disabled';
$msg_user81 = 'With Delete Privileges Enabled';
$msg_user82 = 'With Notepad Access Enabled';
$msg_user83 = 'Can View Only Assigned Tickets';
$msg_user84 = 'Other Options';
$msg_user85 = 'Additional Notification Email Addresses';
$msg_user86 = 'Performance';
$msg_user87 = 'Responses';
$msg_user88 = 'Save Image';
$msg_user89 = 'Date Posted';
$msg_user90 = 'Can View Ticket History';
$msg_user91 = 'Enable Entry Log';
$msg_user92 = 'Selected Range';
$msg_user93 = 'Selected Range (1 Year Earlier)';
$msg_user94 = 'There are no responses to display for this staff member for the selected date range.';
$msg_user95 = 'Enable Mailbox System';
$msg_user96 = 'Can Delete Messages in Mailbox';
$msg_user97 = 'On Login, Go to Mailbox if at least 1 Unread Message in Inbox';
$msg_user98 = 'Send Message Notification to Recipient Email Address(es)';
$msg_user99 = 'Max Folders';
$msg_user100 = 'Additional Page Rules (Comma delimit)';
$msg_user101 = 'Can Merge Tickets';
$msg_user102 = 'Enable Email Digest';
$msg_user103 = 'Include Tickets Awaiting Assignment';
$msg_user104 = 'Email Digest';
$msg_user105 = 'Run Now';
$msg_user106 = 'Auto Purge Messages in Bin Every X Days (includes Unread)';
$msg_user107 = 'Can Update Profile';
$msg_user108 = 'Can View Help Link to Documentation';

$msg_versioncheck = 'Version Check';
$msg_versioncheck2 = 'Please wait..';

$msg_viewticket95 = 'Viewing Ticket';
$msg_viewticket96 = 'Viewing Dispute';
$msg_viewticket97 = 'Custom Fields';
$msg_viewticket98 = 'ID';
$msg_viewticket99 = 'Update Notes';
$msg_viewticket100 = 'Close';
$msg_viewticket101 = 'Close Preview';
$msg_viewticket102 = 'Merge With Other Ticket - Enter Keyword to Locate Previous Ticket';
$msg_viewticket103 = 'If you wish to save this response as  a standard response, enter response title below';
$msg_viewticket104 = 'Specify departments applicable to this new response';
$msg_viewticket105 = 'Post Privileges';
$msg_viewticket106 = 'Posts';
$msg_viewticket107 = 'Dept';
$msg_viewticket108 = 'ORIGINAL TICKET';
$msg_viewticket109 = 'Write to Ticket History';
$msg_viewticket110 = 'Ticket History';
$msg_viewticket111 = 'There is currently no history for this ticket';
$msg_viewticket112 = 'Export History to CSV';
$msg_viewticket113 = 'Date,Time,Action,IP';
$msg_viewticket114 = 'Selected User(s) Removed From Dispute';
$msg_viewticket115 = 'Notify';
$msg_viewticket116 = 'Post Privileges ON';
$msg_viewticket117 = 'No accounts found, please try again.';
$msg_viewticket118 = 'Clear All';
$msg_viewticket119 = 'Select Ticket';
$msg_viewticket120 = 'Edit Ticket';
$msg_viewticket121 = 'Update Staff';
$msg_viewticket122 = 'Reloading ticket #{id}...please wait..';

$msg_script43 = 'Actions';

// DO NOT alter array keys (left values)..
$msg_script44 = array(
  'name_asc' => 'Name (A-Z)',
  'name_desc' => 'Name (Z-A)',
  'subject_asc' => 'Subject (A-Z)',
  'subject_desc' => 'Subject (Z-A)',
  'id_asc' => 'Ticket ID (0-9)',
  'id_desc' => 'Ticket ID (9-0)',
  'pr_asc' => 'Priority (A-Z)',
  'pr_desc' => 'Priority (Z-A)',
  'dept_asc' => 'Department (A-Z)',
  'dept_desc' => 'Department (Z-A)',
  'rev_asc' => 'Date Updated (Newest)',
  'rev_desc' => 'Date Updated (Oldest)',
  'date_asc' => 'Date Added (Newest)',
  'date_desc' => 'Date Added (Oldest)'
);

$msg_script45 = 'Order By';
$msg_script46 = 'Add';
$msg_script47 = 'Remove';
$msg_script48 = 'Enabled';
$msg_script49 = 'Disabled';
$msg_script50 = 'Per Page';
$msg_script51 = 'Show';
$msg_script52 = 'Oops';
$msg_script53 = 'Back to Admin';
$msg_script54 = 'Back to Main Page';
$msg_script55 = 'Please wait..';
$msg_script56 = 'Ticket: #{ticket}' . mswDefineNewline() . 'Subject: {subject}';
$msg_script57 = 'Powered by';
$msg_script58 = 'Thanks to';

// Error related or action confirmation..
$msg_script_action = 'Confirm Action..\n\nAre you sure?';
$msg_script_action2 = 'Error, cannot connect to mailbox. Check connection details or enter folder manually..';
$msg_script_action3 = 'Fatal Error: Imap functions NOT enabled on server.';
$msg_script_action4 = 'User(s) in Dispute';
$msg_script_action5 = 'Warning: If the assigned flag is removed and assigned tickets exist, they will revert to standard tickets viewable by department only.';
$msg_script_action6 = 'Invalid email address..';
$msg_script_action7 = 'No account found, please check email..';
$msg_script_action8 = 'Thank you, please check your inbox at "{email}"';
$msg_script_action9 = '{count} Email(s) Sent, please check inbox(es)..';
$msg_script_action10 = 'This is a test message, sent via the {website} support system.';

// API..
$msg_api = '{count} tickets added';
$msg_api2 = 'No tickets added, view log file if enabled';
$msg_api3 = '{count} accounts added';
$msg_api4 = 'No accounts added, view log file if enabled';

// Email subjects (DO NOT alter array keys (left)..
$emailSubjects = array(
  'add' => '[{website}] Support Account - Please Read',
  'reset' => '[{website}] Password Reset - Please Read',
  'dispute' => '[#{ticket}] Dispute Opened Against You - Please Read',
  'dispute-notify-update' => '[#{ticket}] Ticket/Dispute Update - Please Read',
  'new-ticket' => '[#{ticket}] New Support Ticket - Please Read',
  'ticket-assign' => 'New Support Tickets Assigned - Please Read',
  'new-ticket-team' => '[#{ticket}] New Staff Created Support Ticket - Please Read',
  'admin-reply' => '[#{ticket}] Support Ticket Updated',
  'new-account' => '[{website}] New Support Account',
  'new-ticket' => '[#{ticket}] New Ticket Created',
  'new-ticket-vis' => '[#{ticket}] New Ticket Confirmation',
  'reply-notify' => '[#{ticket}] Ticket Reply Notification',
  'dispute-notify' => '[#{ticket}] Dispute Reply Notification',
  'new-password' => '[{website}] New Password Enclosed',
  'profile-update' => '[{website}] Profile Update Confirmation',
  'acc-verify' => '[{website}] Support Account Verification',
  'acc-verified' => '[{website}] Account Active - Information',
  'new-acc-notify' => '[{website}] New Account Active',
  'email-digest' => '[{website}] Email Digest Ticket Report',
  'db-backup' => '[{website}] Database Backup',
  'auto-close' => '[{website}] Support Ticket Closed',
  'auto-close-vis' => '[{website}] {count} Support Ticket(s) Closed',
  'test-message' => '[{website}] Email Test Message',
  'acc-deletion' => '[{website}] Notification of Account Removal',
  'mailbox-notify' => '[{website}] Private Message Notification',
  'spam-notify' => '[{website}] Ticket On Hold Notification',
  'team-account' => '[{website}] New Support Team Account'
);

// Ticket actions for history log..(DO NOT alter array keys (left)..
$msg_ticket_history = array(
  'assign' => 'Ticket assigned to {users} by {admin}',
  'assign-update' => 'Ticket assignment updated by {admin}. Assigned to: {users}',
  'team-reply-add' => 'Reply ID {id} added by {user}',
  'team-reply-add-merge' => 'Reply ID {id} added and ticket id #{from} merged to #{to} by {user}',
  'reply-edit' => 'Reply ID {id} edited by {user}',
  'reply-delete' => 'Reply ID {id} deleted by {user}. Originally posted by {poster}.',
  'dis-user-rem' => '{users} removed from dispute by {admin}',
  'dis-user-add' => '{users} added to dispute by {admin}',
  'new-ticket-visitor' => 'New ticket created by {visitor}',
  'new-ticket-visitor-imap' => 'New ticket created by {visitor} via email.',
  'new-ticket-visitor-api' => 'New ticket created from {visitor} via api.',
  'new-ticket-admin' => 'New ticket entered manually by {user}',
  'new-ticket-admin-close' => 'Ticket closed by {user}',
  'edit-ticket' => 'Ticket edited by {user}',
  'ticket-notes-edit' => 'Ticket notes updated by {user}',
  'ticket-status-open' => 'Ticket opened by {user}',
  'ticket-status-close' => 'Ticket closed by {user}',
  'ticket-status-lock' => 'Ticket locked by {user}',
  'ticket-status-ticket' => 'Dispute ticket converted back to standard ticket by {user}',
  'ticket-status-dispute' => 'Ticket converted to dispute by {user}',
  'ticket-status-reopen' => 'Ticket re-opened by {user}',
  'edit-ticket-search' => 'Ticket edited via search screen by {user}. Department: {dept}, Priority: {priority}, Status: {status}',
  'vis-ticket-close' => 'Ticket closed by {user}',
  'vis-ticket-open' => 'Ticket re-opened by {user}',
  'vis-reply-add' => 'Reply ID {id} added by {visitor}',
  'vis-reply-add-imap' => 'Reply ID {id} added by {visitor} via email.',
  'vis-ticket-add-spam' => 'Ticket opened by {visitor} but flagged as spam initially. Awaiting acceptance.',
  'ticket-spam-accept' => 'Ticket previously classified as spam now accepted by {user}',
  'ticket-auto-close' => 'Ticket auto closed by system. No further replies after {days} day(s).'
);

// Update action text..(DO NOT alter array keys (left)..
$msg_ticket_actioned = array(
  'open' => 'Ticket Successfully Opened',
  'close' => 'Ticket Successfully Closed',
  'lock' => 'Ticket Successfully Locked',
  'ticket' => 'Dispute Successfully Converted to Ticket',
  'dispute' => 'Ticket Successfully Converted to Dispute'
);

?>