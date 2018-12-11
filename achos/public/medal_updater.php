<?php
include("db.php");
include("classes/medal_updater.php");


if (isset($_GET['user_id'])) {
	$medal_updater = new medal_updater();
	$medal_updater->update_medal($_GET['user_id']);
}
else {
	$sql = "SELECT * FROM users where user_registered > 0";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query))
	{
		$medal_updater = new medal_updater();
		$medal_updater->update_medal($row['user_id']);
	}
}
?>