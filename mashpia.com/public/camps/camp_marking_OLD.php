<?php

function get_number_of_missions($params) {
	$task_date = $params[0];
	$group_id = $params[1];	
	
	$sql = "SELECT ct.camp_task_id, ct.task_name, cm.camp_mission_id, cm.mission_name ";
	$sql = $sql . "FROM member_tasks AS mt ";
	$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
	$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
	$sql = $sql . "WHERE mt.task_date=" . $task_date . " AND mt.group_id=" . $group_id . " ";
	$sql = $sql . "GROUP BY ct.camp_mission_id, mt.camp_task_id  ";
	$sql = $sql . "ORDER BY ct.camp_mission_id, mt.camp_task_id  ";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);

	$prev_camp_mission_id = "";
	$camp_mission_id = "";
	
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		$prev_camp_mission_id = $row['camp_mission_id'];
		
		if ( ($prev_camp_mission_id != $camp_mission_id && $camp_mission_id != "") || ($num_rows == $row_num) ) {
			$mission_number++;
		}
		
		$camp_mission_id = $prev_camp_mission_id;
	}

	return json_encode($mission_number);

}



// $params -> date, group_type_id
//function start_marking_session_users($params) {
function start_marking_session_users($params) {

//SELECT mt.group_id, g.group_name, mt.user_id, CONCAT(u.first, ' ', u.last) AS name 
//FROM member_tasks AS mt 
//JOIN groups AS g USING (group_id) 
//JOIN divisions AS d USING (division_id) 
//JOIN users AS u USING (user_id) 
//WHERE mt.task_date=2455381 AND d.group_type_id=237 
//GROUP BY mt.group_id, mt.user_id 
//ORDER BY mt.group_id, mt.user_id 

	global $camp_id;
	
	$task_date = $params[0];
	$group_type_id = $params[1];
	$group_no = $params[2];
	$mission_no_start = $params[3];
	$mission_no_end = $params[4];
	$mission_no = 0;
	
	$error_code = 0;
	
	$members = array();
		
	$sql = "";
	$sql = $sql . "SELECT gt.group_type_name, g.group_id, g.group_name, mt.user_id, CONCAT(u.first, ' ', u.last) AS name ";
	$sql = $sql . "FROM member_tasks AS mt ";
	$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
	$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
	$sql = $sql . "JOIN users AS u USING (user_id) ";	
	$sql = $sql . "JOIN groups AS g USING (group_id) ";
	$sql = $sql . "JOIN divisions AS d USING (division_id) ";
	$sql = $sql . "JOIN group_types AS gt USING (group_type_id) ";	
	$sql = $sql . "WHERE mt.task_date=" . $task_date . " AND ";
	$sql = $sql . "group_type_id=" . $group_type_id . " ";
	$sql = $sql . "GROUP BY g.group_id, name ";
	$sql = $sql . "ORDER BY g.group_id, name";
	
	$query = mq($sql);
	$num_rows = mysql_num_rows($query);
	$row_num = 0;
	
	$group_number = 0;
	$prev_group_id = "";
	
	while ($row = mysql_fetch_assoc($query)) {
		$group_type_name = $row['group_type_name'];
		$group_name = $row['group_name'];
		$group_id = $row['group_id'];
		
		$group_id = $row['group_id'];
		if ($group_id != $prev_group_id)
			$group_number++;

		if ($group_number = group_no) {	
			$name = $row['name'];
			$user_id = $row['user_id'];
			$member_element = compact('user_id', 'name');
			array_push($members, $member_element);
		}
		
		$prev_group_id = $row['group_id'];
	}
		
	$group_type_members = array();
	$group_type_members_element = compact('group_type_name',  'group_id', 'group_name', 'members');
	array_push($group_type_members, $group_type_members_element);
	
	return json_encode($group_type_members);
}

function start_marking_session_missions($params) {
	global $camp_id;
	
	$task_date = $params[0];
	$group_id = $params[1];
	$mission_no = $params[2];
	
	$missions = array();
	$tasks = array();
	
	$sql = "";
	$sql = $sql . "SELECT cm.camp_mission_id, cm.mission_name, mt.camp_task_id, ct.task_name ";
	$sql = $sql . "FROM member_tasks AS mt ";
	$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
	$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
	$sql = $sql . "WHERE mt.task_date=" . $task_date . " AND ";
	$sql = $sql . "mt.group_id=" . $group_id . " ";
	$sql = $sql . "ORDER BY cm.camp_mission_id, ct.camp_task_id ";
	//echo $sql . "<br />";
	$query = mq($sql);
	$num_rows = mysql_num_rows($query);
	
	$mission_number = 0;
	$prev_camp_mission_id = "";
	$prev_camp_task_id = "";
	
	$row_num = 0;
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		
		$camp_mission_id = $row['camp_mission_id'];
		$camp_task_id = $row['camp_task_id'];
		$task_name = $row['task_name'];		
		
		if (($camp_mission_id != $prev_camp_mission_id && $prev_camp_mission_id != "") || ($row_num == $num_rows)) {
			$mission_number++;						
			$mission_element = compact('mission_number', 'mission_name', 'tasks');
			array_push($missions, $mission_element);
			$tasks = array();
		}
		
		if ($camp_task_id != $prev_camp_task_id) {
			$task_element = compact('camp_task_id', 'task_name');
			array_push($tasks, $task_element);
		}
		
		$mission_name = $row['mission_name'];
		$prev_camp_mission_id = $camp_mission_id;
		$prev_camp_task_id = $camp_task_id;		
	}		
	
	$return_array = array();
	$return_element = compact('missions');
	array_push($return_array, $return_element);
	
	return json_encode($return_array);
}

// application/php/appInterface.php?action=start_marking_session_member_tasks&params=2455381,7536:7538,7:8:17
function start_marking_session_member_tasks($params) {
	$task_date = $params[0];
	$user_ids =explode(":", $params[1]);
	$camp_tasks_ids =explode(":", $params[2]);
	
	$member_tasks = array();
	$tasks = array();
	
	for ($cntr1 = 0; $cntr1 < count($user_ids); $cntr1++) {
	
		for ($cntr2 = 0; $cntr2 < count($camp_tasks_ids); $cntr2++) {
			$camp_task_id = $camp_tasks_ids[$cntr2];
			
			$sql = "SELECT * FROM member_tasks WHERE user_id=" . $user_ids[$cntr1] . " AND camp_task_id=" . $camp_task_id . " AND task_date=" . $task_date;
			$query = mq($sql);
			$row = mysql_fetch_assoc($query);
			$member_task_id = $row['member_task_id'];
			$completed = $row['completed'];
			
			$tasks_element = compact('member_task_id', 'completed');
			array_push($tasks, $tasks_element);
		}
		
		$member_tasks_element = compact('tasks');
		array_push($member_tasks, $member_tasks_element);
		$tasks = array();
		
	}

	$return_array = array();
	$member_tasks_info = "member_tasks_info";
	$return_element = compact('member_tasks_info', 'member_tasks');
	array_push($return_array, $return_element);

	return json_encode($return_array);
}

// $params -> member_task_id, completed
function save_member_mark($params) {
	$member_task_id = $params[0];
	$completed = $params[1];
	
	$error_code = 0;
	
	$sql = "UPDATE member_tasks SET completed=" . $completed . " WHERE member_task_id=" . $member_task_id;
	$query = mq($sql);
	if (!$query)
		$error_code = 1;
		
	//$return_code = compact($error_code);
	return json_encode($error_code);
}

// $params -> group_id
/*function get_group_members($params) {

	$group_id = $params[0];

	
	return json_encode($results);
}

// $params -> group_id
function get_next_missions($params) {

	$user_id = $params[0];
}

// $params -> member_task_id, completed
function save_member_marks($params) {

	$member_task_id = $params[0];
	$completed = $params[1];
	
	return json_encode($results);
}

function get_next_group($params) {

}

function get_next_missions($params) {

}

function save_member_marks($params) {

}
*/
?>