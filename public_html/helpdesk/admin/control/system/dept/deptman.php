<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$title = $msg_dept9;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/dept/deptman.php');
include(PATH . 'templates/footer.php');

?>