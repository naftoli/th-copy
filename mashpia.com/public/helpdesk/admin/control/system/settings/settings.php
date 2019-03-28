<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

if (isset($_GET['mailTest'])) {
  include(PATH . 'templates/system/settings/mail-test.php');
  exit;
}

$title    = $msg_adheader2;
$loadiBox = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/settings/settings.php');
include(PATH . 'templates/footer.php');

?>