<?
require 'db.php';

$subjects = array();
$sql = "select * from subjects where subject_type NOT IN ('school_points', 'home_points')";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$subjects[] = $row['subject_id'];
}

$users = array(20599, 20619, 19274, 20598, 21008);
foreach ($users as $user_id) {
	foreach ($subjects as $subject) {
		$track_id = 1;
		if ($subject == 1) {
			$track_id = 5;
		}
	    $ins = "insert into user_tracks values ($user_id, $subject, $track_id, 6, 1)";
		//echo $ins;
	    mysql_query($ins) or die(mysql_error());
	}
}