<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<?
require("../db.php");
require("../classes/mission_marks_updater.php");
//require("../classes/medal_updater.php");
require("../classes/rank_updater.php");
//$medal_updater = new medal_updater();
$rank_updater = new rank_updater();
$mm = new mission_marks_updater();

$users = array();
$user_sql = "select user_id from users where user_registered > 0 order by user_id";
$user_res = mysql_query($user_sql);
while ($us = mysql_fetch_row($user_res)) {
	$users[] = $us[0];
}

for ($i = 3200; $i < count($users); $i++) {
	//update missions
	echo "User: " . $users[$i] . "<br />";
	$mm->mission_marks_update($users[$i]);
	echo "------------------------------<br /><br />";
	//update medals
	//$medal_updater->update_medal($users[$i]);
	//update ranks
	$rank_updater->update_rank($users[$i]);
}
?>
</body>
</html>