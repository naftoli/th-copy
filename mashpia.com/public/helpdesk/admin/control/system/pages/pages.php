<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$title           = (isset($_GET['edit']) ? $msadminlang3_1cspages[3] : $msadminlang3_1cspages[1]);
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/pages/pages.php');
include(PATH . 'templates/footer.php');

?>