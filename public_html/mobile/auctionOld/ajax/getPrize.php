<?php
require '../../../db.php';

$id = mysql_real_escape_string($_POST['id']);
$sql = "select * from auction_prizes 
		join prizes_auction using (prize_id) 
		where prize_id = " . $id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);

echo json_encode($row);
?>