<?php

// $params -> mission_id, camp_campaign_id, mission_name, points
function add_camp_mission($params) {
	
	$error_code = 0;	
	$camp_mission_id = 0;
	
	$sql = "INSERT INTO camp_missions SET mission_id=" . $params[0] . ", camp_campaign_id=" . $params[1] . ", mission_name=" . ms($params[2]) . ", points=" . $params[3];

	if (!mysql_query($sql)) 
		$error_code = 1;
	else
		$camp_mission_id = mysql_insert_id();
	
	$results = compact('error_code', 'camp_mission_id');
	
	return json_encode($results);
}

// $params -> camp_mission_id
function delete_camp_mission($params) {

	$error_code = 0;
	
	$sql = "DELETE FROM camp_missions WHERE camp_mission_id=" . $params[0];
	
	if (!mysql_query($sql)) 
		$error_code = 1;

	$results = compact('error_code');
	
	return json_encode($results);	
}

// $params -> camp_mission_id, mission_name, points
function edit_camp_mission($params) {

	$error_code = 0;

	$sql = "UPDATE camp_missions SET mission_name=" . ms($params[1]) . ", points=" . $params[2] . " WHERE camp_mission_id=" . $params[0];
	
	if (!mysql_query($sql)) 
		$error_code = 1;
		
	$results = compact('error_code');
	
	return json_encode($results);		
}
?>