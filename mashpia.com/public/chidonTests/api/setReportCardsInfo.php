<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$stmt = $MASHPIA_DB->prepare("
    UPDATE schools 
    SET show_report_cards = :val 
    WHERE school_id = :id
");

$input = json_decode(file_get_contents("php://input"));
echo "<pre>"; print_r($input); echo "</pre>";
$school_id = $input['school_id'];
$value = $input['value'];
$res = $stmt->execute([
    'val'   => $value ? 1 : 0,
    'id'    => $school_id
]);

echo json_encode([
    'success'   => $res
]);