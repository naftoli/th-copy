<?php

/*
CUSTOM MAIL HEADERS
Custom mail headers should always start 'X-'. Array key is custom header name and array
value is the custom header value. Example:

$customMailHeaders = array(
'X-Custom'  => 'Value',
'X-Custom2' => 'Value 2'
);
*/

$customMailHeaders = array();

/*
GLOBAL MAIL TAGS
Tags here are sent to ALL emails..
*/

$MSMAIL->smtp_host  = $SETTINGS->smtp_host;
$MSMAIL->smtp_user  = $SETTINGS->smtp_user;
$MSMAIL->smtp_pass  = $SETTINGS->smtp_pass;
$MSMAIL->smtp_port  = $SETTINGS->smtp_port;
$MSMAIL->debug      = $SETTINGS->smtp_debug;
$MSMAIL->smtp_sec   = $SETTINGS->smtp_security;
$MSMAIL->charset    = $mail_charset;
$MSMAIL->xheaders   = $customMailHeaders;
$MSMAIL->config     = (array) $SETTINGS;
$MSMAIL->mailSwitch = $SETTINGS->enableMail;
$MSMAIL->addTag('{DATE}', $MSDT->mswDateTimeDisplay(0, $SETTINGS->dateformat));
$MSMAIL->addTag('{TIME}', $MSDT->mswDateTimeDisplay(0, $SETTINGS->timeformat));
$MSMAIL->addTag('{WEBSITE_NAME}', $SETTINGS->website);
$MSMAIL->addTag('{WEBSITE_URL}', $SETTINGS->scriptpath);
$MSMAIL->addTag('{ADMIN_FOLDER}', $SETTINGS->afolder);
$MSMAIL->addTag('{IP}', mswIPAddresses());

?>