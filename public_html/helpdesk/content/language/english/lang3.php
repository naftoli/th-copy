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

$msg_newticket47 = 'Staff Assigned';

$msg_offline = 'Helpdesk Currently Offline';

$msg_adheader32 = 'Assign Tickets';
$msg_adheader33 = 'Attachments';
$msg_adheader34 = 'Reports';
$msg_adheader35 = 'Priority Levels';
$msg_adheader36 = 'Password Reset';

$msg_bbcode28 = 'Media Tags';
$msg_bbcode29 = 'Video Display';
$msg_bbcode30 = 'code here..';

$msg_dept22 = 'Manually Assign Tickets to Users';
$msg_dept23 = 'Manual Assign: <span class="highlight">{manual}</span> &#8226; Visible: <span class="highlight">{visible}</span>';

$msg_levels = 'Add';
$msg_levels2 = 'Add New Level';
$msg_levels3 = 'Delete';
$msg_levels4 = 'Current Levels';
$msg_levels5 = 'Update Level';
$msg_levels6 = 'Edit';
$msg_levels7 = 'Priority Level Added';
$msg_levels8 = 'Update Order Sequence';
$msg_levels9 = 'Delete Selected';
$msg_levels10 = 'Update';
$msg_levels11 = 'Cancel';
$msg_levels12 = 'Priority Level Updated';
$msg_levels13 = 'Selected Priority Level(s) Deleted';
$msg_levels15 = 'Display on Open New Ticket Page';
$msg_levels16 = 'There are currently 0 priority levels';
$msg_levels18 = 'Level Display Name';
$msg_levels19 = 'New Priority Level';
$msg_levels20 = 'Order Sequence Updated';

$msg_assign = '<span class="highlight">#{ticket}</span> &#8226; Opened: <span class="highlight">{date}</span> &#8226; Priority: <span class="highlight">{priority}</span> &#8226; Dept: <span class="highlight">{dept}</span> &#8226; Replies: <span class="highlight">{replies}</span><span class="floatright"><a href="?p=view-ticket&amp;id={id}" onclick="window.open(this);return false" title="View Full Ticket">View Full Ticket</a></span>';
$msg_assign2 = '<span class="highlight"><b>{count}</b></span> ticket(s) awaiting assignment to support staff. Use checkboxes to assign users. Note that further notifications are only sent to assigned users, so if you are the administrator and want to receive ticket reply notifications, you should always add yourself to the assigned list.';
$msg_assign3 = 'Assign To';
$msg_assign4 = 'Selected Ticket(s) Assigned';
$msg_assign5 = 'If assigning tickets, send email notification to assigned support team user(s)? Will not be sent to logged in user.';
$msg_assign6 = 'Assign Selected Tickets';

$msg_attachments = 'Attachments';
$msg_attachments2 = 'Add Attachments';
$msg_attachments3 = 'Display Name';
$msg_attachments4 = 'Remote File(s)';
$msg_attachments5 = 'Browse for Local File';
$msg_attachments6 = 'Add Boxes';
$msg_attachments7 = 'Remove Boxes';
$msg_attachments9 = 'There are currently no F.A.Q attachments to display';
$msg_attachments10 = '{count} attachment(s) Added';
$msg_attachments11 = '<span class="highlight">{type}</span>, <span class="highlight">{size}</span>';
$msg_attachments12 = 'Update Attachment';
$msg_attachments13 = 'Attachment Updated';
$msg_attachments14 = 'Selected Attachment(s) Deleted';

$msg_customfields31 = 'Apply to Department(s)';
$msg_customfields32 = 'All';

$msg_home46 = 'New Tickets to be Assigned';
$msg_home47 = 'New Disputes to be Assigned';
$msg_home48 = '<a href="?p=imapman">{imap} Imap Accounts</a>';
$msg_home49 = '<a href="?p=fieldsman">{fields} Custom Fields</a>';

$msg_kbase36 = 'Parent Category';
$msg_kbase37 = 'Sub Category Of';
$msg_kbase38 = 'Category Type';
$msg_kbase39 = 'Status';

$msg_messenger7 = '(Use {name} in message to personalise)';

$msg_open31 = 'Assigned to';

$msg_passreset = 'Reset user login details below. Leave password fields blank to keep current password.';
$msg_passreset2 = 'Email';
$msg_passreset3 = 'Password';
$msg_passreset4 = 'Update Login Details';
$msg_passreset5 = 'Send Email Notification to Users';
$msg_passreset6 = 'Login Details Updated';
$msg_passreset7 = 'Name/Alias';

$msg_reports2 = 'From';
$msg_reports3 = 'To';
$msg_reports4 = 'by Day';
$msg_reports5 = 'by Month';
$msg_reports6 = 'Show';
$msg_reports7 = 'Date';
$msg_reports8 = 'Open Tickets';
$msg_reports9 = 'Closed Tickets';
$msg_reports10 = 'Open Disputes';
$msg_reports11 = 'Closed Disputes';
$msg_reports12 = 'Totals';
$msg_reports13 = 'There are currently no logs to display.';
$msg_reports14 = 'Export to CSV';

$msg_response18 = 'Last Updated';

$msg_settings70 = 'Enable Help Desk';
$msg_settings71 = 'Enabled';
$msg_settings72 = 'Disabled';
$msg_settings73 = 'Auto Enable on Date';
$msg_settings74 = 'Help Desk Status';
$msg_settings75 = 'Email Notification for Auto Closed Tickets';
$msg_settings76 = 'Rename Attachments';

$msg_search14 = 'Batch Update Selected Ticket(s)';
$msg_search15 = 'Department (No Change)';
$msg_search16 = 'Selected Ticket(s) Updated';
$msg_search17 = 'Priority (No Change)';
$msg_search18 = 'Status (No Change)';

$msg_user69 = 'or View Assigned Tickets ONLY';
$msg_user70 = 'Timezone';
$msg_user71 = 'All Privileges';

$msg_viewticket92 = 'Assigned To';
$msg_viewticket93 = 'Attach File';
$msg_viewticket94 = 'Not Yet Assigned';

$msg_script40 = 'Auto Cron Completed, {count} Ticket(s) Closed';
$msg_script41 = 'Return to Previous Screen';
$msg_script42 = array(
  'First',
  'Prev',
  'Next',
  'Last'
); // Pagination

$bb_code_buttons = array(
  'Image',
  'Email',
  'Link',
  'More..',
  'Enter Image Url',
  'Enter Email Address',
  'Enter Hyperlink',
  'Enter YouTube Video Code',
  'Enter Vimeo Video Code'
);

?>