<?php
ini_set("display_errors", 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

$gridId = $_POST['gridId'];
$userId = $_POST['userId'];
$numDays = 90;

$sql = "INSERT INTO hachloto_tasks (grid_id, user_id, year, num_days) 
        VALUES (:gridId, :userId, :year, :numDays)";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([
    'gridId' => $gridId,
    'userId' => $userId,
    'year' => $year,
    'numDays' => $numDays
]);
if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->errorInfo()]);
}