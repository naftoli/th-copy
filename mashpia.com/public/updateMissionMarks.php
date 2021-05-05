<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);
$limit= isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset= isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$all = isset($_GET['all']) ? intval($_GET['all']) : 0;
$output = isset($_GET['output']) ? intval($_GET['output']) : 0;
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
	<p>
		<? if ($offset-$limit >= 0) { ?>
			<a id="previous" href="<?= "/updateMissionMarks.php?all=$all&output=$output&limit=$limit&offset=".($offset-$limit)?>">previous</a>
		<? } else { ?>
			<span>previous</span>
		<? } ?>
		<a id="next" href="<?= "/updateMissionMarks.php?all=$all&output=$output&limit=$limit&offset=".($offset+$limit)?>">next</a>
	</p>
	<?
		require_once('db.php');
		require_once('classes/mission_marks_updater.php');
		//require_once('classes/medal_updater.php');
		//require_once('classes/rank_updater.php');

		$users = array();
		$sql = "select user_id from users where user_registered > 0 order by user_id limit $limit offset $offset";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$users[] = $row['user_id'];
		}

		$mmupdater = new mission_marks_updater();
		//$mupdater = new medal_updater();
		//$rupdater = new rank_updater();

		foreach ($users as $user) {
			$mmupdater->mission_marks_update($user, $output);
			//$mupdater->update_medal_two($user);
			//$rupdater->update_rank_two($user);
		}
	?>
	<? if ($all && count($users) > 0) { ?>
		<script>
			setTimeout(() => {
				document.getElementById("next").click()
			}, 1000)
		</script>
	<? } ?>
</body>
</html>