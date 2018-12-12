<?php 
function assign_tasks($params) {
	$camp_id = $params[0];	
	$camp_task_id = $params[1];
	$groups = $params[2];
	$period_id = $params[3];

	$error_code = 0;
	
	$weekdays = get_weekdays($period_id);	
	$task_dates = get_task_dates($camp_id, $weekdays);	
	
	// ***** The tasks is being assigned to a GROUP TYPE or a DIVISION ***** //
	$pos = strrpos($groups, "group_type");
	if ($pos > -1) {
		$gt_or_div = "group_type";
		$info = explode("_", $groups);
		$group_type_id = $info[2];		
		$error_code = insert_group_type_tasks($group_type_id, $camp_task_id, $period_id);
	}
	else {
		$gt_or_div = "division";
		$info = explode("_", $groups);
		$division_id = $info[1];
		$error_code = insert_division_tasks($division_id, $camp_task_id, $period_id);
	}
	// ***** The tasks is being assigned to a GROUP TYPE or a DIVISION ***** //
	
	if ($error_code == 0) {
	
		if ($gt_or_div == "group_type") { 	
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
		
		// ***** GROUP TYPE ***** //
		$query1 = mysql_query($sql1);
		$num_rows = mysql_num_rows($query1);
		$prev_group_id = "";
		$break_flag = false;
		
		while ($row1 = mysql_fetch_assoc($query1)) {
			$group_id = $row1['group_id'];
				
			for ($tdno = 0; $tdno < count($task_dates); $tdno++) {
				$sql3 = "INSERT INTO member_tasks SET user_id=" . $row1['user_id'] . ", group_id=" . $row1['group_id'] . ", camp_task_id=" . $camp_task_id . ", task_date=" . $task_dates[$tdno] . ", completed=0";
				$query3 = mysql_query($sql3);
				if (!$query3) {
					$error_code = 2;
					$break_flag = true;
					break;
				}
			}
				
			if ($break_flag)
				break;
					
			$prev_group_id = $group_id;
		}

	}
	
	return $error_code;
}

function update_user($params) {	
	$user_id = $params[0]; 
	$item_name = $params[1]; 
	$value = $params[2];
	
	include ("classes/user.php");
	$user = new user();
	return $user->update_item($user_id, $item_name, $value);
}

function update_admin($params) {	
	$admin_id = $params[0]; 
	$field_name = $params[1]; 
	$value = $params[2];
	
	include ("classes/admin.php");
	$admin = new \camps\classes\admin();
	return $admin->update_item($admin_id, $field_name, $value);
}

function delete_prize($params) {
	$prize_id = $params[0];
	
	include ("classes/camp_prize.php");
	$prize = new camp_prize();
	return $prize->delete_prize($prize_id);
}

function install_prize($params) {
	$camp_id = $params[0];
	$prize_id = $params[1];
	
	include ("classes/camp_prize.php");
	$prize = new camp_prize();
	return $prize->install_global_prize($camp_id, $prize_id);
}

function uninstall_prize($params) {
	$prize_id = $params[0];
	
	$sql = "UPDATE prizes_camp SET installed=0 WHERE prize_id=" . $prize_id;
	$query = mysql_query($sql);
	
	if (!$query) 
		return json_encode("1");
	else 
		return json_encode("0");
}

function update_prize($params) {
	$prize_id = $params[0]; 
	$prize_name = $params[1]; 
	$prize_description = $params[2];
	$prize_points = $params[3];
	$prize_available = $params[4];
	
	include ("classes/camp_prize.php");
	$prize = new camp_prize();
	$update = $prize->update_prize($prize_id, $prize_name, $prize_description, $prize_points, $prize_available);	
	
	return json_encode($update);
}

function get_camp_prizes($params){
	$camp_id = $params[0];

	include ("classes/camp_prize.php");
	
	$prizes = array();
	$sql = "SELECT * FROM prizes_camp WHERE camp_id=" .  $camp_id . " and installed=1";
	$query = mq($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$prize = new camp_prize();
		$prize->new_camp_prize($row);	
		$element = compact('prize');
		array_push($prizes, $element);
	}

    return json_encode($prizes);
}

function install_campaign($params) {
	$camp_id = $params[0];
	$campaign_id = $params[1];
	$group_task = $params[2];
	
	include ("classes/camp_campaign.php");
	
	$camp_campaign = new camp_campaign();
	$camp_campaign_id = $camp_campaign->install_campaign($camp_id, $campaign_id, $group_task);
	
	return json_encode($camp_campaign_id);
}

function deactivate_campaign($params) {
	$camp_campaign_id = $params[0];
	
	include ("classes/camp_campaign.php");
	
	$camp_campaign = new camp_campaign();
	$deactivation = $camp_campaign->deactivate_campaign($camp_campaign_id);
	
	return json_encode($deactivation);
}

function get_member_global_campaigns($params) {
	$camp_id = $params[0];
	$group_task = $params[1];
	
	$global_campaigns = array();
	$campaigns = array();
	
	$sql = "SELECT gc.campaign_id, gc.campaign_name, cc.camp_campaign_id, cc.active ";
	$sql = $sql . "FROM global_campaigns AS gc ";
	$sql = $sql . "LEFT JOIN camp_campaigns AS cc ON (gc.campaign_id=cc.campaign_id AND cc.camp_id=" . $camp_id. ") ";
	$sql = $sql . "WHERE gc.group_task=" . $group_task;
	$query = mysql_query($sql);

	while ($row = mysql_fetch_assoc($query)) {
		$campaign_id = $row['campaign_id'];
		$campaign_name = $row['campaign_name'];
		$camp_campaign_id = $row['camp_campaign_id'];
		$active = $row['active'];
		
		$element = compact('campaign_id', 'campaign_name', 'camp_campaign_id', 'active');
		array_push($campaigns, $element);
	}
	
	$element = compact('campaigns');
	array_push($global_campaigns, $element);
	
	return json_encode($global_campaigns);
}

function update_group_task($params) {
	$group_task_date_id = $params[0];
	$completed = $params[1];
	
	$sql = "UPDATE group_task_dates SET completed=" . $completed . " WHERE group_task_date_id=" . $group_task_date_id;
	$query = mysql_query($sql);
	if ($query)
		return json_encode("0");
	else
		return json_encode("1");	
}

function group_date_tasks($params) {
	$task_date = $params[0]; 
	$group_ids =explode(":", $params[1]); 
	$camp_task_ids =explode(":", $params[2]);
	
	$date_tasks = array();
	$group_tasks = array();
	
	for ($gno = 0; $gno < count($group_ids); $gno++) {
		$group_id = $group_ids[$gno];
		
		for ($ctno = 0; $ctno < count($camp_task_ids); $ctno++) {
			$camp_task_id = $camp_task_ids[$ctno];
			
			$sql = "SELECT group_task_date_id, completed ";
			$sql = $sql . "FROM group_task_dates ";
			$sql = $sql . "WHERE task_date=" . $task_date . " AND group_id=" . $group_id . " AND camp_task_id=" . $camp_task_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
	
			$group_task_date_id = $row['group_task_date_id'];
			$completed = $row['completed'];
		
			$element = compact('group_task_date_id', 'completed');
			array_push($date_tasks, $element);
		}
		
		$element = compact('group_id', 'date_tasks');
		array_push($group_tasks, $element);
		$date_tasks = array();
	}
	
	return json_encode($group_tasks);	
}

function get_group_point_groups($params) {
	$group_type_id = $params[0];	
	$task_date = $params[1];

	$divisions = array();
	$groups = array();
	
	$sql = "SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name, g.group_id, g.group_name ";
	$sql = $sql . "FROM group_task_dates AS gtd ";
	$sql = $sql . "JOIN group_tasks AS gt1 ON (gtd.group_task_id=gt1.group_task_id) ";
	$sql = $sql . "JOIN groups AS g ON (gt1.group_id=g.group_id) ";
	$sql = $sql . "JOIN divisions AS d ON (g.division_id=d.division_id) ";
	$sql = $sql . "JOIN group_types AS gt ON (d.group_type_id=gt.group_type_id) ";
	$sql = $sql . "WHERE gtd.task_date=" . $task_date. " AND d.group_type_id=" . $group_type_id . " ";
	$sql = $sql . "ORDER BY d.division_id, g.group_id ";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	$division_id = "";
	$group_id = "";
	$row_num = 0;
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		$prev_division_id = $row['division_id'];
		$prev_group_id = $row['group_id'];
				
		if ($group_id != $prev_group_id && $group_id != "") {
			$element = compact('group_id', 'group_name');
			array_push($groups, $element);
		}
		
		if ($division_id != $prev_division_id && $division_id != "") {
			$element = compact('division_name', 'groups');
			array_push($divisions, $element);
			$groups = array();
		}
		
		$division_id = $prev_division_id;
		$division_name = $row['division_name'];		
		$group_id = $prev_group_id;
		$group_name = $row['group_name'];
		//$group_type_name = $row['group_type_name'];		
		
		if ($row_num == $num_rows) {
			$element = compact('group_id', 'group_name');
			array_push($groups, $element);
			$element = compact('division_name', 'groups');
			array_push($divisions, $element);			
		}
	}

	return json_encode($divisions);
}

function get_group_point_missions($params) {
	$group_type_id = $params[0];	
	$task_date = $params[1];

	$campaigns = array();
	$missions = array();
	$tasks = array();
	
	$sql = "SELECT cc.camp_campaign_id, cc.campaign_name, cm.camp_mission_id, cm.mission_name, gtd.camp_task_id, ct.task_name ";
	$sql = $sql . "FROM group_task_dates AS gtd ";
	$sql = $sql . "JOIN camp_tasks AS ct ON (gtd.camp_task_id=ct.camp_task_id) ";
	$sql = $sql . "JOIN camp_missions AS cm ON (ct.camp_mission_id=cm.camp_mission_id) ";
	$sql = $sql . "JOIN camp_campaigns AS cc ON (cm.camp_campaign_id=cc.camp_campaign_id) ";
	$sql = $sql . "JOIN group_tasks AS gt1 ON (gtd.group_task_id=gt1.group_task_id) ";
	$sql = $sql . "JOIN groups AS g ON (gt1.group_id=g.group_id) ";
	$sql = $sql . "JOIN divisions AS d ON (g.division_id=d.division_id) ";
	$sql = $sql . "WHERE gtd.task_date=" . $task_date. " AND d.group_type_id=" . $group_type_id . " ";
	$sql = $sql . "ORDER BY cc.campaign_id, cm.mission_id, gtd.camp_task_id, d.group_type_id, d.division_id, g.group_id ";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);

	$row_num = 0;
	$campaign_name = "";
	$mission_name = "";
	$task_name = "";
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		
		$prev_campaign_name = $row['campaign_name'];
		$prev_mission_name = $row['mission_name'];
		$prev_task_name = $row['task_name'];
		
		if ($task_name != $prev_task_name && $task_name != "") {
			$element = compact('camp_task_id', 'task_name');
			array_push($tasks, $element);
		}
					
		if ($mission_name != $prev_mission_name && $mission_name != "") {
			$element = compact('camp_mission_id', 'mission_name', 'tasks');
			array_push($missions, $element);
			$tasks = array();
		}
		
		if ($campaign_name != $prev_campaign_name && $campaign_name != "") {
			$element = compact('camp_campaign_id', 'campaign_name', 'missions');
			array_push($campaigns, $element);
			$missions = array();
		}		
				
		$camp_campaign_id = $row['camp_campaign_id'];
		$campaign_name = $prev_campaign_name;
					
		$camp_mission_id = $row['camp_mission_id'];
		$mission_name = $prev_mission_name;
		
		$camp_task_id = $row['camp_task_id'];
		$task_name = $prev_task_name;
		
		if ($row_num == $num_rows) {
			$element = compact('camp_task_id', 'task_name');
			array_push($tasks, $element);
		
			$element = compact('camp_mission_id', 'mission_name', 'tasks');
			array_push($missions, $element);

			$element = compact('camp_campaign_id', 'campaign_name', 'missions');
			array_push($campaigns, $element);
		}	
	}

	return json_encode($campaigns);
}

function assign_group_tasks($params) {
	$camp_id = $params[0];	
	$camp_task_id = $params[1];
	$groups = $params[2];
	$period_id = $params[3];

	$error_code = 0;
	
	$weekdays = get_weekdays($period_id);	
	$task_dates = get_task_dates($camp_id, $weekdays);	
	
	$strrpos = strrpos($groups, "division");
	
	$division = 0;
	if ($strrpos > -1) {
		$division = 1;
		$info =explode("_", $groups);
		$division_id = $info[1];
		$sql = "SELECT * FROM groups WHERE division_id=" . $division_id . " ORDER BY group_id";
	}
	else {
		$info =explode("_", $groups);
		$group_type_id = $info[2];	
		$sql = "SELECT * FROM divisions AS d JOIN groups AS g USING (division_id) WHERE d.group_type_id=" . $group_type_id . " ORDER BY d.division_id, g.group_id ";
	}
	
	$break_flag = false;
	$query = mysql_query($sql);	
	while ($row = mysql_fetch_assoc($query)) {
		if ($division)
			$insert = "INSERT INTO group_tasks SET division_id=" . $row['division_id'] . ", group_id=" . $row['group_id'] . ", camp_task_id=" . $camp_task_id . ", period_id=" . $period_id . ", group_task=1";
		else
			$insert = "INSERT INTO group_tasks SET group_type_id=" . $row['group_type_id'] . ", division_id=" . $row['division_id'] . ", group_id=" . $row['group_id'] . ", camp_task_id=" . $camp_task_id . ", period_id=" . $period_id . ", group_task=1";		
			
		$insert_query = mysql_query($insert);
				
		if ($insert_query) {
			$group_task_id = mysql_insert_id();
			for ($tdno = 0; $tdno < count($task_dates); $tdno++) {
				$insert2 = "INSERT INTO group_task_dates SET group_task_id=" . $group_task_id . ", camp_task_id=" . $camp_task_id . ", group_id=" . $row['group_id'] . ", task_date=" . $task_dates[$tdno] . ", completed=0";
				$insert_query2 = mysql_query($insert2);
				if (!$query) {
					$error_code = 1;
					$break_flag = true;
					break;
				}
			}
			if ($break_flag)
				break;
		}
		else {
			$error_code = 1;
			break;
		}
		
	}
		
	return json_encode($error_code);
}


function update_camp($params) {	
	$camp_id = $params[0]; 
	$field_name = $params[1]; 
	$value = $params[2];
	
	$sql = "UPDATE camps SET " . $field_name . "='" . mysql_real_escape_string($value) . "' WHERE camp_id=" . $camp_id;
	$query = mysql_query($sql);
	if ($query)
		return json_encode("0");
	else
		return json_encode("1");
}

function get_group_task($params) {
	$group_id = $params[0];
	$camp_task_id = $params[1];
	
	$sql = "SELECT * FROM group_tasks WHERE group_id=" . $group_id . " AND camp_task_id=" . $camp_task_id;
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	return json_encode($num_rows);
}

/*function get_camp_missions($params) {
	$camp_id = $params[0];
	$group_task = $params[1];
	
	$campaigns = array();
	$missions = array();
	 
	$camp_campaign_id = $params[0];

	$prev_camp_campaign_id = "";
	$prev_camp_mission_id = "";
	
	$sql = "";
	$sql = $sql . "SELECT cc.camp_campaign_id, cc.campaign_name, cm.camp_mission_id, cm.mission_name ";
	$sql = $sql . "FROM camp_campaigns AS cc ";
	$sql = $sql . "JOIN camp_missions AS cm USING (camp_campaign_id) ";
	$sql = $sql . "WHERE cc.camp_id=" . $camp_id . " AND cc.active=1 AND cc.group_task=" . $group_task;	
	$query = mq($sql);	
	$num_rows = mysql_num_rows($query);
	$row_num = 0;
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		
		$prev_camp_campaign_id  = $row['camp_campaign_id'];
		$prev_camp_mission_id  = $row['camp_mission_id'];
		
		if ($prev_camp_mission_id != $camp_mission_id && $camp_mission_id != "") {
			$sql1 = "SELECT count(*) AS number_of_tasks FROM camp_tasks WHERE camp_mission_id=" . $camp_mission_id;
			$row1 = mysql_fetch_assoc(mysql_query("SELECT count(*) AS number_of_tasks FROM camp_tasks WHERE camp_mission_id=" . $camp_mission_id));
			$number_of_tasks = $row1['number_of_tasks'];
			
			$mission = compact('camp_mission_id', 'mission_name', 'number_of_tasks');
			array_push($missions, $mission);
			$number_of_tasks = 0;
		}
		
		if ($prev_camp_campaign_id != $camp_campaign_id && $camp_campaign_id != "") {
			$campaign = compact('camp_campaign_id', 'campaign_name', 'missions');
			array_push($campaigns, $campaign);
			$missions = array();
		}

		$camp_campaign_id  = $prev_camp_campaign_id;
		$campaign_name = $row['campaign_name'];
				
		$camp_mission_id = $prev_camp_mission_id;
		$mission_name = $row['mission_name'];
		
		if ($row_num == $num_rows) {
			$row = mysql_fetch_assoc(mysql_query("SELECT count(*) AS number_of_tasks FROM camp_tasks WHERE camp_mission_id=" . $camp_mission_id));
			$number_of_tasks = $row['number_of_tasks'];
		
		 	$mission = compact('camp_mission_id', 'mission_name', 'number_of_tasks');
			array_push($missions, $mission);
			$campaign = compact('camp_campaign_id', 'campaign_name', 'missions');
			array_push($campaigns, $campaign);
		}		
	}
	
	return json_encode($campaigns);
}*/

function scan_voucher($params) {
	$voucher_id = $params[0];
	$sql = "SELECT sp.store_purchase_id, sp.prize_quantity, u.first, u.last, pc.prize_name ";
	$sql = $sql . "FROM store_purchases AS sp ";
	$sql = $sql . "JOIN users AS u USING (user_id) ";
	$sql = $sql  ."JOIN prizes_camp AS pc USING (prize_id) ";
	$sql = $sql . "WHERE voucher_id=" . $voucher_id . " AND sp.scan_date IS NULL";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$store_purchase_id = $row['store_purchase_id'];
	$prize_quantity = $row['prize_quantity'];
	$first = $row['first'];
	$last = $row['last'];
	$prize_name = $row['prize_name'];
	
	if ($store_purchase_id > 0) {
		$update = "UPDATE store_purchases SET scan_date=NOW() WHERE store_purchase_id=" . $store_purchase_id;
		$update_query = mysql_query($update);
		if ($update_query) {
			$message = $first . " " . $last . " is entitled to " . $prize_quantity . " " . $prize_name . ".";
			return json_encode($message);
		}
		else {
			return json_encode("1");
		}
	}
	else {
		return json_encode("1");
	}	
}

function reinstall_prize($params) {
	$prize_id = $params[0];
	
	$sql = "UPDATE prizes_camp SET installed=1 WHERE prize_id=" . $prize_id;
	$query = mysql_query($sql);
	
	if (!$query) 
		return json_encode("1");
	else 
		return json_encode("0");
}




function get_ranks($params) {
	$ranks = array();
	
	$sql = "SELECT rank_ord, rank_name FROM ranks WHERE rank_image_id IS NOT NULL";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$rank_ord = $row['rank_ord'];
		$rank_name = $row['rank_name'];
		
		$element = compact('rank_ord', 'rank_name');
		array_push($ranks, $element);
	}

	return json_encode($ranks);	
}

function member_date_tasks($params) {
	$task_date = $params[0];
	$user_ids =explode(":", $params[1]);
	$camp_tasks_ids =explode(":", $params[2]);
	
	$member_tasks = array();
	$tasks = array();
	
	for ($cntr1 = 0; $cntr1 < count($user_ids); $cntr1++) {
	
		for ($cntr2 = 0; $cntr2 < count($camp_tasks_ids); $cntr2++) {
			$camp_task_id = $camp_tasks_ids[$cntr2];
			
			$sql = "SELECT * ";
			$sql = $sql . "FROM member_tasks ";
			$sql = $sql . "WHERE user_id=" . $user_ids[$cntr1] . " AND camp_task_id=" . $camp_task_id . " AND task_date=" . $task_date . " ";
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

function assign_member_group($params) {
	$camp_id = $params[0];
	$user_id = $params[1];
	$group_type_id = $params[2];	
	$division_id = $params[3];	
	$group_id = $params[4];	
	
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

function get_non_assigned_members($params) {
	$camp_id = $params[0];
	$group_id = $params[1];
	
	$campers = array();
	
	$sql = "SELECT u.user_id, u.first, u.last, u.user_photo_id ";
	$sql = $sql . "FROM users AS u ";
	$sql = $sql . "LEFT JOIN member_groups AS mg ON (mg.user_id=u.user_id AND mg.group_id=" . $group_id . " AND mg.end_date=0) ";
	$sql = $sql . "WHERE u.camp_id=" . $camp_id . " AND mg.member_group_id IS NULL";
	
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$user_id = $row['user_id'];
		$user_photo_id = $row['user_photo_id'];
		$first = $row['first'];
		$last = $row['last'];
		
		$element = compact('user_id', 'user_photo_id', 'first', 'last');
		array_push($campers, $element);	
	}
	
	return json_encode($campers);
}

function add_group_type($params) {
    $camp_id = $params[0];
	$new_group_type_name = $params[1];

    $new_group_type_id = 0;
    $error_code = 0;
     
    // Get the group type name
    $new_group_type_name = $params[0];

    // Check that the new group type does already exist    
    $new_group_type_query = 
        "SELECT * FROM group_types WHERE group_type_name=" . ms($new_group_type_name) . " AND camp_id=" . $camp_id;

    $new_group_type_query_result = mq($new_group_type_query);
    $num_rows = mysql_num_rows($new_group_type_query_result);

    // If the new group type does not yet exist, then
    // attempt to insert
    if ($num_rows == 0) {

        $new_group_type_query = "INSERT INTO group_types SET group_type_name=" . ms($new_group_type_name) . ", camp_id=" . $camp_id;
        $new_group_type_query_result = mq($new_group_type_query);
        if ($new_group_type_query_result == FALSE) {
            $error_code = 2;
        }
        else {
            // Do a third query to get back the new group type id
            $new_group_type_query = 
                "SELECT * FROM group_types WHERE group_type_name=" . ms($new_group_type_name) . " AND camp_id=" . $camp_id;
            
            $new_group_type_query_result = mq($new_group_type_query);
            $group_type_row = mysql_fetch_assoc($new_group_type_query_result);
            
            $new_group_type_id = $group_type_row['group_type_id'];
        }
    }
    else {
        $error_code = 1;
    }

    // Generate results
    $results = compact('error_code', 'new_group_type_id');
    
    // Return the array of all group types
    return json_encode($results);
}


function get_group_date_tasks($params) {
	$task_date = $params[0];
	$group_id = $params[1];	
	$mission_no = $params[2];
	
	$missions = array();
	$tasks = array();
	
	$sql = "SELECT ct.camp_task_id, ct.task_name, cm.camp_mission_id, cm.mission_name ";
	$sql = $sql . "FROM member_tasks AS mt ";
	$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
	$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
	$sql = $sql . "JOIN camp_campaigns AS cc USING (camp_campaign_id) ";
	$sql = $sql . "WHERE mt.task_date=" . $task_date . " AND mt.group_id=" . $group_id . " ";
	$sql = $sql . "GROUP BY ct.camp_mission_id, mt.camp_task_id  ";
	$sql = $sql . "ORDER BY cc.campaign_id, ct.camp_mission_id, mt.camp_task_id  ";
		
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);

	$prev_camp_mission_id = "";
	$mission_number = 0;
	$last_mission = false;
	$row_num = 0;
	$camp_mission_id = "";
	
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		$prev_camp_mission_id = $row['camp_mission_id'];
		
		if ($prev_camp_mission_id != $camp_mission_id && $camp_mission_id != "") {
			$mission_number++;
			if ($mission_number == $mission_no) {
				$element = compact('camp_mission_id', 'mission_name', 'last_mission', 'tasks');
				array_push($missions, $element);
				break;
			}
			
		}
		
		if ($mission_number == ($mission_no - 1)) {
			$camp_task_id = $row['camp_task_id'];
			$task_name = $row['task_name'];
			$element = compact('camp_task_id', 'task_name');
			array_push($tasks, $element);
		}
		
		
		$camp_mission_id = $prev_camp_mission_id;
		$mission_name = $row['mission_name'];
		
		if ($num_rows == $row_num) {
			$last_group = true;
			$element = compact('mission_id', 'mission_name', 'last_group', 'tasks');
			array_push($missions, $element);		
		}
		
	}

	return json_encode($missions);
}

function remove_divisions($params) {
	$group_type_id = $params[0];
	
	$error_code = 0;
	
	$sql = "DELETE FROM divisions WHERE group_type_id=" . $group_type_id;
	$query = mysql_query($sql);
	if ($query) {
		$insert = "INSERT INTO divisions SET group_type_id=" . $group_type_id . ", division_name='No Divisions'";
		$insert_query = mysql_query($insert);
	}

	return json_encode($error_code);
}

function add_new_group_type($params) {
	$camp_id = $params[0];
	$group_type_name = $params[1];

	$sql = "INSERT INTO group_types SET camp_id=" . $camp_id . ", group_type_name='" . mysql_real_escape_string($group_type_name) . "'";
	$query = mysql_query($sql);
	
	if ($query)
		return json_encode(mysql_insert_id());
	else
		return json_encode("0");
}

function get_staff_groups($params) {
	$admin_id = $params[0];
	
	$staff_groups = array();
	
	$sql = "SELECT sg.group_id, g.group_name, d.division_id, d.division_name, gt.group_type_id, gt.group_type_name ";
	$sql = $sql . "FROM staff_groups AS sg ";
	$sql = $sql . "JOIN groups AS g USING (group_id) ";
	$sql = $sql . "JOIN divisions AS d USING (division_id) ";
	$sql = $sql . "JOIN group_types AS gt USING (group_type_id) ";
	$sql = $sql . "WHERE admin_id=" . $admin_id . " ";	
	$sql = $sql . "GROUP BY gt.group_type_id, d.division_id, sg.group_id ";
	$sql = $sql . "ORDER BY gt.group_type_id, d.division_id, sg.group_id  ";
	
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$group_id = $row['group_id'];
		$group_name = $row['group_name'];
		$division_id = $row['division_id'];
		//$division_name = $row['division_name'];
		$group_type_id = $row['group_type_id'];
		//$group_type_name = $row['group_type_name'];
		
		$element = compact('group_id', 'group_name', 'division_id', 'group_type_id');
		array_push($staff_groups, $element);
	}
	
	 return json_encode($staff_groups);
}

function add_group($params) {
    $division_id = $params[0];
    $group_name = $params[1];
    $new_group_id = 0;
    $error_code = 0;

    $new_group_query = "SELECT * FROM groups WHERE group_name=" . ms($group_name) . " AND division_id=" . $division_id;
    $new_group_query_result = mq($new_group_query);
    
    // Ensure that the division name doesn't already exist
    $num_rows = mysql_num_rows($new_group_query_result);
    if ($num_rows == 0) {

        $new_group_query = "INSERT INTO groups SET division_id=" . $division_id . ", group_name=" . ms($group_name);
        $new_group_query_result = mq($new_group_query);
        
        if ($new_group_query_result == FALSE) {
            $error_code = 2;
        }
        else {
			$new_group_id = mysql_insert_id();
			
			// ***** Check to see if there are any tasks assigned to the division, and if there is then add the new group ***** //
			$sql = "SELECT gt.group_type_id, gt.camp_task_id, gt.period_id FROM divisions AS d JOIN group_tasks AS gt USING (group_type_id) WHERE d.division_id=" . $division_id;
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
    else {
        $error_code = 1;
    }
    
    // Generate results
    $results = compact('error_code', 'new_group_id');
    
    return json_encode($results);
}

function get_marking_group_members($params) {
	$task_date = $params[0];
	$group_type_id = $params[1];
	$group_no = $params[2];
	
	$group_members = array();
	$members = array();
	
	$sql = "SELECT mt.group_id, g.group_name, mt.user_id, CONCAT(u.first, ' ', u.last) AS name ";
	$sql = $sql . "FROM member_tasks AS mt ";
	$sql = $sql . "JOIN groups AS g USING (group_id) ";
	$sql = $sql . "JOIN divisions AS d USING (division_id) ";
	$sql = $sql . "JOIN users AS u USING (user_id) ";
	$sql = $sql . "WHERE mt.task_date=" . $task_date . " AND d.group_type_id=" . $group_type_id . " ";
	$sql = $sql . "GROUP BY mt.group_id, mt.user_id ";
	$sql = $sql . "ORDER BY mt.group_id, mt.user_id ";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	$prev_group_id = "";
	$group_number = 0;
	$row_num = 0;
	$last_group = false;
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		$prev_group_id = $row['group_id'];
		
		if ($prev_group_id != $group_id && $group_id != "") {
			$group_number++;
			if ($group_number == $group_no) {
				$element = compact('group_id', 'group_name', 'last_group', 'members');
				array_push($group_members, $element);
				$members = array();
				break;
			}
		}
		
		if ($group_number == ($group_no - 1)) {
			$user_id = $row['user_id'];
			$name = $row['name'];
			$element = compact('user_id', 'name');
			array_push($members, $element);
		}
		
		$group_id = $prev_group_id;
		$group_name = $row['group_name'];
		
		if ($num_rows == $row_num) {
			$last_group = true;
			$group_no++;
			$element = compact('group_id', 'group_name', 'last_group', 'members');
			array_push($group_members, $element);		
		}
				
	}
	
	return json_encode($group_members);
}

function get_member_points($params) {
	$user_id = $params[0];
	
	$sql = "SELECT SUM(points) AS points ";
	$sql = $sql . "FROM member_tasks AS mt ";
	$sql = $sql . "LEFT JOIN camp_tasks USING (camp_task_id) ";
	$sql = $sql . "WHERE mt.user_id=" . $user_id . " AND mt.completed=1 ";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$total_points = $row['points'];
	$points = compact('total_points');
	return json_encode($points);
}

function remove_group($params) {
	$group_id = $params[0];

	$sql = "DELETE FROM groups WHERE group_id=" . $group_id;
	$query = mysql_query($sql);
	if ($query) 
		return json_encode("0");
	else
		return json_encode("1");	
}

function remove_group_type($params) {
	$group_type_id = $params[0];

	$sql = "DELETE FROM group_types WHERE group_type_id=" . $group_type_id;
	$query = mysql_query($sql);
	if ($query) 
		return json_encode("0");
	else
		return json_encode("1");	
}

function save_group_type($params) {
	$group_type_id = $params[0];
	$group_type_name = $params[1];

	$sql = "UPDATE group_types SET group_type_name='" . mysql_real_escape_string($group_type_name) . "' WHERE group_type_id=" . $group_type_id;
	$query = mysql_query($sql);
	if ($query) 
		return json_encode("0");
	else
		return json_encode("1");	
}

function remove_division($params) {
	$division_id = $params[0];

	$sql = "DELETE FROM divisions WHERE division_id=" . $division_id;
	$query = mysql_query($sql);
	if ($query) 
		return json_encode("0");
	else
		return json_encode("1");	
}

function add_new_division($params) {
	$group_type_id = $params[0];
	$division_name = $params[1];

	$sql = "INSERT INTO divisions SET group_type_id=" . $group_type_id . ", division_name='" . mysql_real_escape_string($division_name) . "'";
	$query = mysql_query($sql);
	if ($query) 
		return json_encode(mysql_insert_id());
	else
		return json_encode("0");	
}

function save_division($params) {
	$division_id = $params[0];
	$division_name = $params[1];

	$sql = "UPDATE divisions SET division_name='" . mysql_real_escape_string($division_name) . "' WHERE division_id=" . $division_id;
	$query = mysql_query($sql);
	if ($query) 
		return json_encode("0");
	else
		return json_encode("1");	
}


function save_group($params) {
	$group_id = $params[0];
	$group_name = $params[1];

	$sql = "UPDATE groups SET group_name='" . mysql_real_escape_string($group_name) . "' WHERE group_id=" . $group_id;
	$query = mysql_query($sql);
	if ($query) 
		return json_encode("0");
	else
		return json_encode("1");	
}

function add_new_group($params) {
	$division_id = $params[0];
	$group_name = $params[1];
	
	$sql = "INSERT INTO groups SET division_id=" . $division_id . ", group_name='" . mysql_real_escape_string($group_name) . "'";
	$query = mysql_query($sql);
	if ($query) 
		return json_encode(mysql_insert_id());
	else
		return json_encode("0");
}

function generate_new_groups($params) {
	$division_id = $params[0];
	$division_name = $params[1];
	$number_of_groups = $params[2];
	$format = $params[3];
	
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

function get_all_group_type_division_groups($params) {
	$camp_id = $params[0];
	
	$group_types = array();
	$divisions = array();
	$groups = array();
	
	$sql = "SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name, g.group_id, g.group_name ";
	$sql = $sql . "FROM group_types AS gt ";
	$sql = $sql . "JOIN divisions AS d USING (group_type_id) ";
	$sql = $sql . "LEFT JOIN groups AS g USING (division_id) ";
	$sql = $sql . "WHERE gt.camp_id=" . $camp_id . " ";
	$sql = $sql . "ORDER BY gt.group_type_id, d.division_id, g.group_id ";
	$query = mq($sql);
	$num_rows = mysql_num_rows($query);
	
	$prev_group_type_id = "";
	$prev_division_id = "";
	
	$row_num = 0;
	$group_type_id = "";
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		$prev_group_type_id = $row['group_type_id'];
		$prev_division_id = $row['division_id'];
			
		if ($prev_division_id != $division_id && $division_id != "") {			
			$element = compact('division_id', 'division_name', 'groups');
			array_push($divisions, $element);
			$groups = array();
		}
	
		if ($prev_group_type_id != $group_type_id && $group_type_id != "") {
			$element = compact('group_type_id', 'group_type_name', 'divisions');
			array_push($group_types, $element);
			$divisions = array();
		}
					
		$group_id = $row['group_id'];
		$group_name = $row['group_name'];
		
		if ($group_id > 0) {
			$element = compact('group_id', 'group_name');
			array_push($groups, $element);
		}
		
		$group_type_id = $prev_group_type_id;
		$group_type_name = $row['group_type_name'];	

		$division_id = $prev_division_id;
		$division_name = $row['division_name'];	
		
		if ($row_num == $num_rows) {
			$element = compact('division_id', 'division_name', 'groups');
			array_push($divisions, $element);		
			$element = compact('group_type_id', 'group_type_name', 'divisions');
			array_push($group_types, $element);		
		}
	}
	
	return json_encode($group_types);
}

function get_all_group_type_divisions($params) {
	$camp_id = $params[0];
	
	$group_types = array();
	$divisions = array();
	
	$sql = "SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name ";
	$sql = $sql . "FROM group_types AS gt ";
	$sql = $sql . "LEFT JOIN divisions AS d USING (group_type_id) ";
	$sql = $sql . "WHERE gt.camp_id=" . $camp_id . " ";
	$sql = $sql . "ORDER BY gt.group_type_id, d.division_id ";
	$query = mq($sql);
	$num_rows = mysql_num_rows($query);
	
	$prev_group_type_id = "";
	
	$row_num = 0;
	$group_type_id = "";
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		$prev_group_type_id = $row['group_type_id'];
		$division_id = $row['division_id'];
		$division_name = $row['division_name'];
	
		if ($prev_group_type_id != $group_type_id && $group_type_id != "") {
			$element = compact('group_type_id', 'group_type_name', 'divisions');
			array_push($group_types, $element);
			$divisions = array();
		}
				
		$element = compact('division_id', 'division_name');
		array_push($divisions, $element);
					
		$group_type_id = $prev_group_type_id;
		$group_type_name = $row['group_type_name'];	

		if ($row_num == $num_rows) {
			$element = compact('group_type_id', 'group_type_name', 'divisions');
			array_push($group_types, $element);		
		}
	}
	
	return json_encode($group_types);
}

function remove_member_group($params) {
	$error_code = 0;
		
	$member_group_id = $params[0];
	$group_id = $params[1];
	$user_id = $params[2];
	
	$todays_date = get_todays_julian_date();
	
	$sql = "UPDATE member_groups SET end_date=" . $todays_date . " WHERE member_group_id=" . $member_group_id;
	$query = mysql_query($sql);
	if ($query) {
		$sql = "DELETE FROM member_tasks WHERE user_id=" . $user_id . " AND group_id=" . $group_id . " AND task_date > " . $todays_date;
		$query = mysql_query($sql);	
		if (!$query)		
			$error_code = 1;
	}
	else {
		$error_code = 1;
	}
	
	return $error_code;
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
		$starting_date = $today_jd;
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

function get_todays_julian_date() {
	$todays_day = date("j"); 
	$todays_month = date("n"); 
	$todays_year = date("Y"); 
	$today_jd = cal_to_jd  (CAL_GREGORIAN, $todays_month,  $todays_day, $todays_year);

	return $today_jd;
}

function insert_group_type_tasks($group_type_id, $camp_task_id, $period_id) {	
	$error_code = 0;
	
	$sql = "SELECT gt.group_type_id, d.division_id, g.group_id ";
	$sql = $sql . "FROM group_types AS gt ";
	$sql = $sql . "JOIN divisions AS d USING (group_type_id) ";
	$sql = $sql . "JOIN groups AS g USING (division_id) ";
	$sql = $sql . "WHERE gt.group_type_id=" . $group_type_id . " ";
	$sql = $sql . "ORDER BY gt.group_type_id, d.division_id, g.group_id";
	$query = mysql_query($sql);
	
	while ($row = mysql_fetch_assoc($query) ) {
		$group_type_id = $row['group_type_id'];
		$division_id = $row['division_id'];
		$group_id = $row['group_id'];
		
		$sql2 = "SELECT group_task_id FROM group_tasks WHERE division_id=" . $division_id . " AND group_id=" . $group_id . " AND camp_task_id=" . $camp_task_id;
		$query2 = mysql_query($sql2);
		$row2 = mysql_fetch_assoc($query2);
		
		if ($row2['group_task_id'] > 0) {	
			$update = "UPDATE group_tasks SET group_type_id=" . $group_type_id . ", period_id=" . $period_id . " WHERE group_task_id=" . $row2['group_task_id'];
			$update_query = mysql_query($update);
			if (!$update_query) {
				$error_code = 1;
				break;
			}
		}		
		else {
			$insert = "INSERT INTO group_tasks SET group_type_id=" . $group_type_id . ", division_id=" . $division_id . ", group_id=" . $group_id . ", camp_task_id=" . $camp_task_id . ", period_id=" . $period_id . ", group_task=0";
			$insert_query = mysql_query($insert);
			if (!$insert_query) {
				$error_code = 1;
				break;
			}
		}
	}
	
	return $error_code;
}

function insert_division_tasks($division_id, $camp_task_id, $period_id) {
	$sql = "SELECT d.division_id, g.group_id ";
	$sql = $sql . "FROM divisions AS d ";
	$sql = $sql . "JOIN groups AS g USING (division_id) ";
	$sql = $sql . "WHERE d.division_id=" . $division_id . " ";
	$sql = $sql . "ORDER BY d.division_id, g.group_id";	
	$query = mysql_query($sql);		
	
	while ($row = mysql_fetch_assoc($query)) {
		$division_id = $row['division_id'];
		$group_id = $row['group_id'];
		
		$sql2 = "SELECT group_task_id FROM group_tasks WHERE division_id=" . $division_id . " AND group_id=" . $group_id . " AND camp_task_id=" . $camp_task_id;
		$query2 = mysql_query($sql2);
		$num_rows = mysql_num_rows($query2);

		if ($num_rows == 0) {
			$insert = "INSERT INTO group_tasks SET division_id=" . $division_id . ", group_id=" . $group_id . ", camp_task_id=" . $camp_task_id . ", period_id=" . $period_id . ", group_task=0";
			$insert_query = mysql_query($insert);
			if (!$insert_query) {
				$error_code = 1;
				break;
			}
		}
	}
	
	return $error_code;
}



function get_all_staff($params) {
	$camp_id = $params[0];
	$staff_type_id = $params[1];
	
	if ($staff_type_id > 0)
		$and = " AND staff_type_id=" . $staff_type_id . " ";
	else
		$and = " ";
		
	$sql = "SELECT * ";
	$sql = $sql . "FROM admins ";
	$sql = $sql . "WHERE camp_id=" . $camp_id . $and;
	$sql = $sql . "ORDER BY first, last";
	
	$query = mq($sql);
	$staff = array();
	
	while ($row = mysql_fetch_assoc($query)) {
		$admin_id = $row['admin_id'];
		$first = $row['first'];
		$last = $row['last'];
		$staff_photo_id = $row['staff_photo_id'];
		
		$array_element = compact('admin_id', 'first','last', 'staff_photo_id');
		array_push($staff, $array_element);
	}

	return json_encode($staff);
}

function remove_group_type_task($params) {
	$camp_task_id = $params[0];
	$group_type_id = $params[1];
		
	$error_code = 0;
	$todays_date = get_todays_julian_date();

	// ***** Remove all future tasks for the group members within the group type ***** //
	$sql = "SELECT * FROM group_tasks WHERE group_type_id=" . $group_type_id . " AND camp_task_id=" . $camp_task_id;
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$group_id = $row['group_id'];
		$delete = "DELETE FROM member_tasks WHERE group_id=" . $group_id . " AND camp_task_id=" . $camp_task_id . " AND task_date > " . $todays_date;
		$delete_query = mysql_query($delete);
		if (!$delete_query) {
			$error_code = 1;
			break;
		}
	}
	// ***** Remove all future tasks for the group members within the group type ***** //

	// ****** Remove the group type task ***** //
	if ($error_code == 0) {
		$sql = "DELETE FROM group_tasks WHERE group_type_id=" . $group_type_id . " AND camp_task_id=" . $camp_task_id;
		$query = mysql_query($sql);
		if (!$query)
			$error_code = 1;
	}
	// ****** Remove the group type task ***** //
	
	return json_encode($error_code);
}

function remove_division_task($params) {
	$camp_task_id = $params[0];
	$division_id = $params[1];
	
	$error_code = 0;
	$todays_date = get_todays_julian_date();

	// ***** Remove all future tasks for the group members within the division ***** //
	$sql = "SELECT * FROM group_tasks WHERE division_id=" . $division_id . " AND camp_task_id=" . $camp_task_id;
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$group_id = $row['group_id'];
		$delete = "DELETE FROM member_tasks WHERE group_id=" . $group_id . " AND camp_task_id=" . $camp_task_id . " AND task_date > " . $todays_date;
		$delete_query = mysql_query($delete);
		if (!$delete_query) {
			$error_code = 1;
			break;
		}
	}
	// ***** Remove all future tasks for the group members within the division ***** //
	
	// ****** Remove the division task ***** //
	if ($error_code == 0) {
		$sql = "DELETE FROM group_tasks WHERE division_id=" . $division_id . " AND camp_task_id=" . $camp_task_id;
		$query = mysql_query($sql);
		if (!$query)
			$error_code = 1;
	}
	// ****** Remove the division task ***** //
	
	return json_encode($error_code);
}

function get_staff_type($params) {
	$camp_id = $params[0];
	$staff_type_id = $params[1];
}

function get_staff_assignments($params) {
	$camp_id = $params[0];
	
	$staff_assignments = array();
	
	$sql = "SELECT count(*) AS number_of_staff, type_name ";
	$sql = $sql . "FROM staff_types AS st ";
	$sql = $sql . "JOIN admins AS a ON (a.camp_id=" . $camp_id . " AND a.staff_type_id=st.staff_types_id) ";
	$sql = $sql . "GROUP BY st.staff_types_id ";
	$sql = $sql . "ORDER BY st.staff_types_id ";	
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$number_of_staff = $row['number_of_staff'];
		$type_name = $row['type_name'];
		$element = compact('type_name', 'number_of_staff');
		array_push($staff_assignments, $element);
	}
	
	return json_encode($staff_assignments);	
}

function assign_staff_type($params) {
	$admin_id = $params[0];
	$staff_type_id = $params[1];
	
	$query = mysql_query("UPDATE admins SET staff_type_id=" . $staff_type_id . " WHERE admin_id=" . $admin_id);
	if ($query)
		return 0;
	else
		return 1;
}

function get_group_type_task($params) {
	$group_type_id = $params[0];
	$camp_task_id= $params[1];

	$num_rows = mysql_num_rows(mysql_query("SELECT * FROM group_tasks WHERE group_type_id=" . $group_type_id . " AND camp_task_id=" . $camp_task_id));
	
	return $num_rows;	
}

function get_division_task($params) {
	$division_id = $params[0];
	$camp_task_id= $params[1];

	$num_rows = mysql_num_rows(mysql_query("SELECT * FROM group_tasks WHERE division_id=" . $division_id . " AND camp_task_id=" . $camp_task_id));
	
	return $num_rows;	
}

function add_camp_task($params) {
	$camp_id = $params[0];
	$camp_mission_id = $params[1];	
	$task_name = $params[2];
	$points = $params[3];
	$period_id = $params[4];
	
	$error_code = 0;
	
	$row = mysql_fetch_assoc(mysql_query("SELECT camp_type_id, start_date, end_date FROM camps WHERE camp_id=" . $camp_id));
	$camp_type_id = $row['camp_type_id'];
	$start_date = $row['start_date'];
	$end_date = $row['end_date'];
	
	$sql = "INSERT INTO camp_tasks SET camp_mission_id=" . $camp_mission_id . ", period_id=" . $period_id  . ", task_name='" . mysql_real_escape_string($task_name ) . "', points=" . $points  . ",  start_date=" . $start_date  . ", end_date=" . $end_date ;
	if (!mysql_query($sql)) 
		$error_code = 1;
	else
		$error_code = mysql_insert_id();
		
	return json_encode($error_code);	
}

function update_task($params) {
	$camp_task_id = $params[0];
	$task_name = $params[1];
	$points = $params[2];

	$sql = "UPDATE camp_tasks SET task_name='" . $task_name . "', points=" . $points . " WHERE camp_task_id=" . $camp_task_id;	
	$query = mysql_query($sql);
	if ($query)
		return 0;
	else
		return 1;
}

function get_all_group_types($params) {
    $camp_id = $params[0];
    
    // Get the group types
    $all_group_types = array();
    
    $group_type_query = mq("SELECT * FROM group_types WHERE camp_id=" . $camp_id);
    while ( $group_type_row = mysql_fetch_assoc($group_type_query) ) {
    
        // Get the group type data
        $group_type_id = $group_type_row['group_type_id'];
        $group_type_name = $group_type_row['group_type_name'];
        $logo_id = $group_type_row['logo_id'];
		
        // Assemble the group type element
        $group_type_array_element = compact('group_type_id', 'group_type_name', 'logo_id');

        // Add the group type
        array_push($all_group_types, $group_type_array_element);
    }
    
    // Return the array of all group types
    return json_encode($all_group_types);
}

function get_unassigned_camp_members($params) {
	$camp_id = $params[0];
	
	$campers = array();
	
	$sql = "SELECT u.user_id, u.first, u.last, u.user_photo_id ";
	$sql = $sql . "FROM users AS u ";
	$sql = $sql . "LEFT JOIN member_groups AS mg ON (mg.user_id=u.user_id AND mg.end_date=0) ";
	$sql = $sql . "WHERE u.camp_id=" . $camp_id . " AND camp_registered IS NOT NULL AND mg.member_group_id IS NULL";

	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$user_id = $row['user_id'];
		$first = $row['first'];
		$last = $row['last'];
		$user_photo_id = $row['user_photo_id'];
				
		$element = compact('user_id', 'first', 'last', 'user_photo_id');
		array_push($campers, $element);	
	}
	
	return json_encode($campers);	
}

function deassign_staff_group($params) {
	$admin_id = $params[0];
	$group_id = $params[1];	
	
	$error_code = 0;
	
	$sql = "DELETE FROM staff_groups WHERE admin_id=" . $admin_id . " AND group_id=" . $group_id;
	$query = mysql_query($sql);
	if (!$query)
		$error_code = 1;
		
	return json_encode($error_code);
}

function assign_staff_group($params) {
	$admin_id = $params[0];
	$group_id = $params[1];	
	
	$error_code = 0;
	
	$sql = "INSERT INTO staff_groups SET admin_id=" . $admin_id . ", group_id=" . $group_id;
	$query = mysql_query($sql);
	if (!$query)
		$error_code = 1;
	else
		$error_code = mysql_insert_id();			
		
		
	return json_encode($error_code);		
}

function get_member_groups($params) {
	$user_id = $params[0];
	
	$member_groups = array();
	
	$sql = "SELECT mg.group_id, g.group_name, d.division_id, gt.group_type_id, gt.group_type_name ";
	$sql = $sql . "FROM member_groups AS mg ";
	$sql = $sql . "JOIN groups AS g USING (group_id) ";
	$sql = $sql . "JOIN divisions AS d ON (mg.division_id=d.division_id) ";
	$sql = $sql . "JOIN group_types AS gt ON (mg.group_type_id=gt.group_type_id) ";
	$sql = $sql . "WHERE mg.user_id=" . $user_id . " AND mg.end_date=0 ";

	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$group_id = $row['group_id'];
		$group_name = $row['group_name'];
		$division_id = $row['division_id'];
		$group_type_id = $row['group_type_id'];
		$group_type_name = $row['group_type_name'];
				
		$element = compact('group_id', 'group_name', 'division_id', 'group_type_id', 'group_type_name');
		array_push($member_groups, $element);
	}
	
	return json_encode($member_groups);
}

function get_camp_groups($params) {
	$camp_id = $params[0];
	
	$group_types = array();
	$divisions = array();
	$groups = array();

	$sql = "SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name, g.group_id, g.group_name ";
	$sql = $sql . "FROM group_types AS gt ";
	$sql = $sql . "JOIN divisions AS d USING (group_type_id) ";
	$sql = $sql . "JOIN groups AS g USING (division_id) ";
	$sql = $sql . "WHERE camp_id=" . $camp_id . " ";
	$sql = $sql . "ORDER BY gt.group_type_id, d.division_id, g.group_id";
	
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	$prev_group_type_id = "";
	$prev_division_id = "";
	$row_num = 0;
	
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		$prev_group_type_id = $row['group_type_id'];
		$prev_division_id = $row['division_id'];
		
		$group_id = $row['group_id'];
		$group_name = $row['group_name'];
		
		if ($prev_division_id != $division_id && $division_id != "") {
			$element = compact('division_id', 'division_name', 'groups');
			array_push($divisions, $element);
			$groups = array();
		}
		
		if ($prev_group_type_id != $group_type_id && $group_type_id != "") {
			$element = compact('group_type_id', 'group_type_name', 'divisions');
			array_push($group_types, $element);
			$divisions = array();
		}
		
		$element = compact('group_id', 'group_name');
		array_push($groups, $element);
		
		$division_id = $row['division_id'];
		$division_name = $row['division_name'];
		
		$group_type_id = $row['group_type_id'];
		$group_type_name = $row['group_type_name'];
		
		if ($row_num == $num_rows) {
			$element = compact('division_id', 'division_name', 'groups');
			array_push($divisions, $element);
			$element = compact('group_type_id', 'group_type_name', 'divisions');
			array_push($group_types, $element);		
		}
	}
	
	return json_encode($group_types);
}

function set_member_group($params) {
	$error_code = 0;
		
	$user_id = $params[0];
	$group_type_id = $params[1];
	$division_id = $params[2];
	$group_id = $params[3];
	$camp_id = $params[4];
	
	$sql = "SELECT member_group_id FROM member_groups WHERE user_id=" . $user_id . " AND group_type_id=" . $group_type_id . " AND division_id=" . $division_id;
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	if ($num_rows == 0) {
		$sql = "INSERT INTO member_groups SET camp_id=" . $camp_id . ", user_id=" . $user_id . ", group_type_id=" . $group_type_id . ", division_id=" . $division_id . ", group_id=" . $group_id;
		$query = mysql_query($sql);
		if (!$query)
			$error_code = 1;
	}
	else {
		$row = mysql_fetch_assoc($query);
		$member_group_id = $row["member_group_id"];
		$sql = "UPDATE member_groups SET group_id=" . $group_id . " WHERE member_group_id=" . $member_group_id;
		$query = mysql_query($sql);
		if (!$query)
			$error_code = 2;	
	}
	
	return json_encode($error_code);
}


function get_camp_members($params) {
	$camp_id = $params[0];
	
	$campers = array();
	
	$sql = "SELECT * FROM users WHERE camp_id=" . $camp_id . " AND camp_registered IS NOT NULL";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$user_id = $row['user_id'];
		$first = $row['first'];
		$last = $row['last'];
		$user_photo_id = $row['user_photo_id'];
		
		$element = compact('user_id', 'first', 'last', 'user_photo_id');
		array_push($campers, $element);	
	}
	
	return json_encode($campers);
}

function get_register_camp_members($params) {
        $camp_id = $params[0];

        $campers = array();

        $sql = "SELECT * FROM users WHERE camp_id=" . $camp_id . " AND camp_registered IS NULL";
        $query = mysql_query($sql);
        while ($row = mysql_fetch_assoc($query)) {
                $user_id = $row['user_id'];
                $first = $row['first'];
                $last = $row['last'];

                $element = compact('user_id', 'first', 'last');
                array_push($campers, $element);
        }

        return json_encode($campers);
}

function get_group_staff($params) {
	$camp_id = $params[0];
	$group_id = $params[1];
	
	$group_staff = array();
	
	$sql = "SELECT a.admin_id, a.first, a.last ";
	$sql = $sql . "FROM admins AS a ";
	$sql = $sql . "JOIN admin_auths AS aa USING (admin_id) ";
	$sql = $sql . "LEFT JOIN staff_groups AS sg ON (sg.admin_id=a.admin_id AND sg.group_id=" . $group_id. ") ";
	$sql = $sql . "WHERE aa.auth='camp' AND aa.id=" . $camp_id . " AND sg.staff_group_id IS NULL";
	
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$admin_id = $row['admin_id'];
		$first = $row['first'];
		$last = $row['last'];
		
		$element = compact('admin_id', 'first', 'last');
		array_push($group_staff, $element);		
	}
	
	return json_encode($group_staff);
	
}

function remove_staff_member($params) {
	$staff_group_id = $params[0];
	
	$error_code = 0;
	
	$sql = "DELETE FROM staff_groups WHERE staff_group_id=" . $staff_group_id;	
	$query = mysql_query($sql);
	if (!$query)
		$error_code = 1;
		
	return json_encode($error_code);
}

function get_staff_members($params) {
	$group_id = $params[0];
	
	$staff_members = array();
	
	$sql = "SELECT sg.staff_group_id, sg.admin_id, a.first, a.last, a.staff_type_id FROM staff_groups AS sg JOIN admins AS a USING (admin_id) WHERE group_id=" . $group_id;	
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$staff_group_id = $row['staff_group_id'];
		$admin_id = $row['admin_id'];
		$first = $row['first'];
		$last = $row['last'];
		$staff_type_id = $row['staff_type_id'];
		
		$element = compact('staff_group_id', 'admin_id', 'first', 'last', 'staff_type_id');
		array_push($staff_members, $element);		
	}
	
	return json_encode($staff_members);
}

function get_group_members($params) {
	$group_id = $params[0];
	
	$group_members = array();
	
	$sql = "SELECT mg.*, u.first, u.last, u.user_photo_id ";
	$sql = $sql . "FROM member_groups AS mg ";
	$sql = $sql . "JOIN users AS u USING (user_id) ";
	$sql = $sql . "WHERE mg.end_date=0 AND group_id=" . $group_id;	
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$member_group_id = $row['member_group_id'];
		$user_id = $row['user_id'];
		$user_photo_id = $row['user_photo_id'];
		$first = $row['first'];
		$last = $row['last'];
		
		$element = compact('member_group_id', 'user_id', 'first', 'last', 'user_photo_id');
		array_push($group_members, $element);		
	}
	
	return json_encode($group_members);
}

/*function get_camp_types($params) {

	$camp_types = array();

	$query = mq("SELECT * FROM camp_types");
	
	while ($row = mysql_fetch_assoc($query)) {
	
        $camp_type_id = $row['camp_type_id'];
        $camp_type = $row['camp_type'];
        
        $array_element = compact('camp_type_id', 'camp_type');

        array_push($camp_types, $array_element);
	}

	return json_encode($camp_types);
}

//     0             1              2              3               4            5             6           7              8              9               
// $camp_name, $camp_name_he, $camp_gender, $camp_address1, $camp_address2, $camp_city, $camp_state, $camp_postal, $camp_country, $camp_phone
//   10           11        12      13     14     15        16            17              18               19          20             21             22                 23               24                  25
// $username, $password, $title, $first, $last, $lang, $admin_email, $admin_address1, admin_address2, $admin_city, $admin_state, $admin_postal, $admin_country, $admin_phone_work, $admin_phone_home, $admin_phone_mobile
function register_camp($params) {

	$error_code = 0;
	
	$sql = "INSERT INTO camps (camp_name, camp_name_he, camp_gender, camp_address1, camp_address2, camp_city, camp_state, camp_postal, camp_country, camp_phone) VALUES (" . ms($params[0]) . ", " . ms($params[1]) . ", " . ms($params[2]) . ", " . ms($params[3]) . ", " . ms($params[4]) . ", " . ms($params[5]) . ", " . ms($params[6]) . ", " . ms($params[7]) . ", " . ms($params[8]) . ", " . ms($params[9]) . ")";	
	$result = mq($sql);
	$camp_id = mysql_insert_id();
	
	if (!$result) {
		$error_code = 1;
		
	mq('INSERT INTO admins (username, auth, password, title, first, last, lang, admin_email, admin_address1, admin_address2, admin_city, admin_state, admin_postal, admin_country, admin_phone_work, admin_phone_home, admin_phone_mobile) VALUES (' . ms($params[10]) . ", 'inactive', " . ms($params[11]) . ', ' . ms($params[12]) . ', ' . ms($params[13]) . ', ' .ms($params[14]) . ', ' . ms($params[15]) . ', ' . ms($params[16]) . ', ' . ms($params[17]) . ', ' . ms($params[18]) . ', ' . ms($params[19]) . ', ' . ms($params[20]) . ', ' . ms($params[21])) . ', ' . ms($params[22]) . ', ' . ms($params[23]) . ', ' . ms($params[24]) . ', ' . ms($params[25]) . ')');
	$admin_id = mysql_insert_id();
	
	mq("INSERT INTO admin_auths (admin_id, auth, id, role_id) VALUES (" . $admin_id . ", 'camp', " . $camp_id . ", 35)");

	// ***** DEFAULT GROUP TYPES AND DIVISIONS ***** //
	$sql = "SELECT * FROM default_group_types";
	$query = mq($sql);
	while ($row = mysql_fetch_assoc($query)) {
		if ($row['logo_id'] > 0) 
			$insert = "INSERT INTO group_types SET camp_id=" . $camp_id . ", group_type_name=" .ms($row['group_type_name']) . ", logo_id=" . $row['logo_id'] . ", divisions=1";
		else
			$insert = "INSERT INTO group_types SET camp_id=" . $camp_id . ", group_type_name=" .ms($row['group_type_name']) . ", divisions=1";
		mq($insert);			
		$group_type_id = mysql_insert_id();
			
		$sql2 = "SELECT * FROM default_divisions";
		$query2 = mq($sql2);
		while ($row2 = mysql_fetch_assoc($query2)) {
			$insert2 = "INSERT INTO divisions SET group_type_id=" . $group_type_id . ", division_name=" .ms($row2['division_name']) . ", groups=1";
			mq($insert2);
		}
			
	}		
	// ***** DEFAULT GROUP TYPES AND DIVISIONS ***** //
	
	// ***** GLOBAL CAMPAIGNS ***** //
	$sql1 = "SELECT * FROM global_campaigns";
	$query1 = mq($sql1);
	while ($row1 = mysql_fetch_assoc($query)) {
		$sql2 = "INSERT INTO camp_campaigns SET campaign_id=" . $row1['campaign_id'] . ", camp_id=" . $camp_id . ", campaign_name=" . ms($row1['campaign_name']) . ", points=" . $row1['points'];
		mq($sql2);
		$camp_campaign_id =  mysql_insert_id();
		
		// ********** GLOBAL MISSIONS ********** //
		$sql3 = "SELECT * FROM global_missions WHERE campaign_id=" . $row1['campaign_id'];
		$query3 = mq($sql3);
		while ($row3 = mysql_fetch_assoc($query3)) {
			$sql4 = "INSERT INTO camp_missions SET camp_mission_id=" . $row3['mission_id'] . ", camp_campaign_id=" . $camp_campaign_id . ", mission_name=" . ms($row3['mission_name']) . ", points=" . $row3['points']  .  ", sequence=" . $row3['sequence'] ;
			mq($sql4);
			$camp_mission_id = mysql_insert_id();
		
			// ********** GLOBAL TASKS ********** //
			$sql5 = "SELECT * FROM global_tasks WHERE mission_id=" . $row3['mission_id'];
			$query5 = mq($sql5);
			while ($row5 = mysql_fetch_assoc($query5)) {
				$sql6 = "INSERT INTO camp_tasks SET task_id=" . $row5['task_id'] . ", camp_mission_id=" . $camp_mission_id . ", camp_type_id=" . $row5['camp_type_id'] . ", level_id=" . $row5['level_id'] . ", task_name=" . ms($row5['task_name']) . ", period_id=" . $row5['period_id'] . ", points=" . $row5['points'] . ", max_times=" . $row5['max_times'] . ", start_date=" . $row5['start_date'] . ", end_date=" . $row5['end_date']  . ", monday=" . $row5['monday']  . ", tuesday=" . $row5['tuesday']  . ", wednesday=" . $row5['wednesday'] . ", thursday=" . $row5['thursday'] . ", friday=" . $row5['friday'] . ", shabbos=" . $row5['shabbos'] . ", sunday=" . $row5['sunday'];
				mq($sql6);			
			}
			// ********** GLOBAL TASKS ********** //
		}
		// ********** GLOBAL MISSIONS ********** //
		
	}
	// ***** GLOBAL CAMPAIGNS ***** //
	

	$results = compact('error_code');	
	return json_encode($results);

}*/

function get_all_campers(){
	$sql = "SELECT * from users where camp_id is not null order by first";
        $query = mq($sql);
        $campers = array();
        while ($row = mysql_fetch_assoc($query)) {
		$user_id = $row['user_id'];
                $first = $row['first'];
                $last = $row['last'];
                $array_element = compact('user_id', 'first','last');
                array_push($campers, $array_element);
        }

        return json_encode($campers);
}

function get_camper_details($params){
	$camper_id = $params[0];
	$sql = "SELECT * from users where user_id = '$camper_id'";
	$query = mq($sql);
	$campers = array();
	
	while ($row = mysql_fetch_assoc($query)) {
		$user_id = $row['user_id'];
		$first = $row['first'];
		$last = $row['last'];
		$email = $row['email'];
		$first_he = $row['first_he'];
		$last_he = $row['last_he'];
		$gender = $row['gender'];
		$lang = $row['lang'];
		$user_address1 = $row['user_address1'];
		$user_address2 = $row['user_address2'];
		$user_city = $row['user_city'];
		$user_state = $row['user_state'];
		$user_postal = $row['user_postal'];
		$user_phone = $row['user_phone'];
		$user_country = $row['user_country'];
		$user_photo_id = $row['user_photo_id'];
		$user_code = $row['user_code'];

		$array_element = compact('user_id', 'first','last','email','first_he','last_he','gender','lang','user_address1','user_address2','user_city','user_state','user_postal','user_phone','user_country', 'user_photo_id','user_code');
		array_push($campers, $array_element);
	}
	
	return json_encode($campers);
}

function get_staff_details($params){
	$admin_id = $params[0];
	
	$sql = "SELECT * FROM admins WHERE admin_id = " . $admin_id;
	$query = mq($sql);

	$staff_member = array();
	while ($row = mysql_fetch_assoc($query)) {
		$admin_id = $row['admin_id'];
		$first = $row['first'];
		$last = $row['last'];
		$admin_email = $row['admin_email'];
		$lang = $row['lang'];
		$admin_address1 = $row['admin_address1'];
		$admin_address2 = $row['admin_address2'];	
		$admin_city = $row['admin_city'];
		$admin_state = $row['admin_state'];
		$admin_postal = $row['admin_postal'];
		$admin_phone = $row['admin_phone'];
		$admin_country = $row['admin_country'];
		$staff_photo_id = $row['staff_photo_id'];
		$admin_phone_home = $row['admin_phone_home'];
		$admin_phone_work = $row['admin_phone_work'];
		$array_element = compact('user_id', 'first','last','admin_email','lang','admin_address1','admin_address2','admin_city','admin_state','admin_postal','admin_phone','admin_country', 'staff_photo_id', 'admin_phone_home', 'admin_phone_work');
				
		array_push($staff_member, $array_element);
	}
	
       return json_encode($staff_member);
}

function get_camp_details($params){
	$camp_id = $params[0];

	$camp = array();
	
	$sql = "SELECT * FROM camps WHERE camp_id=" .  $camp_id;
	$query = mq($sql);
	
	while ($row = mysql_fetch_assoc($query)) {
		$camp_type = $row['camp_type'];
		$camp_name = $row['camp_name'];
		$camp_name_he = $row['camp_name_he'];
		$camp_logo_id = $row['camp_logo_id'];
		$camp_address1 = $row['camp_address1'];
		$camp_address2 = $row['camp_address2'];
		$camp_city = $row['camp_city'];
		$camp_state = $row['camp_state'];
		$camp_country = $row['camp_country'];
		$camp_postal = $row['camp_postal'];
		$camp_phone = $row['camp_phone'];
		$start_date = $row['start_date'];
		$end_date = $row['end_date'];
		$camp_phone = $row['camp_phone'];
		$session_one_start = $row['session_one_start'];
		$session_one_end = $row['session_one_end'];
		$session_two_start = $row['session_two_start'];
		$session_two_end = $row['session_two_end'];
		
		
		$array_element = compact('camp_type','camp_name','camp_name_he','camp_logo_id','camp_address1','camp_address2','camp_city','camp_state','camp_country','camp_postal','camp_phone','start_date','end_date','camp_phone', 'session_one_start', 'session_one_end', 'session_two_start', 'session_two_end');
		
		array_push($camp, $array_element);
	}
    
    return json_encode($camp);
}


function get_camper_card_details($params){
        $camper_id = $params[0];
        $sql = "SELECT * from users where user_id = '$camper_id'";
        $query = mq($sql);
        $campers = array();

        while ($row = mysql_fetch_assoc($query)) {
                $user_id = $row['user_id'];
                $first = $row['first'];
                $last = $row['last'];
                $email = $row['email'];
                $first_he = $row['first_he'];
                $last_he = $row['last_he'];
                $gender = $row['gender'];
                $lang = $row['lang'];
                $user_address1 = $row['user_address1'];
                $user_address2 = $row['user_address2'];
                $user_city = $row['user_city'];
                $user_state = $row['user_state'];
                $user_postal = $row['user_postal'];
                $user_phone = $row['user_phone'];
                $user_country = $row['user_country'];
                $user_photo_id = $row['user_photo_id'];
		$user_code = $row['user_code'];

                $array_element = compact('user_id', 'first','last','email','first_he','last_he','gender','lang','user_address1','user_address2','user_city','user_state','user_postal','user_phone','user_country', 'user_photo_id','user_code');
                array_push($campers, $array_element);
        }

        return json_encode($campers);
}
?>
