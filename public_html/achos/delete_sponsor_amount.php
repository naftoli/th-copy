<?php
include ("db.php");
$year = date('Y');

$sql = "DELETE FROM admin_sponsors WHERE admin_id=" . $_GET['admin_id'] . " AND year=" . $year;
$query = mysql_query($sql);	
if ($query) 
	echo json_encode('1');
else 
	echo json_encode('0');
?>