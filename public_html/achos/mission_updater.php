<?php
include("db.php");
include("classes/mission_marks_updater.php");

$user_id_min = $_GET['user_id_min'];
$user_id_max = $_GET['user_id_max'];

$sql = "SELECT * FROM users WHERE user_id > " . $user_id_min . " AND user_id < " . $user_id_max ;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$user_id = $row['user_id'];
	echo $user_id . "<br />";
	$mission_marks_updater = new mission_marks_updater();
	$mission_marks_updater->mission_marks_update($user_id);
}
echo "DONE";
?>