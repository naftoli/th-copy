<?php
require 'auth.php';
require 'functions.php';

$action = mysql_real_escape_string($_GET['action']);
$user_id = mysql_real_escape_string($_GET['user_id']);

require $_SERVER['DOCUMENT_ROOT'] . '/raffles/shared/classes/Raffle.php';
use \raffles\weekly\Raffle as Raffle;
$weekly = getRaffleInfo('weekly');

$result = [];
switch ($action) {
    case 'prize-info':
        $prize = getPrizeInfo($weekly['raffle_id']);
        $result = json_encode([
            'img'   => $prize['pic'],
            'thumb' => $prize['thumb'],
            'name'  => $prize['name']
        ]);
        break;
    case 'track-records':
        $result = getRaffleHistory('weekly', $user_id);
        break;
    case 'completed':
        $raffle = Raffle::load($weekly['raffle_id']);
        $completed = $raffle->checkWeekly($user_id);
        $result = json_encode([
            'days_completed' => $completed
        ]);
        break;
}
echo $result;