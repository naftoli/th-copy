<?php 

function get_global_campaigns($params)
{	
	$campaigns = array();
	$missions = array();
	$tasks = array();

	$sql = "SELECT gc.campaign_id, gc.campaign_name, gm.mission_id, gm.mission_name, gm.sequence, gt.task_id, gt.task_name FROM global_campaigns AS gc JOIN global_missions AS gm USING (campaign_id) JOIN global_tasks AS gt USING (mission_id) ORDER BY gc.campaign_id, gm.mission_id, gm.sequence, gt.task_id";
	
	//echo $sql . "<br />";
	
	$query = mq($sql);
	
	$prev_campaign_id = "";
	$prev_mission_id = "";
	
	while ($row = mysql_fetch_assoc($query)) {
		
		// CAMPAIGN //
	    $campaign_id = $row['campaign_id'];
        $campaign_name = $row['campaign_name'];
        
		// MISSION //
	    $mission_id = $row['mission_id'];
        $mission_name = $row['mission_name'];
		$sequence = $row['sequence'];

		// TASK //
	    $task_id = $row['task_id'];
        $task_name = $row['task_name'];
				
		// CAMPAIGNS //
		if ($prev_campaign_id != $campaign_id && $prev_campaign_id != "") {	
			//echo "CAMPAIGN ID:" . $row['campaign_id'] . "<br />";
			$campaign_element = compact('campaign_id', 'campaign_name', 'missions');
			array_push($campaigns, $campaign_element);
			$missions = array();
		}
		
		// MISSIONS //
		if ($prev_mission_id != $row['mission_id'] && $prev_mission_id != "") {
			//echo "MISSION ID:" . $row['mission_id'] . "<br />";
			$mission_element = compact('mission_id', 'mission_name', 'sequence', 'tasks');
			array_push($missions, $mission_element);
			$tasks = array();
		}
		
		// TASKS //
		$task_element = compact('task_id', 'task_name');
		array_push($tasks, $task_element);
		
		//echo "CAMPAIGN ID:" . $row['campaign_id'] . " MISSION ID:" . $row['mission_id'] . " TASK ID:" . $row['task_id'] . "<br />";
		
		$prev_campaign_id = $row['campaign_id'];
		$prev_mission_id = $row['mission_id'];

	}

	//echo "Array size: ".count($campaigns);
	
	return json_encode($campaigns);
}

?>