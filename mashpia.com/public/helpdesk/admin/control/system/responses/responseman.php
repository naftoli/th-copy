<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

// View preview..
if (isset($_GET['view'])) {
  include(PATH.'templates/system/responses/responses-window.php');
  exit;
}

$title     = $msg_adheader54;
$loadiBox  = true;
$loadBBCSS = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/responses/responses-man.php');
include(PATH . 'templates/footer.php');

?>