<?php
$admin_auth = array('camp');
require('../header.php'); 

$group_type_id = $_GET['group_type_id'];
$division_name = $_GET['division_name'];

$sql = "INSERT INTO divisions SET group_type_id=" . $group_type_id . ", division_name=" . ms($division_name);
mq($sql);
$last_group_type_id = mysql_insert_id();
echo $last_group_type_id;
?>