<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

header('Content-Type: application/json');

$info = json_decode(file_get_contents("php://input"));
if (!$info || !isset($info->admin)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
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

$res = $stmt->execute([
    ':year' => $year,
    ':admin' => $info->admin
]);

$paid = [];
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $paid[$row['school_id']] = intval($row['total']) ? intval($row['total']) : 0;
}

echo json_encode($paid);