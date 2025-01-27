<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', 1);

require_once '../../../api/header/db.php';
require_once '../../../chidonTests/class.chidonTests.php';

$ct = new ChidonTests();
$types = $ct->getTypes();
$children = $_POST['children'];

$tracks = [];
foreach ($children as $child) {
    // only need to calculate track if there's no reward type set for this child
    if (empty($child['reward_type']) || $child['reward_type'] === 'highest track passed') {
        $ct->setStudents($child['school_id'], $child['class_id'], $child['user_id']);
        $ct->setScores();
        $ct->calculateMarks();
        $marks = $ct->getMarks();
        if (isset($marks[$child['th_chidon_id']])) {
            $track = $ct->getHighestTrack($marks[$child['th_chidon_id']], $child['user_id']);
        }
    } else {
        $track = $child['reward_type'];
    }
    $tracks[$child['user_id']] = $track ? $types[$track] : '';
}

echo json_encode([
    'success'   => true,
    'tracks'    => $tracks
]);