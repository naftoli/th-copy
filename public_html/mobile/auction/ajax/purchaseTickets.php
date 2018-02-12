<?php
require '../../../db.php';

$auction_id = (int)mysql_real_escape_string($_POST['auction']);
$user = (int)mysql_real_escape_string($_POST['user']);
$admin = mysql_real_escape_string($_POST['admin']);

require '../../reg/ajax/encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

// make sure user is part of admin account
$sql = "select * from admin_auths where id = " . $user . " and admin_id = " . $admin . " and role_id = 1";
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
		
	$cart = json_decode($_POST['cart']);
	$qrys = array();
	
	$sql = "delete from auction_user_prizes where auction_id = " . $auction_id . " and user_id = " . $user;
	$qrys[] = $sql;
	
	foreach ($cart as $item) {
		$prizeID = (int)mysql_real_escape_string($item->prize);
		$qty = (int)mysql_real_escape_string($item->qty);

		$qry = "insert into auction_user_prizes 
				set auction_id = " . $auction_id . ", 
				prize_id = " . $prizeID . ", 
				user_id = " . $user . ", 
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
}
?>