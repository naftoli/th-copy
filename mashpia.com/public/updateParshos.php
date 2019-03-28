<?php
require_once 'db.php';
$parshos = array();
$sql = "select * from parshos where start > 2456919";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$parshos[] = $row;
}

for ($i = 1; $i < count($parshos); $i++) {
	$parsha = $parshos[$i-1]['name'];
	$start = $parshos[$i]['start'];
	$end = $parshos[$i]['end'];
	$sql = "update parshos set start = " . ($start - 2) . ", end = " . ($end - 2) . ", name = '$parsha' "
		. "where start = $start and end = $end";
	//echo $sql . "<br />";
	mysql_query($sql);
}
?>