<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$title = (isset($_GET['edit']) ? $msg_imap25 : $msg_adheader39);

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/imap/imap.php');
include(PATH . 'templates/footer.php');

?>