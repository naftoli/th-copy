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
            missing = :missing, 
            damaged = :damaged
        ON DUPLICATE KEY UPDATE 
            shipped = :shipped, 
            missing = :missing, 
            damaged = :damaged";
$stmt = $MASHPIA_DB->prepare($sql);

$delete = "DELETE FROM th_chidon_shipping WHERE user_id = :id AND year = :year";
$stmtDel = $MASHPIA_DB->prepare($delete);

$MASHPIA_DB->beginTransaction();
$success = true;
foreach ($info as $row) {
    // figure out shipped / missing / damaged
    $action = intval($row['action']);
    if ($action == 0) {
        $res = $stmtDel->execute([
            'year'  => $year,
            'id'    => $row['user']
        ]);
    } else {
        switch ($action) {
            case 1:
                $shipped = 1;
                $missing = 0;
                $damaged = 0;
                break;
            case 2:
                $shipped = 1;
                $missing = 1;
                $damaged = 0;
                break;
            case 3:
                $shipped = 1;
                $missing = 0;
                $damaged = 1;
                break;
        }
        $res = $stmt->execute([
            'year' => $year,
            'user' => $row['user'],
            'item' => $row['item'],
            'shipped' => $shipped,
            'missing' => $missing,
            'damaged' => $damaged
        ]);
    }
    if (! $res) {
//        $stmt->debugDumpParams();
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