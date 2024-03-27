<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$fields = ['chidon_id', 'host', 'host_phone', 'street_num', 'suffix', 'street', 'apt', 'cross1', 'cross2'];
$input = json_decode(file_get_contents('php://input'), true);
foreach ($fields as $field) {
    $$field = $input[$field];
}

$stmt = $MASHPIA_DB->prepare("
    UPDATE th_chidon
    SET
        host = :host, 
        host_number = :host_phone, 
        host_street = :street, 
        host_street_num = :street_num, 
        host_street_num_suffix = :suffix, 
        host_street_apt = :apt, 
        between_streets1 = :cross1, 
        between_streets2 = :cross2
    WHERE
        th_chidon_id = :chidon_id
");

$success = $stmt->execute([
    'host' => $host,
    'host_phone' => $host_phone,
    'street_num' => $street_num,
    'suffix' => $suffix,
    'street' => $street,
    'apt' => $apt,
    'cross1' => $cross1,
    'cross2' => $cross2,
    'chidon_id' => $chidon_id
]);

echo json_encode([
    'success' => $success,
    'error' => $stmt->errorInfo()
]);