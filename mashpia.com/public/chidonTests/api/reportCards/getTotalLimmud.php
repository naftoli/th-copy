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
        1 => '9/8/2022',
        2 => '11/9/2022'
    ],
    [
        1 => '11/10/2022',
        2 => '12/11/2022'
    ],
    [
        1 => '12/12/2022',
        2 => '1/29/2023'
    ]
];

$totals = [];
for ($i = 0; $i < $testNum; $i++) {
    $totals[$i] = $ct->getTotalMinutesLearned($user_id, $dates[$i]);
}

echo json_encode($totals);