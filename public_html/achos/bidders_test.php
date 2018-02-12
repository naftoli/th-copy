<? 
$admin_auth = array('school'); 

require('header.php'); 
require_once('calendar.php');

$found = 0;

$sql = "SELECT * FROM auction_user_prizes WHERE auction_id=23 AND prize_id=79";
$query = mysql_query($sql);	
while ($row = mysql_fetch_assoc($query)) {
	echo "USER ID:" . $row['user_id'] . "<br />";
	$sql2 = "SELECT count(*) AS total FROM auction_winners WHERE (auction_id=15 OR auction_id=22 OR auction_id=23) AND user_id=" . $row['user_id'];
	$query2 = mysql_query($sql2);
	$row2 = mysql_fetch_assoc($query2);

	if ($row2['total'] == 0)	
		$found++;
}

echo "FOUND:" . $found;
?>