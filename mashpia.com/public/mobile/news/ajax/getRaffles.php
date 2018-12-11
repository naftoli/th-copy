<?
require '../../../db.php';

$raffles = array();
$sql = "select * from auctions where auction_ran = 1 and auction_name like '%5777%' and show_mobile > now()";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$raffles[$row['auction_id']] = $row['auction_name'];
}
echo json_encode($raffles);
?>