<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$ct = new ChidonTests();
$types = $ct->getTypes();
$children = $_POST['children'];
$ids = array_map(function($child) { return $child['user_id']; }, $children);
$khk = KHK::getKHKEligibility($ids)[0];

echo json_encode([
    'success'   => true,
    'khk'       => $khk
]);