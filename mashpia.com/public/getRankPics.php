<?
require 'db.php';

$ranks = array();
$sql = "select rank_ord, rank_image_id from ranks";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$ranks[$row['rank_ord']] = $row['rank_image_id'];
}

foreach ($ranks as $k => $v) {
	echo "<img src='../file_view2.php?id=" . $v . "' /><br />";
}
?>