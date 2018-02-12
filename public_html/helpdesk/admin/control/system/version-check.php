<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

$title = $msg_versioncheck;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/version-check.php');
include(PATH . 'templates/footer.php');

?>