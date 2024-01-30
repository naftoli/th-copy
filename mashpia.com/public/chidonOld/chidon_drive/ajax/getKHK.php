<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$ct = new ChidonTests();
$types = $ct->getTypes();
$children = $_POST['children'];

$khk = [];
foreach ($children as $child) {
    $ct->setStudents($child['school_id'], $child['class_id'], $child['user_id']);
    $ct->setScores();
    $ct->calculateMarks();
    $marks = $ct->getMarks();
    $khk = KHK::getKHKEligibility([$child['user_id']], 0, 4, $marks)[0];
    $khk[$child['user_id']] = isset($khk[$child['user_id']]) && $khk[$child['user_id']] ? 1 : 0;
}

echo json_encode([
    'success'   => true,
    'khk'       => $khk
]);