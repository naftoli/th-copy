<?php

// $params -> camp_mission_id
function get_camp_tasks($params) {
	$camp_mission_id = $params[0];
	
	$camp_tasks = array();
	
	$query = mq("SELECT * FROM camp_tasks WHERE camp_mission_id=" . $camp_mission_id);
	
	while ($row = mysql_fetch_assoc($query)) {
		$camp_task_id = $row['camp_task_id'];
		$task_name = $row['task_name'];
		$points = $row['points'];

		$task_element = compact('camp_task_id', 'task_name', 'points');
		array_push($camp_tasks, $task_element);
	}
	
	return json_encode($camp_tasks);
}

// $params -> task_id, camp_mission_id, camp_type_id, level_id, period_id, task_name, points, max_times, start_date, end_date, monday, tuesday, wednesday, thursday, friday, shabbos, sunday

//$params -> task_id, camp_mission_id, camp_type_id,
//function add_camp_task($params) {
//	$error_code = 0;
//	$camp_task_id = 0;
	
//	$sql = "INSERT INTO camp_tasks SET task_id=" . $params[0] . ", camp_mission_id=" . $params[1] . ", camp_type_id=" . $params[2] . ", level_id=" . $params[3] . ", period_id=" . $params[4]  . ", task_name=" . ms($params[5])  . ", points=" . $params[6]  . ", max_times=" . $params[7]  . ", start_date=" . $params[8]  . ", end_date=" . $params[9]  . ", monday=" . $params[10]  . ", tuesday=" . $params[11]  . ", wednesday=" . $params[12]  . ", thursday=" . $params[13]  . ", friday=" . $params[14]  . ", shabbos=" . $params[15] . ", sunday=" . $params[16];
//	if (!mysql_query($sql)) 
//		$error_code = 1;
//	else
//		$camp_task_id = mysql_insert_id();
		
//	$results = compact('error_code', 'camp_task_id');
	
//	return json_encode($results);	
//}

// $params -> camp_task_id
function delete_camp_task($params) {
	$error_code = 0;
	
	$sql = "DELETE FROM camp_tasks WHERE camp_task_id=" . $params[0];
	if (!mysql_query($sql)) 
		$error_code = 1;
	
	$results = compact('error_code');
	
	return json_encode($results);	
}

// $params -> camp_task_id, camp_type_id, level_id, period_id, task_name, points, max_times, start_date, end_date, monday, tuesday, wednesday, thursday, friday, shabbos, sunday
function edit_camp_task($params) {
	$error_code = 0;

	$sql = "UPDATE camp_tasks SET camp_type_id=" . $params[1] . ", level_id=" . $params[2] . ", period_id=" . $params[3] . ", task_name=" . ms($params[4]) . ", points=" . $params[5]  . ", max_times=" . $params[6]  . ", start_date=" . $params[7]  . ", max_times=" . $params[7]  . ", start_date=" . $params[8]  . ", end_date=" . $params[9]  . ", monday=" . $params[10]  . ", tuesday=" . $params[11]  . ", wednesday=" . $params[12]  . ", thursday=" . $params[13]  . ", friday=" . $params[14]  . ", shabbos=" . $params[15] . ", sunday=" . $params[16] . " WHERE camp_task_id=" . $params[0];
	if (!mysql_query($sql)) 
		$error_code = 1;
		
	$results = compact('error_code');
	
	return json_encode($results);	
}

?>