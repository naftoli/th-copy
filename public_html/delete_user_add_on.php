<?php
include ("db.php");
$sql = "DELETE FROM user_add_ons WHERE user_id=" . $_GET['user_id'] . " AND school_add_on_id=" . $_GET['school_add_on_id'];	
$query = mysql_query($sql);
if ($query)
	echo json_encode('1');
else
	echo json_encode('0');
?>