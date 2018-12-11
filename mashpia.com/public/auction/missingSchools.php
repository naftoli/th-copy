<?
require '../db.php';
$winners = array();
$sql = "select school_id from users  
		join auction_winners w using (user_id) 
		where w.auction_id = 65";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$winners[] = $row['school_id'];
}

$schools = array(
	269,269,176,162,162,45,45,30,30,54,54,54,2,2,7,7,112,66,105,63,81,49,192,89,55,106,106,5,5,50,50,21,4,4,4,60,
	264,33,185,185,80,110,110,194,3,39,19,19,19,42,42,42,9,9,9,263,61,61,61,61,255,255,255,48,43,84,11,40,58,58
);

$diff = array();
foreach ($schools as $id) {
	if (!in_array($id, $winners)) {
		$diff[] = $id;
	}
}

echo "<pre>"; print_r($diff); echo "</pre>";

$sql = "select school_name from schools where school_id in (" . implode(',', $diff) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	echo $row['school_name'] . "<br />";
}

echo count($schools);
?>
