<?php
include ("db.php");
$sql = "UPDATE users SET user_registration_fee=" . ms($_GET['user_registration_fee']) . " WHERE user_id=" . intval($_GET['user_id']);
$query = mysql_query($sql);	
if ($query) 
	echo json_encode('1');
else 
	echo json_encode('0');
?>