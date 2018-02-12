<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
<?
require_once 'db.php';

$users = array();
$sql = "select user_id from users where user_registered > 0 limit 0, 100";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row['user_id'];
}

$missions = array();
foreach ($users as $user_id) {
	$sql = "SELECT * FROM date_tasks_mission_marks AS dtmm 
			LEFT JOIN date_tasks_missions AS dtm ON (dtm.date_tasks_mission_id=dtmm.date_tasks_mission_id) 
			WHERE dtmm.user_id = $user_id 
			AND dtmm.subject_id in (select subject_id from user_tracks where user_id = $user_id and enrolled = 1) 
			and dtm.level != (select level from user_tracks where subject_id = 41 and user_id = $user_id) 
			ORDER BY dtm.start_date ASC";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$missions[$user_id][] = $row;
	}
}

echo "<pre>";
print_r($missions);
echo "</pre>";
?>
	</body>
</html>
