<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$user_id = mysql_real_escape_string($_GET['id']);
$testNum = $_GET['test'];
$ct = new ChidonTests();

$dates = [
    [
        1 => '10/17/2023',
        2 => '11/28/2023'
    ],
    [
        1 => '11/29/2023',
        2 => '1/7/2024'
    ],
    [
        1 => '1/8/2024',
        2 => '2/14/2024'
    ]
];

$totals = [];
for ($i = 0; $i < $testNum; $i++) {
    $totals[$i] = $ct->getTotalMinutesLearned($user_id, $dates[$i]);
}

echo json_encode($totals);