<?php

include_once ("../header.php");
require_once('../file_save.php');
require_once('../calendar.php');

session_start();

$auction_winners_file = fopen("auction winners.csv", "r");

$row_num = 0;
while ($data = fgetcsv($auction_winners_file, 1000, ",")) {
	$row_num++;
	
	if ($data[0] > 0) { 
		$sql = "SELECT user_id, first, last FROM users WHERE user_serial=" . $data[0] . "";	
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);	
		$num_rows = mysql_num_rows($row);		
		$user_id =  $row['user_id'];
		
		if ($num_rows) 
			echo "SERIAL NUMBER:" . $data[0] . "<br />";
	}
	
	//if ($data[1] > 0) { 
	//	$sql = "SELECT prize_id, prize_name FROM prizes_auction WHERE prize_number=" . $data[1] . "";
	//	$result = mysql_query($sql);
	//	$row = mysql_fetch_assoc($result);
	//	$prize_id =  $row['prize_id'];
	//}
	
	//if ($user_id > 0 && $prize_id > 0) {
	//	$sql = "INSERT INTO auction_winners (auction_id, user_id, prize_id, quantity) VALUES (15, " . $user_id . ", " . $prize_id . ", 1); ";
	//	echo $sql . "<br />";
	//}
		
}

fclose($auction_winners_file);

?>