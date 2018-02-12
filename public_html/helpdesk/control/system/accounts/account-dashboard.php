<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Check log in..
if (MS_PERMISSIONS == 'guest' || !isset($LI_ACC->id)) {
  header("Location:index.php?p=login");
  exit;
}

$title = $msg_header3;
$tz    = ($LI_ACC->timezone ? $LI_ACC->timezone : $SETTINGS->timezone);

include(PATH . 'control/header.php');

// Show..
$tpl = new Savant3();
$tpl->assign('CATEGORIES', $FAQ->menu(array(
  'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
)));
$tpl->assign('TXT', array(
  $msg_header13,
  $MSDT->mswDateTimeDisplay(strtotime(date('Y-m-d', $MSDT->mswUTC())), $SETTINGS->dateformat, $tz),
  $msg_public_dashboard1,
  $msg_public_dashboard2,
  $msg_public_dashboard3,
  $msg_public_dashboard4,
  $msg_public_dashboard5,
  str_replace('{name}', mswSafeDisplay($LI_ACC->name), $msg_public_dashboard11),
  $msg_public_dashboard12,
  $msg_main2,
  $msg_public_account4,
  str_replace('{count}', $SETTINGS->popquestions, $msg_main10),
  str_replace('{count}', $SETTINGS->popquestions, $msg_public_main3),
  $msadminlangpublic[7],
  $msg_pkbase7,
  $msg_pkbase,
  mswSafeDisplay($msadminlang3_1faq[4])
));
$tpl->assign('TICKETS', $MSTICKET->ticketList(MS_PERMISSIONS, array(
  0,
  99999
), false, 'AND `ticketStatus` = \'open\''));
$tpl->assign('DISPUTES', $MSTICKET->disputeList(MS_PERMISSIONS, $LI_ACC->id, array(
  0,
  99999
), false, 'AND `ticketStatus` = \'open\''));
$tpl->assign('FEATURED', $FAQ->questions(array(
  'id' => 0,
  'limit' => 0,
  'search' => array(),
  'orderor' => '`' . DB_PREFIX . 'faq`.`orderBy`',
  'queryadd' => 'GROUP BY `' . DB_PREFIX . 'faqassign`.`question`',
  'flag' => 'AND `' . DB_PREFIX . 'faq`.`featured` = \'yes\'',
  'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
)));
$tpl->assign('POPULAR', $FAQ->questions(array(
  'id' => 0,
  'limit' => 0,
  'search' => array(),
  'orderor' => '`' . DB_PREFIX . 'faq`.`kviews` DESC',
  'queryadd' => 'GROUP BY `' . DB_PREFIX . 'faqassign`.`question`',
  'flag' => 'AND `' . DB_PREFIX . 'faq`.`featured` = \'no\'',
  'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
)));
$tpl->assign('LATEST', $FAQ->questions(array(
  'id' => 0,
  'limit' => 0,
  'search' => array(),
  'orderor' => '`' . DB_PREFIX . 'faq`.`ts` DESC',
  'queryadd' => 'GROUP BY `' . DB_PREFIX . 'faqassign`.`question`',
  'flag' => 'AND `' . DB_PREFIX . 'faq`.`featured` = \'no\'',
  'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
)));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-dashboard.tpl.php');

include(PATH . 'control/footer.php');

?>