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
    $highestTrack = $ct->getHighestTrackPassed($child)['highest_track'];
    $rewardType = empty($child['reward_type']) ? 'highest track passed' : $child['reward_type'];
    if (!empty($highestTrack) && $rewardType && $rewardType != 'highest track passed') {
        $indexes = array_keys($types);
        $key = array_search($child['test_type'], $indexes);
        $key1 = array_search($highestTrack, $indexes);
        $key2 = array_search($rewardType, $indexes);
        // make sure child passed the track they are on
        if ($key1 >= $key && $key2 > $key1) $highestTrack = $rewardType;
    }
    $tracks[$child['user_id']] = $highestTrack ? ucwords($types[$highestTrack]) : '';
}

echo json_encode([
    'success'   => true,
    'tracks'    => $tracks
]);