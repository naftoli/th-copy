<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = $_POST['year'] ?? GlobalSettings::getChidonRegYear();

$info = $_POST['info'];

$table = 'th_chidon_shipping';

$select = "SELECT * FROM $table WHERE year = :year AND user_id = :user AND item_id = :item AND item_num = :num";
$stmtSelect = $MASHPIA_DB->prepare($select);

// can only insert for not yet shipped or shipped
$insert = "INSERT INTO $table  
            SET 
                year = :year, 
                user_id = :user, 
                item_id = :item, 
                item_num = :num,
                status = :status, 
                date_shipped = :date";
$stmtInsert = $MASHPIA_DB->prepare($insert);

$update = "UPDATE $table 
            SET 
                status = :status,
                description = :desc 
            WHERE 
                year = :year 
            AND user_id = :user 
            AND item_id = :item 
            AND item_num = :num";
$stmtUpdate = $MASHPIA_DB->prepare($update);

$updateWithDate = "UPDATE $table 
                    SET 
                        status = :status,
                        description = :desc,
                        date_shipped = NOW() 
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
                        date_shipped = NULL 
                    WHERE 
                        year = :year 
                    AND user_id = :user 
                    AND item_id = :item 
                    AND item_num = :num";
$stmtUpdateRemoveDate = $MASHPIA_DB->prepare($updateRemoveDate);

$MASHPIA_DB->beginTransaction();
$success = true;
foreach ($info as $row) {
    $res = $stmtSelect->execute([
        'year'      => $year,
        'user'      => $row['user'],
        'item'      => $row['item'],
        'num'       => $row['num']
    ]);
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
                    'status'    => $action,
                    'desc'      => $row['desc']
                ]);
            } else if ($action == 0) {
                $res = $stmtUpdateRemoveDate->execute([
                    'year'      => $year,
                    'user'      => $row['user'],
                    'item'      => $row['item'],
                    'num'       => $row['num'],
                    'status'    => $action,
                    'desc'      => $row['desc']
                ]);
            } else {
                $res = $stmtUpdate->execute([
                    'year'      => $year,
                    'user'      => $row['user'],
                    'item'      => $row['item'],
                    'num'       => $row['num'],
                    'status'    => $action,
                    'desc'      => $row['desc']
                ]);
            }
        } else {
            $res = $stmtInsert->execute([
                'year'      => $year,
                'user'      => $row['user'],
                'item'      => $row['item'],
                'num'       => $row['num'],
                'status'    => $action,
                'date'      => $action == 1 ? date('Y-m-d H:i:s') : NULL
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
    'error'     => 'There was an error updating the status.'
]);