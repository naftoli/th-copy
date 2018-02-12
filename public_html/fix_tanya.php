<?
require_once 'db.php';

/*
$schools = array();
$sql = "select * from schools";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[] = $row['school_id'];
}

foreach ($schools as $id) {
	$sql = "insert ignore into school_subjects values($id, 27)";
	mysql_query($sql);
}
 * 
 */

$subjects = array(12, 42, 4, 45, 40, 16, 41); 
$users = array();
$sql = "select * from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row['user_id'];
}

$info = array();
foreach ($users as $user) {
	$sql = "select level from user_tracks 
			where subject_id in (" . implode(',', $subjects) . ") 
			and user_id = $user 
			and level > 5 
			and level < 15 
			limit 1";
	
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$info[$user]['year'] = $row['level'];
	}
}

$inserted = 0;
$updated = 0;
foreach ($info as $user => $arr) {
	foreach ($arr as $year) {
		$sql = "insert ignore into user_tracks values($user, 27, 1, $year, 1)";
		//echo $sql;
		$result = mysql_query($sql) or die(mysql_error());
		if (mysql_affected_rows() == 0) {
			$sql = "update user_tracks set track_id = 1, level = $year, enrolled = 1 where user_id = $user and subject_id = 27";
			if (mysql_query($sql)) {
				$updated++;
			}
		} else {
			$inserted++;
		}
	}
}

echo "Inserted: " . $inserted . "<br />";
echo "Updated: " . $updated;
?>