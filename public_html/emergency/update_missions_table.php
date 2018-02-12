<?
require_once ("../db.php");

$sql = "SELECT *
		FROM date_tasks_mission_marks_new limit 0, 100000";

$inserted = 0;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$user_id = $row['user_id'];
	$date_tasks_mission_id = $row['date_tasks_mission_id'];
	$subject_id = $row['subject_id'];
	$mission_value = $row['mission_value'];
	$mission_name = $row['mission_name'];
	$mark_date = $row['mark_date'];
	$mark_override = $row['mark_override'];
	$missions_updated = $row['missions_updated'];
	
	$insert = "insert into date_tasks_mission_marks values($user_id, $date_tasks_mission_id, $subject_id, $mission_value, '$mission_name', $mark_date, $mark_override, $missions_updated)";
	if (mysql_query($insert))
		$inserted++;
}
echo "Inserted: " . $inserted;
?>