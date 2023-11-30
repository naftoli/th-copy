<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$user_id = mysql_real_escape_string($_GET['id']);
$ct = new ChidonTests();
$testNum = isset($_GET['test']) ? $_GET['test'] : $ct->figureOutTestNum();
$dates = $ct->getDates();

$totals = [];
for ($i = 0; $i < $testNum; $i++) {
    $totals[$i] = $ct->getTotalMinutesLearned($user_id, $dates[$i]);
}

echo json_encode($totals);