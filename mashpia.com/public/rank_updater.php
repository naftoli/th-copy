<?php
include("db.php");
include("classes/rank_updater.php");


if (isset($_GET['user_id'])) {
	$rank_updater = new rank_updater();
	$rank_updater->update_rank($_GET['user_id']);
}
else {
	$sql = "SELECT * FROM users where user_registered > 0";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query))
	{
		$user_id = $row['user_id'];
		$rank_updater = new rank_updater();
		$rank_updater->update_rank($user_id);
	}
}
?>