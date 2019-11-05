<?php
if ( !isset( $_GET['key'] ) || $_GET['key'] != 'Chidon@5780!' ) {
    echo json_encode([
        'succes'    =>  false, 
        'error'     =>  "Access Forbidden."
    ]);
    exit;
}

$class_id = $_GET['class_id'];
if ( !isset( $class_id ) || !$class_id > 0 ) {
    echo json_encode([
        'success'   =>  false, 
        'error'     =>  'You must provide a class id.'
    ]);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$stmt = $MASHPIA_DB->prepare("
    SELECT user_id, first, last  
    FROM users 
    WHERE class_id = :grade 
    AND user_registered > 0 
");
$stmt->execute([':grade' => $class_id]);
$rows = $stmt->fetchAll();

if ( $rows ) {
    echo json_encode([
        'success'   =>  true, 
        'data'      =>  $rows
    ]);
} else {
    echo json_encode([
        'success'   =>  false, 
        'error'     =>  'No children found.'
    ]);
}