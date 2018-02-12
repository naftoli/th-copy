<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$title = (isset($_GET['edit']) ? $msg_customfields11 : $msg_customfields2);

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/fields/fields.php');
include(PATH . 'templates/footer.php');

?>