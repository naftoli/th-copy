<?php
ini_set('display_errors', 1);
ini_set('error_reporting', 1);

require_once __DIR__ . '/../../../api/header/db.php';
require_once __DIR__ . '/../../../class.globalSettings.php';

require_once __DIR__ . '/../../../chidonTests/class.chidonTests.php';
$ct = new ChidonTests();
$types = $ct->getTypes();

$children = $_POST['children'];
$tracks = [];
foreach ($children as $child) {
    $highestTrack = $ct->getHighestTrackPassed($child, 3)['highest_track'];
    $rewardType = $child['reward_type'];
    if ($rewardType != 'highest track passed') {
        $indexes = array_keys($types);
        $key1 = array_search($highestTrack, $indexes);
        $key2 = array_search($rewardType, $indexes);
        if ($key1 && $key2) $highestTrack = $key1 >= $key2 ? $highestTrack : $rewardType;
        else if ($key2) $highestTrack = $rewardType;
    }
    $tracks[$child['user_id']] = strtolower($types[$highestTrack]);
}

echo json_encode([
    'success'   => true,
    'tracks'    => $tracks
]);