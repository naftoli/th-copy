<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['prize_id']) || !isset($input['user_id'])) {
    die('Invalid input');
}

$stmt = $MASHPIA_DB->prepare("DELETE FROM chidon_user_prizes WHERE prize_id = :prize_id AND user_id = :user_id AND year = :year");
$res = $stmt->execute([':prize_id' => $input['prize_id'], ':user_id' => $input['user_id'], ':year' => $year]);

echo json_encode([
    'success' => intval($res)
]);