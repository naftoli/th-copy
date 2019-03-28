<?
require_once 'db.php';

$sql = "select user_id from users u 
		join user_tracks ut using (user_id) 
		where u.school_type_id in (12,13) 
		and ut.enrolled = 1 
		and ut.subject_id not in (1,4,41,42,45,92,93,94,99) 
		and u.user_registered > 0 
		group by user_id ";
$result = mysql_query($sql);
echo mysql_num_rows($result);
echo "<br />";

$userTracks = array();
$sql = "select user_id, subject_id from users u 
		join user_tracks ut using (user_id) 
		where u.school_type_id in (12,13) 
		and ut.enrolled = 1 
		and ut.subject_id not in (1,4,41,42,45,92,93,94,99)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$userTracks[$row['user_id']][] = $row['subject_id'];
}

foreach ($userTracks as $user => $info) {
	foreach ($info as $subject) {
		$sql = "update user_tracks set enrolled = 0 where user_id = $user and subject_id = $subject";
		echo $sql . "<br />";
	}
}
?>