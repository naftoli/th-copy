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
$sql = "DELETE FROM chidon_confirmations WHERE school_id = :school_id and year = :year";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([':school_id' => $school_id, ':year' => $year]);
echo json_encode(['success' => $stmt->rowCount() > 0]);