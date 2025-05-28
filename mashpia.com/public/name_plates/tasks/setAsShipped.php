<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../header.php';
require_once '../../api/header/db.php';
require_once '../../class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

// make sure we are super user
if ($admin_user['auth'] != 'super') {
    echo "You are not authorized to access this page.";
    exit;
}

// get all name plates that were shipped
$info = [];
$sql = "SELECT * FROM name_plates WHERE shipped = 1";
$stmt = $MASHPIA_DB->query($sql);
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    $info[] = $row;
}

// for each name plate add to th_chidon_shipping table
$stmt = $MASHPIA_DB->prepare("
    INSERT INTO th_chidon_shipping 
    SET 
        year = :year,
        user_id = :user_id,
        item_id = :item_id,
        item_num = :item_num,
        status = 1
");

$MASHPIA_DB->beginTransaction();
$success = true;
foreach ($info as $row) {
    $qty = intval($row['qty']);
    for ($i = 0; $i < $qty; $i++) {
        $res = $stmt->execute([
            ':year' => $year,
            ':user_id' => $row['user_id'],
            ':item_id' => 'NP101',
            ':item_num' => $i
        ]);
        if (!$res) {
            echo "Error: " . $stmt->errorInfo()[2] . "\n";
            $success = false;
            break;
        }
    }
}
if ($success) {
    $MASHPIA_DB->commit();
    echo "Success\n";
} else {
    $MASHPIA_DB->rollBack();
    echo "Failed\n";
}