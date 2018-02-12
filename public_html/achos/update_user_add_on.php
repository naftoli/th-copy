<?php
include ("db.php");
$sql = "UPDATE user_add_ons SET size='" . $_GET['size'] . "' WHERE user_id=" . $_GET['user_id'] . " AND school_add_on_id=" . $_GET['school_add_on_id'];	
$query = mysql_query($sql);
if ($query)
	echo json_encode('1');
else
	echo json_encode('0');

?>