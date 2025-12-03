<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.pesukim.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$settings = json_decode(file_get_contents('php://input'), true);
foreach ($settings as $setting) {
    $sql = "UPDATE pesukim_settings 
            SET type = :type, label = :label, multiplier = :multiplier 
            WHERE pesukim_setting_id = :id";
    $stmt = $MASHPIA_DB->prepare($sql);
    $res = $stmt->execute([
        'type' => $setting['type'],
        'label' => $setting['label'],
        'multiplier' => $setting['multiplier'],
        'id' => $setting['id']
    ]);
}

echo json_encode(['success' => $res, 'error' => $stmt->errorInfo()]);