<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	
	<body>
	
<?php
include("db.php");

require_once("classes/rank_updater.php");
$rank_updater = new rank_updater();
$rank_updater->update_rank(0);

require_once("classes/medal_updater.php");
$medal_updater = new medal_updater();
$medal_updater->update_medal(0);
?>
	</body>
	
</html>