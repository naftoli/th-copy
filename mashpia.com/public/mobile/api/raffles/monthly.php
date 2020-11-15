<?php
require 'auth.php';
require 'functions.php';

$action = mysql_real_escape_string($_GET['action']);
$user_id = mysql_real_escape_string($_GET['user_id']);

$result = [];
switch ($action) {
    case 'raffle-data':
        $result = json_encode([
            getDailyTaskInfo($user_id, 'monthly')
        ]);
        break;
    case 'winner-data':
        $raffle = getRaffleInfo('monthly');
        $raffle_id = $raffle['raffle_id'];
        $year = $raffle['year'];
        $winners = getWinnersInfo('monthly', $year);
        break;
}
echo $result;
