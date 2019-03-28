<?php

if (!defined('PARENT') || USER_EDIT_R_PRIV == 'no') {
  $HEADERS->err403(true);
}

// Check digit..
mswCheckDigit($_GET['id'], true);

// Get reply..
$REPLY = mswGetTableData('replies', 'id', $_GET['id']);

// Checks..
if (!isset($REPLY->id)) {
  $HEADERS->err404(true);
}

// Get ticket data..
$SUPTICK = mswGetTableData('tickets', 'id', $REPLY->ticketID);

// Checks..
if (!isset($SUPTICK->id)) {
  $HEADERS->err403(true);
}

// Department check..
if (mswDeptPerms($MSTEAM->id, $SUPTICK->department, $userDeptAccess) == 'fail') {
  $HEADERS->err403(true);
}

$title      = $msg_viewticket36;
$loadBBCSS  = true;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-edit-reply.php');
include(PATH . 'templates/footer.php');

?>