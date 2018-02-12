<?php
include ("db.php");

$function_name = $_GET['function_name'];
$parameters = $_GET['parameters'];
$parameters = explode(",", $parameters);

echo $function_name($parameters);

function delete_member_tasks($parameters) {

	$sql = "SELECT member_task_id FROM member_tasks  JOIN camp_tasks USING (camp_task_id) JOIN camp_missions USING (camp_mission_id) JOIN camp_campaigns USING (camp_campaign_id) WHERE group_task=1";
	$query = mysql_query($sql);
	$cnt = 0;
	while ($row = mysql_fetch_assoc($query)) {
		$cnt++;
		$member_task_id = $row["member_task_id"];
		$delete = "DELETE FROM member_tasks WHERE member_task_id=" . $member_task_id;
		$delete_query = mysql_query($delete);
	}
	echo $cnt;
}

function delete_camp_task($parameters) {
	$camp_task_id = $parameters[0];
	
	$sql = "DELETE FROM camp_tasks WHERE camp_task_id=" . $camp_task_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;	
}

function deactivate_campaign($parameters) {
	$camp_campaign_id = $parameters[0];
		
	$sql = "DELETE FROM camp_campaigns WHERE camp_campaign_id=" . $camp_campaign_id;
	$query = mysql_query($sql);
	if ($query) {
		$sql1 = "SELECT * FROM camp_missions WHERE camp_campaign_id=" . $camp_campaign_id;
		$query1 = mysql_query($sql1);
		while ($row1 = mysql_fetch_assoc($query1)) {
			$sql2 = "DELETE FROM camp_tasks WHERE camp_mission_id=" . $row1['camp_mission_id'];
			$query2 = mysql_query($sql2);
		}
		
		$sql3 = "DELETE FROM camp_missions WHERE camp_campaign_id=" . $camp_campaign_id;
		$query3 = mysql_query($sql3);
		
		return true;
	}
	else {
		return false;	
	}
}


function delete_user($parameters) {
	$user_id = $parameters[0];
	
	$sql = "DELETE FROM users WHERE user_id=" . $user_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;
}

function delete_group_type($parameters) {
	$group_type_id = $parameters[0];
	
	$sql = "DELETE FROM group_types WHERE group_type_id=" . $group_type_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;
}

function remove_group_type_task($parameters) {
	$camp_task_id = $parameters[0];
	$group_type_id = $parameters[1];
	$group_task = $parameters[2];
	
	$success = true;
	$todays_date = get_todays_julian_date();

	// ***** Remove all future tasks for the group members within the group type ***** //
	$sql = "SELECT * FROM group_tasks WHERE group_type_id=" . $group_type_id . " AND camp_task_id=" . $camp_task_id . " ORDER BY group_id";
	//////////echo $sql . "<br />";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$group_id = $row['group_id'];
		if ($group_task == 0)
			$delete = "DELETE FROM member_tasks WHERE group_id=" . $group_id . " AND camp_task_id=" . $camp_task_id . " AND task_date > " . $todays_date;		
		else
			$delete = "DELETE FROM group_task_dates WHERE group_id=" . $group_id . " AND camp_task_id=" . $camp_task_id . " AND task_date > " . $todays_date;		
		//////////echo $delete . "<br />";
		
		$delete_query = mysql_query($delete);
		if (!$delete_query) {
			$success = false;
			break;
		}
	}
	// ***** Remove all future tasks for the group members within the group type ***** //
	
	// ****** Remove the group type task ***** //
	if ($success) {
		$sql = "DELETE FROM group_tasks WHERE group_type_id=" . $group_type_id . " AND camp_task_id=" . $camp_task_id . " AND group_task=" . $group_task;
		//////////echo $sql . "<br />";
		$query = mysql_query($sql);
		if (!$query)
			$success = false;
	}
	// ****** Remove the group type task ***** //
	
	return json_encode($success);
}

function remove_divisions($params) {
	$group_type_id = $params[0];
	
	$sql = "DELETE FROM divisions WHERE group_type_id=" . $group_type_id;
	$query = mysql_query($sql);
	if ($query) {
		$insert = "INSERT INTO divisions SET group_type_id=" . $group_type_id . ", division_name='No Divisions'";
		$insert_query = mysql_query($insert);
		return true;
	}
	else {
		return false;
	}
}

function remove_group_type($parameters) {
	$group_type_id = $parameters[0];
	
	$sql = "DELETE FROM group_types WHERE group_type_id=" . $group_type_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;
}

function remove_member_group($parameters) {
	$member_group_id = $parameters[0];
	$group_id = $parameters[1];
	$user_id = $parameters[2];
	
	$todays_date = get_todays_julian_date();

	$sql = "UPDATE member_groups SET end_date=" . $todays_date . " WHERE member_group_id=" . $member_group_id;
	$query = mysql_query($sql);
	if ($query) {
		$sql = "DELETE FROM member_tasks WHERE user_id=" . $user_id . " AND group_id=" . $group_id . " AND task_date > " . $todays_date;
		$query = mysql_query($sql);	
		if ($query)		
			return true;
		else
			return false;
	}
	else {
		return false;
	}
	
}

function deassign_staff_group($parameters) {
	$admin_id = $parameters[0];
	$group_id = $parameters[1];	
	
	$sql = "DELETE FROM staff_groups WHERE admin_id=" . $admin_id . " AND group_id=" . $group_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;
}

function remove_staff_member($parameters) {
	$staff_group_id = $parameters[0];
	
	$sql = "DELETE FROM staff_groups WHERE staff_group_id=" . $staff_group_id;	
	$query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;
}

function remove_group($parameters) {
	$group_id = $parameters[0];

	$sql = "DELETE FROM groups WHERE group_id=" . $group_id;
	$query = mysql_query($sql);
	if ($query) true;
	else
		return false;
}

function delete_group($parameters) {
    $group_id = $parameters[0];

    $sql = "DELETE FROM groups WHERE group_id=" . $group_id;
    $query = mysql_query($sql);
    
    if ($query) 
		return true;
    else
		return false;
}

function delete_division($parameters) {
    $division_id = $parameters[0];

    $sql = "DELETE FROM divisions WHERE division_id=" . $division_id;
    $query = mq($sql);
    
    if ($query) 
        return true;
	else
		return false;
}

function delete_prize($parameters) {
	$prize_id = $parameters[0];
	
	$sql = "DELETE FROM prizes_camp WHERE prize_id=" . $prize_id;
	$query = mysql_query($sql);
	if ($insert_query) 
		return true;
	else
		return false;
}


function remove_division_task($parameters) {
	$camp_task_id = $parameters[0];
	$division_id = $parameters[1];
	$group_task = $parameters[2];
	
	$success = true;
	
	$todays_date = get_todays_julian_date();

	// ***** Remove all future tasks for the group members within the division ***** //
	$sql = "SELECT * FROM group_tasks WHERE division_id=" . $division_id . " AND camp_task_id=" . $camp_task_id;
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$group_id = $row['group_id'];
		if ($group_task == 0)
			$delete = "DELETE FROM member_tasks WHERE group_id=" . $group_id . " AND camp_task_id=" . $camp_task_id . " AND task_date > " . $todays_date;
		else
			$delete = "DELETE FROM group_task_dates WHERE group_id=" . $group_id . " AND camp_task_id=" . $camp_task_id . " AND task_date > " . $todays_date;		

		$delete_query = mysql_query($delete);
		if (!$delete_query) {
			$success = false;
			break;
		}
	}
	// ***** Remove all future tasks for the group members within the division ***** //
	
	// ****** Remove the division task ***** //
	if ($success) {
		$sql = "DELETE FROM group_tasks WHERE division_id=" . $division_id . " AND camp_task_id=" . $camp_task_id;
		$query = mysql_query($sql);
		if (!$query)
			$success = false;
	}
	// ****** Remove the division task ***** //
	
	return json_encode($success);
}

function get_todays_julian_date() {
	$todays_day = date("j"); 
	$todays_month = date("n"); 
	$todays_year = date("Y"); 
	$today_jd = cal_to_jd  (CAL_GREGORIAN, $todays_month,  $todays_day, $todays_year);

	return $today_jd;
}


// delete entry in admin_auths table (mmc)
function delete_parent_child_relationship($parameters)
{
	//echo "!";
	$admin_id = mysql_real_escape_string($_COOKIE['admin_id']);	
	$id = mysql_real_escape_string($parameters[0]);
	
	$error_code = 0;
	$sql = "DELETE FROM admin_auths WHERE admin_id = '" . $admin_id . "'  and auth = 'user' and id = '" . $id . "'  ";

	$query = mysql_query($sql);
	//return json_encode($error_code);
	return json_encode(0);
}


?>