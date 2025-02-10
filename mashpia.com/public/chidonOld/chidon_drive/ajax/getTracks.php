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
    $track = '';
    $cumulative = '';
    // only need to calculate track if there's no reward type set for this child
    if (empty($child['reward_type']) || $child['reward_type'] === 'highest track passed') {
        $ct->setStudents($child['school_id'], $child['class_id'], $child['user_id']);
        $ct->setScores();
        $ct->calculateMarks();
        $scores = $ct->getScores();
        if (isset($scores[$child['th_chidon_id']])) {
            $cumulative = $ct->calculateCumulative($child, $scores[$child['th_chidon_id']]);
        };
        if ($cumulative == 'iyun') $track = 'Iyun';
        else {
            $marks = $ct->getMarks();
            if (isset($marks[$child['th_chidon_id']])) {
                $track = $ct->getHighestTrack($marks[$child['th_chidon_id']], $child['user_id']);
            }
        }
    } else {
        $track = $child['reward_type'];
    }
    if (array_key_exists($track, $types)) {
        $track = $types[$track];
    }
    $tracks[$child['user_id']] = $track;
}

echo json_encode([
    'success'   => true,
    'tracks'    => $tracks
]);