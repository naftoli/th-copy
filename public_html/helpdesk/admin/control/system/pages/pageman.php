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
  include(PATH.'templates/system/pages/page-window.php');
  exit;
}

$title     = $msadminlang3_1cspages[2];
$loadiBox  = true;
$loadBBCSS = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/pages/pageman.php');
include(PATH . 'templates/footer.php');

?>