<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Logout..
if (isset($_GET['lo'])) {
  $_SESSION[mswEncrypt(SECRET_KEY) . '_msw_support'] = '';
  unset($_SESSION[mswEncrypt(SECRET_KEY) . '_msw_support'], $_SESSION['portalEmail']);
  // Should we show the login message..
  if (DISPLAY_LOGIN_MSG) {
    $title = $msg_header2;
    include(PATH . 'control/header.php');
    // Show..
    $tpl = new Savant3();
    $tpl->assign('TXT', array(
      $msadminlangpublic[9],
      $msadminlangpublic[10],
      $msg_adheader11
    ));

    // Global vars..
    include(PATH . 'control/lib/global.php');

    // Load template..
    $tpl->display('content/' . MS_TEMPLATE_SET . '/account-logout.tpl.php');

    include(PATH . 'control/footer.php');
  } else {
    header("Location: ?p=login");
  }
  exit;
}

if (MS_PERMISSIONS != 'guest') {
  header("Location: ?p=dashboard");
  exit;
}

$title = $msg_public_login;

include(PATH . 'control/header.php');

// Show..
$tpl = new Savant3();
$tpl->assign('TXT', array(
  $msg_public_login,
  $msg_public_login2,
  $msg_main3,
  $msg_main4,
  $msg_public_login3,
  $msg_main9,
  $msg_main5,
  $msadminlangpublic[1]
));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-login.tpl.php');

include(PATH . 'control/footer.php');

?>