<?php
ini_set('display_errors', 1);
ini_set('memory_limit','256M');
ini_set('max_execution_time', 300);
require '../db.php';

$users = array();
$auction_id = 70;

$sql = "select * from auctions where auction_id = " . $auction_id;
$result = mysql_query($sql);
$auction = mysql_fetch_assoc($result);

$total = 0;
$prizes = array();
$sql = "select pa.prize_points, aup.quantity, aup.user_id 
        from auction_user_prizes aup
        join auction_prizes ap using (prize_id)
        join prizes_auction pa using (prize_id)
        where aup.auction_id = 70";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $total++;
    if (isset($prizes[$row['user_id']])) {
        $prizes[$row['user_id']] += $row['prize_points'] * $row['quantity'];
    } else {
        $prizes[$row['user_id']] = $row['prize_points'] * $row['quantity'];
    }
}
//echo $total; exit;
echo "<pre>"; print_r($prizes); echo "</pre>"; exit;
$i = 0;
$addTickets = array();
foreach ($prizes as $user => $used) {
    $auction_points = auctionPoints($user, $auction, true, true);
    $points = $auction_points["cur"];
    $bal = $points - $used;
    if ($bal > 0) {
        $addTickets[$user] = $bal;
    }
    //if ($i++ == 1200) sleep(1);
}

echo "<pre>"; print_r($addTickets); echo "</pre>";