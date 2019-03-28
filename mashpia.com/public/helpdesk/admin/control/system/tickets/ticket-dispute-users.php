<?php

if (!defined('PARENT') || (!isset($_GET['disputeUsers']) && !isset($_GET['changeState'])) || $SETTINGS->disputes == 'no') {
  $HEADERS->err403(true);
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

// Check digit..
mswCheckDigit($_GET['disputeUsers'], true);

// Load ticket data..
$SUPTICK = mswGetTableData('tickets', 'id', $_GET['disputeUsers']);

// Checks..
if (!isset($SUPTICK->id)) {
  $HEADERS->err404(true);
  exit;
}

// Department check..
if (mswDeptPerms($MSTEAM->id, $SUPTICK->department, $userDeptAccess) == 'fail') {
  $HEADERS->err403(true);
}

$title = $msg_disputes8 . ' (#' . mswTicketNumber($_GET['disputeUsers']) . ')';

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-dispute-users.php');
include(PATH . 'templates/footer.php');

?>