<?php
$admin_auth = ['user'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/header.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";

$year = GlobalSettings::getChidonYear();
$user_id = mysql_real_escape_string($_POST['user_id']);
$sql = "update th_chidon set cert_reviewed = 1 where user_id = " . $user_id . " and year = " . $year;
$result = mysql_query($sql);
echo intval($result);