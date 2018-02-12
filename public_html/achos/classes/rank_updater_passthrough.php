<?php
	require_once ("../db.php");
	$arrUsers = explode(",", @$_POST['user_ids']);
	//$arrUsers = array(257,290,326,5637,5641,5646,608,633,636,637,641,650,651,662,676,678,702,704);
	require_once("rank_updater.php");
	$intItr = 0;
	foreach ($arrUsers as $intUser)
	{
		if (!preg_match("/[0-9]+/", $intUser))
			continue;
		rank_updater::update_rank_two($intUser);
		$intItr++;
	}
	print serialize(array("success" => true, "count" => $intItr));
?>