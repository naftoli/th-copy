<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/chidon_drive/encrypt.php';

$year = GlobalSettings::getChidonYear();

header('Content-Type: application/json');

$info = json_decode(file_get_contents("php://input"));
if (!$info || !isset($info->admin)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Decrypt the admin (it should be encrypted)
$admin_id = encrypt_decrypt('decrypt', $info->admin);
if (!$admin_id || !is_numeric($admin_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid admin ID']);
    exit;
}

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        school_id, COUNT(*) AS total
    FROM
        registration_charges
    WHERE
        type LIKE '%RRS%' AND year = :year
            AND admin_id = :admin
    GROUP BY school_id
    ");

$stmt->execute([
    ':year' => $year,
    ':admin' => $admin_id
]);
$stmt->debugDumpParams();
exit;

$paid = [];
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $paid[$row['school_id']] = intval($row['total']) ? intval($row['total']) : 0;
}

echo json_encode($paid);