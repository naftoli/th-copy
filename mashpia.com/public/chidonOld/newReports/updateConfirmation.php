<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = (int) GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$stmt = $MASHPIA_DB->prepare("
    UPDATE th_chidon SET confirmed_info = :confirmed WHERE user_id = :user_id AND year = :year
");

// get POST info from fetch
$info = json_decode(file_get_contents('php://input'), true);
$user_id = $info['user_id'];
$confirmed = $info['confirmed'];
if (isset($info['year'])) {
    $year = (int) $info['year'];
}

$res = $stmt->execute([':confirmed' => $confirmed, ':user_id' => $user_id, ':year' => $year]);
echo json_encode(['success' => intval($res)]);