<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$chidon_id = $_POST['id'];
$checked = $_POST['checked'];
$notes = $_POST['notes'];

$stmt = $MASHPIA_DB->prepare("
    UPDATE th_chidon 
    SET contacted_parent = :checked, parent_notes = :notes 
    WHERE th_chidon_id = :id
");

$res = $stmt->execute([
    ':id'       => $chidon_id,
    ':checked'  => $checked,
    ':notes'    => $checked ? $notes : ''
]);

return json_encode([
    'success'   => $res,
    'message'   => $res ? 'Saved' : 'Error saving',
    'error'     => $stmt->errorInfo()
]);