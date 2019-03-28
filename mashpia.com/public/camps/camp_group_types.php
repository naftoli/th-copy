<?php 

// $params -> camp_id
function get_camp_campaigns($params) {
	global $camp_id;
	$camp_campaigns = array();
	
	$sql = "SELECT * FROM camp_campaigns WHERE camp_id=" . $camp_id;
	$query = mq($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$camp_campaign_id = $row['camp_campaign_id'];
		$campaign_name = $row['campaign_name'];
		$points = $row['points'];
		
		$campaign_element = compact('camp_campaign_id', 'campaign_name', 'points');
		array_push($camp_campaigns, $campaign_element);
	}
	
	return json_encode($camp_campaigns);
}

// $params -> campaign_id, camp_id, campaign_name, points
function add_camp_campaign($params) {
	$campaign_id = $params[0];
	$camp_id = $params[1];
	$campaign_name = $params[2];
	$points = $params[3];

	$sql = "INSERT INTO camp_campaigns SET campaign_id=" . $campaign_id . " , camp_id=" . $camp_id . " , campaign_name=" . ms($campaign_name) . " , points=" . $points;
	mq($sql);
	$camp_campaign_id = mysql_insert_id();
	
	return $camp_campaign_id;
}

// $params -> camp_campaign_id
function delete_camp_campaign($params) {
	$camp_campaign_id = $params[0];
	
	// ***** CAMP CAMPAIGN ***** //
	$sql = "DELETE FROM camp_campaigns WHERE camp_campaign_id=" . $camp_campaign_id;
	mq($sql);
	// ***** CAMP CAMPAIGN ***** //
	
	// ***** CAMP TASKS ***** //
	$sql = "SELECT * FROM camp_missions WHERE camp_campaign_id=" . $camp_campaign_id;
	$quey = mq($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$delete = "DELETE FROM camp_tasks WHERE camp_mission_id=" . $row['camp_mission_id'];
		mq($delete);
	}
	// ***** CAMP TASKS ***** //
	
	// ***** CAMP MISSIONS ***** //
	$delete = "DELETE FROM camp_missions WHERE camp_campaign_id=" . $row['camp_campaign_id'];
	mq($delete);
	// ***** CAMP MISSIONS ***** //
}

// $params -> camp_campaign_id, campaign_name, points
function save_camp_campaign($params) {
	$sql = "UPDATE camp_campaigns SET campaign_name=" . ms($params[1]) . " , points=" . $params[2] . " WHERE camp_campaign_id=" . $params[0];
	mq($sql);
}
?>