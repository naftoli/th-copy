<?php 
ini_set('display_errors',1);
ini_set('max_execution_time',600);

if (isset($_GET['num'])) $num = $_GET['num'];
define('MULTIPLYBY', 100);
$start = $num * MULTIPLYBY;

include('db.php');
include('classes/mission_marks_updater.php');
include('classes/medal_updater.php');
include('classes/rank_updater.php');

$users = [];
$sql = "select user_id from users where user_registered > 0 limit $start, " . MULTIPLYBY;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

$user_count = 0;
echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
foreach ( $users as $user_id ) {	
	$mission_marks_updater = new mission_marks_updater();
	$mission_marks_updater->mission_marks_update($user_id);

	$medal_updater = new medal_updater();
	$medal_updater->update_medal_two($user_id);

	$rank_updater = new rank_updater();
	$rank_updater->update_rank_two($user_id);
	
	$user_count++;
}
echo $user_count . " users processed<br />";
?>