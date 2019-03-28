<?php
ini_set('display_errors',1);
ini_set('max_execution_time', 600);

if (isset($_GET['num'])) $num = $_GET['num'];
define('MULTIPLYBY', 100);
$start = $num * MULTIPLYBY;

require_once('db.php');
//require_once('classes/mission_marks_updater.php');
require_once('classes/medal_updater.php');
require_once('classes/rank_updater.php');

//$mmupdater = new mission_marks_updater();
$mupdater = new medal_updater();
$rupdater = new rank_updater();

$users = array();
$sql = "select user_id from users where user_registered > 0 limit $start, " . MULTIPLYBY;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

//$user = 17828;
//$users = array(19970, 19990, 23251);
foreach ($users as $user) {
    //$mmupdater->mission_marks_update($user);
    $mupdater->update_medal_two($user);
    $rupdater->update_rank_two($user);
}

$sql = "select count(*) as total from users where user_registered > 0";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
//if (($start + MULTIPLYBY) < $row['total']) header("Location: updateUsers.php?num=" . ++$num);
if ($start > $row['total']) echo "no more users left<br />";
echo "done.";