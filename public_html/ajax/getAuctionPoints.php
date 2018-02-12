<?php
require '../db.php';
$user_id = mysql_real_escape_string($_POST['user_id']);
$auction_id = mysql_real_escape_string($_POST['auction_id']);

$sql = "select * from auctions where auction_id = " . $auction_id;
$result = mysql_query($sql);
$auction = mysql_fetch_assoc($result);
$auction_points = auctionPoints($user_id, $auction);

echo floor($auction_points['cur']);
?>