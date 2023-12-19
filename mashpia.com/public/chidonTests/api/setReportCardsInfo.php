<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$stmt_test_1 = $MASHPIA_DB->prepare("
    UPDATE schools 
    SET show_report_cards = :val 
    WHERE school_id = :id
");

$stmt_test_2 = $MASHPIA_DB->prepare("
    UPDATE schools 
    SET show_report_card_2 = :val 
    WHERE school_id = :id
");

$stmt_test_3 = $MASHPIA_DB->prepare("
    UPDATE schools 
    SET show_report_card_3 = :val 
    WHERE school_id = :id
");

$input = json_decode(file_get_contents("php://input"));
$school_id = $input->school_id;
$value = $input->value;
$test_num = $input->test_num;

$options = [
    1 => $stmt_test_1,
    2 => $stmt_test_2,
    3 => $stmt_test_3
];
$stmt = $options[$test_num];

$res = $stmt->execute([
    'val'   => $value ? 1 : 0,
    'id'    => $school_id
]);

echo json_encode([
    'success'   => $res
]);