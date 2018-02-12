<?php

//-------------------------------------------------------------
// USER DEFINED
// Edit values on right, DO NOT change values in capitals
//-------------------------------------------------------------

/*
 Relative path to files in the support root. In most cases this will NOT need changing.
 Some servers may require the full server path. If this is the case enter the path below.
 Example:
 define('REL_PATH', '/home/serverpath/public_html/support/');
*/
define('REL_PATH', '../');

/*
 If a support team member clicks on an email ticket link and is directed to the admin log in
 page, do you want them to be directed to the ticket after login? Can save time locating
 tickets and be a big time saver.
 1 = Enabled, 0 = Disabled
*/
define('REDIRECT_TO_TICKET_ON_LOGIN', 1);

/*
  TEXT CUT OFF FOR TEAM RESPONSES PAGE
  How much text to display for comments on support team member ressponses
  0 would display all text
*/
define('TEAM_RESPONSE_TXT_LIMIT', 250);

/*
  TICKET SEARCH AUTO CHECK OPTIONS
  Which ticket type checkboxes should be auto checked on search tickets page
*/
define('SEARCH_AUTO_CHECK_TICKETS', 'yes');
define('SEARCH_AUTO_CHECK_DISPUTES', 'yes');
define('SEARCH_AUTO_CHECK_RESPONSES', 'no');

/* IBOX WINDOW SIZES
   Set sizes for ibox pop up windows
*/
define('IBOX_NOTES_WIDTH', 700);
define('IBOX_NOTES_HEIGHT', 400);
define('IBOX_RESPONSE_WIDTH', 700);
define('IBOX_RESPONSE_HEIGHT', 500);
define('IBOX_FAQ_WIDTH', 700);
define('IBOX_FAQ_HEIGHT', 500);
define('IBOX_QVIEW_WIDTH', 700);
define('IBOX_QVIEW_HEIGHT', 500);
define('IBOX_PAGE_WIDTH', 700);
define('IBOX_PAGE_HEIGHT', 500);

/*
  AUTO CREATE API KEY - KEY LENGTH
  Max 100 characters
*/
define('API_KEY_LENGTH', 30);

/*
  ENABLE SOFTWARE VERSION CHECK
  Displays on the top bar and is an easy check option to see if new versions have
  been release. You may wish to disable this for clients.
  0 = Disabled, 1 = Enabled
*/
define('DISPLAY_SOFTWARE_VERSION_CHECK', 1);

/*
  REPORTS
  Default previous range for initial reports screen. Supports strtotime
*/
define('REP_DEF_RANGE_OLD', '-6 months');

/*
  ADMIN MAX ATTACHMENT BOXES
  Admin override for max attachments. Can be higher than visitor restriction.
  Applies only in commercial version.
*/
define('ADMIN_ATTACH_BOX_OVERRIDE', 20);

/*
  SHOW ADMIN DASHBOARD GRAPH
  Do you want to show the admin dashboard graph? 1 = Yes, 0 = No
*/
define('SHOW_ADMIN_DASHBOARD_GRAPH', 1);

/*
  MAILBOX COUNT REFRESH TIME (in milliseconds)
  The amount of time the system checks for unread mailbox messages. Set to 0 to disable.
*/
define('MAILBOX_UNREAD_REFRESH_TIME', 30000);

/*
  REPLY TEXT CUT OFF LIMIT
  For user replies screen. Displays x chars for comments. 0 to disable
*/
define('TEAM_REPLY_COMM_LIMIT', 250);

/*
  STANDARD RESPONSES SELECT TEXT DISPLAY LIMIT
  Restrict display in standard response drop downs to this limit
*/
define('STANDARD_RESPONSE_DD_TEXT_LIMIT', 115);

/*
 CSV UPLOAD PREFERENCES
 Set default options for file import CSVs
*/
define('CSV_IMPORT_DELIMITER', ',');
define('CSV_IMPORT_ENCLOSURE', '"');
define('CSV_MAX_LINES_TO_READ', 999999);

/*
  CATEGORIES SUMMARY TEXT DISPLAY LIMIT
  Restrict display for category summary in admin
*/
define('CATEGORIES_SUMMARY_TEXT_LIMIT', 115);

/*
  ADMIN MERGE REDIRECT TIME
  Time in seconds before screen redirects if tickets are merged
*/
define('TICK_MERGE_RDR_TIME', 3);

/*
  IP LOOKUP
  Service for url lookup. Use {ip} where IP address must be in url
*/
define('IP_LOOKUP', 'http://whatismyipaddress.com/ip/{ip}');

?>