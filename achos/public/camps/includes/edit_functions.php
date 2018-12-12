<?php
include ("db.php");

$function_name = $_GET['function_name'];
$parameters = $_GET['parameters'];
if (is_array($parameters)) {
	$parameters = explode(",", $parameters);
}

echo $function_name($parameters);

// set school registration date, era
// note NULL should not be in quotes
function set_school_era($parameters) {
	$school_id = $parameters;	
	$sql = "UPDATE schools 
			SET school_era = NULL,
				last_register_date = NOW() 
			WHERE school_id=" . $school_id ;	
	$query = mysql_query($sql);
	if ($query) {				
		return "true";
	}
	else {		
		return "false";	
	}
}

function update_user_registered($parameters) {
	//print_r($parameters);
	$user_ids = explode(":", $parameters);
	
	$success_flag = true;
	for ($uno = 0; $uno < count($user_ids); $uno++) {
				
		//check that school is registered added 4/19/13
		$school = "select s.school_era from schools s
                            join users u using (school_id) 
                            where u.user_id = " . $user_ids[$uno];
		$schoolRes = mysql_query( $school );
		$schoolRow = mysql_fetch_assoc( $schoolRes );
		$era = $schoolRow['school_era'];
		if ( !is_null( $era ) ) {
                    return 2;
		} 
		
		$checkGender = "select gender from users where user_id = $user_ids[$uno]";
		$res = mysql_query($checkGender);
		$row = mysql_fetch_row($res);
		$gender = $row[0];
		if ($gender == 'f' || $gender = 'F') $type = 3;
		else $type = 2;
		$updateDate = false;
		$startDate = "select user_start_date from users where user_id = " . $user_ids[$uno];
		$dateResult = mysql_query($startDate);
		$dateRow = mysql_fetch_row($dateResult);
		$date = $dateRow[0];
		$today_jd = gregoriantojd (date("n"), date("j"), date("Y"));
		if (is_null($date)) $updateDate = true;
		$sql = "UPDATE users SET user_registered=NOW(), school_type_id = $type";
		if ($updateDate) $sql .= ", user_start_date = $today_jd";
		$sql .= " WHERE user_id=" . $user_ids[$uno];
		//echo $sql;
		$query = mysql_query($sql);
		if (!$query) {
                    $success_flag = false;
                    break;
		}
	}
	
	return $success_flag;
	
}

function update_label_period($parameters) {
	$label_id = $parameters[0];
	$frequency_id = $parameters[1];
	
	$sql = "UPDATE labels SET frequency_id=" . $frequency_id . " WHERE label_id=" . $label_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else 
		return false;
}

function edit_staff_type($parameters) {
	$admin_id = $parameters[0];
	$staff_type_id = $parameters[1];
	
	$sql = "UPDATE admins SET staff_type_id=" . $staff_type_id . " WHERE admin_id=" . $admin_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else 
		return false;	
}

function remove_staff_type($parameters) {
	$admin_id = $parameters[0];
	$sql = "UPDATE admins SET staff_type_id=NULL WHERE admin_id=" . $admin_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else 
		return false;	
}

function register_camper($parameters) {
	$user_id = $parameters[0];
	$sql = "UPDATE users SET camp_registered=NOW() WHERE user_id=" . $user_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else 
		return false;
}

function update_group_task($parameters) {
	$group_task_date_id = $parameters[0];
	$completed = $parameters[1];
	
	$sql = "UPDATE group_task_dates SET completed=" . $completed . " WHERE group_task_date_id=" . $group_task_date_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;
}


function update_group_tasks($parameters) {
	$group_task_date_ids =explode((":", $parameters[0]);
	$completed = $parameters[1];

	$success = true;
	
	for ($gtdno = 0; $gtdno < count($group_task_date_ids); $gtdno++) {
		$group_task_date_id = $group_task_date_ids[$gtdno];
		$sql = "UPDATE group_task_dates SET completed=" . $completed . " WHERE group_task_date_id=" . $group_task_date_id;		
		$query = mysql_query($sql);
		if (!$query) {
			$success = false;
			break;
		}
	}
	
	return $success;
}


function save_group_type($parameters) {
	$group_type_id = $parameters[0];
	$group_type_name = $parameters[1];

	$sql = "UPDATE group_types SET group_type_name='" . mysql_real_escape_string($group_type_name) . "' WHERE group_type_id=" . $group_type_id;
	$query = mysql_query($sql);
	if ($query) 
		return true;
	else
		return false;
}

function scan_voucher($parameters) {
	$voucher_id = $parameters[0];
	
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

function save_group($parameters) {
	$group_id = $parameters[0];
	$group_name = $parameters[1];

	$sql = "UPDATE groups SET group_name='" . mysql_real_escape_string($group_name) . "' WHERE group_id=" . $group_id;
	$query = mysql_query($sql);
	if ($query) 
		return true;
	else
		return false;
}

function edit_group_type($parameters) {
    $group_type_id = $parameters[0];
    $group_type_name = $parameters[1];

    $sql = "UPDATE group_types SET group_type_name='" . mysql_real_escape_string($group_type_name) . "' WHERE group_type_id=" . $group_type_id;
    $query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;
}

function edit_group($parameters) {
    $group_id = $parameters[0];
    $group_name = $parameters[1];

    $sql = "UPDATE groups SET group_name='" . mysql_real_escape_string($group_name) . "' WHERE group_id=" . $group_id;
    $query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;
}

function edit_division($parameters) {
    $division_id = $parameters[0];
    $division_name = $parameters[1];
	
    $sql = "UPDATE divisions SET division_name='" . mysql_real_escape_string($division_name) . "' WHERE division_id=" . $division_id;
    $query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;
}

function update_camp($parameters) {	
	$camp_id = $parameters[0]; 
	$field_name = $parameters[1]; 
	$value = $parameters[2];
	
	$sql = "UPDATE camps SET " . $field_name . "='" . mysql_real_escape_string($value) . "' WHERE camp_id=" . $camp_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;
}

function update_prize($parameters) {
	$prize_id = $parameters[0]; 
	$prize_name = $parameters[1]; 
	$prize_description = $parameters[2];
	$prize_points = $parameters[3];
	$prize_available = $parameters[4];
	
	$sql = "UPDATE prizes_camp SET prize_name='" . mysql_real_escape_string($prize_name) . "', prize_description='" . mysql_real_escape_string($prize_description) . "', prize_points=" . $prize_points . ", prize_available=" . $prize_available . " WHERE prize_id=" . $prize_id;
	$query = mysql_query($sql);		
	if ($query)
		return true;
	else
		return false;
}

function update_admin($parameters) {	
	$admin_id = $parameters[0]; 
	$field_name = $parameters[1]; 
	$value = $parameters[2];
	
	$sql = "UPDATE admins SET " . $field_name . "='" . mysql_real_escape_string($value) . "' WHERE admin_id=" . $admin_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;	
}

function update_user($parameters) {	
	$user_id = $parameters[0]; 
	$item_name = $parameters[1]; 
	$value = $parameters[2];
	
	$sql = "UPDATE users SET " . $item_name . "='" . mysql_real_escape_string($value) . "' WHERE user_id=" . $user_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;
}

function update_task($parameters) {
	$camp_task_id = $parameters[0]; 
	$task_name = $parameters[1]; 
	$points = $parameters[2];
	
	$sql = "UPDATE camp_tasks SET task_name='" . mysql_real_escape_string($task_name) . "', points=" . $points . " WHERE camp_task_id=" . $camp_task_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;
}

function update_member_task($parameters) {
	$member_task_id = $parameters[0];
	$completed = $parameters[1];
	
	$sql = "UPDATE member_tasks SET completed=" . $completed . " WHERE member_task_id=" . $member_task_id;
	$query = mysql_query($sql);
	if ($query)
		return true;
	else
		return false;
}

function update_member_tasks($parameters) {
	$member_task_ids =explode((":", $parameters[0]);
	$completed = $parameters[1];
	
	$success = true;
	
	for ($mtno = 0; $mtno < count($member_task_ids); $mtno++) {
		$member_task_id = $member_task_ids[$mtno];
		$sql = "UPDATE member_tasks SET completed=" . $completed . " WHERE member_task_id=" . $member_task_id;
		$query = mysql_query($sql);
		if (!$query) {
		 $success = false;
		 break;
		}
	}
	
	return $success;
}
?>