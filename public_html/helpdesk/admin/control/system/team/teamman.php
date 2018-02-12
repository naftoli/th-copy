<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$title = $msg_adheader58;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/team/teamman.php');
include(PATH . 'templates/footer.php');

?>