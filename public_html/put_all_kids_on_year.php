<?
require_once('db.php');

$updated = 0;
$subjects = array();
$subject_sql = "select * from subjects WHERE subject_type = '' and subject_id != 91";
$subject_res = mysql_query($subject_sql);
while ($subject_row = mysql_fetch_assoc($subject_res)) {
	$subjects[] = $subject_row['subject_id'];
}

$sql = "SELECT u.user_id, c.class_grade 
		FROM users as u, classes as c 
		WHERE u.class_id = c.class_id 
		AND user_id NOT
		IN (
		SELECT DISTINCT user_id
		FROM user_tracks
		)";

$result = mysql_query($sql);
$total = mysql_num_rows($result);
while ($row = mysql_fetch_assoc($result)) {

	$user_id = $row['user_id'];
	$grade = $row['class_grade'];
	
	switch ($grade) {
		case 'Pre1a':
			$level = 6;
			break;
		case '1':
			$level = 7;
			break;
		case '2':
			$level = 8;
			break;
		case '3':
			$level = 9;
			break;
		case '4':
			$level = 10;
			break;
		case '5':
			$level = 11;
			break;
		case '6':
			$level = 12;
			break;
		case '7':
			$level = 13;
			break;
		case '8':
			$level = 14;
			break;
	}
	
	foreach ($subjects as $subject) {
		$insert_sql = "insert into user_tracks values ($user_id, $subject, 1, $level, 0)";
		//echo $insert_sql . "<br />";
		$insert_res = mysql_query($insert_sql);
		if ($insert_res) $updated++;
	}
}
?>