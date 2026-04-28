<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

header('Content-Type: application/json');

$year = (int) GlobalSettings::getChidonYear();
if ($admin_user['auth'] !== 'super') {
    echo json_encode(['success' => 0, 'error' => 'Access denied']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    echo json_encode(['success' => 0, 'error' => 'Invalid request body']);
    exit;
}

$user_id = isset($input['user_id']) ? (int) $input['user_id'] : 0;
$prize_id = isset($input['prize_id']) ? (int) $input['prize_id'] : 0;
if (isset($input['year'])) {
    $year = (int) $input['year'];
}

if ($user_id <= 0 || $prize_id <= 0 || $year <= 0) {
    echo json_encode(['success' => 0, 'error' => 'Missing required fields']);
    exit;
}

$adminSchools = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $adminSchools->getSchools();

$stmtUser = $MASHPIA_DB->prepare("
    SELECT u.user_id, u.school_id
    FROM users u
    JOIN th_chidon tc ON tc.user_id = u.user_id AND tc.year = :year
    WHERE u.user_id = :user_id
    LIMIT 1
");
$stmtUser->execute([
    ':year' => $year,
    ':user_id' => $user_id,
]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo json_encode(['success' => 0, 'error' => 'Child not found for selected year']);
    exit;
}
if (!isset($schools[(int) $user['school_id']])) {
    echo json_encode(['success' => 0, 'error' => 'Child is not in your schools']);
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
