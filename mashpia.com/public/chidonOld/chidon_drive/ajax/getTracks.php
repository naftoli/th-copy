<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$ct = new ChidonTests();
$types = $ct->getTypes();
$children = $_POST['children'];

$tracks = [];
foreach ($children as $child) {
    $ct->setStudents($child['school_id'], $child['class_id'], $child['user_id']);
    $ct->setScores();
    $ct->calculateMarks();
    $marks = $ct->getMarks();
    $track = $child['reward_type'] === 'highest track passed' || empty($child['reward_type']) ?
        $ct->getHighestTrack($marks[$child['th_chidon_id']], $child['user_id']) : $child['reward_type'];
    $tracks[$child['user_id']] = $track ? $types[$track] : '';
}

echo json_encode([
    'success'   => true,
    'tracks'    => $tracks
]);