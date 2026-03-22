<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

if ($admin_user['auth'] != 'super') {
    echo json_encode([
        'success'   => false,
        'error'     => 'You are not authorized to view this page.'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$info = $data['info'];
$total = $data['total'];
$shipment_number = $data['shipment_number'];

if ($total != count($info)) {
    echo json_encode(['success' => false, 'error' => 'Error: corrupted file. Total hachayols count does not match.']);
    exit;
}

$stmt = $MASHPIA_DB->prepare("INSERT IGNORE INTO th_chidon_shipping 
                                (year, user_id, item_id, item_num, status, date_shipped, shipment_number) 
                                VALUES (:year, :user, :item, :num, :status, :date, :shipment_number)");

$MASHPIA_DB->beginTransaction();
$success = true;

foreach ($info as $user_id) {
    $res = $stmt->execute([
        'year' => $year,
        'user' => $user_id,
        'item' => 'HACH01',
        'num' => 0,
        'status' => 1,
        'date' => date('Y-m-d H:i:s'),
        'shipment_number' => $shipment_number
    ]);
    if (!$res) {
        $success = false;
        break;
    }
}

if ($success) {
    $MASHPIA_DB->commit();
    echo json_encode(['success' => true]);
} else {
    $MASHPIA_DB->rollBack();
    $stmt->debugDumpParams();
    $MASHPIA_DB->errorInfo();
    echo json_encode(['success' => false, 'error' => 'Failed to set hachayols as shipped.']);
}