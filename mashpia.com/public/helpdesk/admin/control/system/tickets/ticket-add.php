<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

define('DROPZONE_LOADER', 1);

$title           = $msg_open;
$loadBBCSS       = true;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-add.php');
include(PATH . 'templates/footer.php');

?>