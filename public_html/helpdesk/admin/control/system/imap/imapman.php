<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$title = $msadminlang3_1[4];

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/imap/imapman.php');
include(PATH . 'templates/footer.php');

?>