<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();
$year = 5783;

$user_id = mysql_real_escape_string($_POST['user_id']);

$prizes = [];
$sql = "select * from chidon_prizes  
        where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[] = $row;
}

$selected = [];
$sql = "select * from chidon_user_prizes where user_id = " . $user_id . " and year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $selected[] = $row['prize_id'];
}

foreach ($prizes as $idx => $prize) {
    if (in_array($prize['prize_id'], $selected)) $prizes[$idx]['selected'] = 1;

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