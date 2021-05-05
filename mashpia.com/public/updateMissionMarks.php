<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);
$limit= isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset= isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$continue = isset($_GET['continue']) ? intval($_GET['continue']) : 0;
$output = isset($_GET['output']) ? intval($_GET['output']) : 0;
$metals = isset($_GET['metals']) ? intval($_GET['metals']) : 0;
$ranks = isset($_GET['ranks']) ? intval($_GET['ranks']) : 0;
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
	<p>
		<? if ($offset-$limit >= 0) { ?>
			<a id="previous" href="<?= "/updateMissionMarks.php?continue=$continue&output=$output&limit=$limit&offset=".($offset-$limit)."&metals=$metals&ranks=$ranks"?>">previous</a>
		<? } else { ?>
			<span>previous</span>
		<? } ?>
		<a id="next" href="<?= "/updateMissionMarks.php?continue=$continue&output=$output&limit=$limit&offset=".($offset+$limit)."&metals=$metals&ranks=$ranks"?>">next</a>
	</p>
	<?
		echo "<p>Loading...</p>";

		require_once('db.php');
		require_once('classes/mission_marks_updater.php');
		require_once('classes/medal_updater.php');
		require_once('classes/rank_updater.php');

		$users = array();
		$sql = "select user_id from users where user_registered > 0 order by user_id limit $limit offset $offset";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$users[] = $row['user_id'];
		}

		$mmupdater = new mission_marks_updater();
		if ($metals) $mupdater = new medal_updater();
		if ($ranks) $rupdater = new rank_updater();

		foreach ($users as $user) {
			$mmupdater->mission_marks_update($user, $output);
			if ($metals) $mupdater->update_medal_two($user);
			if ($ranks) $rupdater->update_rank_two($user);
		}
		echo "<p>Done</p>";
	?>
	<? if ($continue && count($users) > 0) { ?>
		<script>
			setTimeout(() => {
				document.getElementById("next").click()
			}, 1000)
		</script>
	<? } ?>
</body>
</html>