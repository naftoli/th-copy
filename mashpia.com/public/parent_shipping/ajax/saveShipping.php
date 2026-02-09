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
$table = 'parent_shipping';

$select = "SELECT * FROM $table WHERE year = :year AND admin_id = :admin AND item_id = :item";
$stmtSelect = $MASHPIA_DB->prepare($select);

// can only insert for not yet shipped or shipped
$insert = "INSERT INTO $table  
            SET 
                year = :year, 
                admin_id = :admin, 
                item_id = :item, 
                qty = :qty, 
                status = :status, 
                description = :desc";
$stmtInsert = $MASHPIA_DB->prepare($insert);

$update = "UPDATE $table 
            SET 
                qty = :qty, 
                status = :status,
                description = :desc 
            WHERE 
                year = :year 
                AND admin_id = :admin 
                AND item_id = :item";
$stmtUpdate = $MASHPIA_DB->prepare($update);

$MASHPIA_DB->beginTransaction();
$success = true;
foreach ($info as $row) {
    $res = $stmtSelect->execute([
        'year'      => $year,
        'admin'     => $row['admin'],
        'item'      => $row['item']
    ]);
    if (! $res) {
        $success = false;
        break;
    } else {
        // find out if we need to insert or update
        $found = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        if ($found) {
            $res = $stmtUpdate->execute([
                'year'      => $year,
                'admin'     => $row['admin'],
                'item'      => $row['item'],
                'qty'       => $row['qty'] ?? 1,
                'status'    => intval($row['action']),
                'desc'      => $row['desc']
            ]);
        } else {
            $res = $stmtInsert->execute([
                'year'      => $year,
                'admin'     => $row['admin'],
                'item'      => $row['item'],
                'qty'       => $row['qty'] ?? 1,
                'status'    => intval($row['action']),
                'desc'      => $row['desc']
            ]);
        }
        if (! $res) {
            $success = false;
            break;
        }
    }
}
if ($success) $MASHPIA_DB->commit();
else {
    $MASHPIA_DB->rollBack();
    ob_start();
    if ($found) {
        echo $stmtUpdate->debugDumpParams();
    } else {
        echo $stmtInsert->debugDumpParams();
    }
    $error_info = ob_get_clean();
    ob_end_flush();
}

echo json_encode([
    'success'   => $success,
    'error'     => 'There was an error updating the status.',
    'error_info'=> $error_info
]);