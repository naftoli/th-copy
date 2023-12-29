<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$input = json_decode(file_get_contents('php://input'), true);
$school_id = $input['school'];

$sql = "select * from classes where class_era = 0 and school_id = " . $school_id . " and 
        class_grade in ('4', '5', '6', '7', '8') order by class_grade, class_sub";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $grades[$row['class_id']] = $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : '');
}

echo json_encode([
    'success'   => true,
    'grades'    => $grades,
]);