<?
require('../db.php');

$auction_id = 30;

$big_prizes = array();
$small_prizes = array();

$sql = "select aw.user_id from auction_winners as aw, prizes_auction as pa 
		where aw.prize_id = pa.prize_id 
		and pa.prize_points > 71 
		and aw.auction_id = $auction_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$big_prizes[] = $row['user_id'];
}

$sql = "select aw.user_id from auction_winners as aw, prizes_auction as pa 
		where aw.prize_id = pa.prize_id 
		and pa.prize_points <= 71 
		and aw.auction_id = $auction_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$small_prizes[] = $row['user_id'];
}

//echo "Number of big prize winners: " . count($big_prizes) . "<br />";
//echo "Number of small prize winners: " . count($small_prizes);

$big_success = 0;
$school_success = 0;
foreach ($big_prizes as $user) {
	$sql = "select big_prizes_won from users where user_id = $user";
	$result = mysql_query($sql);
	$row = mysql_fetch_row($result);
	$num = $row[0];
	$num--;
	$sql = "update users set big_prizes_won = $num where user_id = $user";
	$result = mysql_query($sql);
	if ($result)
		$big_success++;
		
	$sql = "select s.big_prizes_won, s.school_id from schools as s, users as u 
			where u.school_id = s.school_id
			and u.user_id = $user";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$num = $row['big_prizes_won'];
	$school_id = $row['school_id'];
	$num--;
	$sql = "update schools set big_prizes_won = $num where school_id = $school_id";
	$result = mysql_query($sql);
	if ($result)
		$school_success++;
}

$small_success = 0;
foreach ($small_prizes as $user) {
	$sql = "select small_prizes_won from users where user_id = $user";
	$result = mysql_query($sql);
	$row = mysql_fetch_row($result);
	$num = $row[0];
	$num--;
	$sql = "update users set small_prizes_won = $num where user_id = $user";
	$result = mysql_query($sql);
	if ($result)
		$small_success++;
}

$sql = "delete from auction_winners where auction_id = $auction_id";
mysql_query($sql);

$sql = "update auctions set auction_ran = 0 where auction_id = $auction_id";
mysql_query($sql);

echo "Big success: " . $big_success . "<br />";
echo "School success: " . $school_success . "<br />";
echo "Small success: " . $small_success . "<br />";
echo "Big prizes: " . count($big_prizes) . "<br />";
echo "Small prizes: " . count($small_prizes);
?>