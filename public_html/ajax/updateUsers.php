<?php
ini_set('display_errors',1);
$iterationNumber = $_POST['num'];
$limit = 300;
$start = $iterationNumber * $limit;

chdir('../');
require_once('db.php');
//require_once('classes/mission_marks_updater.php');
require_once('classes/medal_updater.php');
require_once('classes/rank_updater.php');

//$mmupdater = new mission_marks_updater();
$mupdater = new medal_updater();
$rupdater = new rank_updater();

$schools = array(2,4,7,9,19,30,42,54,58,61,255,269);

$users = array();
$sql = "select user_id from users where user_registered > 0 and school_id not in (" . implode(',', $schools) . ") limit $start, $limit";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

//$user = 17828;
foreach ($users as $user) {
    //$mmupdater->mission_marks_update($user);
    $mupdater->update_medal_two($user);
    $rupdater->update_rank_two($user);
}
echo "Done.";