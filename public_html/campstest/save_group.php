<?php
$admin_auth = array('camp');
require('../header.php'); 

$action = $_GET['action'];

if ($action == "insert") {
	$division_id = $_GET['division_id'];
	$group_name = $_GET['group_name'];

	$sql = "SELECT * FROM groups WHERE group_name=" . ms($group_name) . " AND division_id=" . $division_id;
	$result = mq($sql);
	$num_rows = mysql_num_rows($result);
	
	if ($num_rows == 0) {
		$insert = "INSERT INTO groups SET division_id=" . $division_id . ", group_name=" . ms($group_name);
		mq($insert);
		$group_id = mysql_insert_id();
		echo $group_id;
	}
	else {
		echo "0";
	}
}
elseif ($action == "update") {
	$group_id = $_GET['group_id'];
	$group_name = $_GET['group_name'];
	$sql = "UPDATE groups SET group_name=" . ms($group_name) . " WHERE group_id=" . $group_id;
	mq($sql);
}
elseif ($action == "remove") {
	$group_id = $_GET['group_id'];
	$sql = "DELETE FROM groups WHERE group_id=" . $group_id;
	mq($sql);
}
elseif ($action == "generate") {	
	$format = $_GET['format'];
	$division_id = $_GET['division_id'];
	$number_of_groups = $_GET['number_of_groups'];
	$division_name = $_GET['division_name'];
	$new_division_names = "";
	$letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	
	for ($cntr = 0; $cntr < $number_of_groups; $cntr++) {
		if ($format == "A") 
			$character = substr($letters, $cntr, 1);
		else
			$character = ($cntr + 1) . "";
				
		$sql = "SELECT * FROM groups WHERE division_id=" . $division_id . " AND group_name=" . ms($division_name . " " . $character);
		$query = mysql_query($sql);
		$num_rows = mysql_num_rows($query);

		if ($num_rows == 0) {
			$sql = "INSERT INTO groups SET division_id=" . $division_id . ", group_name=" . ms($division_name . " " . $character);
			
			if (!mysql_query($sql)) 
				 die();
			else 
				$new_group_id = mysql_insert_id();
			
			$new_division_names = $new_division_names . $new_group_id . "~" . $division_name . " " . $character . "|";
		}
	}
	
	$new_division_names = substr($new_division_names, 0, strlen($new_division_names) - 1);
	echo $new_division_names;
}

?>