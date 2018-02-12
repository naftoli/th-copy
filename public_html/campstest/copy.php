<?php
$admin_auth = array('camp');
require('../header.php'); 

$sql1 = "SELECT cm.* FROM campaigns JOIN campaign_missions AS cm USING (campaign_id)";
$query1 = mq($sql1);
while ($row1 = mysql_fetch_assoc($query1)) {
	$insert1 = "INSERT INTO missions SET campaign_id=" . $row1['campaign_id'] . ", mission_name='" . $row1['mission_name'] . "', sequence=" . $row1['sequence'] . ", points=" . $row1['points'];
	mq($insert1);
	$mission_id = mysql_insert_id();
	
	$sql2 = "SELECT FROM campaign_missions_tasks WHERE campaign_mission_id=" . $row1['campaign_mission_id'];
	$query2 = mq($sql2);
	while ($row2 = mysql_fetch_assoc($query2)) {
		$insert2 = "INSERT INTO tasks SET mission_id=" . $mission_id . ", campaign_id=" . $row1['campaign_id'] . ", task_name='" . $row2['task_name'] . "', period_id=" . $row2['period_id'] . ", points=" . $row2['points'] . ", max_times=" . $row2['max_times'] . ", start_date=" . $row2['start_date'] . ", end_date=" . $row2['end_date'] . ", monday=" . $row2['monday']  . ", tuesday=" . $row2['tuesday'] . ", wednesday=" . $row2['wednesday'] . ", thursday=" . $row2['thursday'] . ", friday=" . $row2['friday'] . ", shabbos=" . $row2['shabbos'] . ", sunday=" . $row2['sunday'];
	}
}

?>
