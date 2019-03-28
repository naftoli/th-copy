<?php

if (!defined('PARENT') || !isset($_GET['id'])) {
  $HEADERS->err403(true);
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

// Get account info..
mswCheckDigit($_GET['id'], true);

$ACC = mswGetTableData('portal', 'id', $_GET['id']);

// Checks..
if (!isset($ACC->id)) {
  $HEADERS->err403(true);
  exit;
}

include_once(REL_PATH . 'control/classes/class.tickets.php');
$MSPTICKETS           = new tickets();
$MSPTICKETS->settings = $SETTINGS;
$MSPTICKETS->datetime = $MSDT;
$title                = $msg_header11;
$loadiBox             = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/accounts/history.php');
include(PATH . 'templates/footer.php');

?>