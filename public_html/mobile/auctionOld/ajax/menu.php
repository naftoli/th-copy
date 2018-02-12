<?php
require '../../../db.php';
$id = $_POST['auction'];

$prizes = array();
$sql = "select prize_points as points, count(prize_id) as total
		from prizes_auction pa 
		join auction_prizes ap using (prize_id) 
		where ap.auction_id = " . $id . " 
		group by prize_points 
		order by prize_points";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$prizes[] = $row;
}

echo json_encode($prizes);
?>