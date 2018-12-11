<?php
include("db.php");

$today = gregoriantojd(date("n"), date("j"), date("Y"));

$sql = "UPDATE users SET user_start_date=" . $today . " WHERE user_id=9738";
$query = mysql_query($sql);

if ($query)
	echo "SUCCESS";
else
	echo "FAILURE"
?>