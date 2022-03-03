<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$user_id = mysql_real_escape_string($_POST['user']);
$checked = mysql_real_escape_string($_POST['checked']);

$sql = "update th_chidon set sweater_shipped = " . $checked . " where year = " . $year . " and user_id = " . $user_id;
if (mysql_query($sql)) echo 1;
else echo 0;