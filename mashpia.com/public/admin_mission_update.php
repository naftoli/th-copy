<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	
	<body>
	
<?php
include("db.php");

require('classes/mission_updater.php');
$mission_updater = new mission_updater();
// ***** The two parameters passed are the user id and date tasks mission id ***** //
$mission_updater->mission_update(7035, 40913);
?>
	</body>
	
</html>