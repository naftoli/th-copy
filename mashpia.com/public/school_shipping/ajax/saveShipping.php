<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = $_POST['year'] ?? GlobalSettings::getRegistrationYear();

$info = $_POST['info'];

$table = 'school_shipping';

$select = "SELECT * FROM $table WHERE year = :year AND school_id = :school AND item_id = :item";
$stmtSelect = $MASHPIA_DB->prepare($select);

// can only insert for not yet shipped or shipped
$insert = "INSERT INTO $table  
            SET 
                year = :year, 
                school_id = :school, 
                item_id = :item, 
                qty = :qty, 
                status = :status";
$stmtInsert = $MASHPIA_DB->prepare($insert);

$update = "UPDATE $table 
            SET 
                qty = :qty, 
                status = :status,
                desc = :desc 
            WHERE 
                year = :year 
                AND school_id = :school 
                AND item_id = :item";
$stmtUpdate = $MASHPIA_DB->prepare($update);

$MASHPIA_DB->beginTransaction();
$success = true;
foreach ($info as $row) {
    $res = $stmtSelect->execute([
        'year'      => $year,
        'school'    => $row['school'],
        'item'      => $row['item']
    ]);
    if (! $res) {
        $stmtSelect->debugDumpParams();
        $success = false;
        break;
    } else {
        // find out if we need to insert or update
        $found = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        if ($found) {
            $res = $stmtUpdate->execute([
                'qty'       => $row['qty'],
                'status'    => intval($row['action']),
                'desc'      => $row['desc'],
                'year'      => $year,
                'school'    => $row['school'],
                'item'      => $row['item']
            ]);
        } else {
            $res = $stmtInsert->execute([
                'year'      => $year,
                'school'    => $row['school'],
                'item'      => $row['item'],
                'qty'       => $row['qty'],
                'status'    => intval($row['action'])
            ]);
        }
        if (! $res) {
            $stmtInsert->debugDumpParams();
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
    'error_info'=> "Select Error: " . print_r($stmtSelect->debugDumpParams()) . " Insert Error: " .
        print_r($stmtInsert->debugDumpParams()) . " Update Error: " . print_r($stmtUpdate->debugDumpParams())
]);