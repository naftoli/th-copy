<?php
require_once '../headers.php';

if ( !isset( $_GET['key'] ) || $_GET['key'] != 'Chidon@5780!' ) {
    echo json_encode([
        'succes'    =>  false, 
        'error'     =>  "Access Forbidden."
    ]);
    exit;
}

$school_id = $_GET['school_id'];
if ( !isset( $school_id ) || !$school_id > 0 ) {
    echo json_encode([
        'success'   =>  false, 
        'error'     =>  'You must provide a school id.'
    ]);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$stmt = $MASHPIA_DB->prepare("
    SELECT class_id, class_grade, class_sub 
    FROM classes 
    WHERE school_id = :school 
    AND class_era = 0 
    AND class_grade >= 4 
");
$stmt->execute([':school' => $school_id]);
$rows = $stmt->fetchAll();

if ( $rows ) {
    echo json_encode([
        'success'   =>  true, 
        'data'      =>  $rows
    ]);
} else {
    echo json_encode([
        'success'   =>  false, 
        'error'     =>  'Error getting grades.'
    ]);
}