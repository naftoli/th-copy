<?php
require '../../../db.php';
$cat = $_POST['cat'] ? mysql_real_escape_string($_POST['cat']) : 25;
$auction = mysql_real_escape_string($_POST['auction']);

$prizes = array();
$sql = "select prize_id, prize_name, prize_points, prize_image_id from auction_prizes 
		join prizes_auction using (prize_id) 
		where auction_id = " . $auction . "   
	 	and prize_points = " . $cat . " 
 		order by prize_points, prize_name";

$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$prizes[] = $row;
}

echo json_encode($prizes);
?>