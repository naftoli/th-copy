<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once '../api/header/db.php';

$MASHPIA_DB->beginTransaction();

$info = [];
$sql = "select * from medals_subjects";
$stmt = $MASHPIA_DB->query($sql);
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    $info[$row['shipping_code']] = [
        'subject_id' => $row['subject_id'],
        'medal_ord' => $row['medal_ord'],
    ];
}

// update all medals that were shipped in the medal_marks table to have the date_received date set to the date_shipped date
$stmtUpdate = $MASHPIA_DB->prepare("
    UPDATE medal_marks 
    SET date_received = :date , 
        date_shipped = :date 
    WHERE subject_id = :subject_id 
    AND medal_ord = :medal_ord 
    AND user_id = :user_id
");

$sql = "select * from th_chidon_shipping where item_id like '%MS%'";
$stmtShipped = $MASHPIA_DB->query($sql);
$rowsShipped = $stmtShipped->fetchAll();

$success = true;
foreach ($rowsShipped as $rowShipped) {
    $shipping_code = $rowShipped['item_id'];
    $date = $rowShipped['created'];
    $user_id = $rowShipped['user_id'];
    $subject_id = $info[$shipping_code]['subject_id'];
    $medal_ord = $info[$shipping_code]['medal_ord'];
    $res = $stmtUpdate->execute([
        'date' => $date,
        'subject_id' => $subject_id,
        'medal_ord' => $medal_ord,
        'user_id' => $user_id,
    ]);
    // $stmtUpdate->debugDumpParams();
    if (!$res) {
        $success = false;
        echo "Failed to update medal for user " . $user_id . " and subject " . $subject_id . " and medal " . $medal_ord . "\n";
        $stmtUpdate->errorInfo();
        $stmtUpdate->debugDumpParams();
        break;
    }
}

// $success = false;
if ($success) {
    $MASHPIA_DB->commit();
    echo "Successfully updated all medals that were shipped in the medal_marks table to have the date_received date set to the date_shipped date";
} else {
    $MASHPIA_DB->rollBack();
    echo "Failed to update all medals that were shipped in the medal_marks table to have the date_received date set to the date_shipped date";
}