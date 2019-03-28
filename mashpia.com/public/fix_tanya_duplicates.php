<?
require_once 'db.php';

$missionIDs = array();
$taskIDs = array();
$sql = "select * FROM `date_tasks_missions` dtm 
		join date_tasks dt using (date_tasks_mission_id) 
		join parshos p on (p.start = dtm.start_date) 
		where mission_name = 'תניא בעל פה' 
		and p.name in ('וָאֵרָא', 'בְּשַׁלַּח', 'כִּי תִשָּׂא', 'אַחֲרֵי מוֹת', 'בְּמִדְבַּר', 'חֻקַּת') 
		and dt.name = 'Enter the amount of lines of תניא בעל פה that you know by heart.'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$missionIDs[] = $row['date_tasks_mission_id'];
	$taskIDs[] = $row['date_task_id'];
}

echo "<pre>";
//print_r($missionIDs);
//print_r($taskIDs);
echo "</pre>";

foreach ($missionIDs as $id) {
	mysql_query("delete from date_tasks_missions where date_tasks_mission_id = $id");
}
foreach ($taskIDs as $id) {
	mysql_query("delete from date_tasks where date_task_id = $id");
}
?>