<?php
require __DIR__ . '/../../../db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$prizes = [];
$sql = "select * from chidon_prizes where year = " . $year . " order by prize_name, size, color";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    // find out how many were actually purchased
    $sqlPurchased = "select count(*) as total from chidon_user_prizes where year = " . $year . " and prize_id = " . $row['prize_id'];
    $resPurchased = mysql_query($sqlPurchased);
    $rowPurchased = mysql_fetch_assoc($resPurchased);
    $row['purchased'] = $rowPurchased['total'];
    if ($row['quantity'] > $row['purchased']) $prizes[$row['prize_id']] = $row;
}
echo json_encode($prizes);