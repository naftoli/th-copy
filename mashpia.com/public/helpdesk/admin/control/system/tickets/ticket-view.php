<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

define('DROPZONE_LOADER', 1);

// Access..
if (!in_array('assign', $userAccess) && !in_array('open', $userAccess) && !in_array('close', $userAccess) && !in_array('search', $userAccess) && !in_array('odis', $userAccess) && !in_array('cdis', $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

// Merge redirect?
if (isset($_GET['merged'])) {
  $title      = $msadminlang3_1adminviewticket[19];
  $ID         = (int) $_GET['merged'];
  $SUPTICK    = mswGetTableData('tickets', 'id', $ID);
  $metaReload = '<meta http-equiv="refresh" content="' . TICK_MERGE_RDR_TIME . ';url=index.php?p=view-ticket&id=' . $ID . '">';
  include(PATH . 'templates/header.php');
  include(PATH . 'templates/system/tickets/tickets-merge-msg.php');
  include(PATH . 'templates/footer.php');
  exit;
}

// Export history..
if (isset($_GET['exportHistory']) && $SETTINGS->ticketHistory == 'yes' && $MSTEAM->ticketHistory == 'yes') {
  mswCheckDigit($_GET['exportHistory'], true);
  // Does ticket exists..
  $SUPTICK = mswGetTableData('tickets', 'id', $_GET['exportHistory']);
  if (!isset($SUPTICK->id)) {
    $HEADERS->err404(true);
  }
  // Check permissions for this log..
  if (mswDeptPerms($MSTEAM->id, $SUPTICK->department, $userDeptAccess) == 'fail') {
    $HEADERS->err403(true);
  }
  include_once(REL_PATH . 'control/classes/class.download.php');
  $MSDL = new msDownload();
  $MSTICKET->exportTicketHistory($MSDL, $MSDT);
}

// Download attachments..
if (isset($_GET['attachment'])) {
  mswCheckDigit($_GET['attachment'], true);
  // Does attachment exist..
  $A_DAT = mswGetTableData('attachments', 'id', $_GET['attachment']);
  if (!isset($A_DAT->id)) {
    $HEADERS->err404(true);
  }
  // Check permissions for this attachment..
  if (mswDeptPerms($MSTEAM->id, $A_DAT->department, $userDeptAccess) == 'fail') {
    $HEADERS->err403(true);
  }
  include(REL_PATH . 'control/classes/class.download.php');
  $D = new msDownload();
  $D->ticketAttachment($_GET['attachment'], $SETTINGS, true);
  exit;
}

// At this point id should exist..
if (!isset($_GET['id'])) {
  $HEADERS->err403(true);
}

// Check digit..
mswCheckDigit($_GET['id'], true);

// Load ticket data..
$SUPTICK = mswGetTableData('tickets', 'id', $_GET['id']);

// Checks..
if (!isset($SUPTICK->id)) {
  $HEADERS->err404(true);
}

// Edit notes..
if (isset($_GET['editNotes']) && ($MSTEAM->notePadEnable == 'yes' || $MSTEAM->id == '1')) {
  include(PATH . 'templates/system/tickets/tickets-notes.php');
  exit;
}

// Quick view..
if (isset($_GET['quickView'])) {
  include(PATH . 'templates/system/tickets/tickets-quick-view.php');
  exit;
}

// Department check..
if (mswDeptPerms($MSTEAM->id, $SUPTICK->department, $userDeptAccess) == 'fail') {
  $HEADERS->err403(true);
}

// Add reply..
if (isset($_POST['process'])) {
  define('TICKET_REPLY', 1);
  include(PATH . 'control/system/tickets/ticket-reply.php');
}

// Assign visitor name/email..
$VIS            = mswGetTableData('portal', 'id', $SUPTICK->visitorID);
$SUPTICK->name  = (isset($VIS->name) ? $VIS->name : 'N/A');
$SUPTICK->email = (isset($VIS->email) ? $VIS->email : 'N/A');

// Update status..
if (isset($_GET['act']) && in_array($_GET['act'], array(
  'open',
  'close',
  'lock',
  'ticket',
  'dispute',
  'reopen'
))) {
  $action = str_replace('{user}', $MSTEAM->name, $msg_ticket_history['ticket-status-' . $_GET['act']]);
  $rows   = $MSTICKET->updateTicketStatus();
  // History if affected rows..
  if ($rows > 0) {
    $MSTICKET->historyLog($_GET['id'], str_replace(array(
      '{user}'
    ), array(
      $MSTEAM->name
    ), $action));
    $SUPTICK        = mswGetTableData('tickets', 'id', $_GET['id']);
    $SUPTICK->name  = (isset($VIS->name) ? $VIS->name : 'N/A');
    $SUPTICK->email = (isset($VIS->email) ? $VIS->email : 'N/A');
    $actionMsg      = $msg_ticket_actioned[$_GET['act']];
  }
}

$title        = str_replace('{ticket}', mswTicketNumber($_GET['id']), ($SUPTICK->isDisputed == 'yes' ? $msg_viewticket80 : $msg_viewticket));
$loadBBCSS    = true;
$loadiBox     = true;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-view' . ($SUPTICK->isDisputed == 'yes' ? '-disputed' : '') . '.php');
include(PATH . 'templates/footer.php');

?>