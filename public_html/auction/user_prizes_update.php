<?
require_once '../db.php';

$auction_id = 37;
$wrong_prize = 182;
$right_prize = 32;
$delete = false;

$sql = "SELECT * 
		FROM auction_user_prizes
		WHERE auction_id = $auction_id
		AND prize_id = $wrong_prize";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
	$user_id = $row['user_id'];
	$qty = $row['quantity'];
	
	$sql2 = "select quantity 
			from auction_user_prizes 
			where auction_id = $auction_id 
			and user_id = $user_id 
			and prize_id = $right_prize";
	$result2 = mysql_query( $sql2 );
	if ( mysql_num_rows( $result2 ) > 0 ) {
		//update new prize quantity
		$row2 = mysql_fetch_assoc( $result2 );
		$qty += $row2['quantity'];
		$sql3 = "update auction_user_prizes 
				set quantity = $qty 
				where auction_id = $auction_id 
				and user_id = $user_id 
				and prize_id = $right_prize";
		if ( mysql_query( $sql3 ) ) {
			 $delete = true;
		}
	} else {
		//insert new prize entry
		$sql3 = "insert into auction_user prizes 
				set auction_id = $auction_id, 
				user_id = $user_id, 
				prize_id = $right_prize, 
				quantity = $qty, 
				system_awarded = 0, 
				won = 0";
		if ( mysql_query( $sql3 ) ) {
			$delete = true;
		}	
	}
	
	if ( $delete ) {
		//delete old prize entry
		$sql4 = "delete from auction_user_prizes 
				where auction_id = $auction_id 
				and user_id = $user_id 
				and prize_id = $wrong_prize";
		mysql_query( $sql4 );
	}
}
?>