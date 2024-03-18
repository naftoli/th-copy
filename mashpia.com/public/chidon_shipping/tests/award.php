<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once '../class.chidonShipping.php';
$cs = new ChidonShipping();

$user_id = $_GET['user'];
$sql = "select * from th_chidon where year = 5784 and user_id = " . $user_id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$award = $cs->getAwardTrack($row);

echo "Award: " . $award;