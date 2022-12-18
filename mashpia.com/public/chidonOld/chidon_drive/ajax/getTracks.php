<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', 1);

require_once __DIR__ . '/../../../api/header/db.php';
require_once __DIR__ . '/../../../chidonTests/class.chidonTests.php';

$ct = new ChidonTests();
$types = $ct->getTypes();

$children = $_POST['children'];
$tracks = [];
foreach ($children as $child) {
    $highestTrack = $ct->getHighestTrackPassed($child, 2)['highest_track'];
    $rewardType = !empty($child['reward_type']) ? $child['reward_type'] : $child['test_type'];
    if ($rewardType != 'highest track passed') {
        if ($highestTrack == '') $highestTrack = $rewardType;
        else {
            $indexes = array_keys($types);
            $key1 = array_search($highestTrack, $indexes);
            $key2 = array_search($rewardType, $indexes);
            if ($key2 > $key1) $highestTrack = $rewardType;
        }
    }
    $tracks[$child['user_id']] = ucwords($types[$highestTrack]);
}

echo json_encode([
    'success'   => true,
    'tracks'    => $tracks
]);