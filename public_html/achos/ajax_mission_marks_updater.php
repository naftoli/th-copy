<?
include("db.php");
include("classes/mission_marks_updater.php");
include("classes/medal_updater.php");
include("classes/rank_updater.php");

$user_id = $_GET['user_id'];

$mission_marks_updater = new mission_marks_updater();
$mission_marks_updater->mission_marks_update($user_id);

$medal_updater = new medal_updater();
$medal_updater->update_medal($user_id);

$rank_updater new rank_updater();
$rank_updater->update_rank($user_id);

echo "1";
?>