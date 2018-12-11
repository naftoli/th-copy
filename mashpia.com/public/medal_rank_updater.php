<?php
include("db.php");
include("classes/medal_updater.php");
include("classes/rank_updater.php");


if (isset($_GET['user_id'])) {
	$medal_updater = new medal_updater();
	$medal_updater->update_medal($_GET['user_id']);
	
	$rank_updater = new rank_updater();
	$rank_updater->update_rank($_GET['user_id']);
}
?>
