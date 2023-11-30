<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$user_id = mysql_real_escape_string($_GET['id']);
$testNum = $_GET['test'];
$ct = new ChidonTests();
$dates = $ct->getDates();

$totals = [];
for ($i = 0; $i < $testNum; $i++) {
    $totals[$i] = $ct->getTotalMinutesLearned($user_id, $dates[$i]);
}

echo json_encode($totals);