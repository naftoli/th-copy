<?php
require __DIR__ . '/../../../db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$prizes = [];
$sql = "select * from chidon_prizes where year = " . $year . " and (quantity - purchased > 0) order by prize_name, size, color";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[$row['prize_id']] = $row;
}
echo json_encode($prizes);