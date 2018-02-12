<?php
include ("db.php");

$function_name = $_GET['function_name'];
$parameters = $_GET['parameters'];
$parameters = explode(",", $parameters);

echo $function_name($parameters);


function install_campaign($parameters) {
	$camp_id = $parameters[0];
	$campaign_id = $parameters[1];
	$group_task = $parameters[2];
		
	$camp_campaign_id = 0;
	
	$sql = "SELECT * FROM global_campaigns WHERE campaign_id=" . $campaign_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$insert1 = "INSERT INTO camp_campaigns SET campaign_id=" . $campaign_id . ", camp_id=" . $camp_id . ", campaign_name='" . mysql_real_escape_string($row['campaign_name']) . "', points=" . $row['points'] . ", group_task=" . $group_task . ", active=1";
	$insert_query1 = mysql_query($insert1);
	
	if ($insert_query1) {
		$camp_campaign_id = mysql_insert_id();
			
		$sql = "SELECT * FROM global_missions WHERE campaign_id=" . $campaign_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$insert2 = "INSERT INTO camp_missions SET mission_id=" . $row['mission_id'] . ", camp_campaign_id=" . $camp_campaign_id . ", mission_name='" . mysql_real_escape_string($row['mission_name']) . "', points=" . $row['points']  .  ", sequence=" . $row['sequence'] ;
			$insert_query2 = mysql_query($insert2);
			$camp_mission_id = mysql_insert_id();
			
			$sql2 = "SELECT * FROM global_tasks WHERE mission_id=" . $row['mission_id'];
			$query2 = mysql_query($sql2);
			while ($row2 = mysql_fetch_assoc($query2)) {
				$insert3 = "INSERT INTO camp_tasks SET task_id=" . $row2['task_id'] . ", camp_mission_id=" . $camp_mission_id . ", camp_type_id=" . $row2['camp_type_id'] . ", level_id=" . $row2['level_id'] . ", task_name='" . mysql_real_escape_string($row2['task_name']) . "', period_id=" . $row2['period_id'] . ", points=" . $row2['points'];
				$insert_query3 = mysql_query($insert3);			
			}
		}
		
		return $camp_campaign_id;
	}
	else {
		return 0;
	}
		
}


function add_staff_type($parameters) {
	$type_name = $parameters[0];
	
	$sql = "INSERT INTO staff_types SET type_name='" . mysql_real_escape_string($type_name) . "'";
	$query = mysql_query($sql);
	if ($query)
		return  mysql_insert_id();
	else
		return 0;
}

function add_new_division($parameters) {
	$group_type_id = $parameters[0];
	$division_name = $parameters[1];
	
	$division_id = 0;
	$sql = "INSERT INTO divisions SET group_type_id=" . $group_type_id . ", division_name='" . mysql_real_escape_string($division_name) . "'";
	$query = mysql_query($sql);
	if ($query)
		return  mysql_insert_id();
	else
		return false;
}


function add_new_group_type($parameters) {
	$camp_id = $parameters[0];
	$group_type_name = $parameters[1];

	$sql = "INSERT INTO group_types SET camp_id=" . $camp_id . ", group_type_name='" . mysql_real_escape_string($group_type_name) . "'";
	$query = mysql_query($sql);	
	$group_type_id = mysql_insert_id();
	return $group_type_id;
}

function assign_staff_group($parameters) {
	$admin_id = $parameters[0];
	$group_id = $parameters[1];	
	
	$staff_group_id = 0;
	
	$sql = "INSERT INTO staff_groups SET admin_id=" . $admin_id . ", group_id=" . $group_id;
	$query = mysql_query($sql);
	if ($query)
		$staff_group_id = mysql_insert_id();		
		
	return $staff_group_id;
}

function add_new_group($parameters) {
	$division_id = $parameters[0];
	$group_name = $parameters[1];
	
	$sql = "INSERT INTO groups SET division_id=" . $division_id . ", group_name='" . mysql_real_escape_string($group_name) . "'";
	$query = mysql_query($sql);
	if ($query) {
		$group_id = mysql_insert_id();
		return json_encode($group_id);
	}
	else {
		return json_encode("0");
	}
}

function generate_new_groups($parameters) {
	$division_id = $parameters[0];
	$division_name = $parameters[1];
	$number_of_groups = $parameters[2];
	$format = $parameters[3];
	
	$error_code = 0;
	
	$letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$names = array("alef", "beis", "gimmel", "daled", "hei", "vov", "zayin", "ches", "tes", "yud", "yud alef", "yud beis", "yud gimmel", "yud daled", "tes vov", "tes zayin", "yud zayin", "yud ches", "yud tes", "chof", "chof aled", "chof beis", "chof gimmel", "chof daled", "chof hei", "chof vov", "chof zayin", "chof ches", "shof tes", "lamed");
	
	for ($cntr = 0; $cntr < $number_of_groups; $cntr++) {
		if ($format == "A") 
			$character = substr($letters, $cntr, 1);
		elseif ($format == "1")
			$character = ($cntr + 1) . "";
		else 
			$character = $names[$cntr];
		
		$group_name = $division_name . " " . $character;
	
		$sql = "SELECT * FROM groups WHERE division_id=" . $division_id . " AND group_name='" . mysql_real_escape_string($group_name) . "'";
		$query = mysql_query($sql);
		$num_rows = mysql_num_rows($query);

		if ($num_rows == 0) {
			$sql = "INSERT INTO groups SET division_id=" . $division_id . ", group_name='" . mysql_real_escape_string($group_name) . "'";
			
			if (!mysql_query($sql)) {
				 $error_code = 1;
				 break;
			}
			else {
				$new_group_id = mysql_insert_id();
			}
			
			$new_division_names = $new_division_names . $new_group_id . "~" . $group_name . "|";
		}
			
	}

	$new_division_names = substr($new_division_names, 0, strlen($new_division_names) - 1);
	
	if ($error_code == 0)
		return json_encode($new_division_names);	
	else
		return json_encode($error_code);
}

function add_group_type($parameters) {
    $camp_id = $parameters[0];
	$group_type_name = $parameters[1];

	$group_type_id = 0;
	
    $sql = "SELECT * FROM group_types WHERE group_type_name='" . mysql_real_escape_string($group_type_name) . "' AND camp_id=" . $camp_id;
    $query = mysql_query($sql);
    $num_rows = mysql_num_rows($query);

    if ($num_rows == 0) {
        $insert = "INSERT INTO group_types SET group_type_name='" . mysql_real_escape_string($group_type_name) . "', camp_id=" . $camp_id;
        $insert_query = mysql_query($insert);
		$group_type_id = mysql_insert_id();
    }
	
	if ($group_type_id > 0)
		return $group_type_id;
	else
		return false;
}

function add_group($parameters) {
    $division_id = $parameters[0];
    $group_name = $parameters[1];

	$group_id = 0;
	
    $sql = "SELECT * FROM groups WHERE group_name='" . mysql_real_escape_string($group_name) . "' AND division_id=" . $division_id;
    $query = mysql_query($sql);
    $num_rows = mysql_num_rows($query);
	
    if ($num_rows == 0) {
        $insert = "INSERT INTO groups SET division_id=" . $division_id . ", group_name='" . mysql_real_escape_string($group_name) . "'";
        $insert_query = mysql_query($insert);
        
		if ($insert_query) {
			$group_id = mysql_insert_id();
			
			// ***** Check to see if there are any tasks assigned to the division, and if there is then add the new group ***** //
			$sql = "SELECT gt.group_type_id, gt.camp_task_id, gt.period_id FROM divisions AS d JOIN group_tasks AS gt USING (division_id) WHERE d.division_id=" . $division_id;
			$query = mysql_query($sql);			
			$num_rows = mysql_num_rows($query);
			if ($num_rows > 0) {
				$row = mysql_fetch_assoc($query);
				$group_type_id = $row['group_type_id'];
				$camp_task_id = $row['camp_task_id'];
				$period_id = $row['period_id'];
					
				$insert = "INSERT INTO group_tasks SET group_type_id=" . $group_type_id . ", division_id=" . $division_id . ", group_id=" . $new_group_id . ", camp_task_id=" . $camp_task_id . ", period_id=" . $period_id . ", group_task=0";
				mysql_query($insert);
			}
			// ***** Check to see if there are any tasks assigned to the division, and if there is then add the new group ***** //
        }
    }
	
    if ($group_id > 0) {
		return $group_id;
	}
	else 
		return false;
}

function add_division($parameters) {
    $group_type_id = $parameters[0];
    $division_name = $parameters[1];
	
	$division_id = 0;
	
    $sql = "SELECT * FROM divisions WHERE group_type_id=" . mysql_real_escape_string($group_type_id) . " AND division_name='" . mysql_real_escape_string($division_name) . "'";
    $query = mq($sql);
    $num_rows = mysql_num_rows($query);
	
    if ($num_rows == 0) {

        $insert = "INSERT INTO divisions SET group_type_id=" . mysql_real_escape_string($group_type_id) .", division_name='" . mysql_real_escape_string($division_name) . "'";
        $insert_query = mq($insert);
        $division_id = mysql_insert_id();		
    }
    
	return $division_id;
}

function install_prize($parameters) {
	$camp_id = $parameters[0];
	$prize_id = $parameters[1];
	
	$sql = "SELECT prize_id, prize_name, prize_description, prize_points, prize_image_id FROM global_prizes WHERE prize_id=" . $prize_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	
	$insert = "INSERT INTO prizes_camp SET global_prize_id=" . $prize_id . ", camp_id=" . $camp_id . ", prize_name='" . mysql_real_escape_string($row['prize_name']) . "', prize_description='" .  mysql_real_escape_string($row['prize_description']) . "', prize_points=" . $row['prize_points'] . ", prize_image_id=" . $row['prize_image_id'] . ", prize_available=0";
	$insert_query = mysql_query($insert);
	if ($insert_query) 
		return true;
	else
		return false;
}

function assign_tasks($parameters) {
	$camp_id = $parameters[0];	
	$camp_task_id = $parameters[1];
	$groups = $parameters[2];
	$period_id = $parameters[3];
	$group_task = $parameters[4];
	
	$success = true;
	
	$weekdays = get_weekdays($period_id);
	$task_dates = get_task_dates($camp_id, $weekdays);	
	
	// ***** The tasks is being assigned to a GROUP TYPE or a DIVISION ***** //
	$group_type = false;
	$strrpos = strrpos($groups, "group_type");
	if ($strrpos > -1) {
		$group_type = true;
		$info = explode("_", $groups);
		$group_type_id = $info[2];		
		$success = insert_group_type_tasks($group_type_id, $camp_task_id, $period_id, $group_task);
	}
	else {
		$info = explode("_", $groups);
		$division_id = $info[1];
		$success = insert_division_tasks($division_id, $camp_task_id, $period_id, $group_task);
	}
	// ***** The tasks is being assigned to a GROUP TYPE or a DIVISION ***** //

	if ($success) {
	
		if ($group_type) {
			$info = explode("_", $groups);
			$group_type_id = $info[2];
			$sql1 = "SELECT mg.* FROM member_groups AS mg ";
			if ($group_type_id == "all") 
				$sql1 = $sql1 . "WHERE mg.camp_id=" . $camp_id . " ";
			else
				$sql1 = $sql1 . "WHERE mg.group_type_id=" . $group_type_id . " AND mg.end_date=0 ";
			$sql1 = $sql1 . " ORDER BY mg.group_type_id, mg.division_id, mg.group_id, mg.user_id";		
		}
		else {
			$info = explode("_", $groups);
			$division_id = $info[1];
			$sql1 = "SELECT mg.* FROM member_groups AS mg ";
			$sql1 = $sql1 . "WHERE mg.division_id=" . $division_id . " AND mg.end_date=0 ";
			$sql1 = $sql1 . " ORDER BY mg.division_id, mg.group_id, mg.user_id";		
		}

		$query1 = mysql_query($sql1);
		while ($row1 = mysql_fetch_assoc($query1)) {
			$group_id = $row1['group_id'];
			for ($tdno = 0; $tdno < count($task_dates); $tdno++) {
				$sql2 = "INSERT INTO member_tasks SET user_id=" . $row1['user_id'] . ", group_id=" . $row1['group_id'] . ", camp_task_id=" . $camp_task_id . ", task_date=" . $task_dates[$tdno] . ", completed=0";
				$query2 = mysql_query($sql2);				
			}
		}
	}
	
	return json_encode($success);
}

function assign_member_group($parameters) {
	$camp_id = $parameters[0];
	$user_id = $parameters[1];
	$group_type_id = $parameters[2];	
	$division_id = $parameters[3];	
	$group_id = $parameters[4];	
	
	$error_code = 0;

	$todays_date = get_todays_julian_date();
		
	$sql = "SELECT member_group_id, group_id FROM member_groups WHERE user_id=" . $user_id . " AND group_type_id=" . $group_type_id . " AND end_date=0";	
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$member_group_id = $row['member_group_id'];	
	$old_group_id = $row['group_id'];
	
	if ($member_group_id > 0) {
		// ***** Remove the camp member from the group within the division before placing him in the new group ***** //
		$sql = "UPDATE member_groups SET end_date=" . $todays_date . " WHERE member_group_id=" . $member_group_id;
		$query = mysql_query($sql);
		if (!$query)
			$error_code = 1;
			
		// ***** Remove the camp members tasks from the group within the division before assigning the new tasks for the new group ***** //
		if ($error_code == 0) {
			$sql = "DELETE FROM member_tasks WHERE user_id=" . $user_id . " AND group_id=" . $old_group_id . " AND task_date > " . $todays_date;
			$query = mysql_query($sql);
			if (!$query)
				$error_code = 2;		
		}
	}
	
	if ($error_code == 0) {
		// ***** See if there are any tasks already assigned to the group ***** //
		$sql = "SELECT * FROM group_tasks WHERE group_id=" . $group_id;
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$error_code = assign_new_tasks($camp_id, $user_id, $row['group_id'], $row['camp_task_id'], $row['period_id']);
			if ($error_code > 0)
				break;
		}
	}
	
	if ($error_code == 0) {
		// ***** Assign member to group ***** //
		$sql = "INSERT INTO member_groups SET camp_id=" . $camp_id . ", user_id=" . $user_id . ", group_type_id=" . $group_type_id . ", division_id=" . $division_id . ", group_id=" . $group_id . ", start_date=" . $todays_date;
		$query = mysql_query($sql);
		if (!$query)
			$error_code = 3;	
		else
			$error_code = mysql_insert_id();			
	}
	
	return json_encode($error_code);		
}

function assign_new_tasks($camp_id, $user_id, $group_id, $camp_task_id, $period_id) {
	$error_code = 0;
	
	$weekdays = get_weekdays($period_id);	
	
	$task_dates = get_task_dates($camp_id, $weekdays);	
	
	for ($tdno = 0; $tdno < count($task_dates); $tdno++) {
		$sql = "INSERT INTO member_tasks SET user_id=" . $user_id . ", group_id=" . $group_id . ", camp_task_id=" . $camp_task_id . ", task_date=" . $task_dates[$tdno] . ", completed=0";
		$query = mysql_query($sql);
		if (!$query) {
			$error_code = 1;
			break;
		}
	}
	
	return $error_code;
}

function get_task_dates($camp_id, $weekdays) {
	$sql = "SELECT start_date, end_date FROM camps WHERE camp_id=" . $camp_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$start_date = $row['start_date'];
	$end_date = $row['end_date'];	
	
	// ***** Get all the task dates for weekly and daily tasks ***** //
	$task_dates = array();	
	$todays_day = date("j"); 
	$todays_month = date("n"); 
	$todays_year = date("Y"); 
	$today_jd = cal_to_jd  (CAL_GREGORIAN, $todays_month,  $todays_day, $todays_year);
		
	if ($start_date < $today_jd) 
		/////////// ********** TEST ONLY ********** ////////////
		$starting_date = ($today_jd - 7);
		///////////$starting_date = $today_jd;
	else
		$starting_date = $start_date;
				
	$difference = $end_date - $starting_date;
				
	for ($cntr = 1; $cntr < $difference; $cntr++) {
		$task_date = $starting_date + $cntr;								
								
		if ($today_jd >= $start_date) {								
			$day_of_the_week = strtolower(jddayofweek($task_date, 1));
			if ($day_of_the_week == "saturday")
				$day_of_the_week = "shabbos";
			
			$strpos = strrpos($weekdays, $day_of_the_week);
			
			if ($strpos > -1) 
				array_push($task_dates, $task_date);
		}						
	}		

	return $task_dates;
}

function get_weekdays($period_id) {
	$weekdays = "";
	
	$days_of_the_week = array("monday", "tuesday", 'wednesday', 'thursday', 'friday', 'shabbos', "sunday");
	
	$sql = "SELECT * FROM periods WHERE period_id=" . $period_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	
	for ($dno = 0; $dno < count($days_of_the_week); $dno++) {
		if ($row[$days_of_the_week[$dno]] == 1) {
			$weekdays = $weekdays . $days_of_the_week[$dno] . ":";
		}
	}
	$weekdays = substr($weekdays, 0, strlen($weekdays) - 1);

	return $weekdays;
}


function add_camp_task($parameters) {
	$camp_mission_id = $parameters[0];
	$task_name = $parameters[1];
	$points = $parameters[2];
	
	$sql = "INSERT INTO camp_tasks SET camp_mission_id=" . $camp_mission_id . ", task_name='" . mysql_real_escape_string($task_name) . "', points=" . $points; 
	$query = mysql_query($sql);
	$camp_task_id = mysql_insert_id();
	if ($query)
		return json_encode($camp_task_id);
	else
		return json_encode(false);
}

function assign_group_tasks($parameters) {
	include ("classes/group_task.php");
	
	$camp_id = $parameters[0];	
	$camp_task_id = $parameters[1];
	$groups = $parameters[2];
	$period_id = $parameters[3];
	$group_task = $parameters[4];
	
	$success = true;
	
	$weekdays = get_weekdays($period_id);	
	$task_dates = get_task_dates($camp_id, $weekdays);	

	$division = false;
	$strrpos = strrpos($groups, "division");
	if ($strrpos > -1) {
		$division = true;
		$info = split("_", $groups);
		$division_id = $info[1];
		$sql = "SELECT * FROM groups WHERE division_id=" . $division_id . " ORDER BY group_id";
	}
	else {
		$info = split("_", $groups);
		$group_type_id = $info[2];	
		$sql = "SELECT * FROM divisions AS d JOIN groups AS g USING (division_id) WHERE d.group_type_id=" . $group_type_id . " ORDER BY d.division_id, g.group_id ";
	}
	
	$query = mysql_query($sql);	
	while ($row = mysql_fetch_assoc($query)) {
		$grp_tsk = new group_task();
		if ($division)
			$insert = $grp_tsk->add_new_group_task(0, $row['division_id'], $row['group_id'], $camp_task_id, $period_id, $group_task);			
		else
			$insert = $grp_tsk->add_new_group_task($row['group_type_id'], $row['division_id'], $row['group_id'], $camp_task_id, $period_id, $group_task);
				
		if ($insert) {
			$group_task_id = mysql_insert_id();
			for ($tdno = 0; $tdno < count($task_dates); $tdno++) {
				$insert2 = "INSERT INTO group_task_dates SET group_task_id=" . $group_task_id . ", camp_task_id=" . $camp_task_id . ", group_id=" . $row['group_id'] . ", task_date=" . $task_dates[$tdno] . ", completed=0";
				$insert_query2 = mysql_query($insert2);
			}		
		}
		else {
			$success = false;
			break;
		}
	}
	
	return json_encode($success);
}

function insert_division_tasks($division_id, $camp_task_id, $period_id, $group_task) {
	include ("classes/division.php");
	include ("classes/group.php");
	include ("classes/group_task.php");
	
	$error_code = true;
	
	$sql = "SELECT * FROM divisions WHERE division_id=" . $division_id;
	$query = mysql_query($sql);	
	while ($row = mysql_fetch_assoc($query)) {
		$division = new division($row);
		$division->get_groups();
		$group_type_id = $division->group_type_id;
		$division_id = $division->division_id;
		
		for ($gno = 0; $gno < count($division->groups); $gno++) {
			$group_id = $division->groups[$gno]->group_id;			
			$grp_tsk = new group_task();
			$insert = $grp_tsk->add_new_group_task(0, $division_id, $group_id, $camp_task_id, $period_id, $group_task);			
		}
		
	}
	
	return $error_code;
}

function insert_group_type_tasks($group_type_id, $camp_task_id, $period_id, $group_task) {
	include ("classes/group_type.php");
	include ("classes/division.php");
	include ("classes/group.php");
	include ("classes/group_task.php");
	
	$error_code = true;
	
	$sql = "SELECT * FROM group_types WHERE group_type_id=" . $group_type_id;
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query) ) {
		$group_type = new group_type($row);
		$group_type_id = $group_type->group_type_id;
		$group_type->get_divisions();
		
		for ($dno = 0; $dno < count($group_type->divisions); $dno++) {
			$division_id = $group_type->divisions[$dno]->division_id;
			$group_type->divisions[$dno]->get_groups();
			
			for ($gno = 0; $gno < count($group_type->divisions[$dno]->groups); $gno++) {
				$group_id = $group_type->divisions[$dno]->groups[$gno]->group_id;				
				$grp_tsk = new group_task();
				$insert = $grp_tsk->add_new_group_task($group_type_id, $division_id, $group_id, $camp_task_id, $period_id, $group_task);
			}
			
		}
		
	}
	
	return $error_code;
}

function get_todays_julian_date() {
	$todays_day = date("j"); 
	$todays_month = date("n"); 
	$todays_year = date("Y"); 
	$today_jd = cal_to_jd  (CAL_GREGORIAN, $todays_month,  $todays_day, $todays_year);

	return $today_jd;
}

// insert a record into admin_auth (mmc)
function insert_admin_auth_table($parameters) {
	$admin_id = mysql_real_escape_string($parameters[0]);
	$user_id = mysql_real_escape_string($parameters[1]);
	
	$sql = "UPDATE admins SET is_parent=1 WHERE admin_id=" . $admin_id;
	$query = mysql_query($sql);
	
	$insert = "INSERT INTO admin_auths SET admin_id = '"  . $admin_id . "', auth = 'user' ," . " id= '" .  $user_id  ."' , role_id='1'";	
	$result = mysql_query($insert);
	if (!$result)
		return json_encode(false);
	else	
		return json_encode(true);
		
}

// update user record on new registration - mmc
function update_users_for_new_registration($parameters) {
// to test: http://www.mashpia.com/admin_users_register_ajax.php?&function_name=update_users_for_new_registration&parameters=82,8253,4,true,true,true,64.00
	
	$school_id = $parameters[0];
	$user_id = $parameters[1];
	$fee_id = $parameters[2];
	$checkbox1 = $parameters[3];
	$checkbox2 = $parameters[4];
	$checkbox3 = $parameters[5];
	$registration_fee = $parameters[6];
	$user_start_date = $parameters[7];	
	$shirt_size = $parameters[8];	
	$cc_transaction_number = $parameters[9];	
	// add on one and two
	$add_on_one = 0;
	$add_on_two = 0;
	if($checkbox2 = 'true')
		$add_on_one = 1;
	if($checkbox3 = 'true')
		$add_on_two = 1;
	
	// if user_start_date is NULL then use today
	if ($user_start_date == NULL)
		$user_start_date  = unixtojd();
	
	$sql = 	" UPDATE users SET  " .
			" user_registered = NOW(), " .			
			" user_start_date = '" . $user_start_date . "', " .
			" user_registration_fee = '" . $registration_fee . "', " .
			" add_on_one = '" . $add_on_one . "', " .
			" add_on_two = '" . $add_on_two . "', " .
			" fee_id = 4 "  .  ", " .
			" cc_ref = '" . $cc_transaction_number . "', " .
			" shirt_size = '"  . $shirt_size . "'". 
			" WHERE user_id= '" . $user_id . "'" .
			" and school_id= '" . $school_id  . "'" ;

		$query = mysql_query($sql);
		if (!$query){	
			return json_encode("false");					
		}
	return json_encode("true");								
			
}	

// Create Invoice record - mmc
// to test: http://www.mashpia.com/camps/includes/add_functions.php?&function_name=insert_invoice_for_new_registration&parameters=82,50.00,school_packages,4,Registration-name-name
function insert_invoice_for_new_registration($parameters) {
	$school_id = $parameters[0];
	$item_price = $parameters[1];
	$item_ref_type = $parameters[2];
	$item_ref_id = $parameters[3];
	$item_description  = $parameters[4];		
	$item_cc_ref  = $parameters[5];		
	
	$sql = 	" INSERT INTO invoice_items (
			school_id ,			
			item_price ,
			item_date ,
			item_ref_type ,
			item_ref_id ,
			item_description,
			item_cc_ref
			)
		VALUES ('$school_id', '$item_price',  CURRENT_TIMESTAMP ,  '$item_ref_type', '$item_ref_id', '$item_description' , '$item_cc_ref') ";

		$query = mysql_query($sql);
		if (!$query){	
			return json_encode("false");					
		}
	return json_encode("true");					
}	
 

?>