<?php
require_once('../db.php');

$sql = "update schools set big_prizes_won = 0";
mysql_query($sql);
$sql = "update users set small_prizes_won = 0, big_prizes_won = 0";
mysql_query($sql);

$schools = array();
$users = array();
$sql = "select aw.*, pa.prize_points, s.school_id from auction_winners aw 
		join prizes_auction pa using (prize_id) 
		join users u using (user_id) 
		join schools s on (u.school_id = s.school_id) 
		where auction_id in (29,30) 
		order by user_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	
	$user_id = $row['user_id'];
	$quantity = $row['quantity'];
	$prize_id = $row['prize_id'];
	$points = $row['prize_points'];
	$school_id = $row['school_id'];
	
	if ($points > 71) {
		$schools[$school_id]['big']++;
		$users[$user_id]['big']++;
	} else {
		$users[$user_id]['small']++;
	}
}

foreach ($schools as $id => $val) {
	$sql = "update schools set big_prizes_won = " . $schools[$id]['big'] . " where school_id = " . $id;
	//echo $sql . "<br />"; 
	mysql_query($sql);	
}

foreach ($users as $id => $val) {
	$big = $users[$id]['big'];
	$small = $users[$id]['small'];
	
	$sql = "update users set big_prizes_won = " . 
	(empty($big) ? 0 : $big) . 
	" and small_prizes_won = " . 
	(empty($small) ? 0 : $small) . 
	" where user_id = " . $id;
	
	//echo $sql . "<br />";
	mysql_query($sql);	
}
echo "Done";
/*
echo "<pre>";
print_r($schools);
echo "</pre>";
echo "----------------------<br />";
echo "<pre>";
print_r($users);
echo "</pre>";

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
 * 
 */
?>