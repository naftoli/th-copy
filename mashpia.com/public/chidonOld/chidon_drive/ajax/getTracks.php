<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', 1);

require_once __DIR__ . '/../../../api/header/db.php';
require_once __DIR__ . '/../../../chidonTests/class.chidonTests.php';

$ct = new ChidonTests();
$types = $ct->getTypes();

$children = $_POST['children'];
$tracks = [];
foreach ($children as $child) $tracks[$child['user_id']] = $ct->getHighestTrackPassed($child)['highest_track'];

echo json_encode([
    'success'   => true,
    'tracks'    => $tracks
]);