<?php
chdir('../../../');
require 'db.php';

$user = 8273;
$auction_id = 70;

$sql = "select * from auctions where auction_id = " . $auction_id;
$result = mysql_query($sql);
$auction = mysql_fetch_assoc($result);
$date = $auction['auction_date'];
$points = auctionPoints($user, $auction);

//check how many tickets were already purchased
$subtract = 0;
$sql = "select quantity, prize_points from auction_user_prizes 
		join prizes_auction using (prize_id) 
		where auction_id = " . $auction_id . " 
		and user_id = " . $user;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$subtract += ($row['quantity'] * $row['prize_points']);
}
$points["cur"] -= $subtract;

echo json_encode($points);
?>