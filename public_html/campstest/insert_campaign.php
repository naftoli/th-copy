<?php
$admin_auth = array('camp');
require('../header.php');

$camp_id = $_GET['camp_id'];
$campaign_id = $_GET['campaign_id'];
$camp_type_id = $_GET['camp_type_id'];
$action = $_GET['action'];

$camp_campaign_id = 0;
$camp_mission_id = 0;

if ($action == "insert") {
	// ****************************** CAMPAIGNS ****************************** //
	$sql = "SELECT * FROM global_campaigns WHERE campaign_id=" . $campaign_id;
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$insert = "INSERT INTO camp_campaigns SET campaign_id=" . $campaign_id . ", camp_id=" . $camp_id . ", campaign_name=" . ms($row['campaign_name']) . ", points=" . $row['points'];
		mq($insert);
		$camp_campaign_id = mysql_insert_id();
	}
	// ****************************** CAMPAIGNS ****************************** //

	// ****************************** MISSIONS ****************************** //
	$sql1 = "SELECT * FROM global_missions WHERE campaign_id=" . $campaign_id;
	$query1 = mysql_query($sql1);
	while ($row1 = mysql_fetch_assoc($query1)) {
		$insert1 = "INSERT INTO camp_missions SET mission_id=" . $row1['mission_id'] . ", camp_campaign_id=" . $camp_campaign_id . ", mission_name=" . ms($row1['mission_name']) . ", points=" . $row1['points'];
		mq($insert1);
		$camp_mission_id = mysql_insert_id();
		
		if ($camp_type_id == 0)
			$sql2 = "SELECT * FROM global_tasks WHERE mission_id=" . $row1['mission_id'];
		else
			$sql2 = "SELECT * FROM global_tasks WHERE mission_id=" . $row1['mission_id'] . " AND (camp_type_id=" . $camp_type_id . " OR camp_type_id=0)";
		
		$query2 = mq($sql2);
		while ($row2 = mysql_fetch_assoc($query2)) {
			$insert2 = "INSERT INTO camp_tasks SET task_id=" . $row2['task_id'] . ", camp_mission_id=" . $camp_mission_id . ", camp_type_id=" . $row2['camp_type_id'] . ", level_id=" . $row2['level_id'] . ", task_name=" . ms($row2['task_name']) . ", period_id=" . $row2['period_id'] . ", points=" . $row2['points'] . ", max_times=" . $row2['max_times'] . ", start_date=" . $row2['start_date'] . ", end_date=" . $row2['end_date']  . ", monday=" . $row2['monday']  . ", tuesday=" . $row2['tuesday']  . ", wednesday=" . $row2['wednesday'] . ", thursday=" . $row2['thursday'] . ", friday=" . $row2['friday'] . ", shabbos=" . $row2['shabbos'] . ", sunday=" . $row2['sunday'];
			mq($insert2);		
		}
	}
	// ****************************** MISSIONS ****************************** //
}
if ($action == "remove") {
	$sql1 = "SELECT camp_campaign_id FROM camp_campaigns WHERE camp_id=" . $camp_id . " AND campaign_id=" . $campaign_id;
	$query1 = mysql_query($sql1);
	while ($row1 = mysql_fetch_assoc($query1)) {
	
		$sql2 = "SELECT camp_mission_id FROM camp_missions WHERE camp_campaign_id=" . $row1['camp_campaign_id'];
		$query2 = mysql_query($sql2);
		while ($row2 = mysql_fetch_assoc($query2)) {
		
			$remove1 = "DELETE FROM camp_tasks WHERE camp_mission_id=" . $row2['camp_mission_id'];
			mq($remove1);
		}
		
		$remove2 = "DELETE FROM camp_missions WHERE camp_campaign_id=" . $row1['camp_campaign_id'];
		mq($remove2);
		
		$remove3 = "DELETE FROM camp_campaigns WHERE camp_id=" . $camp_id . " AND campaign_id=" . $campaign_id;
		mq($remove3);		
		
	}

}
?>