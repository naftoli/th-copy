<?php
require 'auth.php';
require 'functions.php';

$weekly = getRaffleInfo('weekly');
$monthly = getRaffleInfo('monthly');
$yearly = getRaffleInfo('yearly');

$user_id = mysql_real_escape_string($_GET['user_id']);
$raffle5 = 5 - checkTasks( $user_id, $weekly['start'], $weekly['end'], 'weekly' );
$raffle60 = 60 - checkTasks( $user_id, $monthly['start'], $monthly['end'], 'monthly' );
$raffle180 = 180 - checkTasks( $user_id, $yearly['start'], $yearly['end'], 'yearly' );

echo json_encode([
    'raffle5'   => [
        'daysLeft'  => ($raffle5 > 0 ? $raffle5 : 0),
        'raffleName'=> $weekly['name']
    ],
    'raffle60'  => [
        'daysLeft'  => ($raffle60 > 0 ? $raffle60 : 0),
        'raffleName'=> $monthly['name']
    ],
    'raffle180' => [
        'daysLeft'  => ($raffle180 > 0 ? $raffle180 : 0),
        'raffleName'=> $yearly['name']
    ]
]);