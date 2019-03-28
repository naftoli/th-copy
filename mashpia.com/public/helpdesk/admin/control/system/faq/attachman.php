<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

// Download attachment..
if (isset($_GET['fattachment'])) {
  include(REL_PATH . 'control/classes/class.download.php');
  $D = new msDownload();
  $D->faqAttachment((int) $_GET['fattachment'], $SETTINGS, true);
  exit;
}

$title = $msg_adheader49;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/faq/faq-attachman.php');
include(PATH . 'templates/footer.php');

?>