<?
require '../db.php';

$sql = "select count(*) as total from users where user_registered > 0";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$registered = $row['total'];

$sql = "select count(*) as total from medal_marks 
		join users u using (user_id) 
		where u.user_registered > 0";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$medals = $row['total'];

$sql = "select count(*) as total from date_tasks_mission_marks 
		join users u using (user_id) 
		where u.user_registered > 0";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$missions = $row['total'];

$sql = "select count(*) as total from date_tasks_marks 
		join users u using (user_id) 
		where u.user_registered > 0";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$tasks = $row['total'];

echo json_encode(array($registered, $medals, $missions, $tasks));
?>