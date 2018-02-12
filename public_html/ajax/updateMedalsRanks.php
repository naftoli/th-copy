<?php
require '../db.php';
require '../classes/medal_updater.php';
require '../classes/rank_updater.php';

$medal_updater = new medal_updater();
$rank_updater = new rank_updater();

$user_id = (int) mysql_real_escape_string($_POST['user']);

//if($user_id == 51030){
//    error_reporting(E_ALL);
//    ini_set('display_errors', 1);
//}

if ($user_id > 0) {
    $medal = $medal_updater->update_medal_two($user_id);
    $rank = $rank_updater->update_rank_two($user_id);
}

http_response_code(200); // set the response code to 200 in case the rank_updater touches wordpress...
echo json_encode(['medal' => $medal ? $medal : false, 'rank' => $rank ? $rank : false]);