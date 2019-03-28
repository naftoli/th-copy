<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

mswClearExportFiles();

// Update default days..
if (isset($_GET['dd']) && $_GET['dd'] != $MSTEAM->defDays) {
  $MSUSERS->updateDefDays($MSTEAM->id);
  $MSTEAM->defDays = $_GET['dd'];
}

$title      = $msg_adheader11;
$loadJQAPI  = true;
$loadJQPlot = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/home.php');
include(PATH . 'templates/footer.php');

?>