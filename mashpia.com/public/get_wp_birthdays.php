<?
require 'db.php';

$ids = array();
$sql = "select ID from wp.wp_posts where post_type = 'birthday'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$ids[] = $row['ID'];
}

$userIDs = array();
foreach ($ids as $id) {
	$sql = "select meta_value from wp.wp_postmeta where post_id = " . $id . " and meta_key = 'user_id'";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$userIDs[] = (int)$row['meta_value'];
	}
}

//echo "<pre>"; print_r( $userIDs ); echo "</pre>"; exit;

foreach ($userIDs as $k => $id) {
	$sql = "update he_dob set wp_synced = 1 where user_id = " . $id;
	echo $k . ": " . $sql . "<br />";
	mysql_query($sql);
}
?>