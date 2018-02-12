<?php
$admin_auth = array('camp');
require('../header.php'); 

$group_type_name = $_GET['group_type_name'];
$camp_id = $_GET['camp_id'];

$sql = "SELECT * FROM group_types WHERE group_type_name=" . ms($group_type_name) . " AND camp_id=" . $camp_id;
$result = mq($sql);
$num_rows = mysql_num_rows($result);

if ($num_rows == 0) {
	$sql = "INSERT INTO group_types SET group_type_name=" . ms($group_type_name) . ", camp_id=" . $camp_id;
	mq($sql);
	$last_group_type_id = mysql_insert_id();

	if ($last_group_type_id)
		echo $last_group_type_id . ":" . $group_type_name;
	else
		echo "0";
}
else {
	echo "0";
}
?>