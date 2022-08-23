<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();
$year = 5783;

$user_id = mysql_real_escape_string($_POST['user_id']);
$user_prizes = [];
$sql = "select * from chidon_user_prizes where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $user_prizes[] = $row['prize_id'];
}

$prizes = [];
$sql = "select * from chidon_prizes  
        where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if (in_array($row['prize_id'], $user_prizes)) $row['selected'] = 1;
    else $row['selected'] = 0;
    $prizes[] = $row;
}

foreach ($prizes as $idx => $prize) {
    $sql = "select count(*) as total 
            from chidon_user_prizes 
            where year = " . $year . " 
            and prize_id = " . $prize['prize_id'];
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        $total = $row['total'];
        $qty = intval($prize['quantity']) - intval($total);
        if ($qty < 0) $qty = 0;
        $prizes[$idx]['quantity'] = $qty;
    }
}

echo json_encode($prizes);