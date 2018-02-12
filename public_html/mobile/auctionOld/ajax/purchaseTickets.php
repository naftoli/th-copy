<?php
require '../../../db.php';

$auction_id = mysql_real_escape_string($_POST['auction']);
$user_id = mysql_real_escape_string($_POST['user']);
$info = $_POST['info'];
$qrys = array();

$sql = "delete from auction_user_prizes where auction_id = " . $auction_id . " and user_id = " . $user_id;
$qrys[] = $sql;

foreach ($info as $row) {
	$prize_id = mysql_real_escape_string($row['id']);
	$qty = mysql_real_escape_string($row['qty']);
	
	/*
	$sql = "select * from auction_user_prizes 
			where auction_id = " . $auction_id . " 
			and user_id = " . $user_id . " 
			and prize_id = " . $prize_id;
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$row2 = mysql_fetch_assoc($result);
		$qry = "update auction_user_prizes 
				set quantity = " . ($row2['quantity'] + $qty) . " 
				where prize_id = " . $prize_id . " 
				and user_id = " . $user_id . " 
				and auction_id = " . $auction_id;
	} else {
		$qry = "insert into auction_user_prizes 
				set auction_id = " . $auction_id . ", 
				prize_id = " . $prize_id . ", 
				user_id = " . $user_id . ", 
				quantity = " . $qty;
	}
	*/
	$qry = "insert into auction_user_prizes 
			set auction_id = " . $auction_id . ", 
			prize_id = " . $prize_id . ", 
			user_id = " . $user_id . ", 
			quantity = " . $qty;
	$qrys[] = $qry;
}

$success = true;
mysql_query("set autocommit=0");
mysql_query("begin");
foreach ($qrys as $qry) {
	if (! @mysql_query($qry)) {
		$success = false;
		break;
	}
}
if ($success) {
	mysql_query("commit");
	mysql_query("set autocommit=1");
	echo 0;
} else {
	mysql_query("rollback");
	mysql_query("set autocommit=1");
	echo 1;
}
?>