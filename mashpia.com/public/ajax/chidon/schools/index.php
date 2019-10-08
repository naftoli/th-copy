<?php
if ( !isset( $_GET['key'] ) || $_GET['key'] != 'Chidon@5780!' ) {
    echo json_encode([
        'succes'    =>  false, 
        'error'     =>  "Access Forbidden."
    ]);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$stmt = $MASHPIA_DB->query("
    SELECT school_id, school_name 
    FROM schools 
    WHERE chidon = 1 
    AND test_school = 0
");
$rows = $stmt->fetchAll();
echo json_encode([
    'success'   =>  true, 
    'data'      =>  $rows
]);