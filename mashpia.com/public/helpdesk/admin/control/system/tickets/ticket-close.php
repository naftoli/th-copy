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

if (isset($_POST['re-open'])) {
  $_POST['ticket'] = $_POST['id'];
  $rows            = $MSTICKET->reOpenTicket();
  // If rows were affected, write log for each ticket..
  if ($rows > 0) {
    foreach ($_POST['ticket'] AS $tID) {
      $MSTICKET->historyLog($tID, str_replace(array(
        '{user}'
      ), array(
        $MSTEAM->name
      ), $msg_ticket_history['ticket-status-reopen']));
    }
  }
  $OK2 = true;
}

// Call relevant classes..
include_once(REL_PATH . 'control/classes/class.tickets.php');
$MSPTICKETS           = new tickets();
$MSPTICKETS->settings = $SETTINGS;
$MSPTICKETS->datetime = $MSDT;
$title                = $msg_adheader6;
$loadiBox             = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-closed.php');
include(PATH . 'templates/footer.php');

?>