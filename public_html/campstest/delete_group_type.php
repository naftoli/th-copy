<?php
$admin_auth = array('camp');
require('../header.php'); 

$group_type_id = $_GET['group_type_id'];

$sql = "DELETE FROM group_types WHERE group_type_id=" . $group_type_id;
mq($sql);
?>