<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$title = (isset($_GET['edit']) ? $msg_dept5 : $msg_dept2);

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/dept/dept.php');
include(PATH . 'templates/footer.php');

?>