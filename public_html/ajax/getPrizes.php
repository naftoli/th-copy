<?php
require '../db.php';

$auction_id = mysql_real_escape_string($_POST['auction']);
$prizes = array();
$sql = "select prize_id, prize_points, prize_name, prize_image_id from auction_prizes ap 
        join prizes_auction pa using (prize_id) 
        where ap.auction_id = " . $auction_id . " 
        order by prize_points, prize_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[] = $row;
}
echo json_encode($prizes);
?>