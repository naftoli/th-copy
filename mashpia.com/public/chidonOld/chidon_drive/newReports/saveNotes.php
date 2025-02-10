<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$fields = $_POST['fields'];
foreach ($fields as $field) {
    $$field = $_POST[$field];
}

if (in_array('checked', $fields)) {
    $stmt = $MASHPIA_DB->prepare("
        UPDATE th_chidon 
        SET contacted_parent = :checked 
        WHERE th_chidon_id = :id
    ");
    $res = $stmt->execute([
        ':id'       => $id,
        ':checked'  => $checked
    ]);
} else if (in_array('notes', $fields)) {
    $stmt = $MASHPIA_DB->prepare("
        UPDATE th_chidon 
        SET parent_notes = :notes  
        WHERE th_chidon_id = :id
    ");
    $res = $stmt->execute([
        ':id'       => $id,
        ':notes'    => $notes
    ]);
}

echo json_encode([
    'success'   => $res,
    'message'   => $res ? 'Saved' : 'Error saving',
    'error'     => $stmt->errorInfo()
]);