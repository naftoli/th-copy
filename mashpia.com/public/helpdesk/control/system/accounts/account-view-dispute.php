<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS') || $SETTINGS->disputes == 'no') {
  $HEADERS->err403();
}

include(PATH . 'control/classes/class.upload.php');
$MSUPL  = new msUpload();

// Upload dropzone..
$mSize = $MSUPL->getMaxSize();
if ($SETTINGS->attachment == 'yes' && $SETTINGS->attachboxes > 0) {
  $ms_js_css_loader['uploader'] = 'yes';
  $aMax   = (LICENCE_VER == 'locked' && $SETTINGS->attachboxes > RESTR_ATTACH ? RESTR_ATTACH : $SETTINGS->attachboxes);
  $mswUploadDropzone = array(
    'ajax' => 'create-ticket',
    'multiple' => ($SETTINGS->attachboxes > 1 && $aMax > 1 ? 'true' : 'false'),
    'max-files' => $aMax,
    'max-size' => ($SETTINGS->maxsize > 0 ? ($SETTINGS->maxsize > $mSize ? $mSize : $SETTINGS->maxsize) : $mSize),
    'allowed' => ($SETTINGS->filetypes ? str_replace(array('|','.'),array(',',''),strtolower($SETTINGS->filetypes)) : '*'),
    'drag' => 'false',
    'txt' => str_replace("'", "\'", $msadminlang3_1uploads[5]),
    'div' => 'three'
  );
}

// For redirection..
if (MS_PERMISSIONS == 'guest' && isset($_GET['d']) && (int) $_GET['d'] > 0) {
  $_SESSION['disputeAccessID'] = (int) $_GET['d'];
}

// Load account globals..
include(PATH . 'control/system/accounts/account-global.php');

// Check log in..
if (MS_PERMISSIONS == 'guest' || !isset($_GET['d'])) {
  header("Location:index.php?p=login");
  exit;
}

// Check id..
mswCheckDigit($_GET['d']);

// Get ticket information and check permissions..
$T = mswGetTableData('tickets', 'id', $_GET['d'], 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `spamFlag` = \'no\' AND `isDisputed` = \'yes\'');
if (!isset($T->id)) {
  // Check if this user is in the dispute list...
  $PRIV = mswGetTableData('disputes', 'visitorID', $LI_ACC->id, 'AND `ticketID` = \'' . $_GET['d'] . '\'');
  // If privileges allow viewing of dispute, requery without email..
  if (isset($PRIV->id)) {
    $T    = mswGetTableData('tickets', 'id', $_GET['d']);
    // Get person who started ticket..
    $ORGL = mswGetTableData('portal', 'id', $T->visitorID);
  } else {
    $HEADERS->err403();
  }
}

// Users in dispute..
$usersInDispute = $MSTICKET->disputeUserNames($T, (isset($ORGL->name) ? mswSafeDisplay($ORGL->name) : mswSafeDisplay($LI_ACC->name)));

// Post privileges..
$userPostPriv = (isset($PRIV->id) ? $PRIV->postPrivileges : $T->disPostPriv);

// Check admin restriction of not allowing any more posts until admin has replied..
if (in_array($T->replyStatus, array(
  'admin',
  'start'
)) && $SETTINGS->disputeAdminStop == 'yes') {
  $userPostPriv = 'no';
}

// Re-open..can only be re-opened by original user..
if ($T->ticketStatus == 'close' && isset($_GET['lk']) && $T->visitorID == $LI_ACC->id) {
  $rows = $MSTICKET->openclose($T->id);
  // History if affected rows..
  if ($rows > 0) {
    $MSTICKET->historyLog($T->id, str_replace('{user}', mswSafeDisplay($LI_ACC->name), $msg_ticket_history['vis-ticket-open']));
    $T               = mswGetTableData('tickets', 'id', $T->id);
    $ticketSystemMsg = $msg_public_ticket14;
  }
}

// Close..can only be re-opened by original user..
if ($T->ticketStatus != 'close' && isset($_GET['cl']) && $T->visitorID == $LI_ACC->id) {
  $rows = $MSTICKET->openclose($T->id, 'close');
  // History if affected rows..
  if ($rows > 0) {
    $MSTICKET->historyLog($T->id, str_replace('{user}', mswSafeDisplay($LI_ACC->name), $msg_ticket_history['vis-ticket-close']));
    $T               = mswGetTableData('tickets', 'id', $T->id);
    $ticketSystemMsg = $msg_public_ticket13;
  }
}

// Add reply..
if (isset($_POST['process'])) {
  define('T_PERMS', 'd');
  include(PATH . 'control/system/accounts/account-ticket-reply.php');
}

// Is IP blank?
if ($T->ipAddresses == '' && $T->visitorID == $LI_ACC->id) {
  $MSTICKET->updateIP($T->id);
  $T->ipAddresses = mswIPAddresses();
}

// Variables..
$title = str_replace('{ticket}', mswTicketNumber($_GET['d']), $msg_showticket32);

include(PATH . 'control/header.php');

$tpl = new Savant3();
$tpl->assign('TICKET', $T);
$tpl->assign('TXT', array(
  $title,
  $msg_header16,
  $msg_header3,
  $msg_main11,
  $MSYS->levels($T->priority),
  $MSDT->mswDateTimeDisplay($T->ts, $SETTINGS->dateformat),
  $MSDT->mswDateTimeDisplay($T->ts, $SETTINGS->timeformat),
  $msg_viewticket75,
  $MSYS->department($T->department, $msg_script30),
  str_replace('{url}', 'index.php?d=' . $_GET['d'] . '&amp;lk=yes', $msg_viewticket45),
  $msg_public_ticket,
  $msg_open19,
  $msg_newticket43,
  $msg_viewticket101,
  $msg_showticket5,
  $msg_viewticket78,
  $msg_newticket37,
  $msg_newticket38,
  $attachRestrictions,
  $bb_code_buttons,
  str_replace('{count}', count($usersInDispute), $msg_showticket30),
  $msg_public_ticket4,
  $msg_public_ticket9,
  $msg_viewticket27,
  $msg_public_ticket10,
  $msg_public_ticket3,
  $msg_public_ticket11,
  $msg_public_ticket15,
  $msg_script43,
  $msadminlang3_1adminviewticket[8],
  $msg_viewticket40,
  $msg_add2
));
$tpl->assign('REPTXT', array(
  $msadminlang3_1adminviewticket[14],
  $msg_add2,
  $msg_attachments,
  $msg_accounts8,
  str_replace(
    array('{max}','{files}','{types}'),
    array(
      ($SETTINGS->maxsize > 0 ? ($SETTINGS->maxsize > $mSize ? mswFileSizeConversion($mSize) : mswFileSizeConversion($SETTINGS->maxsize)) : mswFileSizeConversion($mSize)),
      (LICENCE_VER == 'locked' && $SETTINGS->attachboxes > RESTR_ATTACH ? RESTR_ATTACH : $SETTINGS->attachboxes),
      ($SETTINGS->filetypes ? str_replace(array('|','.'),array(', ',''), $SETTINGS->filetypes) : $msadminlang3_1uploads[4])
    ),
    $msadminlang3_1uploads[3]
  )
));
$tpl->assign('COMMENTS', $MSPARSER->mswTxtParsingEngine($T->comments));
$tpl->assign('USERS_IN_DISPUTE', $usersInDispute);
$tpl->assign('ORG_USER', (isset($ORGL->name) ? $ORGL : ''));
$tpl->assign('USERS_IN_DISPUTE_COUNT', count($usersInDispute));
$tpl->assign('CUSTOM_FIELD_DATA', $MSFIELDS->display($T->id));
$tpl->assign('CUSTOM_FIELD_DATA_COUNT', $MSFIELDS->display($T->id, 0, 1));
$tpl->assign('ATTACHMENTS', $MSTICKET->attachments($T->id));
$tpl->assign('ATTACHMENTS_COUNT', $MSTICKET->attachments($T->id, 0, 1));
$tpl->assign('TICKET_REPLIES', $MSTICKET->replies($T->id, mswSafeDisplay($LI_ACC->name), $LI_ACC->id));
$tpl->assign('ENTRY_CUSTOM_FIELDS', $MSFIELDS->build('reply', $T->department));
$tpl->assign('REPLY_PERMISSIONS', $userPostPriv);
$tpl->assign('SYSTEM_MESSAGE', $ticketSystemMsg);
$tpl->assign('TICKET_CLOSE_PERMS', ($T->visitorID == $LI_ACC->id ? 'yes' : 'no'));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-view-dispute.tpl.php');

include(PATH . 'control/footer.php');

?>