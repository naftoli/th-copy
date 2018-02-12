<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

include(REL_PATH . 'control/lib/b8/call_b8.php');

$title           = $msg_adheader62;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/imap/imap-filters.php');
include(PATH . 'templates/footer.php');

?>