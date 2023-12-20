<?php
$admin_auth = ['school'];
require 'header.php';

if ($admin_user['auth'] != 'super') exit;

require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

$id = $_POST['id'];
$amount = $_POST['amount'];
$reason = $_POST['reason'];
$type = $_POST['type'];

$stmt = $MASHPIA_DB->prepare("
    update registration_charges 
    set refunded = 1, refund_reason = :reason, 
    where registration_charge_id = :id and amount = :amount 
");
$res = $stmt->execute([
    ':id' => $id,
    ':amount' => $amount,
    ':reason' => $reason
]);

if ($res) {
    // check type
    switch ($type) {
        case 'chayolei':
        case 'THE':
            break;

    }
}

echo json_encode([
    'success' => $res,
    'error' => $stmt->errorInfo()
]);