<?php
include ("db.php");
$sql = "UPDATE user_add_ons SET size='" . ms($_GET['size']) . "' WHERE user_id=" . intval($_GET['user_id']) . " AND school_add_on_id=" . intval($_GET['school_add_on_id']);	
$query = mysql_query($sql);
if ($query)
	echo json_encode('1');
else
	echo json_encode('0');

?>