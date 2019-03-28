<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

if (isset($_GET['view'])) {
  include(PATH . 'templates/system/faq/faq-window.php');
  exit;
}

$title     = $msg_adheader47;
$loadiBox  = true;
$loadBBCSS = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/faq/faqman.php');
include(PATH . 'templates/footer.php');

?>