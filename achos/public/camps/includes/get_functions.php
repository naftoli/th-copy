<?php
include ("db.php");

$function_name = $_GET['function_name'];
$parameters = $_GET['parameters'];
$parameters = explode(",", $parameters);

echo $function_name($parameters);

function get_password($parameters) {
	include_once("classes/email.php");
	
	$email_address = mysql_real_escape_string($parameters[0]);
	
	$sql = "SELECT username, password FROM admins WHERE admin_email='" . $email_address . "'";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	if ($num_rows == 0) {
		return json_encode("0");
	}
	else {
		$row = mysql_fetch_assoc($query);
		
		$mail_parms = array();
		$mail_parms['to'] = $email_address;
		$mail_parms['subject'] = "Forgotten Username / Password";
		$mail_parms['message'] = "Your username / password was requested for mashpia.com.
Your username is: " . $row['username'] . ". 
Your password is " . $row['password'] . ".";
		$mail_parms['headers'] = "From: accounts@mashpia.com\r\nReply-To: accounts@mashpia.com";
		
		$email = new email();
		$success = $email->send_mail($mail_parms);
		
		return json_encode($success);
	}
}

function get_parents_and_children($parameters) {
	$admin_id = $parameters[0];
	
	$parents = array();
	$children = array();
	
	$sql = "SELECT a.admin_id, u.user_id, u.first, u.last ";
	$sql = $sql . "FROM admins AS a ";
	$sql = $sql . "LEFT JOIN admin_auths AS aa ON (a.admin_id=aa.admin_id AND aa.auth='user' AND aa.role_id=1) ";
	$sql = $sql . "LEFT JOIN users AS u ON (aa.id=u.user_id) ";
	$sql = $sql . "WHERE a.admin_id=" . $admin_id;
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	$row_num = 0;
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		
		$user_id = $row["user_id"];
		$first = $row["first"];
		$last = $row["last"];
		$child = compact('user_id', 'first', 'last');
		array_push($children, $child);
		
		if ($row_num == $num_rows) {
			$admin_id = $row["admin_id"];
			$parent = compact('admin_id', 'children');
			array_push($parents, $parent);
		}
	}
	
	return json_encode($parents);
}

function get_group_point_campaigns($parameters) {
	$group_type_id = $parameters[0];	
	$task_date = $parameters[1];

	include ("classes/group_marking.php");
	
	$cmpgns = array();
	$dvsns = array();
	$grps = array();
	
	$sql = "SELECT camp_campaign_id, campaign_name, division_id, division_name, group_id, group_name ";
	$sql = $sql . "FROM group_task_dates AS gtd ";
	$sql = $sql . "JOIN groups AS g USING (group_id) ";
	$sql = $sql . "JOIN divisions AS d USING (division_id) ";
	$sql = $sql . "JOIN camp_tasks AS ct ON (gtd.camp_task_id=ct.camp_task_id) ";
	$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
	$sql = $sql . "JOIN camp_campaigns AS cc USING (camp_campaign_id) ";
	$sql = $sql . "WHERE gtd.task_date=" . $task_date . " AND d.group_type_id=" . $group_type_id .  " ";
	$sql = $sql . "ORDER BY camp_campaign_id, d.division_id, g.group_id ";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	$camp_campaign_id = "";
	$division_id = "";
	$group_id = "";
	$row_num = 0;
	while ($row = mysql_fetch_assoc($query)) {
		$prev_camp_campaign_id = $row["camp_campaign_id"];
		$prev_division_id = $row["division_id"];
		$prev_group_id = $row["group_id"];
		$row_num++;
		
		if ($prev_group_id != $group_id && $group_id != "") {
			$group = compact('group_id', 'group_name');
			array_push($grps, $group);
		}
		
		if ($prev_division_id != $division_id && $division_id != "") {
			$division = compact('division_id', 'division_name', 'grps');
			array_push($dvsns, $division);
			$grps = array();
		}
		
		if ($prev_camp_campaign_id != $camp_campaign_id && $camp_campaign_id != "") {
			$campaign = compact('camp_campaign_id', 'campaign_name', 'dvsns');
			array_push($cmpgns, $campaign);
			$dvsns = array();
		}
		
		$camp_campaign_id = $prev_camp_campaign_id;
		$campaign_name = $row['campaign_name'];
		$division_id = $prev_division_id;
		$division_name = $row['division_name'];		
		$group_id = $prev_group_id;
		$group_name = $row['group_name'];
		
		if ($row_num == $num_rows) {
			$group = compact('group_id', 'group_name');
			array_push($grps, $group);
			$division = compact('division_id', 'division_name', 'grps');
			array_push($dvsns, $division);		
			$campaign = compact('camp_campaign_id', 'campaign_name', 'dvsns');
			array_push($cmpgns, $campaign);		
		}
	}
	
	$campaigns = array();
	//$divisions = array();
	//$groups = array();
	for ($cno = 0; $cno < count($cmpgns); $cno++) {
		$camp_campaign_id = $cmpgns[$cno]["camp_campaign_id"];
		$campaign_name = $cmpgns[$cno]["campaign_name"];
		$dvsns = $cmpgns[$cno]["dvsns"];
		
		$divisions = array();
		for ($dno = 0; $dno < count($dvsns); $dno++) {
			$dvsn = $dvsns[$dno];
			$division_id = $dvsn["division_id"];
			$division_name = $dvsn["division_name"];
			$groups = $dvsn["grps"];
			
			// ***** GET MISSIONS ***** //
			$missions = array();
			$tasks = array();
			$sql = "SELECT ct.camp_mission_id, cm.mission_name, gtd.group_task_date_id, gt.camp_task_id, ct.task_name ";
			$sql = $sql . "FROM group_task_dates AS gtd ";
			$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";			
			$sql = $sql . "JOIN group_tasks AS gt USING (group_task_id) ";
			$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
			$sql = $sql . "JOIN camp_campaigns AS cc USING (camp_campaign_id) ";
			$sql = $sql . "WHERE gt.division_id=" . $division_id . " AND gtd.task_date=" . $task_date . " AND cc.camp_campaign_id=" . $camp_campaign_id . " ";
			$sql = $sql . "ORDER BY ct.camp_mission_id, gtd.camp_task_id";
			
			$query = mysql_query($sql);
			$num_rows = mysql_num_rows($query);
			
			$camp_mission_id = "";
			$camp_task_id = "";
			$row_num = 0;
			
			while ($row = mysql_fetch_assoc($query)) {
				$prev_camp_mission_id = $row["camp_mission_id"];
				$prev_camp_task_id = $row["camp_task_id"];				
				$row_num++;
				
				if ($prev_camp_task_id != $camp_task_id && $camp_task_id != "") {
					$task = compact('camp_task_id', 'task_name');
					array_push($tasks, $task);
				}
				
				if ($prev_camp_mission_id != $camp_mission_id && $camp_mission_id != "") {
					$mission = compact('camp_mission_id', 'mission_name', 'tasks');
					array_push($missions, $mission);
					$tasks = array();
				}
				
				$camp_mission_id = $prev_camp_mission_id;
				$mission_name = $row["mission_name"];
				$camp_task_id = $prev_camp_task_id;
				$task_name = $row["task_name"];

				if ($row_num == $num_rows) {
					$task = compact('camp_task_id', 'task_name');
					array_push($tasks, $task);
					$mission = compact('camp_mission_id', 'mission_name', 'tasks');
					array_push($missions, $mission);				
				}
			}
			// ***** GET MISSIONS ***** //
			
			$division = compact('division_id', 'division_name', 'groups', 'missions');
			array_push($divisions, $division);			
		}
		
		$campaign = compact('camp_campaign_id', 'campaign_name', 'divisions');
		array_push($campaigns, $campaign);
		//$divisions = array();
		//$groups = array();
	}
	
	return json_encode($campaigns);
	
	/*$campaigns = array();
	
	while ($row = mysql_fetch_assoc($query)) {
		$divisions = array();
		$groups = array();
	
		
		echo "CAMPAIGN NAME:" . $campaign_name . "<br />";
		
		$sql2 = "SELECT d.division_id, d.division_name, g.group_id, g.group_name, gtd.group_task_date_id, ct.camp_mission_id, cm.mission_name, ct.task_name ";
		$sql2 = $sql2 . "FROM group_task_dates AS gtd ";
		$sql2 = $sql2 . "JOIN groups AS g USING (group_id) ";
		$sql2 = $sql2 . "JOIN divisions AS d USING (division_id) ";
		$sql2 = $sql2 . "JOIN camp_tasks AS ct ON (gtd.camp_task_id=ct.camp_task_id) ";
		$sql2 = $sql2 . "JOIN camp_missions AS cm USING (camp_mission_id) ";
		$sql2 = $sql2 . "JOIN camp_campaigns AS cc USING (camp_campaign_id) ";
		$sql2 = $sql2 . "WHERE cc.camp_campaign_id=" . $camp_campaign_id . " AND gtd.task_date=" . $task_date . " AND d.group_type_id=" . $group_type_id .  " ";
		$sql2 = $sql2 . "ORDER BY d.division_id, g.group_id, ct.camp_mission_id ";
		
		$query2 = mysql_query($sql2);
		$num_rows = mysql_num_rows($query2);
		$division_id = "";
		$group_id = "";
		$camp_mission_id = "";
		$row_num = 0;
		$tasks = array();
		$missions = array();
		while ($row2 = mysql_fetch_assoc($query2)) {
			$prev_division_id = $row2["division_id"];
			$prev_group_id = $row2["group_id"];
			$prev_camp_mission_id = $row2["camp_mission_id"];
			$row_num++;
			
			if ($prev_camp_mission_id != $camp_mission_id && $camp_mission_id != "") {
				$mission = compact('camp_mission_id', 'mission_name', 'tasks');
				array_push($missions, $mission);
				$tasks = array();
			}
			
			if ($prev_group_id != $group_id && $group_id != "") {
				echo "GROUP NAME:" . $group_name . "<br />";
				//////////$group = compact('group_id', 'group_name', 'missions');
				$group = compact('group_id', 'group_name');
				array_push($groups, $group);
				//$missions = array();
			}			
			
			if ($prev_division_id != $division_id && $division_id != "") {
				echo "DIVISION NAME:" . $division_name . "<br />";
				$division = compact('division_id', 'division_name', 'groups', 'missions');
				array_push($divisions, $division);
				$groups = array();
				$missions = array();
			}
			

			
			$group_task_date_id = $row2["group_task_date_id"];
			$task_name = $row2["task_name"];
			$task = compact('group_task_date_id', 'task_name');
			array_push($tasks, $task);
			
			$division_id = $prev_division_id;
			$division_name = $row2["division_name"];
			$group_id = $prev_group_id;
			$group_name = $row2["group_name"];			
			
			$camp_mission_id = $prev_camp_mission_id;
			$mission_name = $row2["mission_name"];
			
			if ($row_num == $num_rows) {
				echo "DIVISION NAME:" . $division_name . "<br />";
				
				//////////$group = compact('group_id', 'group_name', 'tasks');
				$group = compact('group_id', 'group_name');
				array_push($groups, $group);
					
				$division = compact('division_id', 'division_name', 'groups', 'missions');
				array_push($divisions, $division);			
			}
			
		}
		echo "<br />";
		
		$campaign = compact('camp_campaign_id', 'campaign_name', 'divisions');
		array_push($campaigns, $campaign);
		$divisions = array();
		$groups = array();
		$members = array();
		$missions = array();
		$tasks = array();
		
	}
	
	return json_encode($campaigns);*/
}	

function get_user_code($parameters) {
	$admin_id = $parameters[0];
	$user_code = $parameters[1];
	
	include ("classes/user.php");
	include ("classes/school.php");
	include ("classes/school_class.php");
	
	// ***** Check to see if the user exists ***** //
	$sql = "SELECT * FROM users WHERE user_code=" . $user_code;

	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	if ($num_rows > 0) {
		$row = mysql_fetch_assoc($query);
		$user = new user($row);
		$user->get_school();
		$user->get_parent();
		
		if ($user->parent_id > 0 && $user->parent_id != $admin_id) {
			return json_encode("1");
		}
		else {
			if ($user->school->school_settings != "home_school") {
				$user->get_school_class();
				$user->get_school_info();
			}
			return json_encode($user);
		}
	}
	else {
		return json_encode("0");
	}
}


function get_marking_divisions($parameters) {
	include ("classes/group.php");
	include ("classes/user.php");
	
	$task_date = $parameters[0]; 
	$group_type_id = $parameters[1];
	
	$divisions = array();
	$groups = array();
	
	$sql = "SELECT d.division_id, d.division_name, g.* ";
	$sql = $sql . "FROM member_tasks AS mt ";
	$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
	$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
	$sql = $sql . "JOIN camp_campaigns AS cc USING (camp_campaign_id) ";
	$sql = $sql . "JOIN groups AS g USING (group_id) ";
	$sql = $sql . "JOIN divisions AS d USING (division_id) ";
	$sql = $sql . "JOIN group_types AS gt USING (group_type_id) ";
	$sql = $sql . "WHERE mt.task_date=" . $task_date . " AND d.group_type_id=" . $group_type_id . " ";
	$sql = $sql . "ORDER BY gt.group_type_id, d.division_id, g.group_id";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	$row_num = 0;
	$division_id = "";
	$group_id = "";	
	while ($row = mysql_fetch_assoc($query)) {
		$prev_division_id = $row['division_id'];
		$prev_group_id = $row['group_id'];
		$row_num++;		
		
		if ($prev_group_id != $group_id && $group_id != "") {			
			$group = new group($row);
			$group->get_members_info($group_id);	
			$group->get_missions($task_date, $group_id);						
			$members = $group->members;	
			$missions = $group->missions;
			
			$group = compact('group_id', 'group_name', 'members', 'missions');
			array_push($groups, $group);
		}			
		
		if ($prev_division_id != $division_id && $division_id != "") {	
			$division = compact('division_id', 'division_name', 'groups');
			array_push($divisions, $division);
			$groups = array();			
		}			
		
		$division_id = $prev_division_id;
		$division_name = $row['division_name'];
					
		$group_id = $prev_group_id;
		$group_name = $row['group_name'];

		if ($row_num == $num_rows) {
			$group = new group($row);
			$group->get_members_info($group_id);	
			$group->get_missions($task_date, $group_id);						
			$members = $group->members;	
			$missions = $group->missions;
			
			$group = compact('group_id', 'group_name', 'members', 'missions');
			array_push($groups, $group);
		
			$division = compact('division_id', 'division_name', 'groups');
			array_push($divisions, $division);		
		}
	}
	
	return json_encode($divisions);
}

/*function get_marking_group_members($parameters) {
	include ("classes/group.php");
	include ("classes/user.php");
	
	$task_date = $parameters[0]; 
	$group_type_id = $parameters[1];
	
	$divisions = array();
	$groups = array();
	
	$sql = "SELECT d.division_id, d.division_name, g.* ";
	$sql = $sql . "FROM member_tasks AS mt ";
	$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
	$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
	$sql = $sql . "JOIN camp_campaigns AS cc USING (camp_campaign_id) ";
	$sql = $sql . "JOIN groups AS g USING (group_id) ";
	$sql = $sql . "JOIN divisions AS d USING (division_id) ";
	$sql = $sql . "JOIN group_types AS gt USING (group_type_id) ";
	$sql = $sql . "WHERE mt.task_date=" . $task_date . " AND d.group_type_id=" . $group_type_id . " ";
	$sql = $sql . "ORDER BY gt.group_type_id, d.division_id, g.group_id";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	$row_num = 0;
	$division_id = "";
	$group_id = "";	
	while ($row = mysql_fetch_assoc($query)) {
		$prev_division_id = $row['division_id'];
		$prev_group_id = $row['group_id'];
		$row_num++;		
		
		if ($prev_group_id != $group_id && $group_id != "") {
			$group = new group($row);
			$group->get_members_info();	
			$group->get_missions($task_date);			
			$members = $group->members;	
			$missions = $group->missions;			
			$group = compact('group_id', 'group_name', 'members', 'missions');
			array_push($groups, $group);
		}			
		
		if ($prev_division_id != $division_id && $division_id != "") {		
			$division = compact('division_id', 'division_name', 'groups');
			array_push($divisions, $division);
			$groups = array();			
		}			
		
		$division_id = $prev_division_id;
		$division_name = $row['division_name'];
			
		$group_id = $prev_group_id;
		$group_name = $row['group_name'];

		if ($row_num == $num_rows) {
			$division = compact('division_id', 'division_name', 'groups');
			array_push($divisions, $division);		
		}
	}
	
	return json_encode($divisions);
}*/

function get_mission_tasks($parameters) {
	$task_date = $parameters[0]; 
	$group_type_id = $parameters[1];

	$missions = array();
	$tasks = array();
	
	$sql = "SELECT cc.camp_campaign_id, cc.campaign_name, cm.camp_mission_id, cm.mission_name, ct.camp_task_id, ct.task_name ";
	$sql = $sql . "FROM member_tasks AS mt ";
	$sql = $sql . "JOIN camp_tasks AS ct USING (camp_task_id) ";
	$sql = $sql . "JOIN camp_missions AS cm USING (camp_mission_id) ";
	$sql = $sql . "JOIN camp_campaigns AS cc USING (camp_campaign_id) ";
	$sql = $sql . "JOIN groups AS g USING (group_id) ";
	$sql = $sql . "JOIN divisions AS d USING (division_id) ";
	$sql = $sql . "JOIN group_types AS gt USING (group_type_id) ";
	$sql = $sql . "WHERE mt.task_date=" . $task_date. " AND d.group_type_id=" . $group_type_id . " ";
	$sql = $sql . "GROUP BY cc.camp_campaign_id, cm.camp_mission_id, ct.camp_task_id ";
	$sql = $sql . "ORDER BY cc.camp_campaign_id, cm.camp_mission_id, ct.camp_task_id";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);

	$camp_mission_id = "";
	$row_num = 0;
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		$prev_camp_mission_id = $row['camp_mission_id'];
		
		if ($prev_camp_mission_id != $camp_mission_id && $camp_mission_id != "") {
			$element = compact('mission_name', 'tasks');
			array_push($missions, $element);
			$tasks = array();
		}
		
		$mission_name = $row['mission_name'];
		$camp_task_id = $row['camp_task_id'];
		$task_name = $row['task_name'];
		$element = compact('camp_task_id', 'task_name');
		array_push($tasks, $element);
				
		if ($row_num == $num_rows) {
			$element = compact('mission_name', 'tasks');
			array_push($missions, $element);		
		}
		
		$camp_mission_id = $prev_camp_mission_id;
	}
	
	return json_encode($missions);
}

function get_username($parameters) {
	$username = $parameters[0];
	$admin_email = $parameters[1];
	
	$sql = "SELECT username FROM admins WHERE username='" . mysql_real_escape_string($username) . "'";	
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$num_rows = mysql_num_rows($query);
	
	if ($num_rows > 0) {
		return json_encode("User name already exists. Please enter a new one.");
	}
	else {
		$sql = "SELECT admin_email FROM admins WHERE admin_email='" . mysql_real_escape_string($admin_email) . "'";
		$query = mysql_query($sql);
		$num_rows = mysql_num_rows($query);
		
		if ($num_rows > 0) {
			return json_encode("Email address already exists. Please enter a new one.");
		}
		else {
			return json_encode("");
		}
	}
}

// is username duplicate
function is_username_duplicate($parameters) {
	$username = $parameters[0];
	
	$sql = "SELECT username FROM admins WHERE username='" . mysql_real_escape_string($username) . "'";	
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$num_rows = mysql_num_rows($query);
	
	if ($num_rows > 0) {
		return json_encode(true);
	}
	else {
		return json_encode(false);
	}
}

// is email duplicate
function is_email_duplicate($parameters) {
	$email = $parameters[0];
	
	$sql = "SELECT admin_email FROM admins WHERE admin_email='" . mysql_real_escape_string($email) . "'";	
	//echo $sql;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$num_rows = mysql_num_rows($query);
	
	if ($num_rows > 0) {
		return json_encode(true);
	}
	else {
		return json_encode(false);
	}
}


function get_all_group_types_divisions_groups($parameters) {
	$camp_id = $parameters[0];
	
    $group_types = array();
    $divisions = array();
	$groups = array();
	
	$sql = "SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name, g.group_id, g.group_name ";
	$sql = $sql . "FROM group_types AS gt ";
	$sql = $sql . "LEFT JOIN divisions AS d USING (group_type_id) ";
	$sql = $sql . "LEFT JOIN groups AS g USING (division_id) ";
	$sql = $sql . "WHERE camp_id=" . $camp_id . " ";
	$sql = $sql . "ORDER BY gt.group_type_id, d.division_id, g.group_id";

    $query = mq($sql);
	$num_rows = mysql_num_rows($query);
	$row_num = 0;
    while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		
        $prev_group_type_id = $row['group_type_id'];
        $prev_division_id = $row['division_id'];
		$group_id = $row['group_id'];
		$group_name = $row['group_name'];
		
		if ($prev_division_id != $division_id && $division_id != "") {
			$division = compact('division_id', 'division_name', 'groups');
			array_push($divisions, $division);		
			$groups = array();
		}
		
		if ($prev_group_type_id != $group_type_id && $group_type_id != "") {
			$group_type = compact('group_type_id', 'group_type_name', 'divisions');
			array_push($group_types, $group_type);		
			$divisions = array();
		}		
				
		$group = compact('group_id', 'group_name');
		array_push($groups, $group);
		
        $group_type_id = $prev_group_type_id;
        $group_type_name = $row['group_type_name'];

		$division_id = $prev_division_id;
        $division_name = $row['division_name'];		
		
		if ($row_num == $num_rows) {
			$group = compact('group_id', 'group_name');
			array_push($groups, $group);
		
			$division = compact('division_id', 'division_name', 'groups');
			array_push($divisions, $division);		
		
			$group_type = compact('group_type_id', 'group_type_name', 'divisions');
			array_push($group_types, $group_type);				
		}

    }
    
    return json_encode($group_types);
}

function check_camp_name($parameters) {
	$camp_name = $parameters[0];
	
	$sql = "SELECT COUNT(*) AS camp_names FROM camps WHERE camp_name='" . mysql_real_escape_string($camp_name) . "'";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$camp_names = $row['camp_names'];
		
	return $camp_names;
}

function check_admin_username($parameters) {
	$username = $parameters[0];
	
	$sql = "SELECT COUNT(*) AS no_of_usernames FROM admins WHERE username='" . mysql_real_escape_string($username) . "'";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$no_of_usernames = $row['no_of_usernames'];
		
	return $no_of_usernames;
}

function get_group_tasks($parameters) {
	$task_date = $parameters[0]; 
	$group_ids =explode((":", $parameters[1]); 
	$camp_task_ids =explode((":", $parameters[2]);
	
	$tasks = array();
	$groups = array();
	
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
		
			$task = compact('group_task_date_id', 'completed');
			array_push($tasks, $task);
		}
		
		$group = compact('group_id', 'tasks');
		array_push($groups, $group);
		$tasks = array();
	}
	
	return json_encode($groups);	
}

function get_group_date_tasks($parameters) {
	$task_date = $parameters[0];
	$group_id = $parameters[1];	
	$mission_no = $parameters[2];
	
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

function get_group_point_missions($parameters) {
	$group_type_id = $parameters[0];	
	$task_date = $parameters[1];

	$campaigns = array();
	$missions = array();
	$tasks = array();
	
	$sql = "SELECT cc.camp_campaign_id, cc.campaign_name, cm.camp_mission_id, cm.mission_name, gtd.camp_task_id, ct.task_name ";
	$sql = $sql . "FROM group_task_dates AS gtd ";
	$sql = $sql . "JOIN camp_tasks AS ct ON (gtd.camp_task_id=ct.camp_task_id) ";
	$sql = $sql . "JOIN camp_missions AS cm ON (ct.camp_mission_id=cm.camp_mission_id) ";
	$sql = $sql . "JOIN camp_campaigns AS cc ON (cm.camp_campaign_id=cc.camp_campaign_id) ";
	$sql = $sql . "JOIN group_tasks AS gt1 ON (gtd.group_task_id=gt1.group_task_id AND gt1.group_task=1) ";
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


function get_group_task($parameters) {
	$groups = $parameters[0]; 
	$camp_task_id = $parameters[1];
	$group_task =  $parameters[2];
	
	$strrpos = strrpos($groups, "division");
	
	if ($strrpos > -1) {
		$info = explode("_", $groups);
		$division_id = $info[1];
		$sql = "SELECT group_task_id FROM group_tasks WHERE division_id=" . $division_id . " AND camp_task_id=" . $camp_task_id . " AND group_task=" . $group_task;
	}
	else {
		$info = explode("_", $groups);		
		$group_type_id = $info[2];	
		$sql = "SELECT group_task_id FROM group_tasks WHERE group_type_id=" . $group_type_id . " AND camp_task_id=" . $camp_task_id . " AND group_task=" . $group_task;		
	}

	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	return json_encode($num_rows);
}

function get_member_tasks($parameters) {
	$task_date = $parameters[0];
	$user_ids = explode(":", $parameters[1]); 
	$camp_task_ids = explode(":", $parameters[2]);
	
	$member_tasks = array();
	$mission_tasks = array();
	
	for ($uno = 0; $uno < count($user_ids); $uno++) {
		$user_id = $user_ids[$uno];
		
		for ($ctno = 0; $ctno < count($camp_task_ids); $ctno++) {
			$sql = "SELECT member_task_id, user_id, completed FROM member_tasks WHERE user_id=" . $user_id . " AND camp_task_id=" . $camp_task_ids[$ctno] . " AND task_date=" . $task_date;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$member_task_id = $row['member_task_id'];
			$completed = $row['completed'];
			$element = compact('member_task_id', 'completed');
			array_push($member_tasks, $element);
		}
		
		$element = compact('user_id', 'member_tasks');
		array_push($mission_tasks, $element);
		$member_tasks = array();
	}

	return json_encode($mission_tasks);
}



// get all children associated with a parent (mmc)
function get_children_of_admin_id($parameters) {
	$admin_id = mysql_real_escape_string($parameters[0]); 	
	
	include ("classes/admin.php");
	include ("classes/user.php");
	
	$children = array();
	$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$admin = new admin($row);
	$admin->get_children();
	
	for ($cno = 0; $cno < count($admin->children); $cno++) {
		$child = $admin->children[$cno];
		array_push($children, $child);
	}
	
	return json_encode($children);
}



// convert results to JSON format
function convertResult2JSON($recordset) {
	$rs = array();
	while($rs[] = mysql_fetch_assoc($recordset)) {    
	}
	return json_encode($rs);
}


?>