<?
$admin_auth = array('school'); 
require('header.php');

$missionIDs = array();
$sql = "select date_tasks_mission_id from date_tasks dt 
		join date_tasks_missions dtm using (date_tasks_mission_id) 
		where dt.cat like 'My Personal Task%' 
		and dtm.created_by_school = 82 
		and dt.name = 'New Tefillah Task'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$missionIDs[] = $row['date_tasks_mission_id'];
}

foreach ($missionIDs as $id) {
	mysql_query("delete from date_task where date_tasks_mission_id = " . $id);
	mysql_query("delete from date_tasks_missions where date_tasks_mission_id = " . $id);
}
?>