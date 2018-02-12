<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

$title = $msg_public_login5;

include(PATH . 'control/header.php');

$tpl = new Savant3();
$tpl->assign('CHARSET', $msg_charset);
$tpl->assign('TITLE', $msg_public_login5);
$tpl->assign('TXT', array(
  $msg_public_login5,
  ($LI_ACC->reason ? mswCleanData($LI_ACC->reason) : $msg_main20)
));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-suspended.tpl.php');

include(PATH . 'control/footer.php');

?>