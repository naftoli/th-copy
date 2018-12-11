<?
require 'db.php';

$sql = "select dtmm.date_tasks_mission_id, dtm.mission_value 
	from date_tasks_mission_marks as dtmm 
	join date_tasks_missions as dtm using (date_tasks_mission_id) 
	where dtmm.mission_value = 0.0 limit 200";
$result = mysql_query($sql);
//echo mysql_num_rows($result);
$num = 0;
while ($row = mysql_fetch_assoc($result)) {
	$sql2 = "update date_tasks_mission_marks 
			set mission_value = " . $row['mission_value'] . " 
			where date_tasks_mission_id = " . $row['date_tasks_mission_id'];
	if (mysql_query($sql2)) $num++;
}
echo "Updated: " . $num;
?>