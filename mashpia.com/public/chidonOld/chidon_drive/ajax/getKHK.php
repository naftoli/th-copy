<?php
require_once '../../../api/header/db.php';
require_once '../../../chidonTests/class.chidonTests.php';

$khk = [];
$ct = new ChidonTests();
$types = $ct->getTypes();
$children = $_POST['children'];
//echo "<pre>"; print_r($children); echo "</pre>";
// get ids from children if they are in 8th grade
$ids = [];
foreach ($children as $child) {
    if ($child['class_grade'] == '8') {
        $ids[] = $child['user_id'];
    }
}
//echo "<pre>"; print_r($ids); echo "</pre>";
if (count($ids)) $khk = KHK::getKHKEligibility($ids)[0];

echo json_encode([
    'success'   => true,
    'khk'       => $khk
]);