<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

// Check if data is compressed
$input = file_get_contents('php://input');

if (isset($_SERVER['HTTP_CONTENT_ENCODING']) && $_SERVER['HTTP_CONTENT_ENCODING'] === 'gzip') {
    $input = gzdecode($input);
}

$data = json_decode($input, true);
$info = $data['info'];
$year = $data['year'] ?? GlobalSettings::getCurrentYear();
$table = 'th_chidon_shipping';

$select = "SELECT * FROM $table WHERE year = :year 
            AND user_id = :user 
            AND item_id = :item 
            AND item_num = :num";
$stmtSelect = $MASHPIA_DB->prepare($select);

// can only insert for not yet shipped
$insert = "INSERT INTO $table  
            SET 
                year = :year, 
                user_id = :user, 
                item_id = :item, 
                item_num = :num,
                status = :status, 
                date_shipped = :date, 
                shipment_number = :ship_num";
$stmtInsert = $MASHPIA_DB->prepare($insert);

$update = "UPDATE $table 
            SET 
                status = :status,
                description = :desc      
            WHERE 
                year = :year 
                    AND user_id = :user 
                    AND item_id = :item 
                    AND item_num = :num
                    AND shipment_number = :ship_num";
$stmtUpdate = $MASHPIA_DB->prepare($update);

$updateWithDate = "UPDATE $table 
                    SET 
                        status = 1, 
                        description = :desc,
                        date_shipped = NOW(), 
                        shipment_number = :ship_num
                    WHERE 
                        year = :year 
                            AND user_id = :user 
                            AND item_id = :item 
                            AND item_num = :num";
$stmtUpdateWithDate = $MASHPIA_DB->prepare($updateWithDate);

$updateRemoveDate = "UPDATE $table 
                    SET 
                        status = :status,
                        description = :desc,
                        date_shipped = NULL, 
                        shipment_number = 0 
                    WHERE 
                        year = :year 
                            AND user_id = :user 
                            AND item_id = :item 
                            AND item_num = :num 
                            AND shipment_number = :ship_num";
$stmtUpdateRemoveDate = $MASHPIA_DB->prepare($updateRemoveDate);

$MASHPIA_DB->beginTransaction();
$success = true;
foreach ($info as $row) {
    $res = $stmtSelect->execute([
        'year'      => $year,
        'user'      => $row['user'],
        'item'      => $row['item'],
        'num'       => $row['num'],
    ]);
    // $stmtSelect->debugDumpParams();
    if (! $res) {
        $success = false;
        break;
    } else {
        // find out if we need to insert or update
        $found = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $action = intval($row['action']);
        if ($found) {
            if ($action == 1) {
                $res = $stmtUpdateWithDate->execute([
                    'year'      => $year,
                    'user'      => $row['user'],
                    'item'      => $row['item'],
                    'num'       => $row['num'],
                    'desc'      => $row['desc'], 
                    'ship_num'  => $row['ship_num']
                ]);
                // if (! $res) {
                //     $stmtUpdateWithDate->debugDumpParams();
                // }
            } else if ($action == 0) {
                $res = $stmtUpdateRemoveDate->execute([
                    'year'      => $year,
                    'user'      => $row['user'],
                    'item'      => $row['item'],
                    'num'       => $row['num'],
                    'status'    => $action,
                    'desc'      => $row['desc'],
                    'ship_num'  => $row['ship_num']
                ]);
            } else {
                $res = $stmtUpdate->execute([
                    'year'      => $year,
                    'user'      => $row['user'],
                    'item'      => $row['item'],
                    'num'       => $row['num'],
                    'status'    => $action,
                    'desc'      => $row['desc'],
                    'ship_num'  => $row['ship_num']
                ]);
                // $stmtUpdate->debugDumpParams();
            }
        } else {
            $res = $stmtInsert->execute([
                'year'      => $year,
                'user'      => $row['user'],
                'item'      => $row['item'],
                'num'       => $row['num'],
                'status'    => $action,
                'date'      => $action == 1 ? date('Y-m-d H:i:s') : NULL, 
                'ship_num'  => $row['ship_num']
            ]);
        }
        if (! $res) {
            $success = false;
            break;
        }
    }
}
if ($success) $MASHPIA_DB->commit();
else $MASHPIA_DB->rollBack();

echo json_encode([
    'success'   => $success,
    'error'     => 'There was an error updating the status.',
    'info'      => $MASHPIA_DB->errorInfo()
]);