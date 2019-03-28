<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

// Department check for filter..
if (isset($_GET['dept'])) {
  // Are we viewing assigned department?
  if (substr($_GET['dept'], 0, 1) == 'u') {
    if ($MSTEAM->id > 1 && $MSTEAM->id != substr($_GET['dept'], 1)) {
      $HEADERS->err403(true);
    }
  } else{
    if (mswDeptPerms($MSTEAM->id, $_GET['dept'], $userDeptAccess) == 'fail') {
      $HEADERS->err403(true);
    }
  }
}

// Call relevant classes..
include_once(REL_PATH . 'control/classes/class.tickets.php');
$MSPTICKETS           = new tickets();
$MSPTICKETS->settings = $SETTINGS;
$MSPTICKETS->datetime = $MSDT;
$title                = $msg_adheader5;
$loadiBox             = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-open.php');
include(PATH . 'templates/footer.php');

?>
