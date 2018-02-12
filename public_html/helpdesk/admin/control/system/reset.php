<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Permissions..
if (!defined('PASS_RESET')) {
  $HEADERS->err403(true, 'This page cannot be accessed.<br>Refer to the <a href="../docs/reset.html" onclick="window.open(this);return false">documentation</a> on how to access the reset page');
}

$title = $msg_adheader36;

if (file_exists(PATH . 'templates/reset.php')) {
  define('RESET_LOADER', 1);
  include(PATH . 'templates/reset.php');
} else {
  $HEADERS->err403(true, 'Reset template file is missing. Did you rename it?<br>Refer to the <a href="../docs/reset.html" onclick="window.open(this);return false">documentation</a>.');
}

?>