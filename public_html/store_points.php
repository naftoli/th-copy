<?
require('db.php');

$sql = "select * from schools where school_era is null order by school_name";
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result)) {
	echo "<a href='store_points_detail.php?school_id=" . $row['school_id'] . "'>" . $row['school_name'] . "</a></br />";
}
?>
