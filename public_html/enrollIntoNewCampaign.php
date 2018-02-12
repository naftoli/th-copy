<?
require_once 'db.php';
/*
$users = array();
$sql = "select user_id, level from user_tracks where subject_id = 4";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
}

foreach ($users as $user) {
	$id = $user['user_id'];
	$level = $user['level'];
	$sql = "insert ignore into user_tracks values($id, 100, 1, $level, 1)";
	mysql_query($sql);
}
 * 
 */
 
 $schools = array();
 $sql = "select school_id from schools";
 $result = mysql_query($sql);
 while ($row = mysql_fetch_assoc($result)) {
 	$schools[] = $row['school_id'];
 }
 
 foreach ($schools as $id) {
 	$sql = "insert into school_subjects values($id, 100)";
	mysql_query($sql);
 }
