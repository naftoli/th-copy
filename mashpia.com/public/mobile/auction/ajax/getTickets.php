<?php
require '../../../db.php';

$user_id = mysql_real_escape_string($_POST['user']);
$auction_id = mysql_real_escape_string($_POST['auction']);

$tickets = array();
$sql = "select aup.quantity, aup.prize_id, pa.prize_name, pa.prize_points, pa.prize_image_id from auction_user_prizes aup
		join prizes_auction pa on aup.prize_id = pa.prize_id 
		where auction_id = " . $auction_id . "
        and user_id = " . $user_id;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $tickets[] = $row;
}

echo json_encode($tickets);
?>