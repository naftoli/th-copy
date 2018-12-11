<?php
//require_once('../header.php');
require_once('../db.php');
/*
$total = 0;

$sql = "select u.user_id, s.school_name, c.class_grade, c.class_sub 
		from users u, schools s, classes c 
		where u.user_registered > 0
		and u.school_id = s.school_id 
		and u.class_id = c.class_id 
		order by school_name, class_grade, class_sub 
		";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	echo "School: " . $row['school_name'] . "<br />";
	echo "Class: " . $row['class_grade'] . "-" . $row['class_sub'] . "<br />";	
	echo "ID: " . $row['user_id'] . "<br />";
	
	$qry = "select * from auctions where auction_id = 31";
	$res = mysql_query($qry);
	$auction = mysql_fetch_assoc($res);
	
	$auction_points = auctionPoints($row['user_id'], $auction, FALSE);
	echo "Points: " . $auction_points['cur'] . "<br />";
	
	$sql2 = "SELECT IFNULL( SUM( prize_points * quantity ) , 0 ) total
			FROM auction_user_prizes
			JOIN prizes_auction
			USING ( prize_id )
			WHERE auction_id =31
			AND user_id = " . $row['user_id'];
	$result2 = mysql_query($sql2);
	$row2 = mysql_fetch_row($result2);
	echo "Used: " . $row2[0] . "<br />";
	
	$remaining = $auction_points['cur'] - $row2[0];
	if ($remaining > 0)	
		$total++;
	echo "Remaining: " . $remaining . "<br /><br />";	
}
echo "Total number of children with points left: " . $total;
 * 
 */

function updateBig($user_id, $qty) {
	$sql = "select big_prizes_won from users where user_id = $user_id";
	$res = mysql_query($sql);
	$row = mysql_fetch_row($res);
	$num = $row[0];
	echo "Num of big prizes: " . $num . "<br />";
	
	$sql = "update users set big_prizes_won = " . ($num - $qty);
	//echo $sql . "<br />";
	mysql_query($sql);
}

function updateSmall($user_id, $qty) {
	$sql = "select small_prizes_won from users where user_id = $user_id";
	$res = mysql_query($sql);
	$row = mysql_fetch_row($res);
	$num = $row[0];
	//echo "Num of small prizes: " . $num . "<br />";	
	
	$sql = "update users set big_prizes_won = " . ($num - $qty);
	//echo $sql . "<br />";
	mysql_query($sql);
}

function updateSchools($user_id) {
	$sql = "select school_id from users where user_id = $user_id";
	$res = mysql_query($sql);
	$row = mysql_fetch_row($res);
	$school_id = $row[0];
	
	$sql = "select big_prizes_won from schools where school_id = $school_id";
	$res = mysql_query($sql);
	$row = mysql_fetch_row($res);
	$num = $row[0];
	//echo "Num of big prizes won in school: " . $num . "<br />";
	
	$sql = "update schools set big_prizes_won = " .($num - 1);
	//echo $sql . "<br />";
	mysql_query($sql);
}
 
$sql = "select * from auction_winners aw 
		join prizes_auction pa using (prize_id) 
		where auction_id = 31";
//echo $sql;
$result = mysql_query($sql);
$updated = 0;
while ($row = mysql_fetch_assoc($result)) {
	//echo "<pre>";
	//print_r($row);
	//echo "</pre>";
	
	$user_id = $row['user_id'];
	$quantity = $row['quantity'];
	$prize_id = $row['prize_id'];
	$points = $row['prize_points'];
	
	if ($points > 71) {
		$type = 'big';
	} else {
		$type = 'small';
	}
	
	//echo "Type: " . $type . "<br />";
	
	if ($type == 'big') {
		updateBig($user_id, $quantity);
		$updated++;
		updateSchools($user_id);
	} else if ($type == 'small') {
		updateSmall($user_id, $quantity);
		$updated++;
	}
}
echo "Updated: " . $updated;
$sql = "delete from auction_winners where auction_id = 31";
mysql_query($sql);
?>