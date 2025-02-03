<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

header('Content-Type: application/json');

$info = json_decode(file_get_contents("php://input"));
if (!$info || !isset($info->admin) || !isset($info->school)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM registration_charges 
    WHERE type like '%RRS%' 
    AND year = :year 
    AND school_id = :school
    AND user_id in (
        SELECT id FROM admin_auths WHERE admin_id = :admin
    ) 
");

$res = $stmt->execute([
    ':year' => $year,
    ':admin' => $info->admin,
    ':school' => $info->school
]);

$paid = [];
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $paid[$row['school_id']][] = $row;
}

echo json_encode($paid);