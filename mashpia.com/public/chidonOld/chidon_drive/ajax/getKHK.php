<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$khk = [];
$ct = new ChidonTests();
$types = $ct->getTypes();
$children = $_POST['children'];
// get ids from children if they are in 8th grade
$ids = [];
foreach ($children as $child) {
    if ($child['class_grade'] == '8') {
        $ids[] = $child['user_id'];
    }
}
$khk = KHK::getKHKEligibility($ids)[0];

echo json_encode([
    'success'   => true,
    'khk'       => $khk
]);