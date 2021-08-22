<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require '../../header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$prizes = [];
$sql = "select * from chidon_prizes where year = 5781";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[] = $row;
}

$qrys = [];
foreach ($prizes as $row) {
    $qrys[] = "insert into chidon_prizes
                set year = 5782, 
                prize_picture = '" . $row['prize_picture'] . "', 
                prize_name = '" . $row['prize_name'] . "', 
                quantity = 100, 
                size = '" . $row['size'] . "', 
                color = '" . $row['color'] . "', 
                price = " . $row['price'] . ", 
                our_price = " . $row['our_price'] . ", 
                made_possible_by = '" . $row['made_possible_by'] . "', 
                purchased = 0";
}

foreach ($qrys as $sql) {
//    echo $sql . "<br />";
    mysql_query($sql);
}

echo "done.";