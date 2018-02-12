<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$title           = (isset($_GET['edit']) ? $msg_kbase13 : $msg_adheader46);
$loadBBCSS       = true;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/faq/faq.php');
include(PATH . 'templates/footer.php');

?>