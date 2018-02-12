<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$title = (isset($_GET['edit']) ? $msg_kbasecats5 : $msg_kbase16);

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/faq/faq-cat.php');
include(PATH . 'templates/footer.php');

?>