<?php
// make sure we are already logged in
$admin_auth = ['school', 'user'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require 'functions.php';
$weekly = getRaffleInfo('weekly');
$monthly = getRaffleInfo('monthly');
$yearly = getRaffleInfo('yearly');

echo json_encode([
    'raffle5'   => [
        'daysLeft'  => $weekly['daysLeft'],
        'raffleName'=> $weekly['name']
    ],
    'raffle60'  => [
        'daysLeft'  => $monthly['daysLeft'],
        'raffleName'=> $monthly['name']
    ],
    'raffle180' => [
        'daysLeft'  => $yearly['daysLeft'],
        'raffleName'=> $yearly['name']
    ]
]);