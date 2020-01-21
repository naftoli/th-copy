<?php
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$school = $_POST['school_id'];
$year = $_POST['year'];

$stmt = $MASHPIA_DB->prepare("
    SELECT count(*) as total FROM th_chidon 
    WHERE school_id = :school 
    AND year = :year
");

$res = $stmt->execute([
    ':school'   =>  $school, 
    ':year'     =>  $year
]);

if ( $res ) {
    $row = $stmt->fetch();
    echo json_encode([
        'success'   =>  true, 
        'num'       =>  $row['total']
    ]);
} else {
    echo json_encode([
        'success'   =>  false,
        'error'     =>  "There was an error retrieving the number of kids in school: " . $school
    ]);
}