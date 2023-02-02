<?php
require_once __DIR__ . '/../../../api/header/db.php';
require_once __DIR__ . '/../../../class.globalSettings.php';
require __DIR__ . '/../encrypt.php';

$year = GlobalSettings::getChidonYear();
$admin = mysql_real_escape_string( $_POST['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin);

$amount = 0;
$paid = false;

$stmt = $MASHPIA_DB->prepare("
        SELECT * FROM chidon_parent_shipping WHERE year = :year AND parent_id = :id 
    ");
$res = $stmt->execute([
    'year'  => $year,
    'id'    => $admin_id
]);
if ($res && $row = $stmt->fetch()) {
    $amount = $row['cost'];
    if ($row['amount_paid']) $paid = true;
}

echo json_encode([
    'success'   => true,
    'amount'    => $amount,
    'paid'      => $paid
]);