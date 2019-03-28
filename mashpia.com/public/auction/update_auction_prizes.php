<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	
	<BODY>
	
<?
include("../db.php");

//get auction id
$sql = "select auction_id from auctions where auction_ran = 1 order by auction_id desc limit 1";
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );
$auction_id = $row['auction_id'];
//echo $auction_id; exit;

//get prizes
$prizes = array();
$sql = "select prize_id, prize_number from prizes_auction";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$prizes[$row['prize_number']] = $row['prize_id'];
}

//delete current winners
//$sql = "delete from auction_winners where auction_id = " . $auction_id;
//mysql_query( $sql );

//update winners from file
$updated = 0;
$new_students = fopen("winners.csv", "r");
$contents = stream_get_contents($new_students);
$arrRows = preg_split("/[\n\r]+/", $contents);
foreach ($arrRows as $strLine) {

	$data =explode(",", $strLine);	
	$user = $data[0];
	$prize = $prizes[$data[1]];
	
	$sql = "update auction_winners 
			set prize_id = " . $prize . " 
			where auction_id = " . $auction_id . " 
			and user_id = " . $user;
	//echo $sql . "<br />";
	if (mysql_query($sql)) 
		$updated++;
}
echo "Records Updated: " . $updated;
?>