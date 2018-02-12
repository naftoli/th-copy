<?php
include ("db.php");
$sql = "UPDATE users SET user_registration_fee=" . $_GET['user_registration_fee'] . " WHERE user_id=" . $_GET['user_id'];
$query = mysql_query($sql);	
if ($query) 
	echo json_encode('1');
else 
	echo json_encode('0');
?>