<?php
$admin_auth = ['school'];
require 'header.php';

if ($admin_user['auth'] != 'super') exit;

require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

$id = $_POST['id'];
$amount = $_POST['amount'];

$stmt = $MASHPIA_DB->prepare("update registration_charges set refunded = 1 where registration_charge_id = :id and amount = :amount");
$res = $stmt->execute([':id' => $id, ':amount' => $amount]);

echo json_encode([
    'success' => $res,
    'error' => $stmt->errorInfo()
]);