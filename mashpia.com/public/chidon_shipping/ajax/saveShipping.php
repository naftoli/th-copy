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
            damaged = :damaged, 
            received = :received, 
            description = :desc, 
            item_num = :num
        ON DUPLICATE KEY UPDATE 
            shipped = :shipped, 
            missing = :missing, 
            damaged = :damaged, 
            received = :received,
            description = :desc, 
            item_num = :num";
$stmt = $MASHPIA_DB->prepare($sql);

// for setting as shipped, we update shipped status only, when already in database
$sqlShipped = "INSERT IGNORE INTO th_chidon_shipping
        SET
            year = :year,
            user_id = :user,
            item_id = :item,
            shipped = :shipped,
            missing = :missing, 
            damaged = :damaged,
            item_num = :num
        ON DUPLICATE KEY UPDATE
            shipped = :shipped,
            missing = :missing,
            damaged = :damaged,
            item_num = :num";
$stmtShipped = $MASHPIA_DB->prepare($sqlShipped);

// when marking as received, we don't update the status if it wasn't yet shipped
$sqlReceived = "UPDATE th_chidon_shipping 
            SET 
                missing = :missing,
                damaged = :damaged,
                received = :received, 
                item_num = :num 
            WHERE
                year = :year
                AND user_id = :user
                AND item_id = :item
                AND shipped = 1";
$stmtReceived = $MASHPIA_DB->prepare($sqlReceived);

$MASHPIA_DB->beginTransaction();
$success = true;
foreach ($info as $row) {
    // figure out shipped / missing / damaged
    switch (intval($row['action'])) {
        case 0:
            $shipped = 0;
            $missing = 0;
            $damaged = 0;
            $received = 0;
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
            $received = 0;
            break;
        case 3:
            $shipped = 1;
            $missing = 0;
            $damaged = 1;
            $received = 0;
            break;
        case 4:
            $shipped = 1;
            $missing = 0;
            $damaged = 0;
            $received = 1;
            break;
    }
    if (intval($row['action']) == 1) {
        $res = $stmtShipped->execute([
            'year' => $row['year'] ?? $year,
            'user' => $row['user'],
            'item' => $row['item'],
            'shipped' => $shipped,
            'missing' => $missing,
            'damaged' => $damaged,
            'num' => $row['num']
        ]);
        if (! $res) {
            $stmtShipped->debugDumpParams();
            $success = false;
            break;
        }
    } else if (intval($row['action']) == 4) {
        $res = $stmtReceived->execute([
            'year' => $row['year'] ?? $year,
            'user' => $row['user'],
            'item' => $row['item'],
            'missing' => $missing,
            'damaged' => $damaged,
            'received' => $received,
            'num' => $row['num']
        ]);
        if (! $res) {
            $stmtReceived->debugDumpParams();
            $success = false;
            break;
        }
    } else {
        $res = $stmt->execute([
            'year'      => $row['year'] ?? $year,
            'user'      => $row['user'],
            'item'      => $row['item'],
            'shipped'   => $shipped,
            'missing'   => $missing,
            'damaged'   => $damaged,
            'received'  => $received,
            'desc'      => $row['desc'],
            'num'       => $row['num']
        ]);
        if (! $res) {
            $stmt->debugDumpParams();
            $success = false;
            break;
        }
    }
}
if ($success) $MASHPIA_DB->commit();
else $MASHPIA_DB->rollBack();

echo json_encode([
    'success'   => $success,
    'error'     => 'There was an error updating the status.'
]);