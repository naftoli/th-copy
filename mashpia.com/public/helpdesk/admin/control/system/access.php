<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

if ($cmd == 'logout') {
  @session_unset();
  @session_destroy();
  unset($_SESSION[mswEncrypt(SECRET_KEY) . '_ms_mail'], $_SESSION[mswEncrypt(SECRET_KEY) . '_ms_key']);
  if (isset($_SESSION['autoPurgeRan'])) {
    unset($_SESSION['autoPurgeRan']);
  }
  if (isset($_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_mail'])) {
    @setcookie(mswEncrypt(SECRET_KEY) . '_msc_mail', '');
    @setcookie(mswEncrypt(SECRET_KEY) . '_msc_key', '');
    unset($_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_mail'], $_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_key']);
  }
  header("Location: index.php?p=login");
  exit;
}

// Are we already logged in via cookie..
if (isset($MSTEAM->name)) {
  header("Location: index.php");
  exit;
}

include(PATH . 'templates/system/login.php');

?>