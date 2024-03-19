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
if (strlen($user_id) == 7 && strpos($user_id, '7') === 0) {
    $user_serial = $user_id;
    $user_id = 0;
}
$sql = "select * from th_chidon where year = $year and user_id = ";
if ($user_id) $sql .= $user_id;
else $sql .= "(select user_id from users where user_serial = $user_serial)";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$award = $cs->getAwardTrack($row);

echo "Award: " . $award;