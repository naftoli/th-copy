<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Check log in..
if (MS_PERMISSIONS == 'guest' || !isset($LI_ACC->id)) {
  header("Location:index.php?p=login");
  exit;
}

$title = $msg_header15;

include(PATH . 'control/header.php');

// Show..
$tpl = new Savant3();
$tpl->assign('TXT', array(
  $msg_header3,
  $msg_public_account,
  $msg_public_account2,
  $msg_public_account3,
  $msg_public_account4,
  $msg_public_create4,
  $msg_public_account5,
  $msg_main3,
  $msg_public_create3,
  $msg_public_profile3,
  $msg_public_profile4,
  $msg_public_profile6,
  $msg_public_profile7,
  $msg_public_profile8,
  $msg_public_profile9,
  $msg_header15
));
$tpl->assign('TIMEZONES', $timezones);
$tpl->assign('LANGUAGES', $MSYS->languages());
$tpl->assign('ACCOUNT', (array) $LI_ACC);

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-profile.tpl.php');

include(PATH . 'control/footer.php');

?>