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

$sql = "select *, tcf.khk as khk_final from th_chidon_finals tcf 
                join th_chidon tc using (user_id, year) 
                join users u using (user_id) 
                where tcf.year = $year 
                    AND (track_1 > 0 OR track_2 > 0
                    OR track_3 > 0
                    OR track_4 > 0
                    OR tcf.khk > 0)";
if ($user_id) $sql .= " AND tcf.user_id = $user_id";
else $sql .= " AND u.user_id in (select user_id from users where user_serial = $user_serial)";
$result = mysql_query($sql);
$row2 = mysql_fetch_assoc($result);
$row += $row2;

$award = $cs->getAwardTrack($row);

echo "Award: " . $award;