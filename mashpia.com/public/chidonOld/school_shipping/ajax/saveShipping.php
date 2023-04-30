<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = $_POST['info'];

$sql = "INSERT IGNORE INTO school_shipping 
        SET 
            year = :year, 
            school_id = :school, 
            item_id = :item, 
            shipped = :shipped, 
            missing = :missing, 
            qty = :qty, 
            damaged = :damaged, 
            desc_of_damage = :desc
        ON DUPLICATE KEY UPDATE 
            shipped = :shipped, 
            missing = :missing, 
            qty = :qty, 
            damaged = :damaged, 
            desc_of_damage = :desc";
$stmt = $MASHPIA_DB->prepare($sql);

$MASHPIA_DB->beginTransaction();
$success = true;
foreach ($info as $row) {
    // figure out shipped / missing / damaged
    switch (intval($row['action'])) {
        case 0:
            $shipped = 0;
            $missing = 0;
            $damaged = 0;
            break;
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
        'year'      => $year,
        'school'    => $row['school'],
        'item'      => $row['item'],
        'shipped'   => $shipped,
        'missing'   => $missing,
        'damaged'   => $damaged,
        'qty'       => $row['qty'],
        'desc'      => $row['desc']
    ]);
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