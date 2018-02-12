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
	global $camp_id;
	
	$campaign_id = $params[0];
	$campaign_name = $params[1];
	$points = $params[2];
	$error_code = 0;

	$sql = "INSERT INTO camp_campaigns SET campaign_id=" . $campaign_id . " , camp_id=" . $camp_id . " , campaign_name=" . ms($campaign_name) . " , points=" . $points;
	$camp_campaign_id = 0;
	
	if (!mysql_query($sql)) 
		$error_code = 1;
	else
		$camp_campaign_id = mysql_insert_id();

	$results = compact('error_code', 'camp_campaign_id');
	
	return json_encode($results);
}

// $params -> camp_campaign_id
function delete_camp_campaign($params) {

	$error_code = 0;

	$sql = "DELETE FROM camp_campaigns WHERE camp_campaign_id=" . $params[0];
	
	if (!mysql_query($sql)) 
		$error_code = 1;
		
	$results = compact('error_code');
	
	return json_encode($results);		
}

// $params -> camp_campaign_id, campaign_name, points
function edit_camp_campaign($params) {
	
	$error_code = 0;
	
	$sql = "UPDATE camp_campaigns SET campaign_name=" . ms($params[1]) . " , points=" . $params[2] . " WHERE camp_campaign_id=" . $params[0];

	if (!mysql_query($sql))
	{
		$error_code = 1;
	
	}
		
	$results = compact('error_code');
	
	return json_encode($results);

}
?>