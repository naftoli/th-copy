<?php
require 'auth.php';
require 'functions.php';

$weekly = getRaffleInfo('weekly');
$monthly = getRaffleInfo('monthly');
$yearly = getRaffleInfo('yearly');

$user_id = mysql_real_escape_string($_GET['user_id']);
$raffle5 = ($weekly['days_of_tasks'] ?? 5) - checkTasks( $user_id, $weekly['start'], $weekly['end'], 'weekly' );
$raffle60 = ($monthly['days_of_tasks'] ?? 60) - checkTasks( $user_id, $monthly['start'], $monthly['end'], 'monthly' );
$raffle180 = ($yearly['days_of_tasks'] ?? 180) - checkTasks( $user_id, $yearly['start'], $yearly['end'], 'yearly' );

echo json_encode([
    'raffle5'   => [
        'daysLeft'  => ($raffle5 > 0 ? $raffle5 : 0),
        'raffleName'=> $weekly['name'],
        'expired'   => new DateTime($weekly['run_date']) < new DateTime()
    ],
    'raffle60'  => [
        'daysLeft'  => ($raffle60 > 0 ? $raffle60 : 0),
        'raffleName'=> $monthly['name'],
        'expired'   => new DateTime($monthly['run_date']) < new DateTime()
    ],
    'raffle180' => [
        'daysLeft'  => ($raffle180 > 0 ? $raffle180 : 0),
        'raffleName'=> $yearly['name'],
        'expired'   => new DateTime($yearly['run_date']) < new DateTime()
    ]
]);