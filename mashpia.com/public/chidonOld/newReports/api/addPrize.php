<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

header('Content-Type: application/json');

if ($admin_user['auth'] !== 'super') {
    echo json_encode(['success' => 0, 'error' => 'Access denied']);
    exit;
}

$user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
$prize_id = isset($_POST['prize_id']) ? (int) $_POST['prize_id'] : 0;
$year = isset($_POST['year']) ? (int) $_POST['year'] : 0;


if ($user_id <= 0 || $prize_id <= 0 || $year <= 0) {
    echo json_encode(['success' => 0, 'error' => 'Missing required fields']);
    exit;
}

$stmtPrize = $MASHPIA_DB->prepare("SELECT prize_id FROM chidon_prizes WHERE prize_id = :prize_id LIMIT 1");
$stmtPrize->execute([':prize_id' => $prize_id]);
if (!$stmtPrize->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode(['success' => 0, 'error' => 'Invalid prize id']);
    exit;
}

$stmtExists = $MASHPIA_DB->prepare("
    SELECT 1
    FROM chidon_user_prizes
    WHERE user_id = :user_id AND prize_id = :prize_id AND year = :year
    LIMIT 1
");
$stmtExists->execute([
    ':user_id' => $user_id,
    ':prize_id' => $prize_id,
    ':year' => $year,
]);
if ($stmtExists->fetchColumn()) {
    echo json_encode(['success' => 0, 'error' => 'Prize already assigned to this child']);
    exit;
}

$stmtInsert = $MASHPIA_DB->prepare("
    INSERT INTO chidon_user_prizes (user_id, prize_id, year)
    VALUES (:user_id, :prize_id, :year)
");
$res = $stmtInsert->execute([
    ':user_id' => $user_id,
    ':prize_id' => $prize_id,
    ':year' => $year,
]);

echo json_encode([
    'success' => (int) $res,
    'error' => $res ? null : ($stmtInsert->errorInfo()[2] ?? 'Failed to add prize'),
]);
