<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = $_POST['info'];

$sql = "INSERT IGNORE INTO th_chidon_shipping
        SET 
            year = :year, 
            user_id = :user, 
            item_id = :item, 
            shipped = :shipped, 
            missing = :missing 
        ON DUPLICATE KEY UPDATE 
            shipped = :shipped, 
            missing = :missing";
$stmt = $MASHPIA_DB->prepare($sql);

$MASHPIA_DB->beginTransaction();
$success = true;
foreach ($info as $row) {
    $res = $stmt->execute([
        'year'      => $year,
        'user'      => $row['user'],
        'item'      => $row['item'],
        'shipped'   => $row['action'] == 0 ? 0 : 1,
        'missing'   => $row['action'] == 2 ? 1 : 0
    ]);
    if (! $res) {
        $stmt->debugDumpParams();
        $success = false;
        break;
    }
}
if ($success) $MASHPIA_DB->commit();
else $MASHPIA_DB->rollBack();

echo json_encode([
    'success'   => $success,
    'error'     => 'There was an error updating the status.'
]);