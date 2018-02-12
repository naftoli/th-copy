<?php
ini_set('display_errors', 1);
ini_set('memory_limit','256M');
ini_set('max_execution_time', 600);
require 'db.php';

$users = array();
$auction_id = 70;

$sql = "select * from auctions where auction_id = " . $auction_id;
$result = mysql_query($sql);
$auction = mysql_fetch_assoc($result);

$info = array();
$points = array();
$addTickets = array();
$sql = "select user_id from users u 
        where u.user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $user_id = $row['user_id'];
	$auction_points = auctionPoints($user_id, $auction, true, true);
    $points[$user_id] = $auction_points["cur"];

	$total = 0;
	$sql2 = "select pa.prize_points, aup.quantity
			from auction_user_prizes aup
			join auction_prizes ap using (prize_id)
			join prizes_auction pa using (prize_id)
			where aup.auction_id = 70
			and aup.user_id = " . $user_id;
	//echo $sql2; exit;
	$result2 = mysql_query($sql2);
	while ($row2 = mysql_fetch_assoc($result2)) {
		$total += $row2['prize_points'] * $row2['quantity'];
	}
	$info[$user_id]['avail'] = $auction_points["cur"];
	$info[$user_id]['used'] = $total;
	$balance = $auction_points["cur"] - $total;
	if ($balance > 0) {
		$addTickets[$user_id] = $balance;
	}
}

echo "<pre>"; print_r($addTickets); echo "</pre>";