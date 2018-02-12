<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$title    = (isset($_GET['edit']) ? $msg_user14 : $msg_adheader57);
$loadiBox = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/team/team.php');
include(PATH . 'templates/footer.php');

?>