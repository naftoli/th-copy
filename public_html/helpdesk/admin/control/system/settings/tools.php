<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$title = $msg_adheader18;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/settings/tools.php');
include(PATH . 'templates/footer.php');

?>