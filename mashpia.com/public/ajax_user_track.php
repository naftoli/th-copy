<?php
include("db.php");

$user_id = $_GET['user_id'];
$subject_id = $_GET['subject_id'];
$track_id = $_GET['track_id'];
$level = $_GET['level'];
$enrolled = $_GET['enrolled'];

$sql = "SELECT * FROM user_tracks WHERE user_id=" . $user_id . " AND subject_id=" . $subject_id;
$query = mysql_query($sql);
$num_rows = mysql_num_rows($query);

if ($num_rows == 0)
{
	$sql = "INSERT INTO user_tracks SET user_id=" . $user_id . ", subject_id=" . $subject_id . ", track_id=" . $track_id . ", level=" . $level . ", enrolled=" . $enrolled;
	$query = mysql_query($sql);
	if ($query)
		echo "1";
	else
		echo "0";
}
else
{
	$sql = "UPDATE user_tracks SET track_id=" . $track_id . ", level=" . $level . ", enrolled=" . $enrolled . " WHERE user_id=" . $user_id . " AND subject_id=" . $subject_id;
	$query = mysql_query($sql);
	if ($query)
		echo "1";
	else
		echo "0";
}
?>