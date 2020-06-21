<?php
require '../db.php';

$old_auction_id = 80;
$new_auction_id = 81;

$info = [];
$sql = "select * from auction_prizes where auction_id = " . $old_auction_id;
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[] = $row;
}

$qrys = [];
foreach ($info as $row) {
    $sql = "insert into auction_prizes 
            set auction_id = " . $new_auction_id . ",
            available = 1, 
            prize_id = " . $row['prize_id'];
    $qrys[] = $sql;
}

foreach ($qrys as $qry) {
    mysql_query($qry);
}
echo "done";