<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$input = json_decode(file_get_contents('php://input'), true);
$school_id = $input['school_id'];
$value = $input['value'];
$field = $input['field'];

$sql = "INSERT IGNORE INTO chidon_open_reg (school_id, year, $field) 
        VALUES (:school_id, :year, :value)
        ON DUPLICATE KEY UPDATE $field = :value";
$stmt = $MASHPIA_DB->prepare($sql);
$res = $stmt->execute([
    ':school_id' => $school_id,
    ':year' => $year,
    ':value' => $value,
]);
echo json_encode([
    'success' => $res,
    'error' => $res ? null : $stmt->errorInfo()[2]
]);