<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if (!$admin_user['admin_id']) {
    die('You are not authorized to view this page.');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$sql = "SELECT aa.admin_id, sum(amount) as total FROM mashpia_backup.registration_charges rc 
        join admin_auths aa on aa.id = rc.user_id 
        where year = :year and (type like 'RR%' or type like 'PRR%') 
        group by aa.admin_id";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([':year' => $year]);
$res = $stmt->fetchAll();
foreach ($res as $row) {
    $info[$row['admin_id']] = $row['total'];
}

$MASHPIA_DB->startTransaction();
$success = true;

$updated = 0;
$stmt = $MASHPIA_DB->prepar("
    UPDATE family_prepaid_balances 
    SET real_prepaid = :amount
    WHERE admin_id = :id
");
foreach ($info as $id => $amount) {
    if ($stmt->execute([
        ':amount' => $amount,
        ':id' => $id
    ])) $updated++;
    else {
        $success = false;
        $stmt->errorInfo();
        $stmt->debugDumpParams();
        break;
    }
}
if ($success) {
    $MASHPIA_DB->commit();
} else {
    $MASHPIA_DB->rollBack();
}
echo "Updated " . count($info) . " family balances.";