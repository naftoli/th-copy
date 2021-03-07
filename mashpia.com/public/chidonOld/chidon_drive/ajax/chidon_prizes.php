<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$prizes = [];
$sql = "select * from chidon_prizes where year = " . $year . " and (quantity - purchased > 0) order by prize_name, size, color";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[$row['prize_id']] = $row;
}
echo json_encode($prizes);