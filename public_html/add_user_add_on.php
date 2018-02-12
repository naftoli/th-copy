<?php
include ("db.php");

if (isset($_GET['size']))
	$sql = "INSERT INTO user_add_ons (user_id, school_add_on_id, size, date) VALUES (" . $_GET['user_id'] . ", " . $_GET['school_add_on_id'] . ", '" . $_GET['size'] . "', CURDATE())";	
else
	$sql = "INSERT INTO user_add_ons (user_id, school_add_on_id, date) VALUES (" . $_GET['user_id'] . ", " . $_GET['school_add_on_id'] . ", CURDATE())";	

$query = mysql_query($sql);
	
if ($query) {
	echo json_encode('1');
}
else { 
	echo json_encode('0');
}
?>