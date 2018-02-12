<?php

if (!defined('PARENT') || (!in_array('add', $userAccess) && $MSTEAM->id != '1') || USER_EDIT_T_PRIV == 'no') {
  $HEADERS->err403(true);
}

// Check digit..
mswCheckDigit($_GET['id'], true);

// Get ticket data..
$SUPTICK = mswGetTableData('tickets', 'id', $_GET['id']);

// Checks..
if (!isset($SUPTICK->id)) {
  $HEADERS->err404(true);
  exit;
}

// Department check..
if (mswDeptPerms($MSTEAM->id, $SUPTICK->department, $userDeptAccess) == 'fail') {
  $HEADERS->err403(true);
}

$title      = str_replace('{ticket}', mswTicketNumber($SUPTICK->id), $msg_viewticket20);
$loadBBCSS  = true;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-edit.php');
include(PATH . 'templates/footer.php');

?>