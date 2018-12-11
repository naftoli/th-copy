<?php
ini_set('display_errors', 1);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<?php
$user = $_GET['user'];
require_once('db.php');
require_once('classes/mission_marks_updater.php');
require_once('classes/medal_updater.php');
require_once('classes/rank_updater.php');

$mmupdater = new mission_marks_updater();
$mupdater = new medal_updater();
$rupdater = new rank_updater();

$mmupdater->mission_marks_update($user, true);
$mupdater->update_medal_two($user);
$rupdater->update_rank_two($user);
?>
</html>