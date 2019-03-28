<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<?
include('db.php');
include('classes/mission_marks_updater.php');
include('classes/medal_updater.php');
include('classes/rank_updater.php');

$sql = "select user_id from users where user_registered > 0 order by user_id limit 3700, 100";
$result = mysql_query($sql);

$user_count = 0;
while ($user = mysql_fetch_assoc($result)) {

	$user_id = $user['user_id'];
	
	$mission_marks_updater = new mission_marks_updater();
	$mission_marks_updater->mission_marks_update($user_id, true);

	$medal_updater = new medal_updater();
	$medal_updater->update_medal_two($user_id);

	$rank_updater = new rank_updater();
	$rank_updater->update_rank_two($user_id);
	
	$user_count++;
}

echo $user_count . " users processed<br />";
?>