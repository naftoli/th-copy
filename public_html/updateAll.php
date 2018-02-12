<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<?
require_once('db.php');
require_once('classes/mission_marks_updater.php');
//require_once('classes/medal_updater.php');
//require_once('classes/rank_updater.php');

$users = array();
$sql = "select user_id from users where user_registered > 0 limit 250, 500";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row['user_id'];
}

$mmupdater = new mission_marks_updater();
//$mupdater = new medal_updater();
//$rupdater = new rank_updater();

foreach ($users as $user) {
	$mmupdater->mission_marks_update($user, true);
	//$mupdater->update_medal_two($user);
	//$rupdater->update_rank_two($user);
}
?>
</html>