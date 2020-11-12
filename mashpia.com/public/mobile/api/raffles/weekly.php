<?php
// make sure we are already logged in
$admin_auth = ['school', 'user'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/raffles/shared/classes/Raffle.php';
use \raffles\weekly\Raffle as Raffle;
require 'functions.php';

$action = mysql_real_escape_string($_GET['action']);
$user_id = mysql_real_escape_string($_GET['user_id']);

$weekly = getRaffleInfo('weekly');

switch ($action) {
    case 'prize-info':
        $prize = getPrizeInfo($weekly['raffle_id']);
        echo json_encode([
            'img'   => $prize['pic'],
            'thumb' => $prize['thumb'],
            'name'  => $prize['name']
        ]);
        break;
    case 'track-records':
        echo getRaffleHistory('weekly', $user_id);
        break;
    case 'completed':
        $raffle = Raffle::load($weekly['raffle_id']);
        $completed = $raffle->checkWeekly($user_id);
        echo json_encode([
            'days_completed' => $completed
        ]);
        break;
}