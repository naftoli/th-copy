<?php
//ini_set('max_execution_time', 300);
chdir('../');
require 'db.php';
$user_id = mysql_real_escape_string($_POST['user']);

//require_once("classes/mission_marks_updater.php");
require_once("classes/medal_updater.php");
require_once("classes/rank_updater.php");

//$mm = new mission_marks_updater();
//$mm->mission_marks_update($user_id);

$medal_updater = new medal_updater();
$medal_updater->update_medal_two($user_id);

$rank_updater = new rank_updater();
$rank_updater->update_rank_two($user_id);
?>