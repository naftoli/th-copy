<?php
require '../../../db.php';

$auction_id = (int)mysql_real_escape_string($_POST['auction']);
$user = (int)mysql_real_escape_string($_POST['user']);

// ****************** PROCESSING GUARD (similar to Hei Teves) ***************************/
function startPayment() {
    global $user;
    if (!$user) return;
    $sql = "INSERT INTO payment_processing (user_id) VALUES (" . intval($user) . ")";
    mysql_query($sql);
}

function endPayment() {
    global $user;
    if (!$user) return;
    $sql = "DELETE FROM payment_processing WHERE user_id = " . intval($user);
    mysql_query($sql);
}

function paymentInProgress() {
    global $user;
    if (!$user) return false;
    $sql = "SELECT * FROM payment_processing WHERE user_id = " . intval($user);
    $result = mysql_query($sql);
    return $result && mysql_num_rows($result) > 0;
}
// **************************************************************************************

// make sure user is part of admin account
// $sql = "select * from admin_auths where id = " . $user . " and admin_id = " . $admin . " and role_id = 1";
// $result = mysql_query($sql);
// if (mysql_num_rows($result) > 0) {

    // Guard: ensure no other purchase is in progress for this user
    if (paymentInProgress()) {
        echo "Another save is currently processing. Please wait a moment and try again.";
        exit;
    }

    // Mark processing start
    startPayment();
		
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
		endPayment();
		mysql_query("set autocommit=1");
		echo 0;
	} else {
		mysql_query("rollback");
		endPayment();
		mysql_query("set autocommit=1");
		echo 1;
	}
// }
?>